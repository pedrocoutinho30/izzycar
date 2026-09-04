from urllib.parse import quote, urlencode

# Confirmed empirically (2026-09-01) against autoscout24.de/lst/{make}/{model}:
#   fregfrom / fregto   -> first registration year, from/to
#   kmfrom / kmto        -> mileage in km, from/to
#   powerfrom / powerto  -> power, from/to (paired with powertype)
#   powertype            -> "hp" or "kw"
#   fuel                 -> code, full list confirmed 2026-09-03 via the site's own taxonomy
#                            (AutoscoutTaxonomyService::getFuelTypes on the Laravel side):
#                            D = Diesel, B = Benzin (petrol), E = Elektro (electric),
#                            2 = Elektro/Benzin (petrol plug-in hybrid), 3 = Elektro/Diesel
#                            (diesel plug-in hybrid), C = Erdgas/CNG, L = Autogas/LPG,
#                            M = Ethanol, O = Sonstige (other). NOT "H" - that's Hydrogen
#                            (Wasserstoff), a common mistake since AutoScout24 has no code
#                            that reads as "hybrid".
#   gear                 -> M = manual, A = automatic, S = semi-automatic (confirmed 2026-09-03)
#   pricefrom / priceto  -> price in EUR, from/to
#   custtype             -> seller type: D = Händler (dealer), P = Privat (private) -
#                            confirmed 2026-09-03 (8241 dealer + 2509 private = 10750 total,
#                            unfiltered count, on a live search).
#   eq                   -> equipment/condition code (comma-separated for multiple, untested).
#                            49 = "Scheckheftgepflegt" (full service history/booklet) - confirmed
#                            2026-09-03. Full list of codes in AutoscoutTaxonomyService (Laravel
#                            side) via taxonomy.conditionEquipment / taxonomy.equipment. NOT the
#                            "equipment" param name - that one is silently ignored by the site.
#   sort / desc          -> sort field + direction
#   page                 -> pagination
#
# Still unconfirmed / not yet reverse-engineered: body type, zip + radius, doors, colour.
# Extend DEFAULTS / pass extra raw query params through `filters` in the search YAML once
# known - grab a URL from the site's own filter UI and diff it against this list.
#
# Além de make/model no path, a AutoScout24 aceita mais 3 segmentos extra a
# seguir ao modelo, confirmados empiricamente (2026-09-03) e combináveis entre
# si (testado /mt_e-220-d/tn_avantgarde junto - filtragem por interseção):
#   mt_{slug}  -> motorização/submodelo real (ex.: "E 220 d", "E 300 de")
#   va_{slug}  -> variante de carroçaria (ex.: "T-modell" = carrinha)
#   tn_{slug}  -> linha de equipamento (ex.: "AMG Line", "Avantgarde")
# Os slugs válidos para um dado make/model vêm do próprio site (ver
# AutoscoutTaxonomyService::getSubmodelOptions no lado Laravel) - não são
# inventáveis à mão, tal como make/model.

DEFAULTS = {
    "atype": "C",  # car
    "cy": "D",  # country: Germany
    "ustate": "N,U",  # new + used
    "sort": "standard",
    "desc": "0",
}


def build_base_url(make, model, filters, motor_type=None, model_variant=None, trim=None):
    """Build the AutoScout24 search URL (without `page`) for a make/model + raw filter dict.

    `filters` keys are passed straight through as AutoScout24 query params - see the
    confirmed list above. This intentionally does not introduce our own field-name
    abstraction, since AutoScout24's real parameter names aren't fully documented and
    a wrong guess would silently produce an unfiltered search.
    """
    path = "https://www.autoscout24.de/lst/{}".format(quote(make))
    if model:
        path += "/{}".format(quote(model))
        for segment in (motor_type, model_variant, trim):
            if segment:
                path += "/{}".format(quote(segment))

    params = dict(DEFAULTS)
    params.update(filters or {})

    return "{}?{}".format(path, urlencode(params))
