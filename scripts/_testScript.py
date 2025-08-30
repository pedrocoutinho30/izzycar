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


def extrair_dados_do_anuncio(url):
    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
    resp = requests.get(url, headers=headers)
    resp.raise_for_status()

    soup = BeautifulSoup(resp.text, "html.parser")

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

    print("-----")
    # Encontrar o div com class_="offer-price__number"
    # preco = soup.find("span", class_="offer-price__number").get_text(strip=True)
    # print("Preço:", preco)

    # # Encontrar o div com data-testid="make"
    # div_marca = soup.find("div", {"data-testid": "make"})
    # valor_marca = div_marca.find_all("p")[1].get_text(strip=True)
    # print("Marca:", valor_marca)

    # # Encontrar o div com data-testid="model"
    # div_modelo = soup.find("div", {"data-testid": "model"})
    # # Buscar o segundo <p> dentro desse div (onde está o valor)
    # valor_modelo = div_modelo.find_all("p")[1].get_text(strip=True)
    # print("Modelo:", valor_modelo)

    # # Encontrar o div com data-testid="engine_code"
    # div_submodelo = soup.find("div", {"data-testid": "engine_code"})
    # # Buscar o segundo <p> dentro desse div (onde está o valor)
    # valor_submodelo = div_submodelo.find_all("p")[1].get_text(strip=True)
    # print("Submodelo:", valor_submodelo)

    # # Encontrar o div com data-testid="version"
    # div_versao = soup.find("div", {"data-testid": "version"})
    # valor_versao = div_versao.find_all("p")[1].get_text(strip=True)
    # print("Versão:", valor_versao)

    # # Encontrar o div com data-testid="cor"
    # div_cor = soup.find("div", {"data-testid": "color"})
    # valor_cor = div_cor.find_all("p")[1].get_text(strip=True)
    # print("Cor:", valor_cor)

    # # Encontrar o div com data-testid="door_count"
    # div_portas = soup.find("div", {"data-testid": "door_count"})
    # valor_portas = div_portas.find_all("p")[1].get_text(strip=True)
    # print("Portas:", valor_portas)

    # # Encontrar o div com data-testid="fuel_type"
    # div_combustivel = soup.find("div", {"data-testid": "fuel_type"})
    # valor_combustivel = div_combustivel.find_all("p")[1].get_text(strip=True)
    # print("Combustível:", valor_combustivel)

    # # Encontrar o div com data-testid="engine_capacity"
    # div_capacidade_motor = soup.find("div", {"data-testid": "engine_capacity"})
    # valor_capacidade_motor = div_capacidade_motor.find_all("p")[1].get_text(strip=True)
    # print("Capacidade do motor:", valor_capacidade_motor)

    # # Encontrar o div com data-testid="engine_power"
    # div_potencia = soup.find("div", {"data-testid": "engine_power"})
    # valor_potencia = div_potencia.find_all("p")[1].get_text(strip=True)
    # print("Potência:", valor_potencia)

    # # Encontrar o div com data-testid="body_type"
    # div_tipo_carroceria = soup.find("div", {"data-testid": "body_type"})
    # valor_tipo_carroceria = div_tipo_carroceria.find_all("p")[1].get_text(strip=True)
    # print("Tipo de carroceria:", valor_tipo_carroceria)

    # # Encontrar o div com data-testid="gearbox"
    # div_caixa_marchas = soup.find("div", {"data-testid": "gearbox"})
    # valor_caixa_marchas = div_caixa_marchas.find_all("p")[1].get_text(strip=True)
    # print("Caixa de marchas:", valor_caixa_marchas)

    # # Encontrar o div com data-testid="gearbox_shifts"
    # div_marchas = soup.find("div", {"data-testid": "gearbox_shifts"})
    # valor_marchas = div_marchas.find_all("p")[1].get_text(strip=True)
    # print("Marchas:", valor_marchas)

    # # Encontrar o div com data-testid="transmission"
    # div_transmissao = soup.find("div", {"data-testid": "transmission"})
    # valor_transmissao = div_transmissao.find_all("p")[1].get_text(strip=True)
    # print("Transmissão:", valor_transmissao)

    # # Encontrar o div com data-testid="first_registration_month"
    # div_mes_registro = soup.find("div", {"data-testid": "first_registration_month"})
    # valor_mes_registro = div_mes_registro.find_all("p")[1].get_text(strip=True)
    # print("Mês de registro:", valor_mes_registro)

    # # Encontrar o div com data-testid="first_registration_year"
    # div_ano_registro = soup.find("div", {"data-testid": "first_registration_year"})
    # valor_ano_registro = div_ano_registro.find_all("p")[1].get_text(strip=True)
    # print("Ano de registro:", valor_ano_registro)

    # # Encontrar o div com data-testid="mileage"
    # div_quilometragem = soup.find("div", {"data-testid": "mileage"})
    # valor_quilometragem = div_quilometragem.find_all("p")[1].get_text(strip=True)
    # print("Quilometragem:", valor_quilometragem)
    # print("-----")

    # Criar um dicionário com os dados extraídos
    # dados_anuncio = {
    #     "Preço": preco,
    #     "Marca": marca,
    #     "Modelo": modelo,
    #     "Submodelo": submodelo,
    #     "Versão": versao,
    #     "Cor": cor,
    #     "Portas": portas,
    #     "Combustível": combustivel,
    #     "Capacidade do motor": capacidade_motor,
    #     "Potência": potencia,
    #     "Tipo de carroceria": tipo_carroceria,
    #     "Caixa de marchas": caixa_marchas,
    #     "Marchas": marchas,
    #     "Transmissão": transmissao,
    #     "Mês de registro": mes_registro,
    #     "Ano de registro": ano_registro,
    #     "Quilometragem": quilometragem,
    #     "URL": url,
    # }

    dados_anuncio = {
        "ID": 1,
        "Título": marca + " " + modelo + " " + submodelo + " " + versao,
        "Preço": preco,
        "Ano": ano_registro,
        "Quilometragem": quilometragem,
        "Transmissão": marchas,
        # "Localidade"
        # "Publicação"
        "URL": url,
    }



    # Retornar o dicionário com os dados do anúncio
    return dados_anuncio

    # def get_anuncios(
    #     makeToSearch,
    #     modelToSearch,
    #     submodelToSearch,
    #     segment,
    #     yearFrom,
    #     yearTo,
    #     fuelType,
    #     description,
    # ):
    #     url = "https://www.standvirtual.com/carros"

    #     # Configurações do Chrome
    #     options = Options()
    #     options.add_argument("--start-maximized")
    #     options.add_argument("--disable-notifications")

    #     # Iniciar o driver com webdriver_manager (sem CHROME_DRIVER_PATH)
    #     driver = webdriver.Chrome(
    #         service=Service(ChromeDriverManager().install()), options=options
    #     )
    #     wait = WebDriverWait(driver, 20)

    #     # Abrir página de criação de anúncio
    #     driver.get(url)
    #     time.sleep(1)

    #     # Aceitar cookies se aparecer
    #     try:
    #         aceitar_cookies = wait.until(
    #             EC.element_to_be_clickable((By.ID, "onetrust-accept-btn-handler"))
    #         )
    #         aceitar_cookies.click()
    #         print("Cookies aceites.")
    #     except:
    #         print("Nenhum botão de cookies encontrado.")

    #     set_fuel_type(driver, wait, fuelType)
    #     time.sleep(2)

    #     time.sleep(1)
    #     set_make(driver, wait, makeToSearch)
    #     time.sleep(2)
    #     set_model(driver, wait, modelToSearch)
    #     time.sleep(2)
    #     set_submodel(driver, wait, submodelToSearch)
    #     time.sleep(2)
    #     # set_segment(driver, wait, segment)
    #     time.sleep(2)
    #     set_year_from(driver, wait, yearFrom)
    #     time.sleep(2)
    #     set_year_to(driver, wait, yearTo)
    #     time.sleep(2)
    #     set_fuel_type(driver, wait, fuelType)
    #     time.sleep(2)
    #     set_description(driver, wait, description)
    #     time.sleep(2)

    #     # Espera os artigos carregarem
    #     wait.until(
    #         EC.presence_of_all_elements_located((By.CSS_SELECTOR, "article[data-id]"))
    #     )

    #     articles = driver.find_elements(By.CSS_SELECTOR, "article[data-id]")
    #     print(f"Encontrados {len(articles)} anúncios.")

    #     driver.quit()

    # def set_make(driver, wait, makeToSearch):
    #     try:
    #         input_marca = wait.until(
    #             EC.element_to_be_clickable(
    #                 (By.CSS_SELECTOR, 'input[data-testid="custom-multiselect"]')
    #             )
    #         )
    #         input_marca.clear()
    #         input_marca.send_keys(makeToSearch)

    #         # Clicar no botão para abrir o dropdown da marca
    #         botao_dropdown_marca = wait.until(
    #             EC.element_to_be_clickable(
    #                 (
    #                     By.CSS_SELECTOR,
    #                     'div[data-testid="filter_enum_make"] button[data-testid="arrow"]',
    #                 )
    #             )
    #         )
    #         botao_dropdown_marca.click()

    #         # Esperar a lista aparecer e encontrar o item da marca
    #         li_marca = wait.until(
    #             EC.element_to_be_clickable(
    #                 (By.XPATH, f"//li//p[contains(text(), '{makeToSearch}')]")
    #             )
    #         )

    #         # O <p> está dentro do <label>, que está dentro do div, que tem o checkbox
    #         # Então sobe até o checkbox e clica nele
    #         checkbox = li_marca.find_element(
    #             By.XPATH, ".//preceding::input[@type='checkbox'][1]"
    #         )
    #         checkbox.click()

    #         # Clicar no botão para fechar o dropdown (o botão que tem arrowUp)
    #         botao_fechar_dropdown = wait.until(
    #             EC.element_to_be_clickable(
    #                 (
    #                     By.CSS_SELECTOR,
    #                     'div[data-testid="filter_enum_make"] button[data-testid="arrow"]',
    #                 )
    #             )
    #         )
    #         botao_fechar_dropdown.click()
    #         print(f"Marca '{makeToSearch}' selecionada na checkbox.")
    #     except Exception as e:
    #         print(f"Erro ao adicionar marca: ", e)

    # def set_model(driver, wait, modelToSearch):
    #     # --- SELEÇÃO DO MODELO ---
    #     try:

    #         # Clicar no botão para abrir o dropdown do modelo
    #         botao_dropdown_modelo = wait.until(
    #             EC.element_to_be_clickable(
    #                 (
    #                     By.CSS_SELECTOR,
    #                     'div[data-testid="filter_enum_model"] button[data-testid="arrow"]',
    #                 )
    #             )
    #         )
    #         botao_dropdown_modelo.click()

    #         # Esperar a lista aparecer e encontrar o item do modelo
    #         li_modelo = wait.until(
    #             EC.element_to_be_clickable(
    #                 (By.XPATH, f"//li//p[contains(text(), '{modelToSearch}')]")
    #             )
    #         )

    #         # Selecionar o checkbox do modelo
    #         checkbox_modelo = li_modelo.find_element(
    #             By.XPATH, ".//preceding::input[@type='checkbox'][1]"
    #         )
    #         checkbox_modelo.click()

    #         # Fechar dropdown do modelo
    #         botao_dropdown_modelo.click()
    #         print(f"Modelo '{modelToSearch}' selecionado.")
    #     except Exception as e:
    #         print(f"Erro ao adicionar modelo: ", e)

    # def set_submodel(driver, wait, submodelToSearch):
    #     # --- SELEÇÃO DO SUB-MODELO ---
    #     try:
    #         # Scroll até o dropdown (opcional, dependendo da visibilidade)
    #         submodelo_container = wait.until(
    #             EC.presence_of_element_located(
    #                 (By.CSS_SELECTOR, "[data-testid='filter_enum_engine_code']")
    #             )
    #         )
    #         ActionChains(driver).move_to_element(submodelo_container).perform()

    #         # 1. Clica no botão para expandir o dropdown
    #         expand_button = submodelo_container.find_element(
    #             By.CSS_SELECTOR, "[data-testid='dropdown-expand-button']"
    #         )
    #         expand_button.click()

    #         # 2. Espera os itens aparecerem
    #         dropdown_items = wait.until(
    #             EC.presence_of_all_elements_located(
    #                 (By.CSS_SELECTOR, "[data-testid='dropdown-item']")
    #             )
    #         )

    #         # 3. Percorre e clica no item com texto "submodelo"
    #         for item in dropdown_items:
    #             span = item.find_element(
    #                 By.CSS_SELECTOR, "[data-testid='dropdown-item-text']"
    #             )
    #             if submodelToSearch in span.text:
    #                 span.click()
    #                 break
    #     except Exception as e:
    #         print(f"Erro ao adicionar submodelo: ", e)

    # def set_segment(driver, wait, segment):
    #     # --- SELEÇÃO DO SEGMENTO ---
    #     try:
    #         # Abrir dropdown de segmento
    #         botao_dropdown_segmento = wait.until(
    #             EC.element_to_be_clickable(
    #                 (
    #                     By.CSS_SELECTOR,
    #                     'div[data-testid="filter_enum_body_type"] button[data-testid="arrow"]',
    #                 )
    #             )
    #         )
    #         print("Clicando no botão de dropdown de segmento...")
    #         botao_dropdown_segmento.click()

    #         # Esperar o <li> correspondente ao segmento
    #         li_segmento = wait.until(
    #             EC.presence_of_element_located(
    #                 (By.XPATH, f"//li[.//p[contains(text(), '{segment}')]]")
    #             )
    #         )

    #         # Esperar o checkbox correspondente estar clicável
    #         checkbox = wait.until(
    #             EC.element_to_be_clickable(
    #                 (
    #                     By.XPATH,
    #                     f"//li[.//p[contains(text(), '{segment}')]]//input[@type='checkbox']",
    #                 )
    #             )
    #         )

    #         # Scroll até o checkbox e clique
    #         driver.execute_script("arguments[0].scrollIntoView(true);", checkbox)
    #         time.sleep(0.3)
    #         checkbox.click()

    #         print(f"Segmento '{segment}' selecionado com sucesso.")

    #         # Fechar o dropdown
    #         botao_fechar_dropdown_segmento = wait.until(
    #             EC.element_to_be_clickable(
    #                 (
    #                     By.CSS_SELECTOR,
    #                     'div[data-testid="filter_enum_body_type"] button[data-testid="arrow"]',
    #                 )
    #             )
    #         )
    #         botao_fechar_dropdown_segmento.click()

    #     except Exception as e:
    #         print(f"Erro ao adicionar segmento '{segment}': {e}")

    # def set_year_from(driver, wait, yearFrom):
    #     # --- SELEÇÃO DO ANO DE:  ---
    #     try:
    #         ano_de_input = wait.until(
    #             EC.presence_of_element_located(
    #                 (By.CSS_SELECTOR, 'input[placeholder="Ano de"]')
    #             )
    #         )

    #         # Clica no campo para ativar (caso necessário)
    #         ano_de_input.click()

    #         # Limpa e preenche o ano
    #         ano_de_input.clear()
    #         ano_de_input.send_keys(str(yearFrom))
    #         print(f"Ano de '{yearFrom}' selecionado.")
    #     except Exception as e:
    #         print(f"Erro ao adicionar Ano de: ", e)

    # def set_year_to(driver, wait, yearTo):
    #     # --- SELEÇÃO DO ANO ATE:  ---
    #     try:

    #         # Espera e encontra o input com placeholder "Ano até"
    #         ano_ate_input = wait.until(
    #             EC.presence_of_element_located(
    #                 (By.CSS_SELECTOR, 'input[placeholder="Ano até"]')
    #             )
    #         )

    #         # Clica no campo para ativar (caso necessário)
    #         ano_ate_input.click()
    #         time.sleep(0.5)

    #         # Limpa e preenche o ano
    #         ano_ate_input.clear()

    #         ano_ate_input.send_keys(str(yearTo))
    #         ano_ate_input.send_keys(Keys.ENTER)
    #         print(f"Ano até '{yearTo}' selecionado.")
    #     except Exception as e:
    #         print(f"Erro ao adicionar Ano até: ", e)

    # def set_fuel_type(driver, wait, fuelType):
    #     try:
    #         # Clicar no botão para abrir o dropdown da marca
    #         botao_dropdown_fuel = wait.until(
    #             EC.element_to_be_clickable(
    #                 (
    #                     By.CSS_SELECTOR,
    #                     'div[data-testid="filter_enum_fuel_type"] button[data-testid="arrow"]',
    #                 )
    #             )
    #         )
    #         botao_dropdown_fuel.click()

    #         # Esperar a lista aparecer e encontrar o item da marca
    #         li_fuel = wait.until(
    #             EC.element_to_be_clickable(
    #                 (By.XPATH, f"//li//p[contains(text(), '{fuelType}')]")
    #             )
    #         )

    #         # O <p> está dentro do <label>, que está dentro do div, que tem o checkbox
    #         # Então sobe até o checkbox e clica nele
    #         checkbox = li_fuel.find_element(
    #             By.XPATH, ".//preceding::input[@type='checkbox'][1]"
    #         )
    #         checkbox.click()

    #         # Clicar no botão para fechar o dropdown (o botão que tem arrowUp)
    #         botao_fechar_dropdown_fuel = wait.until(
    #             EC.element_to_be_clickable(
    #                 (
    #                     By.CSS_SELECTOR,
    #                     'div[data-testid="filter_enum_fuel_type"] button[data-testid="arrow"]',
    #                 )
    #             )
    #         )
    #         botao_fechar_dropdown_fuel.click()
    #         print(f"Combustivel '{fuelType}' selecionada na checkbox.")
    #     except Exception as e:
    #         print(f"Erro ao adicionar combustivel: ", e)

    # def set_description(driver, wait, description):
    try:
        input_pesquisa = wait.until(
            EC.presence_of_element_located(
                (
                    By.CSS_SELECTOR,
                    'input[placeholder="Procurar modelo, versão e outros"]',
                )
            )
        )
        input_pesquisa.clear()
        input_pesquisa.send_keys(description)
        print(f"Campo de pesquisa preenchido com: {description}")
        time.sleep(3)
        # Clicar no botão de pesquisa ao lado do campo
        # Seleciona o botão que está no mesmo container do input
        container = input_pesquisa.find_element(
            By.XPATH,
            "./ancestor::div[contains(@class, 'ooa-1qsx3gp')]/parent::div/parent::div",
        )
        botao_pesquisa = container.find_element(
            By.CSS_SELECTOR, 'button[data-button-variant="primary"]'
        )
        botao_pesquisa.click()
        print("Botão de pesquisa clicado.")
    except Exception as e:
        print("Erro ao preencher/clicar no campo de pesquisa:", e)


def get_anuncios_by_url(url):

    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
    resp = requests.get(url, headers=headers)
    resp.raise_for_status()

    soup = BeautifulSoup(resp.text, "html.parser")

    # Encontrar todas as tags <a> com target="_self"
    links = []
    for a in soup.find_all("a", target="_self"):
        href = a.get("href")
        if href:
            links.append(href)
    return links


if __name__ == "__main__":
    # Testar a função get_anuncios
    # makeToSearch = "Mercedes-Benz"
    # modelToSearch = "Classe CLA"
    # submodelToSearch = "CLA 250"
    # segment = "Carrinha"
    # yearFrom = "2020"
    # yearTo = "2022"
    # fuelType = "Híbrido (Gasolina)"
    # description = "AMG"
    # get_anuncios(
    #     makeToSearch,
    #     modelToSearch,
    #     submodelToSearch,
    #     segment,
    #     yearFrom,
    #     yearTo,
    #     fuelType,
    #     description,
    # )

    marca = sys.argv[1]
    modelo = sys.argv[2]
    submodelo = sys.argv[3]
    ano_init = sys.argv[4]
    ano_fin = sys.argv[5]

    combustivel = sys.argv[6]

    descricao = sys.argv[7]

    # url = "https://www.standvirtual.com/carros/mercedes-benz/cla-250/desde-2020/q-amg?search%5Bfilter_enum_engine_code%5D=classe-cla&search%5Bfilter_enum_fuel_type%5D=plugin-hybrid&search%5Bfilter_float_first_registration_year%3Ato%5D=2022&search%5Badvanced_search_expanded%5D=true"
    url = sys.argv[8]
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
            "Marca",
            "Modelo",
            "Submodelo",
            "Versão",
            # "Cor",
            # "Portas",
            "Combustível",
            # "Capacidade do motor",
            # "Potência",
            # "Tipo de carroceria",
            #  "Caixa de marchas",
            # "Marchas",
            "Mês de registro",
            "Ano de registro",
            "Quilometragem",
            "Transmissão",
            "Preço",
            # "Localidade",
            # "Tempo Publicação",
            "URL",
        ]
    )


    for url in links:
        dados_anuncio = extrair_dados_do_anuncio(url)
        print("Dados do anúncio:", dados_anuncio.values())
        writer.writerow([
            dados_anuncio.get("Marca"),
            dados_anuncio.get("Modelo"),
            dados_anuncio.get("Submodelo"),
            dados_anuncio.get("Versão"),
            # dados_anuncio.get("Cor"),
            # dados_anuncio.get("Portas"),
            dados_anuncio.get("Combustível"),
            # dados_anuncio.get("Capacidade do motor"),
            # dados_anuncio.get("Potência"),
            # dados_anuncio.get("Tipo de carroceria"),
            # dados_anuncio.get("Caixa de marchas"),
            # dados_anuncio.get("Marchas"),
            # dados_anuncio.get("Mês de registro"),
            dados_anuncio.get("Ano de registro"),
            dados_anuncio.get("Quilometragem"),
            dados_anuncio.get("Transmissão"),
            dados_anuncio.get("Preço"),  # Localidade (não extraído)
            
            dados_anuncio.get("URL"),
        ])

