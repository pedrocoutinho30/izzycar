from urllib.parse import quote, urlencode

# Confirmado empiricamente (2026-09-04) contra carmine.pt/carros-usados/{make}:
#   preco_ate  -> preço máximo em EUR
#   ano_ate    -> ano de matrícula máximo
#   km_ate     -> quilometragem máxima
#
# Ainda não confirmados: os equivalentes "de"/mínimo (testados preco_de, ano_de,
# km_de, kms_min/max, ano_min/max, desde, ranges tipo "preco=5000-15000" -
# nenhum teve efeito). A resposta do site mostra um "brotherField" para estes
# campos (confirma que o motor de busca suporta um mínimo), só não descobrimos
# ainda o nome do parâmetro de URL certo - testar de novo se precisarmos disto.
#
# O Carmine.pt não separa marca/modelo no path como a AutoScout24/Standvirtual -
# usa um único slug combinado "{make}-{model}" (ex. "renault-clio"). Confirmado
# (2026-09-05) que isto FILTRA MESMO por modelo (não só por marca) - mas só
# quando o slug do modelo é exatamente o que o Carmine usa internamente, que
# App\Services\CarmineTaxonomyService::getModels() descobre e confirma
# empiricamente (lendo o "model.name" de anúncios reais e testando o URL
# resultante), por isso o "model" recebido aqui já vem confirmado do Laravel -
# não construir/adivinhar este slug aqui.

BASE_URL = "https://carmine.pt/carros-usados"


def build_base_url(make, filters, model=None):
    slug = "{}-{}".format(make, model) if model else make
    path = "{}/{}".format(BASE_URL, quote(slug))
    return "{}?{}".format(path, urlencode(filters)) if filters else path
