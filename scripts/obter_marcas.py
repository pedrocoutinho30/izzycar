from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

driver = webdriver.Chrome()

try:
    driver.get(
        "https://www.standvirtual.com/carros/mercedes-benz?search%5Border%5D=created_at%3Adesc&search%5Badvanced_search_expanded%5D=true"
    )

    wait = WebDriverWait(driver, 15)

    # --- Aceitar cookies se aparecer ---
    try:
        consent_button = wait.until(
            EC.element_to_be_clickable((By.CSS_SELECTOR, "button#onetrust-accept-btn-handler"))
        )
        consent_button.click()
    except:
        pass

    # --- Abre o input da marca ---
    input_marca = wait.until(
        EC.element_to_be_clickable((By.CSS_SELECTOR, "input[placeholder='Marca']"))
    )
    input_marca.click()

    # --- Busca todas as divs com role="option" ---
    opcoes = wait.until(
        EC.presence_of_all_elements_located((By.CSS_SELECTOR, "div[role='option']"))
    )

    # --- Abre o arquivo para salvar ---
    with open("marcas.txt", "w", encoding="utf-8") as f:
        for opcao in opcoes:
            texto = opcao.text.strip()
            if "(" in texto:
                marca = texto.split("(")[0].strip()
            else:
                marca = texto

            if marca.lower() == "todas as marcas":
                continue

            print(f"Marca encontrada: {marca}")
            f.write(marca + "\n")

finally:
    driver.quit()
