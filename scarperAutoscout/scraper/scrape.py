from . import carmine_client, standvirtual_client
from .autoscout_client import BlockedError as AutoscoutBlockedError
from .autoscout_client import iter_search_results as iter_autoscout_results
from .db import Database
from .equipment_client import FETCHERS as EQUIPMENT_FETCHERS
from .standvirtual_client import BlockedError as StandvirtualBlockedError

SOURCES = {
    "autoscout24": {
        "url_field": "base_url",
        "iter_results": iter_autoscout_results,
        "blocked_error": AutoscoutBlockedError,
    },
    "standvirtual": {
        "url_field": "standvirtual_base_url",
        "iter_results": standvirtual_client.iter_search_results,
        "blocked_error": StandvirtualBlockedError,
    },
    "carmine": {
        "url_field": "carmine_base_url",
        "iter_results": carmine_client.iter_search_results,
        "blocked_error": carmine_client.BlockedError,
    },
}

# Origens portuguesas que podem ter o mesmo carro anunciado nas duas (o dealer
# publica em ambos os sites) - ver Database.mark_pt_duplicates.
PT_SOURCES = {"standvirtual", "carmine"}


def _run_source(source, base_url, search_id, db, max_pages=None):
    """Runs one search against one source end-to-end: paginate results, upsert
    listings + price history, mark disappeared listings (for that source only) as
    removed, and log the run.

    `max_pages` caps how many result pages are fetched - intended for smoke-testing
    against production data. When set, marking removed listings is skipped, since a
    capped run only sees a partial result set and would otherwise wrongly flag real
    listings beyond the cap as removed.
    """
    iter_results = SOURCES[source]["iter_results"]
    blocked_error = SOURCES[source]["blocked_error"]

    run_id = db.start_run(search_id, source=source)

    seen_external_ids = []
    pages_scraped = 0
    status = "ok"
    error_message = None

    equipment_fetcher = EQUIPMENT_FETCHERS.get(source)

    try:
        for listing, page in iter_results(base_url, max_pages=max_pages):
            listing_id, is_new = db.upsert_listing(listing, search_id)
            seen_external_ids.append(listing.external_id)
            pages_scraped = max(pages_scraped, page)

            # Equipamento só se vai buscar a anúncios NOVOS (pedido HTTP extra por
            # anúncio à página de detalhe) - ver equipment_client.py. Falhas aqui
            # não abortam a recolha do anúncio em si, só ficam sem equipamento.
            if is_new and equipment_fetcher and listing.url:
                try:
                    raw_items = equipment_fetcher(listing.url)
                    equipment_ids = [
                        db.get_or_create_equipment(source, item["raw_key"], item["raw_label"])
                        for item in raw_items
                    ]
                    db.set_listing_equipment(listing_id, equipment_ids)
                except Exception:  # noqa: BLE001 - equipamento é best-effort
                    pass

        if max_pages is None:
            db.mark_removed(search_id, source, seen_external_ids)
    except blocked_error as exc:
        status = "blocked"
        error_message = str(exc)
    except Exception as exc:  # noqa: BLE001 - surface any failure into the run log
        status = "error"
        error_message = str(exc)
    finally:
        db.finish_run(
            run_id,
            status=status,
            listings_found=len(seen_external_ids),
            pages_scraped=pages_scraped,
            error_message=error_message,
        )

    return {
        "source": source,
        "status": status,
        "listings_found": len(seen_external_ids),
        "pages_scraped": pages_scraped,
        "error_message": error_message,
    }


def run_search(search_row, db=None, max_pages=None):
    """Runs one saved search against every source it has a URL for: always
    AutoScout24 (Alemanha), plus Standvirtual e/ou Carmine.pt (Portugal) quando a
    pesquisa tem esse bloco configurado (search_row['standvirtual_base_url'] /
    ['carmine_base_url'] preenchidos).

    Depois de correr as origens portuguesas, deteta e marca anúncios duplicados
    entre elas (ver Database.mark_pt_duplicates) - só faz sentido comparar quando
    pelo menos uma correu nesta chamada, mas corre sempre que qualquer uma correu,
    para apanhar duplicados novos mesmo que só uma das duas tenha sido atualizada.

    Returns a list with one result dict per source that actually ran.
    """
    own_db = db is None
    db = db or Database()

    try:
        results = []
        for source, source_config in SOURCES.items():
            base_url = search_row.get(source_config["url_field"])
            if not base_url:
                continue
            results.append(_run_source(source, base_url, search_row["id"], db, max_pages=max_pages))

        if max_pages is None and any(r["source"] in PT_SOURCES for r in results):
            duplicates_found = db.mark_pt_duplicates(search_row["id"])
            results.append({"source": "pt_dedup", "duplicates_found": duplicates_found})

        return results
    finally:
        if own_db:
            db.close()
