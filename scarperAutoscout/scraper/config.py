import os
from pathlib import Path

from dotenv import dotenv_values

PROJECT_ROOT = Path(__file__).resolve().parent.parent
IZZYCAR_ENV_PATH = PROJECT_ROOT.parent / ".env"

_env = dotenv_values(IZZYCAR_ENV_PATH)


def _required(key):
    value = _env.get(key) or os.environ.get(key)
    if not value:
        raise RuntimeError(
            "Missing {} in {} — is the izzycar .env file present?".format(key, IZZYCAR_ENV_PATH)
        )
    return value


DB_HOST = _required("DB_HOST")
DB_PORT = int(_env.get("DB_PORT") or 3306)
DB_DATABASE = _required("DB_DATABASE")
DB_USERNAME = _required("DB_USERNAME")
DB_PASSWORD = _required("DB_PASSWORD")

# Politeness / anti-bot pacing between page fetches, in seconds.
MIN_DELAY_SECONDS = 2.0
MAX_DELAY_SECONDS = 5.0

# AutoScout24 caps search results at 200 pages x 20 listings.
MAX_PAGES = 200
LISTINGS_PER_PAGE = 20
