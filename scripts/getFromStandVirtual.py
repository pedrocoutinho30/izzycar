import requests
from bs4 import BeautifulSoup

import re
import csv

import time
import sys
import os


def safe_get_text(soup, tag, attrs=None, index=1):
    try:
        element = soup.find(tag, attrs)
        return element.find_all("p")[index].get_text(strip=True)
    except Exception:
        return None


def safe_find_text(soup, tag, class_=None):
    try:
        return soup.find(tag, class_=class_).get_text(strip=True)
    except Exception:
        return None


def extrair_id(soup):
    p = soup.find("p", class_="e1j3ff6y1 ooa-1yhbi78")
    if p:
        match = re.search(r"ID:\s*(\d+)", p.get_text())
        if match:
            return match.group(1)
    return None


def verificar_extras(soup):
    
    texto = soup.get_text(separator=' ', strip=True)

    tecto_abrir = "Tecto de abrir" in texto
    tecto_panoramico = "Tecto panorâmico" in texto
    camara360 = "Câmara 360" in texto
    appleCarplay = "Apple CarPlay" in texto
    androidAuto = "Android Auto" in texto
    estofosAlcantara = "Estofos em alcântara" in texto
    estofosPele = "Estofos em pele" in texto
    estofosTecido = "Estofos em tecido" in texto
    bancoCondutorAquecido = "Banco do condutor aquecido" in texto
    bancoPassageiroAquecido = "Banco do passageiro aquecido" in texto
    bancoCondutorRegulacaoEletrica = "Banco do condutor com regulação eléctrica" in texto
    bancosMemoria = "Bancos com memória" in texto
    fechoCentralSemChave = "Fecho central sem chave" in texto
    arranqueSemChave = "Arranque sem chave" in texto
    cruiseControl = "Cruise Control" in texto
    cruiseControlAdaptativo = "Cruise Control adaptativo" in texto
    cruiseControlPredictivo = "Cruise Control Predictivo" in texto
    sensorEstacionamentoDianteiro = "Sensor de estacionamento dianteiro" in texto
    sensorEstacionamentoTraseiro = "Sensor de estacionamento traseiro" in texto
    assistenteEstacionamento = "Assistente de estacionamento" in texto
    suspensaoDesportiva = "Suspensão desportiva" in texto
    sistemaEstacionamentoAutonomo = "Sistema de estacionamento autónomo" in texto
    bluetooth = "Bluetooth" in texto
    portaUSB = "Porta USB" in texto
    carregadorSmartphoneWireless = "Carregador de smartphone wireless" in texto
    sistemaNavegacao = "Sistema de navegação" in texto
    camaraMarchaAtras = "Câmara de marcha-atrás" in texto
    retrovisoresRetrateis = "Retrovisores exteriores eletricamente retrateis" in texto
    assistenteAnguloMorto = "Assistente de ângulo morto" in texto
    assistenteMudancaFaixa = "Assistente de mudança de faixa" in texto
    controloProximidade = "Controlo de proximidade" in texto
    conducaoAutonomaBasica = "Condução autónoma básica" in texto
    return tecto_abrir,tecto_panoramico,camara360,appleCarplay,androidAuto,estofosAlcantara,estofosPele,estofosTecido, bancoCondutorAquecido, bancoPassageiroAquecido, bancoCondutorRegulacaoEletrica, bancosMemoria, fechoCentralSemChave, arranqueSemChave, cruiseControl, cruiseControlAdaptativo, cruiseControlPredictivo, sensorEstacionamentoDianteiro, sensorEstacionamentoTraseiro, assistenteEstacionamento, suspensaoDesportiva, sistemaEstacionamentoAutonomo, bluetooth, portaUSB, carregadorSmartphoneWireless, sistemaNavegacao, camaraMarchaAtras, retrovisoresRetrateis, assistenteAnguloMorto, assistenteMudancaFaixa, controloProximidade, conducaoAutonomaBasica


def extrair_dados_do_anuncio(url):
    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
    resp = requests.get(url, headers=headers)
    resp.raise_for_status()

    soup = BeautifulSoup(resp.text, "html.parser")

    print("Analisando URL:", url)

    # Salvar o HTML parseado em um ficheiro para debug
    with open("soup_debug.html", "w", encoding="utf-8") as f:
        f.write(soup.prettify())

    # Tecto
    tecto_abrir,tecto_panoramico,camara360,appleCarplay,androidAuto,estofosAlcantara,estofosPele,estofosTecido, bancoCondutorAquecido, bancoPassageiroAquecido, bancoCondutorRegulacaoEletrica, bancosMemoria, fechoCentralSemChave, arranqueSemChave, cruiseControl, cruiseControlAdaptativo, cruiseControlPredictivo, sensorEstacionamentoDianteiro, sensorEstacionamentoTraseiro, assistenteEstacionamento, suspensaoDesportiva, sistemaEstacionamentoAutonomo, bluetooth, portaUSB, carregadorSmartphoneWireless, sistemaNavegacao, camaraMarchaAtras, retrovisoresRetrateis, assistenteAnguloMorto, assistenteMudancaFaixa, controloProximidade, conducaoAutonomaBasica = verificar_extras(soup)
    print("Tecto de abrir:", tecto_abrir)
    print("Tecto panorâmico:", tecto_panoramico)
    print("Camara 360:", camara360)

    # Preço
    preco = safe_find_text(soup, "span", class_="offer-price__number")
    print("Preço:", preco)

    # Marca
    marca = safe_get_text(soup, "div", {"data-testid": "make"})
    print("Marca:", marca)

    # Modelo
    modelo = safe_get_text(soup, "div", {"data-testid": "model"})
    print("Modelo:", modelo)

    # Submodelo
    submodelo = safe_get_text(soup, "div", {"data-testid": "engine_code"})
    print("Submodelo:", submodelo)

    # Versão
    versao = safe_get_text(soup, "div", {"data-testid": "version"})
    print("Versão:", versao)

    # Cor
    cor = safe_get_text(soup, "div", {"data-testid": "color"})
    print("Cor:", cor)

    # Portas
    portas = safe_get_text(soup, "div", {"data-testid": "door_count"})
    print("Portas:", portas)

    # Combustível
    combustivel = safe_get_text(soup, "div", {"data-testid": "fuel_type"})
    print("Combustível:", combustivel)

    # Capacidade do motor
    capacidade_motor = safe_get_text(soup, "div", {"data-testid": "engine_capacity"})
    print("Capacidade do motor:", capacidade_motor)

    # Potência
    potencia = safe_get_text(soup, "div", {"data-testid": "engine_power"})
    print("Potência:", potencia)

    # Tipo de carroceria
    tipo_carroceria = safe_get_text(soup, "div", {"data-testid": "body_type"})
    print("Tipo de carroceria:", tipo_carroceria)

    # Caixa de marchas
    caixa_marchas = safe_get_text(soup, "div", {"data-testid": "gearbox"})
    print("Caixa de marchas:", caixa_marchas)

    # Marchas
    marchas = safe_get_text(soup, "div", {"data-testid": "gearbox_shifts"})
    print("Marchas:", marchas)

    # Transmissão
    transmissao = safe_get_text(soup, "div", {"data-testid": "transmission"})
    print("Transmissão:", transmissao)

    # Mês de registro
    mes_registro = safe_get_text(
        soup, "div", {"data-testid": "first_registration_month"}
    )
    print("Mês de registro:", mes_registro)

    # Ano de registro
    ano_registro = safe_get_text(
        soup, "div", {"data-testid": "first_registration_year"}
    )
    print("Ano de registro:", ano_registro)

    # Quilometragem
    quilometragem = safe_get_text(soup, "div", {"data-testid": "mileage"})
    print("Quilometragem:", quilometragem)

    id_extraido = extrair_id(soup)
    print("ID:", id_extraido)
    print("-----")

    dados_anuncio = {
        "ID": id_extraido,
        "Título": f"{marca} {modelo} {submodelo or ''} {versao}".strip(),
        "Preço": preco,
        "Ano": ano_registro,
        "Quilometragem": quilometragem,
        "Transmissão": caixa_marchas,
    
        "Teto de abrir": tecto_abrir,
        "Teto panorâmico": tecto_panoramico,
        "Câmara 360": camara360,
        "Apple CarPlay": appleCarplay,
        "Android Auto": androidAuto,
        "Estofos em alcântara": estofosAlcantara,
        "Estofos em pele": estofosPele,
        "Estofos em tecido": estofosTecido,
        "Banco do condutor aquecido": bancoCondutorAquecido,
        "Banco do passageiro aquecido": bancoPassageiroAquecido,
        "Banco do condutor com regulação eléctrica": bancoCondutorRegulacaoEletrica,
        "Bancos com memória": bancosMemoria,
        "Fecho central sem chave": fechoCentralSemChave,
        "Arranque sem chave": arranqueSemChave,
        "Cruise Control": cruiseControl,
        "Cruise Control adaptativo": cruiseControlAdaptativo,
        "Cruise Control Predictivo": cruiseControlPredictivo,
        "Sensor de estacionamento dianteiro": sensorEstacionamentoDianteiro,
        "Sensor de estacionamento traseiro": sensorEstacionamentoTraseiro,
        "Assistente de estacionamento": assistenteEstacionamento,
        "Suspensão desportiva": suspensaoDesportiva,
        "Sistema de estacionamento autónomo": sistemaEstacionamentoAutonomo,
        "Bluetooth": bluetooth,
        "Porta USB": portaUSB,
        "Carregador de smartphone wireless": carregadorSmartphoneWireless,
        "Sistema de navegação": sistemaNavegacao,
        "Câmara de marcha-atrás": camaraMarchaAtras,
        "Retrovisores exteriores eletricamente retrateis": retrovisoresRetrateis,
        "Assistente de ângulo morto": assistenteAnguloMorto,
        "Assistente de mudança de faixa": assistenteMudancaFaixa,
        "Controlo de proximidade": controloProximidade,
        "Condução autónoma básica": conducaoAutonomaBasica,
        "URL": url,
    }

    # Retornar o dicionário com os dados do anúncio
    return dados_anuncio


def get_anuncios_by_url(url):

    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
    resp = requests.get(url, headers=headers)
    resp.raise_for_status()

    soup = BeautifulSoup(resp.text, "html.parser")

    # Salvar o HTML parseado em um ficheiro para debug
    with open("soup_debug.html", "w", encoding="utf-8") as f:
        f.write(soup.prettify())
    # Encontrar todas as tags <a> com target="_self"
    links = []
    for a in soup.find_all("a", target="_self"):
        href = a.get("href")
        if href:
            links.append(href)
    return links


if __name__ == "__main__":

    marca = sys.argv[1]
    modelo = sys.argv[2]
    submodelo = sys.argv[3]
    ano_init = sys.argv[4]
    ano_fin = sys.argv[5]
    combustivel = sys.argv[6]
    descricao = sys.argv[7]
    url = sys.argv[8]

    # Testar a função get_anuncios
    # marca = "Mercedes-Benz"
    # modelo = "Classe CLA"
    # submodelo = "CLA 250"
    # segmento = "Carrinha"
    # ano_init = "2020"
    # ano_fin = "2022"
    # combustivel = "Híbrido (Gasolina)"
    # descricao = "AMG"
    # url = "https://www.standvirtual.com/carros/mercedes-benz/cla-250/desde-2020/q-amg?search%5Bfilter_enum_engine_code%5D=classe-cla&search%5Bfilter_enum_fuel_type%5D=plugin-hybrid&search%5Bfilter_float_first_registration_year%3Ato%5D=2022&search%5Badvanced_search_expanded%5D=true"
     
    links = get_anuncios_by_url(url)
    print(f"Encontrados {len(links)} anúncios.")

    root_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    adsearch_dir = os.path.join(root_dir, "AdSearch")
    os.makedirs(adsearch_dir, exist_ok=True)
    csv_filename = (
        marca
        + "_"
        + modelo
        + "_"
        + submodelo
        + "_"
        + ano_init
        + "_"
        + ano_fin
        + "_"
        + combustivel
        + "_"
        + descricao
        + ".csv"
    )
    csv_path = os.path.join(adsearch_dir, csv_filename)

    with open(
        csv_path,
        "w",
        newline="",
        encoding="utf-8",
    ) as f:
        writer = csv.writer(f)
        writer.writerow(
            [
            "ID",
            "Título",
            "Preço",
            "Ano",
            "Quilometragem",
            "Transmissão",
            "Teto de abrir",
            "Teto panorâmico",
            "Câmara de marcha-atrás",
            "Câmara 360",
            "Sensor de estacionamento dianteiro",
            "Sensor de estacionamento traseiro",
            "Assistente de estacionamento",
            "Sistema de estacionamento autónomo",
            "Apple CarPlay",
            "Android Auto",
            "Bluetooth",
            "Porta USB",
            "Carregador de smartphone wireless",
            "Sistema de navegação",
            "Estofos em alcântara",
            "Estofos em pele",
            "Estofos em tecido",
            "Banco do condutor aquecido",
            "Banco do passageiro aquecido",
            "Banco do condutor com regulação eléctrica",
            "Bancos com memória",
            "Fecho central sem chave",
            "Arranque sem chave",
            "Cruise Control",
            "Cruise Control adaptativo",
            "Cruise Control Predictivo",
            "Suspensão desportiva",
            "Retrovisores exteriores eletricamente retrateis",
            "Assistente de ângulo morto",
            "Assistente de mudança de faixa",
            "Controlo de proximidade",
            "Condução autónoma básica",
            "URL",
            ]
        )

        for url in links:
            dados_anuncio = extrair_dados_do_anuncio(url)
            writer.writerow(
            [
                dados_anuncio.get("ID"),
                dados_anuncio.get("Título"),
                dados_anuncio.get("Preço"),
                dados_anuncio.get("Ano"),
                dados_anuncio.get("Quilometragem"),
                dados_anuncio.get("Transmissão"),
                dados_anuncio.get("Teto de abrir"),
                dados_anuncio.get("Teto panorâmico"),
                dados_anuncio.get("Câmara de marcha-atrás"),
                dados_anuncio.get("Câmara 360"),
                dados_anuncio.get("Sensor de estacionamento dianteiro"),
                dados_anuncio.get("Sensor de estacionamento traseiro"),
                dados_anuncio.get("Assistente de estacionamento"),
                dados_anuncio.get("Sistema de estacionamento autónomo"),
                dados_anuncio.get("Apple CarPlay"),
                dados_anuncio.get("Android Auto"),
                dados_anuncio.get("Bluetooth"),
                dados_anuncio.get("Porta USB"),
                dados_anuncio.get("Carregador de smartphone wireless"),
                dados_anuncio.get("Sistema de navegação"),
                dados_anuncio.get("Estofos em alcântara"),
                dados_anuncio.get("Estofos em pele"),
                dados_anuncio.get("Estofos em tecido"),
                dados_anuncio.get("Banco do condutor aquecido"),
                dados_anuncio.get("Banco do passageiro aquecido"),
                dados_anuncio.get("Banco do condutor com regulação eléctrica"),
                dados_anuncio.get("Bancos com memória"),
                dados_anuncio.get("Fecho central sem chave"),
                dados_anuncio.get("Arranque sem chave"),
                dados_anuncio.get("Cruise Control"),
                dados_anuncio.get("Cruise Control adaptativo"),
                dados_anuncio.get("Cruise Control Predictivo"),
                dados_anuncio.get("Suspensão desportiva"),
                dados_anuncio.get("Retrovisores exteriores eletricamente retrateis"),
                dados_anuncio.get("Assistente de ângulo morto"),
                dados_anuncio.get("Assistente de mudança de faixa"),
                dados_anuncio.get("Controlo de proximidade"),
                dados_anuncio.get("Condução autónoma básica"),
                dados_anuncio.get("URL"),
            ]
            )
