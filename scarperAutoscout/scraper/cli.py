import argparse
import json
import sys

from .autoscout_client import fetch_next_data
from .carmine_client import fetch_classifieds
from .db import Database
from .scrape import run_search
from .standvirtual_client import fetch_advert_search
from .sync_searches import sync_all

import httpx


def cmd_sync_searches(args):
    for name, search_id, base_url in sync_all():
        print("[{}] {} -> {}".format(search_id, name, base_url))


def cmd_run(args):
    with Database() as db:
        search = db.get_search_by_name(args.name)
        if not search:
            print("No saved search named '{}'. Run sync-searches first.".format(args.name), file=sys.stderr)
            sys.exit(1)
        result = run_search(search, db=db, max_pages=args.max_pages)
        print(json.dumps(result, indent=2))


def cmd_run_all(args):
    with Database() as db:
        for search in db.list_searches():
            print("Running '{}'...".format(search["name"]))
            result = run_search(search, db=db)
            print(json.dumps(result, indent=2))


def cmd_inspect_json(args):
    """Fetches a URL and pretty-prints the raw listing payload, to check real field
    names/keys before trusting autoscout_client.map_raw_listing /
    standvirtual_client.map_raw_listing.
    """
    with httpx.Client() as client:
        if args.source == "standvirtual":
            advert_search = fetch_advert_search(args.url, client)
            edges = advert_search.get("edges", [])
            print("totalCount:", advert_search.get("totalCount"))
            print("pageInfo:", advert_search.get("pageInfo"))
            print("listings count on this page:", len(edges))
            if edges:
                print("\nFirst listing raw JSON:")
                print(json.dumps(edges[0]["node"], indent=2, ensure_ascii=False))
            return

        if args.source == "carmine":
            classifieds = fetch_classifieds(args.url, client)
            items = classifieds.get("classifiedList", [])
            print("total:", classifieds.get("total"))
            print("listings count on this page:", len(items))
            if items:
                print("\nFirst listing raw JSON:")
                print(json.dumps(items[0], indent=2, ensure_ascii=False))
            return

        data = fetch_next_data(args.url, client)
    page_props = data.get("props", {}).get("pageProps", {})
    listings = page_props.get("listings", [])
    print("numberOfResults:", page_props.get("numberOfResults"))
    print("numberOfPages:", page_props.get("numberOfPages"))
    print("listings count on this page:", len(listings))
    if listings:
        print("\nFirst listing raw JSON:")
        print(json.dumps(listings[0], indent=2, ensure_ascii=False))


def main():
    parser = argparse.ArgumentParser(prog="scraper")
    sub = parser.add_subparsers(dest="command", required=True)

    sub.add_parser("sync-searches").set_defaults(func=cmd_sync_searches)

    run_parser = sub.add_parser("run")
    run_parser.add_argument("name")
    run_parser.add_argument("--max-pages", type=int, default=None)
    run_parser.set_defaults(func=cmd_run)

    sub.add_parser("run-all").set_defaults(func=cmd_run_all)

    inspect_parser = sub.add_parser("inspect-json")
    inspect_parser.add_argument("url")
    inspect_parser.add_argument("--source", choices=["autoscout24", "standvirtual", "carmine"], default="autoscout24")
    inspect_parser.set_defaults(func=cmd_inspect_json)

    args = parser.parse_args()
    args.func(args)


if __name__ == "__main__":
    main()
