from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.keys import Keys

import traceback
import re
import csv
import time
import sys

country = "Germany"
make = "Mercedes-Benz"
model = "CLA 250"
additional_info = "AMG"
fuel_type = "Hybrid (Electric/Gasoline)"  # Hybrid (Electric/Gasoline) Hybrid (Electric/Diesel) Gasoline Diesel Electric
from_date = "2021"
to_date = "2022"
from_price = ""
to_price = "€30,000"
from_milage = ""
to_milage = "60,000"


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
driver.get(
    "https://www.autoscout24.com/refinesearch?atype=C&cy=D%2CA%2CB%2CE%2CF%2CI%2CL%2CNL&damaged_listing=exclude&desc=0&powertype=kw&search_id=1hh4wuoay07&sort=standard&source=homepage_search-mask&ustate=N%2CU"
)
print("Aguardando a página carregar...")
time.sleep(2)

# Aceitar cookies se aparecer
try:
    aceitar_cookies = wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'button[data-testid="as24-cmp-accept-all-button"]')
        )
    )
    aceitar_cookies.click()
    print("Cookies aceites.")
except:
    print("Nenhum botão de cookies encontrado.")


try:
    print("Iniciar preenchimento do campo 'country'...")

    button = driver.find_element(By.ID, "country-input")
    button.click()

    # 2. Espera a lista aparecer
    wait = WebDriverWait(driver, 10)
    suggestions = wait.until(
        EC.visibility_of_element_located((By.ID, "country-input-suggestions"))
    )

    # 3. Procura o <li> que tenha o texto "2023"
    items = driver.find_elements(
        By.CSS_SELECTOR, "#country-input-suggestions > li.suggestion-item"
    )

    for item in items:
        if country in item.text:
            print(f"Item encontrado: {item.text}", country)

            item.click()
            break
    print(f"Campo 'country' preenchido com: {country}")
except Exception as e:
    print("Erro ao preencher o campo 'country':", e)


# Preencher o campo de marca (make)
try:
    print("Iniciar preenchimento do campo 'marca'...")
    make_input = wait.until(
        EC.element_to_be_clickable((By.ID, "make-input-primary-filter"))
    )
    make_input.clear()
    make_input.send_keys(make)
    print(f"Campo 'make' preenchido com: {make}")
    time.sleep(1)  # Aguarda sugestões aparecerem (opcional)
    make_input.send_keys(Keys.ENTER)  # Seleciona a sugestão (opcional)
except Exception as e:
    print("Erro ao preencher o campo 'make':", e)

time.sleep(3)

# Preencher o campo de modelo (model)
try:
    print("Iniciar preenchimento do campo 'modelo'...")
    model_input = WebDriverWait(driver, 20).until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, "#select-model-container input.input")
        )
    )

    # Confirma se está habilitado
    print("Disabled:", model_input.get_attribute("disabled"))  # Deve imprimir None

    if model_input.is_enabled():
        # Preencher o valor do modelo
        model_input.clear()
        model_input.send_keys(model)
        print(f"Campo 'modelo' preenchido com: {model}")
        time.sleep(1)  # Aguarda sugestões aparecerem (opcional)
        model_input.send_keys(Keys.ENTER)  # Seleciona a sugestão (opcional)

except Exception as e:
    print(f"Erro ao preencher o campo 'model':", e)
    traceback.print_exc()

time.sleep(3)
try:
    print("Iniciar preenchimento do campo 'adicional'...")
    version_input = wait.until(
        EC.element_to_be_clickable(
            (
                By.CSS_SELECTOR,
                'input[placeholder="e.g. Plus, GTI, etc."][name="Version"]',
            )
        )
    )
    version_input.clear()
    version_input.send_keys(additional_info)
    print(f"Campo 'version' preenchido com: {additional_info}")
    time.sleep(1)  # Aguarda sugestões aparecerem (opcional)
    version_input.send_keys(Keys.ENTER)  # Seleciona a sugestão (opcional)
except Exception as e:
    print("Erro ao preencher o campo 'version':", e)


try:
    print("Iniciar preenchimento do campo 'combustível'...")
    # 1. Clicar no botão para abrir o dropdown
    # 1. Clica para abrir o dropdown
    btn_dropdown = wait.until(EC.element_to_be_clickable((By.ID, "fuel-type-select")))
    btn_dropdown.click()

    # 2. Espera a dropdown ficar visível

    # 3. Busca todos os labels dentro dos itens do dropdown
    labels = driver.find_elements(
        By.CSS_SELECTOR, ".MultiSelect_dropdownOpen__6xodZ ul li label"
    )

    for label in labels:
        texto = label.text.strip()
        if texto.startswith(fuel_type):
            # Clica no label para marcar o checkbox
            label.click()
            break

    # Opcional: fecha o dropdown clicando no botão novamente
    btn_dropdown.click()
    print(f"Campo 'combustível' preenchido com: {fuel_type}")

except Exception as e:
    print("Erro ao preencher o campo 'fuel_type':", e)
    print(traceback.format_exc())

try:
    print("Iniciar preenchimento do campo 'from_date'...")

    button = driver.find_element(By.ID, "firstRegistrationFrom-input")
    button.click()

    # 2. Espera a lista aparecer
    wait = WebDriverWait(driver, 10)
    suggestions = wait.until(
        EC.visibility_of_element_located(
            (By.ID, "firstRegistrationFrom-input-suggestions")
        )
    )

    # 3. Procura o <li> que tenha o texto "2023"
    items = driver.find_elements(
        By.CSS_SELECTOR, "#firstRegistrationFrom-input-suggestions > li.suggestion-item"
    )

    for item in items:
        if from_date in item.text:
            item.click()
            break
    print(f"Campo 'form_date' preenchido com: {from_date}")
except Exception as e:
    print("Erro ao preencher o campo 'from_date':", e)


try:
    print("Iniciar preenchimento do campo 'to_date'...")

    buttonTo_date = driver.find_element(By.ID, "firstRegistrationTo-input")
    buttonTo_date.click()

    # 2. Espera a lista aparecer
    wait = WebDriverWait(driver, 10)
    suggestions = wait.until(
        EC.visibility_of_element_located(
            (By.ID, "firstRegistrationTo-input-suggestions")
        )
    )

    # 3. Procura o <li> que tenha o texto "2023"
    itemsToDate = driver.find_elements(
        By.CSS_SELECTOR, "#firstRegistrationTo-input-suggestions > li.suggestion-item"
    )

    for item in itemsToDate:
        if to_date in item.text:
            item.click()
            break
    print(f"Campo 'form_date' preenchido com: {to_date}")
except Exception as e:
    print("Erro ao preencher o campo 'to_date':", e)


try:
    print("Iniciar preenchimento do campo 'from_price'...")

    button = driver.find_element(By.ID, "price-from")
    button.click()

    # 2. Espera a lista aparecer
    wait = WebDriverWait(driver, 10)
    suggestions = wait.until(
        EC.visibility_of_element_located((By.ID, "price-from-suggestions"))
    )

    # 3. Procura o <li> que tenha o texto "2023"
    items = driver.find_elements(
        By.CSS_SELECTOR, "#price-from-suggestions > li.suggestion-item"
    )
    print(f"Items encontrados: {len(items)}")

    for item in items:
        if from_price in item.text:
            print(f"Item encontrado: {item.text}", from_price)

            item.click()
            break
    print(f"Campo 'from_price' preenchido com: {from_price}")
except Exception as e:
    print("Erro ao preencher o campo 'from_price':", e)

try:
    print("Iniciar preenchimento do campo 'to_price'...")

    button = driver.find_element(By.ID, "price-to")
    button.click()

    # 2. Espera a lista aparecer
    wait = WebDriverWait(driver, 10)
    suggestions = wait.until(
        EC.visibility_of_element_located((By.ID, "price-to-suggestions"))
    )

    # 3. Procura o <li> que tenha o texto "2023"
    items = driver.find_elements(
        By.CSS_SELECTOR, "#price-to-suggestions > li.suggestion-item"
    )
    print(f"Items encontrados: {len(items)}")

    for item in items:
        if to_price in item.text:
            print(f"Item encontrado: {item.text}", to_price)

            item.click()
            break
    print(f"Campo 'to_price' preenchido com: {to_price}")
except Exception as e:
    print("Erro ao preencher o campo 'to_price':", e)

try:
    print("Iniciar preenchimento do campo 'from_milage'...")

    button = driver.find_element(By.ID, "mileageFrom-input")
    button.click()

    # 2. Espera a lista aparecer
    wait = WebDriverWait(driver, 10)
    suggestions = wait.until(
        EC.visibility_of_element_located((By.ID, "mileageFrom-input-suggestions"))
    )

    # 3. Procura o <li> que tenha o texto "2023"
    items = driver.find_elements(
        By.CSS_SELECTOR, "#mileageFrom-input-suggestions > li.suggestion-item"
    )
    print(f"Items encontrados: {len(items)}")

    for item in items:
        if from_milage in item.text:
            print(f"Item encontrado: {item.text}", from_milage)

            item.click()
            break
    print(f"Campo 'from_milage' preenchido com: {from_milage}")
except Exception as e:
    print("Erro ao preencher o campo 'from_milage':", e)

try:
    print("Iniciar preenchimento do campo 'to_milage'...")

    button = driver.find_element(By.ID, "mileageTo-input")
    button.click()

    # 2. Espera a lista aparecer
    wait = WebDriverWait(driver, 10)
    suggestions = wait.until(
        EC.visibility_of_element_located((By.ID, "mileageTo-input-suggestions"))
    )

    # 3. Procura o <li> que tenha o texto "2023"
    items = driver.find_elements(
        By.CSS_SELECTOR, "#mileageTo-input-suggestions > li.suggestion-item"
    )
    print(f"Items encontrados: {len(items)}")

    for item in items:
        if to_milage in item.text:
            print(f"Item encontrado: {item.text}", to_milage)

            item.click()
            break
    print(f"Campo 'to_milage' preenchido com: {to_milage}")
except Exception as e:
    print("Erro ao preencher o campo 'to_milage':", e)

try:
    # localizar o input pelo id
    radio_button = driver.find_element("id", "seller-type_dealer_radio")

    # clicar para selecionar
    radio_button.click()
except Exception as e:
    print("Escolher dealer:", e)

button = driver.find_element(
    "css selector",
    "button.scr-button.scr-button--primary.DetailSearchPage_button__jcuaw",
)
button.click()
print("Aguardando a página de resultados carregar...")


# Encontrar todos os blocos <article>
wait.until(EC.presence_of_all_elements_located((By.CSS_SELECTOR, "article")))

articles = driver.find_elements(By.CSS_SELECTOR, "article")


carros = []

while True:
    # Espera os artigos carregarem
    wait.until(EC.presence_of_all_elements_located((By.CSS_SELECTOR, "article")))
    articles = driver.find_elements(By.CSS_SELECTOR, "article")
    print(f"Total de artigos encontrados nesta página: {len(articles)}")

    for article in articles:

        try:
            # Título: dentro do <h2> do link com classe ListItem_title__ndA4s
            titulo_elem = article.find_element(
                By.CSS_SELECTOR, "a.ListItem_title__ndA4s h2"
            )
            titulo = titulo_elem.text.strip()
        except Exception as e:
            titulo = None
            print("Erro ao extrair título:", e)

        try:
            # Preço: <p> com data-testid="regular-price"
            preco_elem = article.find_element(
                By.CSS_SELECTOR, 'p[data-testid="regular-price"]'
            )
            preco = preco_elem.text.strip()
        except Exception as e:
            preco = None
            print("Erro ao extrair preço:", e)

        try:
            km_elem = article.find_element(
                By.CSS_SELECTOR, 'span[data-testid="VehicleDetails-mileage_road"]'
            )
            quilometros = km_elem.text.strip()
        except Exception as e:
            quilometros = None
            print("Erro ao extrair quilómetros:", e)

        try:
            transm_elem = article.find_element(
                By.CSS_SELECTOR, 'span[data-testid="VehicleDetails-transmission"]'
            )
            transmissao = transm_elem.text.strip()
        except Exception as e:
            transmissao = None
            print("Erro ao extrair transmissão:", e)

        try:
            ano_elem = article.find_element(
                By.CSS_SELECTOR, 'span[data-testid="VehicleDetails-calendar"]'
            )
            ano = ano_elem.text.strip()
        except Exception as e:
            ano = None
            print("Erro ao extrair ano:", e)

        try:
            combustivel_elem = article.find_element(
                By.CSS_SELECTOR, 'span[data-testid="VehicleDetails-gas_pump"]'
            )
            combustivel = combustivel_elem.text.strip()
        except Exception as e:
            combustivel = None
            print("Erro ao extrair combustível:", e)

        try:
            potencia_elem = article.find_element(
                By.CSS_SELECTOR, 'span[data-testid="VehicleDetails-speedometer"]'
            )
            potencia = potencia_elem.text.strip()
        except Exception as e:
            potencia = None
            print("Erro ao extrair potência:", e)
        try:
            localizacao_elem = article.find_element(
                By.CSS_SELECTOR, '[data-testid="sellerinfo-address"]'
            )
            localizacao = localizacao_elem.text.strip()
        except Exception as e:
            localizacao = None
            print("Erro ao extrair localização:", e)
        try:
            link_elem = article.find_element(
                By.CSS_SELECTOR, "a.ListItem_title__ndA4s"
            )
            url = link_elem.get_attribute("href")
        except Exception as e:
            url = None
            print("Erro ao extrair URL:", e)

        # Adiciona o carro ao dicionário
        carros.append({
            "titulo": titulo,
            "preco": preco,
            "quilometros": quilometros,
            "transmissao": transmissao,
            "ano": ano,
            "combustivel": combustivel,
            "potencia": potencia,
            "localizacao": localizacao,
            "url": url
        })

    # Tenta encontrar o botão "Next" e verifica se está habilitado
    try:
        next_button = driver.find_element(By.CSS_SELECTOR, 'li.prev-next button[aria-label="Go to next page"]')
        if next_button.get_attribute("aria-disabled") == "true":
            print("Última página alcançada.")
            break
        else:
            print("Indo para a próxima página...")
            next_button.click()
            time.sleep(2)  # Aguarda a próxima página carregar
    except Exception as e:
        print("Não foi possível encontrar ou clicar no botão 'Next':", e)
        break

# Exibir os carros encontrados
print(f"Total de carros encontrados: {len(carros)}")
# Exemplo: printar todos os carros encontrados
for carro in carros:
    print(carro)

# Grava em CSV
# Caminho absoluto para a pasta AdSearch na raiz do projeto
import os

root_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
adsearch_dir = os.path.join(root_dir, "AutoScoutSearch")
os.makedirs(adsearch_dir, exist_ok=True)

# csv_filename = (
#     make
#     + "_"
#     + model
#     + "_"
#     + additional_info
#     + "_"
#     + from_date
#     + "_"
#     + to_date
#     + "_"
#     + fuel_type.replace(" ", "_")
#     + "_"
#     + from_price.replace("€", "").replace(",", "")
#     + "_"
#     + to_price.replace("€", "").replace(",", "")
#     + "_"
#     + from_milage.replace(" ", "")
#     + "_"
#     + to_milage.replace(" ", "")
#     + ".csv"
# )
# csv_path = os.path.join(adsearch_dir, csv_filename)


import re

def sanitize_filename(filename):
    # Remove caracteres inválidos para nomes de arquivos
    filename = re.sub(r'[\\/:"*?<>|,()\s]+', '_', filename)
    filename = re.sub(r'_+', '_', filename)  # Substitui múltiplos _ por um só
    return filename.strip('_')

csv_filename = sanitize_filename(
    make
    + "_" + model
    + "_" + additional_info
    + "_" + from_date
    + "_" + to_date
    + "_" + fuel_type
    + "_" + from_price.replace("€", "")
    + "_" + to_price.replace("€", "")
    + "_" + from_milage
    + "_" + to_milage
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
            "Título",
            "Preço",
            "Ano",
            "Quilometragem",
            "Transmissão",
            "Localidade",
            "Combustível",
            "Potência",
            "URL",
        ]
    )
    for carro in carros:
        writer.writerow([
            carro["titulo"],
            carro["preco"],
            carro["ano"],
            carro["quilometros"],
            carro["transmissao"],
            carro["localizacao"],
            carro["combustivel"],
            carro["potencia"],
            carro["url"],
        ])

print(f"{len(carros)} anúncios exportados para csv")

time.sleep(20)
driver.quit()
