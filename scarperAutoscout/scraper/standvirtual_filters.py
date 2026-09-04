from urllib.parse import quote, urlencode

# Confirmed empirically (2026-09-03) against standvirtual.com/carros/{make}/{model}:
#   filter_float_price:from / :to                       -> price in EUR
#   filter_float_first_registration_year:from / :to      -> first registration year
#   filter_float_mileage:from / :to                       -> mileage in km
#   filter_float_engine_power:from / :to                   -> power in cv
#   filter_enum_fuel_type -> code, confirmed by grepping real listing payloads (not
#                            guessed): diesel, gaz (= Gasolina/petrol - NOT "petrol" or
#                            "gasoline", both silently return 0 results), electric,
#                            hibride-gaz (Híbrido Gasolina, non-plugin),
#                            plugin-hybrid (Híbrido Plug-In), gpl.
#   filter_enum_gearbox   -> automatic, manual
#   page                  -> pagination, 1-indexed, 32 results/page
#
# Unlike AutoScout24, query params are nested under search[...], e.g.
# search[filter_float_price:to]=20000 - urlencode() below builds that shape from a
# flat `filters` dict whose keys are these real param names verbatim (same
# no-abstraction philosophy as autoscout_client/filters.py: wrong guesses fail loudly
# instead of silently producing an unfiltered search).
#
# Still unconfirmed / not yet reverse-engineered: seller type filter (private vs
# dealer - listings do expose this via `standId`, read on our side, but no confirmed
# search filter param yet), body type, zip + radius, colour, equipment features.

BASE_URL = "https://www.standvirtual.com/carros"


def build_base_url(make, model, filters):
    """Build the Standvirtual search URL (without `page`) for a make/model + raw
    filter dict. `filters` keys are the site's real `search[...]` param names (minus
    the wrapper) - see confirmed list above.
    """
    path = BASE_URL
    if make:
        path += "/{}".format(quote(make))
        if model:
            path += "/{}".format(quote(model))

    params = {"search[{}]".format(key): value for key, value in (filters or {}).items()}

    return "{}?{}".format(path, urlencode(params)) if params else path
