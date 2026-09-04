import json
import re

import pymysql
import pymysql.cursors

from . import config


def get_connection():
    return pymysql.connect(
        host=config.DB_HOST,
        port=config.DB_PORT,
        user=config.DB_USERNAME,
        password=config.DB_PASSWORD,
        database=config.DB_DATABASE,
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=False,
    )


class Database:
    def __init__(self, conn=None):
        self.conn = conn or get_connection()

    def close(self):
        self.conn.close()

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc, tb):
        self.close()

    # -- radar_searches --------------------------------------------------

    def upsert_search(self, name, make, model, filters, base_url, standvirtual_base_url=None, carmine_base_url=None):
        with self.conn.cursor() as cur:
            cur.execute("SELECT id FROM radar_searches WHERE name = %s", (name,))
            row = cur.fetchone()
            filters_json = json.dumps(filters or {})
            if row:
                cur.execute(
                    """
                    UPDATE radar_searches
                    SET make = %s, model = %s, filters = %s, base_url = %s,
                        standvirtual_base_url = %s, carmine_base_url = %s, updated_at = NOW()
                    WHERE id = %s
                    """,
                    (make, model, filters_json, base_url, standvirtual_base_url, carmine_base_url, row["id"]),
                )
                search_id = row["id"]
            else:
                cur.execute(
                    """
                    INSERT INTO radar_searches (
                        name, make, model, filters, base_url, standvirtual_base_url,
                        carmine_base_url, created_at, updated_at
                    )
                    VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
                    """,
                    (name, make, model, filters_json, base_url, standvirtual_base_url, carmine_base_url),
                )
                search_id = cur.lastrowid
        self.conn.commit()
        return search_id

    def get_search_by_name(self, name):
        with self.conn.cursor() as cur:
            cur.execute("SELECT * FROM radar_searches WHERE name = %s", (name,))
            return cur.fetchone()

    def list_searches(self):
        with self.conn.cursor() as cur:
            cur.execute("SELECT * FROM radar_searches ORDER BY name")
            return cur.fetchall()

    # -- radar_search_runs ------------------------------------------------

    def start_run(self, search_id, source="autoscout24"):
        with self.conn.cursor() as cur:
            cur.execute(
                """
                INSERT INTO radar_search_runs (radar_search_id, source, started_at, status)
                VALUES (%s, %s, NOW(), 'running')
                """,
                (search_id, source),
            )
            run_id = cur.lastrowid
        self.conn.commit()
        return run_id

    def finish_run(self, run_id, status, listings_found, pages_scraped, error_message=None):
        with self.conn.cursor() as cur:
            cur.execute(
                """
                UPDATE radar_search_runs
                SET finished_at = NOW(), status = %s, listings_found = %s,
                    pages_scraped = %s, error_message = %s
                WHERE id = %s
                """,
                (status, listings_found, pages_scraped, error_message, run_id),
            )
        self.conn.commit()

    # -- radar_listings / radar_price_history -----------------------------

    def upsert_listing(self, listing, search_id):
        with self.conn.cursor() as cur:
            cur.execute(
                "SELECT id FROM radar_listings WHERE source = %s AND external_id = %s",
                (listing.source, listing.external_id),
            )
            row = cur.fetchone()
            fields = (
                search_id,
                listing.make,
                listing.model,
                listing.version,
                listing.first_registration_year,
                listing.mileage_km,
                listing.power_hp,
                listing.fuel,
                listing.gearbox,
                listing.body_type,
                listing.seller_type,
                listing.seller_name,
                listing.seller_phone,
                listing.location_zip,
                listing.location_city,
                listing.price_eur,
                listing.url,
            )
            is_new = row is None
            if row:
                listing_id = row["id"]
                cur.execute(
                    """
                    UPDATE radar_listings
                    SET radar_search_id = %s, make = %s, model = %s, version = %s,
                        first_registration_year = %s, mileage_km = %s, power_hp = %s,
                        fuel = %s, gearbox = %s, body_type = %s, seller_type = %s,
                        seller_name = %s, seller_phone = %s, location_zip = %s, location_city = %s,
                        price_eur = %s, url = %s, last_seen_at = NOW(), removed_at = NULL,
                        updated_at = NOW()
                    WHERE id = %s
                    """,
                    fields + (listing_id,),
                )
            else:
                cur.execute(
                    """
                    INSERT INTO radar_listings (
                        external_id, source, radar_search_id, make, model, version,
                        first_registration_year, mileage_km, power_hp, fuel, gearbox,
                        body_type, seller_type, seller_name, seller_phone, location_zip, location_city,
                        price_eur, url, first_seen_at, last_seen_at, created_at, updated_at
                    )
                    VALUES (
                        %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                        NOW(), NOW(), NOW(), NOW()
                    )
                    """,
                    (listing.external_id, listing.source) + fields,
                )
                listing_id = cur.lastrowid

            if listing.price_eur is not None:
                cur.execute(
                    """
                    INSERT INTO radar_price_history (radar_listing_id, price_eur, scraped_at)
                    VALUES (%s, %s, NOW())
                    """,
                    (listing_id, listing.price_eur),
                )
        self.conn.commit()
        return listing_id, is_new

    def mark_removed(self, search_id, source, seen_external_ids):
        seen_external_ids = list(seen_external_ids)
        with self.conn.cursor() as cur:
            if seen_external_ids:
                placeholders = ", ".join(["%s"] * len(seen_external_ids))
                cur.execute(
                    """
                    UPDATE radar_listings
                    SET removed_at = NOW()
                    WHERE radar_search_id = %s AND source = %s AND removed_at IS NULL
                      AND external_id NOT IN ({})
                    """.format(placeholders),
                    [search_id, source] + seen_external_ids,
                )
            else:
                cur.execute(
                    """
                    UPDATE radar_listings
                    SET removed_at = NOW()
                    WHERE radar_search_id = %s AND source = %s AND removed_at IS NULL
                    """,
                    (search_id, source),
                )
        self.conn.commit()

    # -- deduplicação Standvirtual <-> Carmine.pt --------------------------

    def mark_pt_duplicates(self, search_id, km_tolerance=300):
        """Deteta o mesmo carro anunciado tanto no Standvirtual como no Carmine.pt
        (ex.: um stand publica nos dois sites) e marca a versão mais recente como
        duplicado da mais antiga, via duplicate_of_listing_id - a listagem só mostra
        anúncios "primários" (duplicate_of_listing_id IS NULL), mas ambas as linhas
        ficam guardadas na BD (nada se perde se a correspondência estiver errada).

        Corresponde por marca + modelo + ano + preço exatos e quilometragem dentro de
        `km_tolerance` km - sem nome de vendedor (o Standvirtual não o expõe na
        pesquisa, só no anúncio individual), mas dado que aqui só comparamos dentro
        da MESMA pesquisa (mesma marca/modelo/gama de filtros), a probabilidade de
        dois carros DIFERENTES bater tudo isto ao mesmo tempo é muito baixa.

        Corre sempre do zero (reset + redeteção) para não acumular estado obsoleto
        quando um dos dois anúncios desaparece de um dos sites.
        """
        with self.conn.cursor() as cur:
            cur.execute(
                """
                UPDATE radar_listings SET duplicate_of_listing_id = NULL
                WHERE radar_search_id = %s AND source IN ('standvirtual', 'carmine') AND removed_at IS NULL
                """,
                (search_id,),
            )

            cur.execute(
                """
                SELECT id, source, make, model, first_registration_year, price_eur, mileage_km
                FROM radar_listings
                WHERE radar_search_id = %s AND source IN ('standvirtual', 'carmine') AND removed_at IS NULL
                ORDER BY first_seen_at ASC
                """,
                (search_id,),
            )
            rows = cur.fetchall()

        groups = {}
        for row in rows:
            key = (
                (row["make"] or "").strip().lower(),
                (row["model"] or "").strip().lower(),
                row["first_registration_year"],
                row["price_eur"],
            )
            groups.setdefault(key, []).append(row)

        pairs = []  # (duplicate_id, primary_id)
        for group in groups.values():
            if len(group) < 2:
                continue
            claimed = set()
            for i, primary in enumerate(group):
                if primary["id"] in claimed:
                    continue
                for other in group[i + 1:]:
                    if other["id"] in claimed or other["source"] == primary["source"]:
                        continue
                    if primary["mileage_km"] is None or other["mileage_km"] is None:
                        continue
                    if abs(primary["mileage_km"] - other["mileage_km"]) <= km_tolerance:
                        pairs.append((other["id"], primary["id"]))
                        claimed.add(other["id"])
                        claimed.add(primary["id"])
                        break

        if pairs:
            with self.conn.cursor() as cur:
                for duplicate_id, primary_id in pairs:
                    cur.execute(
                        "UPDATE radar_listings SET duplicate_of_listing_id = %s WHERE id = %s",
                        (primary_id, duplicate_id),
                    )
        self.conn.commit()
        return len(pairs)

    def get_or_create_equipment(self, source, raw_key, raw_label):
        """Devolve o id do item de equipamento canónico associado a este
        (source, raw_key) - cria um item novo (escondido dos filtros, por omissão)
        e o seu alias na primeira vez que este texto/chave aparece. Chamado só para
        anúncios novos (ver equipment_client.py) - o mesmo alias é reutilizado em
        todas as recolhas seguintes, mesmo que o utilizador já tenha renomeado ou
        fundido o item canónico manualmente no Laravel.
        """
        with self.conn.cursor() as cur:
            cur.execute(
                "SELECT radar_equipment_id FROM radar_equipment_aliases WHERE source = %s AND raw_key = %s",
                (source, raw_key),
            )
            row = cur.fetchone()
            if row:
                return row["radar_equipment_id"]

            slug = re.sub(r"[^a-z0-9]+", "-", raw_label.strip().lower()).strip("-")
            cur.execute(
                "INSERT INTO radar_equipment (label, slug, show_in_filters, created_at, updated_at) "
                "VALUES (%s, %s, 0, NOW(), NOW())",
                (raw_label, slug),
            )
            equipment_id = cur.lastrowid
            cur.execute(
                "INSERT INTO radar_equipment_aliases (radar_equipment_id, source, raw_key, raw_label, created_at, updated_at) "
                "VALUES (%s, %s, %s, %s, NOW(), NOW())",
                (equipment_id, source, raw_key, raw_label),
            )
        self.conn.commit()
        return equipment_id

    def set_listing_equipment(self, listing_id, equipment_ids):
        with self.conn.cursor() as cur:
            cur.execute("DELETE FROM radar_listing_equipment WHERE radar_listing_id = %s", (listing_id,))
            if equipment_ids:
                cur.executemany(
                    "INSERT INTO radar_listing_equipment (radar_listing_id, radar_equipment_id) VALUES (%s, %s)",
                    [(listing_id, equipment_id) for equipment_id in equipment_ids],
                )
        self.conn.commit()
