from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.keys import Keys

import re
import csv

import time
import sys


# Dados do carro (podes alterar estes valores)
# marca = "Mercedes-Benz"
# modelo = "Classe C"
# submodelo = "C 220"
# ano_init = "2021"
# ano_fin = "2021"
# combustivel = "Diesel"
# descricao = "Avantgarde"

# sys.argv[0] é o nome do script
# os argumentos começam a partir de sys.argv[1]

if len(sys.argv) < 8:
    print("Número insuficiente de argumentos.")
    print("Uso: script.py marca modelo submodelo ano_init ano_fin combustivel descricao")
    exit(1)

marca = sys.argv[1]
modelo = sys.argv[2]
submodelo = sys.argv[3]
ano_init = sys.argv[4]
ano_fin = sys.argv[5]

combustivel = sys.argv[6]

descricao = sys.argv[7]


# Configurações do Chrome
options = Options()
options.add_argument("--start-maximized")
options.add_argument("--disable-notifications")

# Iniciar o driver com webdriver_manager (sem CHROME_DRIVER_PATH)
driver = webdriver.Chrome(
    service=Service(ChromeDriverManager().install()), options=options
)
wait = WebDriverWait(driver, 20)

# Abrir página de criação de anúncio
driver.get("https://www.standvirtual.com")
time.sleep(3)

# Aceitar cookies se aparecer
try:
    aceitar_cookies = wait.until(
        EC.element_to_be_clickable((By.ID, "onetrust-accept-btn-handler"))
    )
    aceitar_cookies.click()
    print("Cookies aceites.")
except:
    print("Nenhum botão de cookies encontrado.")


# Clicar em "Pesquisa Avançada"
try:
    pesquisa_avancada = wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, "//button[.//span[text()='Pesquisa avançada']]")
        )
    )
    pesquisa_avancada.click()
    print("Clicou em Pesquisa Avançada.")
except Exception as e:
    print("Erro ao clicar em Pesquisa Avançada:", e)


# --- SELEÇÃO DA MARCA ---
try:
    input_marca = wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'input[data-testid="custom-multiselect"]')
        )
    )
    input_marca.clear()
    input_marca.send_keys(marca)

    # Clicar no botão para abrir o dropdown da marca
    botao_dropdown_marca = wait.until(
        EC.element_to_be_clickable(
            (
                By.CSS_SELECTOR,
                'div[data-testid="filter_enum_make"] button[data-testid="arrow"]',
            )
        )
    )
    botao_dropdown_marca.click()

    # Esperar a lista aparecer e encontrar o item da marca
    li_marca = wait.until(
        EC.element_to_be_clickable((By.XPATH, f"//li//p[contains(text(), '{marca}')]"))
    )

    # O <p> está dentro do <label>, que está dentro do div, que tem o checkbox
    # Então sobe até o checkbox e clica nele
    checkbox = li_marca.find_element(
        By.XPATH, ".//preceding::input[@type='checkbox'][1]"
    )
    checkbox.click()

    # Clicar no botão para fechar o dropdown (o botão que tem arrowUp)
    botao_fechar_dropdown = wait.until(
        EC.element_to_be_clickable(
            (
                By.CSS_SELECTOR,
                'div[data-testid="filter_enum_make"] button[data-testid="arrow"]',
            )
        )
    )
    botao_fechar_dropdown.click()
    print(f"Marca '{marca}' selecionada na checkbox.")
except Exception as e:
    print(f"Erro ao adicionar marca: ", e)
time.sleep(3)

try:
    # --- SELEÇÃO DO MODELO ---
    # Clicar no botão para abrir o dropdown do modelo
    botao_dropdown_modelo = wait.until(
        EC.element_to_be_clickable(
            (
                By.CSS_SELECTOR,
                'div[data-testid="filter_enum_model"] button[data-testid="arrow"]',
            )
        )
    )
    botao_dropdown_modelo.click()

    # Esperar a lista aparecer e encontrar o item do modelo
    li_modelo = wait.until(
        EC.element_to_be_clickable((By.XPATH, f"//li//p[contains(text(), '{modelo}')]"))
    )

    # Selecionar o checkbox do modelo
    checkbox_modelo = li_modelo.find_element(
        By.XPATH, ".//preceding::input[@type='checkbox'][1]"
    )
    checkbox_modelo.click()

    # Fechar dropdown do modelo
    botao_dropdown_modelo.click()
    print(f"Modelo '{modelo}' selecionado.")
except Exception as e:
    print(f"Erro ao adicionar modelo: ", e)
time.sleep(3)

# --- SELEÇÃO DO SUB-MODELO ---
try:
    # Scroll até o dropdown (opcional, dependendo da visibilidade)
    submodelo_container = wait.until(
        EC.presence_of_element_located(
            (By.CSS_SELECTOR, "[data-testid='filter_enum_engine_code']")
        )
    )
    ActionChains(driver).move_to_element(submodelo_container).perform()

    # 1. Clica no botão para expandir o dropdown
    expand_button = submodelo_container.find_element(
        By.CSS_SELECTOR, "[data-testid='dropdown-expand-button']"
    )
    expand_button.click()

    # 2. Espera os itens aparecerem
    dropdown_items = wait.until(
        EC.presence_of_all_elements_located(
            (By.CSS_SELECTOR, "[data-testid='dropdown-item']")
        )
    )

    # 3. Percorre e clica no item com texto "submodelo"
    for item in dropdown_items:
        span = item.find_element(By.CSS_SELECTOR, "[data-testid='dropdown-item-text']")
        if submodelo in span.text:
            span.click()
            break
except Exception as e:
    print(f"Erro ao adicionar submodelo: ", e)
time.sleep(3)

# --- SELEÇÃO DO ANO DE:  ---
try:
    ano_de_input = wait.until(
        EC.presence_of_element_located((By.CSS_SELECTOR, 'input[placeholder="Ano de"]'))
    )

    # Clica no campo para ativar (caso necessário)
    ano_de_input.click()

    # Limpa e preenche o ano
    ano_de_input.clear()
    ano_de_input.send_keys(str(ano_init))
    print(f"Ano de '{ano_init}' selecionado.")
except Exception as e:
    print(f"Erro ao adicionar Ano de: ", e)
time.sleep(3)


# --- SELEÇÃO DO ANO ATE:  ---
try:

    # Espera e encontra o input com placeholder "Ano até"
    ano_ate_input = wait.until(
        EC.presence_of_element_located(
            (By.CSS_SELECTOR, 'input[placeholder="Ano até"]')
        )
    )

    # Clica no campo para ativar (caso necessário)
    ano_ate_input.click()
    time.sleep(0.5)

    # Limpa e preenche o ano
    ano_ate_input.clear()

    ano_ate_input.send_keys(str(ano_fin))
    ano_ate_input.send_keys(Keys.ENTER)
    print(f"Ano até '{ano_fin}' selecionado.")
except Exception as e:
    print(f"Erro ao adicionar Ano até: ", e)
time.sleep(3)


# --- SELEÇÃO DO COMBUSTIVEL ---
try:
    # Clicar no botão para abrir o dropdown da marca
    botao_dropdown_fuel = wait.until(
        EC.element_to_be_clickable(
            (
                By.CSS_SELECTOR,
                'div[data-testid="filter_enum_fuel_type"] button[data-testid="arrow"]',
            )
        )
    )
    botao_dropdown_fuel.click()

    # Esperar a lista aparecer e encontrar o item da marca
    li_fuel = wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, f"//li//p[contains(text(), '{combustivel}')]")
        )
    )

    # O <p> está dentro do <label>, que está dentro do div, que tem o checkbox
    # Então sobe até o checkbox e clica nele
    checkbox = li_fuel.find_element(
        By.XPATH, ".//preceding::input[@type='checkbox'][1]"
    )
    checkbox.click()

    # Clicar no botão para fechar o dropdown (o botão que tem arrowUp)
    botao_fechar_dropdown_fuel = wait.until(
        EC.element_to_be_clickable(
            (
                By.CSS_SELECTOR,
                'div[data-testid="filter_enum_fuel_type"] button[data-testid="arrow"]',
            )
        )
    )
    botao_fechar_dropdown_fuel.click()
    print(f"Combustivel '{combustivel}' selecionada na checkbox.")
except Exception as e:
    print(f"Erro ao adicionar combustivel: ", e)
time.sleep(3)

try:
    input_pesquisa = wait.until(
        EC.presence_of_element_located(
            (By.CSS_SELECTOR, 'input[placeholder="Procurar modelo, versão e outros"]')
        )
    )
    input_pesquisa.clear()
    input_pesquisa.send_keys(descricao)
    print(f"Campo de pesquisa preenchido com: {descricao}")
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

time.sleep(3)


# Encontra o elemento <p> que contém o texto "Número de anúncios:"
# element = driver.find_element(By.XPATH, "//p[contains(text(), 'Número de anúncios:')]")

# # Dentro desse <p>, procura a tag <b> que contém o número
# numero_texto = element.find_element(By.TAG_NAME, "b").text

# # Converte para inteiro, se quiseres
# num_anuncios = int(numero_texto)


# print(num_anuncios)
# Espera os artigos carregarem
wait.until(EC.presence_of_all_elements_located((By.CSS_SELECTOR, "article[data-id]")))

articles = driver.find_elements(By.CSS_SELECTOR, "article[data-id]")

dados = []

for art in articles:
    try:
        # ID (data-id)
        ad_id = art.get_attribute("data-id").strip()

        # Título e URL
        link_element = art.find_element(By.CSS_SELECTOR, "h2 a")
        titulo = link_element.text.strip()
        print(f"Título: {titulo}")
        url = link_element.get_attribute("href").strip()

        # Preço
        try:
            preco_element = art.find_element(
                By.CSS_SELECTOR, "h3.efzkujb1.ooa-1d59yzt"
            )
            preco = preco_element.text.strip()
        except:
            preco = ""

        print(f"Preço: {preco}")

        try:
            km_element = art.find_element(
                By.CSS_SELECTOR, 'dd[data-parameter="mileage"]'
            )
            quilometragem = km_element.text.strip()
        except:
            quilometragem = ""

        print(f"KM: {quilometragem}")

        try:
            transmissao_element = art.find_element(
                By.CSS_SELECTOR, 'dd[data-parameter="gearbox"]'
            )
            transmissao = transmissao_element.text.strip()
        except:
            transmissao = ""

        print(f"KM: {transmissao}")

        try:
            ano_element = art.find_element(
                By.CSS_SELECTOR, 'dd[data-parameter="first_registration_year"]'
            )
            ano = ano_element.text.strip()
        except:
            ano = ""

        print(f"ANO: {ano}")
        
            
        # Localidade e tempo de publicação
        try:
            dl_element = art.find_element(By.CSS_SELECTOR, "dl.ooa-1o0axny.efvvx2t0")
            dd_elements = dl_element.find_elements(
                By.CSS_SELECTOR, "dd.ooa-1jb4k0u.efvvx2t1"
            )
            if dd_elements:
                localidade = dd_elements[0].text.strip()
                tempo_pub = dd_elements[1].text.strip()
            else:
                localidade = ""
                tempo_pub = ""
        except:
            localidade = ""

        print(f"localidade: {localidade}")
        print(f"tempo_pub: {tempo_pub}")

# Verificação de ano válido e dentro do intervalo desejado


        dados.append(
            [ad_id, titulo, preco, ano, quilometragem, transmissao, localidade, tempo_pub, url]
        )

    except Exception as e:
        print("Erro ao extrair dados de um anúncio:", e)

# Grava em CSV
# Caminho absoluto para a pasta AdSearch na raiz do projeto
import os

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
            "Localidade",
            "Tempo Publicação",
            "URL",
        ]
    )
    writer.writerows(dados)

print(f"{len(dados)} anúncios exportados para csv")

# Esperar uns segundos para ver resultado
time.sleep(40)
driver.quit()
