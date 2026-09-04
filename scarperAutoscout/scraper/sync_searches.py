from pathlib import Path

import yaml

from .carmine_filters import build_base_url as build_carmine_base_url
from .db import Database
from .filters import build_base_url
from .standvirtual_filters import build_base_url as build_standvirtual_base_url

SEARCHES_DIR = Path(__file__).resolve().parent.parent / "searches"


def load_search_files():
    for path in sorted(SEARCHES_DIR.glob("*.yaml")):
        with open(path) as f:
            spec = yaml.safe_load(f)
        spec["_path"] = path
        yield spec


def sync_all(db=None):
    own_db = db is None
    db = db or Database()
    synced = []
    try:
        for spec in load_search_files():
            base_url = build_base_url(
                spec.get("make"),
                spec.get("model"),
                spec.get("filters"),
                motor_type=spec.get("motor_type"),
                model_variant=spec.get("model_variant"),
                trim=spec.get("trim"),
            )

            standvirtual_spec = spec.get("standvirtual")
            standvirtual_base_url = None
            if standvirtual_spec:
                standvirtual_base_url = build_standvirtual_base_url(
                    standvirtual_spec.get("make"),
                    standvirtual_spec.get("model"),
                    standvirtual_spec.get("filters"),
                )

            carmine_spec = spec.get("carmine")
            carmine_base_url = None
            if carmine_spec:
                carmine_base_url = build_carmine_base_url(
                    carmine_spec.get("make"),
                    carmine_spec.get("filters"),
                    model=carmine_spec.get("model"),
                )

            search_id = db.upsert_search(
                name=spec["name"],
                make=spec.get("make"),
                model=spec.get("model"),
                filters=spec.get("filters"),
                base_url=base_url,
                standvirtual_base_url=standvirtual_base_url,
                carmine_base_url=carmine_base_url,
            )
            synced.append((spec["name"], search_id, base_url))
    finally:
        if own_db:
            db.close()
    return synced
