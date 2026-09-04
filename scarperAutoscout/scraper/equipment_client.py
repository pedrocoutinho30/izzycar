"""Vai buscar a lista de equipamento à página de DETALHE de um anúncio - só é
chamado para anúncios NOVOS (ver scrape.py), nunca em cada re-execução para
anúncios já conhecidos, porque cada chamada é um pedido HTTP extra por anúncio
(o equipamento de um anúncio específico não muda, por isso não vale a pena
voltar a pedir).

Devolve sempre uma lista de {"raw_key": str, "raw_label": str} - "raw_key" é o
que Database.get_or_create_equipment usa para associar aliases a um item de
equipamento canónico (por isso fica sempre no texto/chave ORIGINAL, para
continuar a bater certo com o mesmo termo em recolhas futuras). "raw_label" é
só para mostrar ao utilizador, por isso já vem traduzido para português quando
possível (ver equipment_translations.py) - a Standvirtual já devolve o label em
português, só a AutoScout24 (alemão) precisa de tradução.
"""
import json

import httpx

from .autoscout_client import HEADERS as AUTOSCOUT_HEADERS
from .autoscout_client import fetch_next_data
from .equipment_translations import translate
from .standvirtual_client import HEADERS as STANDVIRTUAL_HEADERS
from .standvirtual_client import NEXT_DATA_RE


def fetch_autoscout_equipment(url):
    with httpx.Client(headers=AUTOSCOUT_HEADERS) as client:
        data = fetch_next_data(url, client)

    vehicle = (
        data.get("props", {})
        .get("pageProps", {})
        .get("listingDetails", {})
        .get("vehicle", {})
        or {}
    )
    equipment = vehicle.get("equipment") or {}

    items = []
    for category_items in equipment.values():
        for item in category_items or []:
            label = item.get("id")
            if label:
                items.append({"raw_key": label, "raw_label": translate("autoscout24", label)})
    return items


def fetch_standvirtual_equipment(url):
    with httpx.Client(headers=STANDVIRTUAL_HEADERS, follow_redirects=True) as client:
        response = client.get(url, timeout=60)

    match = NEXT_DATA_RE.search(response.text)
    if not match:
        return []

    try:
        data = json.loads(match.group(1))
    except json.JSONDecodeError:
        return []

    advert = data.get("props", {}).get("pageProps", {}).get("advert") or {}

    items = []
    for group in advert.get("equipment") or []:
        for value in group.get("values") or []:
            key = value.get("key")
            if key:
                items.append({"raw_key": key, "raw_label": value.get("label") or key})
    return items


FETCHERS = {
    "autoscout24": fetch_autoscout_equipment,
    "standvirtual": fetch_standvirtual_equipment,
}
