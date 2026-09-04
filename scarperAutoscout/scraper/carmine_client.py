import json
import random
import re
import time

import httpx

from . import config
from .models import Listing

NEXT_DATA_RE = re.compile(
    r'<script id="__NEXT_DATA__"[^>]*>(.*?)</script>', re.S
)

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    ),
    "Accept-Language": "pt-PT,pt;q=0.9,en;q=0.8",
}

PAGE_SIZE = 20  # confirmado empiricamente (2026-09-04)


class BlockedError(Exception):
    """Raised when Carmine.pt returns a page without the expected data payload."""


def _sleep_politely():
    time.sleep(random.uniform(config.MIN_DELAY_SECONDS, config.MAX_DELAY_SECONDS))


def fetch_classifieds(url, client):
    """Fetches a Carmine.pt search page and returns the `classifieds` block embedded
    in __NEXT_DATA__ (props.pageProps.classifieds) - ao contrário do Standvirtual,
    aqui os anúncios vêm diretos na página, sem indireção via cache GraphQL.
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

    return data.get("props", {}).get("pageProps", {}).get("classifieds", {}) or {}


def map_raw_listing(raw):
    """Maps a raw Carmine.pt classified (classifieds.classifiedList[i]) to our Listing
    model. Field paths confirmed against a real payload fetched 2026-09-04 - re-run
    `python -m scraper.cli inspect-json <url> --source carmine` if Carmine changes shape.

    Carmine.pt não expõe um URL/slug direto no próprio anúncio, mas confirmámos que
    qualquer slug antes do id real funciona (o site resolve pelo id, ignora o resto) -
    por isso usamos sempre "anuncio" como slug genérico.
    """
    listing_id = raw.get("id")
    dealer = raw.get("dealer") or {}
    contact = (dealer.get("contactList") or [{}])[0]
    showroom = (raw.get("showroomList") or [{}])[0]
    registration = raw.get("registration") or {}
    price = raw.get("price") or {}
    engine = raw.get("engine") or {}

    year = registration.get("year")

    return Listing(
        external_id=str(listing_id),
        source="carmine",
        url="https://carmine.pt/carros-usados/anuncio/{}".format(listing_id),
        make=(raw.get("make") or {}).get("name"),
        model=(raw.get("model") or {}).get("name"),
        version=(raw.get("version") or {}).get("name"),
        first_registration_year=int(year) if year and str(year).isdigit() else None,
        mileage_km=(raw.get("mileage") or {}).get("amount"),
        power_hp=engine.get("powerCv"),
        fuel=(raw.get("fuel") or {}).get("name"),
        gearbox=(raw.get("transmission") or {}).get("name"),
        body_type=None,
        # O Carmine.pt é um portal só de stands/profissionais (não há anúncios de
        # particulares) - confirmado por todos os anúncios terem sempre "dealer".
        seller_type="dealer",
        seller_name=dealer.get("name"),
        seller_phone=contact.get("phone"),
        location_zip=showroom.get("postalCode"),
        location_city=showroom.get("city") or (raw.get("currentProvince") or {}).get("name"),
        price_eur=price.get("amount"),
    )


def iter_search_results(base_url, max_pages=None):
    """Yields (Listing, page_number) for a search, paginating until exhausted.

    Raises BlockedError if a challenge page is encountered - callers should stop the
    run and record status='blocked' rather than retrying aggressively.
    """
    max_pages = max_pages or config.MAX_PAGES

    with httpx.Client() as client:
        page = 1
        total = None
        while page <= max_pages and (total is None or (page - 1) * PAGE_SIZE < total):
            separator = "&" if "?" in base_url else "?"
            page_url = "{}{}page={}".format(base_url, separator, page)

            classifieds = fetch_classifieds(page_url, client)
            items = classifieds.get("classifiedList") or []
            total = classifieds.get("total") or total or 0

            if not items:
                break

            for raw in items:
                yield map_raw_listing(raw), page

            page += 1
            if page <= max_pages and (page - 1) * PAGE_SIZE < total:
                _sleep_politely()
