import urllib.parse
from unidecode import unidecode  # pip install unidecode
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

# Caminho para o ficheiro
with open("marcas_modelos.txt", "r", encoding="utf-8") as f:
    lines = [line.strip() for line in f.readlines()]


marca = None
modelos = []
urls = []
with open("marcas_modelos_submodelos.txt", "w", encoding="utf-8") as arquivo_saida:
    for line in lines:
        if line.startswith("Marca:"):
            # Se já tivermos uma marca anterior, processamos os modelos
            if marca and modelos:
                for modelo in modelos:
                    marca_url = urllib.parse.quote(marca.lower().replace(" ", "-"))
                    modelo_url = urllib.parse.quote(
                        unidecode(modelo.lower().replace(" ", "-"))
                    )
                    url = f"https://www.standvirtual.com/carros/{marca_url}?search%5Border%5D=relevance_web&search%5Bfilter_enum_engine_code%5D={modelo_url}"
                    urls.append(url)
            # Atualiza a nova marca
            marca = line.replace("Marca:", "").strip()
            modelos = []
        elif line.startswith("Modelos:"):
            modelos = [m.strip() for m in line.replace("Modelos:", "").split(",")]
        elif line.strip() == "-":
            marca = None
            modelos = []
        # Para a última marca no ficheiro
        if marca and modelos:
            for modelo in modelos:
                marca_url = urllib.parse.quote(marca.lower().replace(" ", "-"))
                modelo_url = urllib.parse.quote(unidecode(modelo.lower().replace(" ", "-")))
                url = f"https://www.standvirtual.com/carros/{marca_url}?search%5Border%5D=relevance_web&search%5Bfilter_enum_engine_code%5D={modelo_url}"
                urls.append(url)


        
                # Inicializa o navegador
                driver = webdriver.Chrome()
                wait = WebDriverWait(driver, 15)

                driver.get(url)

                # --- Aceitar cookies se aparecer ---
                try:
                    consent_button = wait.until(
                        EC.element_to_be_clickable(
                            (By.CSS_SELECTOR, "button#onetrust-accept-btn-handler")
                        )
                    )
                    consent_button.click()
                except:
                    pass
                # --- Espera o input de modelo e abre o dropdown ---
                try:
                    input_submodelo = wait.until(
                        EC.presence_of_element_located(
                            (By.CSS_SELECTOR, "input[placeholder='Sub-modelo']")
                        )
                    )
                    input_submodelo.click()
                    time.sleep(2)  # espera o dropdown aparecer
                    try:
                        # Captura todas as opções que aparecem
                        opcoes_submodelos = wait.until(
                            EC.presence_of_all_elements_located(
                                (By.CSS_SELECTOR, "div[role='option']")
                            )
                        )
                        submodelos_lista = []
                        for opcao in opcoes_submodelos:
                            texto = opcao.text.strip()
                            if "(" in texto:
                                submodelo = texto.split("(")[0].strip()
                            else:
                                submodelo = texto
                            print(f"Submodelo encontrado: {submodelo}")
                            if (
                                submodelo.lower() != "todos os modelos"
                                and submodelo not in submodelos_lista
                            ):
                                submodelos_lista.append(submodelo)
                        # --- Grava no ficheiro ---
                        arquivo_saida.write(f"Marca: {marca}\n")
                        arquivo_saida.write(f"Modelo: {modelo}\n")
                        arquivo_saida.write(f"Submodelos: {', '.join(submodelos_lista)}\n")
                        arquivo_saida.write("-\n")
                        print(f"✅ Marca {marca} com modelo {modelo} gravada com {len(submodelos_lista)} modelos.")
                    except Exception as e:
                        arquivo_saida.write(f"Marca: {marca}\n")
                        arquivo_saida.write(f"Modelo: {modelo}\n")
                        arquivo_saida.write(f"Submodelos: \n")
                        arquivo_saida.write("-\n")
                        print(f"Erro ao obter submodelos: {e}")
                finally:
                    driver.quit()
                    time.sleep(3)  # dá um tempo entre urls

print("Processo concluído.")
