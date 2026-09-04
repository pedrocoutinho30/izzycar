import json
import random
import re
import time

import httpx

from . import config
from .models import Listing

NEXT_DATA_RE = re.compile(
    # Standvirtual's script tag has extra attributes (nonce, crossorigin) that
    # AutoScout24's doesn't - match any attributes, not the literal `type="..."`.
    r'<script id="__NEXT_DATA__"[^>]*>(.*?)</script>', re.S
)

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    ),
    "Accept-Language": "pt-PT,pt;q=0.9,en;q=0.8",
}


class BlockedError(Exception):
    """Raised when Standvirtual returns a page without the expected data payload."""


def _sleep_politely():
    time.sleep(random.uniform(config.MIN_DELAY_SECONDS, config.MAX_DELAY_SECONDS))


def fetch_advert_search(url, client):
    """Fetches a Standvirtual search page and returns the decoded `advertSearch`
    GraphQL result embedded in it.

    Standvirtual (Next.js + urql/GraphQL) embeds query results in
    __NEXT_DATA__.props.pageProps.urqlState, keyed by an opaque cache key, each value
    holding a JSON *string* (not object) under "data" - unlike AutoScout24, which puts
    listings directly in pageProps. Confirmed against a real payload 2026-09-03.
    """
    response = client.get(url, headers=HEADERS, timeout=60, follow_redirects=True)
    if response.status_code != 200:
        raise BlockedError("Unexpected status {} for {}".format(response.status_code, url))

    match = NEXT_DATA_RE.search(response.text)
    if not match:
        raise BlockedError("__NEXT_DATA__ not found in response for {} (likely a challenge page)".format(url))

    try:
        data = json.loads(match.group(1))
    except json.JSONDecodeError as exc:
        raise BlockedError("Could not decode __NEXT_DATA__ JSON for {}: {}".format(url, exc))

    urql_state = data.get("props", {}).get("pageProps", {}).get("urqlState") or {}
    for entry in urql_state.values():
        try:
            payload = json.loads(entry["data"])
        except (KeyError, TypeError, json.JSONDecodeError):
            continue
        advert_search = payload.get("advertSearch")
        if advert_search:
            return advert_search

    raise BlockedError("advertSearch not found in urqlState for {} (site layout may have changed)".format(url))


def _param(parameters, key):
    for p in parameters or []:
        if p.get("key") == key:
            return p.get("value")
    return None


def _param_display(parameters, key):
    """Like _param, but the human-readable displayValue (e.g. "2.0 TDI S tronic
    Design") instead of the URL-slug value (e.g. "ver-2-0-tdi-s-tronic-design") -
    used for fields we show as free text rather than match against a filter.
    """
    for p in parameters or []:
        if p.get("key") == key:
            return p.get("displayValue")
    return None


def _param_int(parameters, key):
    value = _param(parameters, key)
    return int(value) if value and value.isdigit() else None


def map_raw_listing(node):
    """Maps a raw Standvirtual advert node (advertSearch.edges[i].node) to our Listing
    model. Field paths confirmed against a real payload fetched 2026-09-03 - re-run
    `python -m scraper.cli inspect-json <url> --source standvirtual` if Standvirtual
    changes shape.
    """
    parameters = node.get("parameters")
    price = (node.get("price") or {}).get("amount") or {}
    city = ((node.get("location") or {}).get("city") or {}).get("name")

    return Listing(
        external_id=str(node.get("id")),
        source="standvirtual",
        url=node.get("url") or "",
        make=_param_display(parameters, "make"),
        model=_param_display(parameters, "model"),
        version=_param_display(parameters, "version"),
        first_registration_year=_param_int(parameters, "first_registration_year"),
        mileage_km=_param_int(parameters, "mileage"),
        power_hp=_param_int(parameters, "engine_power"),
        fuel=_param_display(parameters, "fuel_type"),
        gearbox=_param_display(parameters, "gearbox"),
        body_type=None,  # not present on list-page payload
        # standId only appears on ads from a professional stand - reliable proxy for
        # dealer vs private, confirmed against real payloads (present <=> dealer).
        seller_type="dealer" if node.get("standId") else "private",
        seller_name=None,
        location_zip=None,
        location_city=city,
        price_eur=int(price["units"]) if price.get("units") is not None else None,
    )


def iter_search_results(base_url, max_pages=None):
    """Yields (Listing, page_number) for a search, paginating until exhausted.

    Raises BlockedError if a challenge page is encountered - callers should stop the
    run and record status='blocked' rather than retrying aggressively.
    """
    max_pages = max_pages or config.MAX_PAGES

    with httpx.Client() as client:
        page = 1
        total_pages = None
        while page <= max_pages and (total_pages is None or page <= total_pages):
            separator = "&" if "?" in base_url else "?"
            page_url = "{}{}page={}".format(base_url, separator, page)

            advert_search = fetch_advert_search(page_url, client)
            edges = advert_search.get("edges") or []
            page_size = (advert_search.get("pageInfo") or {}).get("pageSize") or 32
            total_count = advert_search.get("totalCount") or 0
            if total_pages is None:
                total_pages = max(1, -(-total_count // page_size))  # ceil division

            if not edges:
                break

            for edge in edges:
                yield map_raw_listing(edge["node"]), page

            page += 1
            if page <= max_pages and page <= total_pages:
                _sleep_politely()
