import json
import random
import re
import time
from urllib.parse import parse_qsl, urlencode, urlsplit, urlunsplit

import httpx

from . import config
from .models import Listing

# Países cobertos pela opção "Europa" da própria AutoScout24.de, confirmados
# via taxonomy.country (2026-09-04) - a AutoScout24 é fundamentalmente um site
# alemão/DACH+vizinhos, "Europa" no filtro de país só agrega estes 8, não
# cobre o continente todo (não inclui Portugal, Polónia, etc.). Não existe um
# único valor de "cy" que ative isto de uma vez (testado "cy=", "cy=eu" e
# "cy=D,A,B,..." - todos falham/voltam à Alemanha) - por isso corremos uma
# pesquisa por país e juntamos os resultados.
EUROPE_COUNTRY_CODES = ["D", "A", "B", "E", "F", "I", "L", "NL"]

NEXT_DATA_RE = re.compile(
    r'<script id="__NEXT_DATA__" type="application/json">(.*?)</script>', re.S
)

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    ),
    "Accept-Language": "en-US,en;q=0.9,pt-PT;q=0.8",
}


class BlockedError(Exception):
    """Raised when AutoScout24 returns a page without the expected __NEXT_DATA__ payload."""


def _sleep_politely():
    time.sleep(random.uniform(config.MIN_DELAY_SECONDS, config.MAX_DELAY_SECONDS))


def fetch_next_data(url, client):
    response = client.get(url, headers=HEADERS, timeout=60, follow_redirects=True)
    if response.status_code != 200:
        raise BlockedError(
            "Unexpected status {} for {}".format(response.status_code, url)
        )
    match = NEXT_DATA_RE.search(response.text)
    if not match:
        raise BlockedError("__NEXT_DATA__ not found in response for {} (likely a challenge page)".format(url))
    try:
        return json.loads(match.group(1))
    except json.JSONDecodeError as exc:
        raise BlockedError("Could not decode __NEXT_DATA__ JSON for {}: {}".format(url, exc))


def _get(d, *paths, default=None):
    """Try several dotted paths (tuples of keys) against nested dicts, return first hit."""
    for path in paths:
        node = d
        ok = True
        for key in path:
            if isinstance(node, dict) and key in node:
                node = node[key]
            else:
                ok = False
                break
        if ok and node is not None:
            return node
    return default


POWER_PS_RE = re.compile(r"\((\d+)\s*PS\)")


def _power_hp_from_vehicle_details(vehicle_details):
    for item in vehicle_details or []:
        if item.get("iconName") == "speedometer":
            match = POWER_PS_RE.search(item.get("data") or "")
            if match:
                return int(match.group(1))
    return None


def _first_registration_year(raw):
    # tracking.firstRegistration is formatted "MM-YYYY".
    value = _get(raw, ("tracking", "firstRegistration"))
    if value and "-" in value:
        year = value.split("-")[-1]
        if year.isdigit():
            return int(year)
    return None


def _seller_phone(raw):
    phones = _get(raw, ("seller", "phones")) or []
    if not phones:
        return None
    chosen = next((p for p in phones if p.get("phoneType") == "Mobile"), phones[0])
    return chosen.get("formattedNumber")


def map_raw_listing(raw, base_host):
    """Maps a raw AutoScout24 list-page listing (props.pageProps.listings[i]) to our
    Listing model. Field paths confirmed against a real payload fetched 2026-09-01 -
    see searches/example.yaml for the search used. Re-run
    `python -m scraper.cli inspect-json <search_url>` if AutoScout24 changes shape.
    """
    external_id = str(_get(raw, ("id",)))
    url = _get(raw, ("url",))
    if url and url.startswith("/"):
        url = "https://{}{}".format(base_host, url)

    mileage_raw = _get(raw, ("tracking", "mileage"))

    return Listing(
        external_id=external_id,
        url=url or "",
        make=_get(raw, ("vehicle", "make")),
        model=_get(raw, ("vehicle", "model")),
        version=_get(raw, ("vehicle", "modelVersionInput")),
        first_registration_year=_first_registration_year(raw),
        mileage_km=int(mileage_raw) if mileage_raw and mileage_raw.isdigit() else None,
        power_hp=_power_hp_from_vehicle_details(raw.get("vehicleDetails")),
        fuel=_get(raw, ("vehicle", "fuel")),
        gearbox=_get(raw, ("vehicle", "transmission")),
        body_type=None,  # not present on list-page payload; would need the detail page
        seller_type=_get(raw, ("seller", "type")),
        seller_name=_get(raw, ("seller", "companyName"), ("seller", "contactName")),
        seller_phone=_seller_phone(raw),
        location_zip=_get(raw, ("location", "zip")),
        location_city=_get(raw, ("location", "city")),
        price_eur=_get(raw, ("price", "priceRaw")),
    )


def _with_country(url, country_code):
    parts = urlsplit(url)
    query = dict(parse_qsl(parts.query))
    query["cy"] = country_code
    return urlunsplit((parts.scheme, parts.netloc, parts.path, urlencode(query), parts.fragment))


def iter_search_results(base_url, max_pages=None):
    """Yields (Listing, page_number) for a search across every country in
    EUROPE_COUNTRY_CODES, paginating each one until exhausted ("page_number" is a
    running count across all countries, not reset per country - só serve para o
    relatório de "páginas percorridas", não é usado para paginar).

    `max_pages` caps pages *per country* (not overall) - a smoke-test cap should
    still sample every country rather than exhausting the budget on the first one.

    Raises BlockedError if a challenge page is encountered - callers should stop the
    run and record status='blocked' rather than retrying aggressively.
    """
    page_counter = 0
    for country_code in EUROPE_COUNTRY_CODES:
        country_url = _with_country(base_url, country_code)
        last_seen_page = 0
        for listing, page in _iter_country_results(country_url, max_pages=max_pages):
            if page != last_seen_page:
                page_counter += 1
                last_seen_page = page
            yield listing, page_counter


def _iter_country_results(base_url, max_pages=None):
    """Yields (Listing, page_number) for a search in a single country, paginating
    until exhausted. `base_url` must already carry the desired `cy` country code.
    """
    max_pages = max_pages or config.MAX_PAGES
    base_host = httpx.URL(base_url).host

    with httpx.Client() as client:
        page = 1
        total_pages = None
        while page <= max_pages and (total_pages is None or page <= total_pages):
            separator = "&" if "?" in base_url else "?"
            page_url = "{}{}page={}".format(base_url, separator, page)

            data = fetch_next_data(page_url, client)
            page_props = _get(data, ("props", "pageProps"), default={})
            listings = page_props.get("listings") or []
            total_pages = page_props.get("numberOfPages") or total_pages or 1

            if not listings:
                break

            for raw in listings:
                yield map_raw_listing(raw, base_host), page

            page += 1
            if page <= max_pages and (total_pages is None or page <= total_pages):
                _sleep_politely()
