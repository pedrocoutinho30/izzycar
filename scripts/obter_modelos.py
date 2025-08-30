from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import urllib.parse  # Para codificar nomes de marcas com espaços ou caracteres especiais

# --- Lê as marcas do arquivo ---
with open("marcas.txt", "r", encoding="utf-8") as f:
    marcas = [linha.strip() for linha in f.readlines() if linha.strip()]

with open("marcas_modelos.txt", "w", encoding="utf-8") as arquivo_saida:
    for marca in marcas:
        driver = webdriver.Chrome()
        wait = WebDriverWait(driver, 15)
        try:
            marca_url = urllib.parse.quote(marca.lower().replace(" ", "-"))
            driver.get(f"https://www.standvirtual.com/carros/{marca_url}")

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
                input_modelo = wait.until(
                    EC.presence_of_element_located(
                        (By.CSS_SELECTOR, "input[placeholder='Modelo']")
                    )
                )
                input_modelo.click()
                time.sleep(2)  # espera o dropdown aparecer

                # Captura todas as opções que aparecem
                opcoes_modelos = wait.until(
                    EC.presence_of_all_elements_located(
                        (By.CSS_SELECTOR, "div[role='option']")
                    )
                )

                modelos_lista = []
                for opcao in opcoes_modelos:
                    texto = opcao.text.strip()
                    if "(" in texto:
                        modelo = texto.split("(")[0].strip()
                    else:
                        modelo = texto

                    if modelo.lower() != "todos os modelos" and modelo not in modelos_lista:
                        modelos_lista.append(modelo)

                # --- Grava no ficheiro ---
                arquivo_saida.write(f"Marca: {marca}\n")
                arquivo_saida.write("Modelos: " + ", ".join(modelos_lista) + "\n")
                arquivo_saida.write("-\n")
                print(f"✅ Marca {marca} gravada com {len(modelos_lista)} modelos.")

            except Exception as e:
                print(f"⚠️ Sem modelos encontrados para a marca {marca}: {e}")

        except Exception as e:
            print(f"⚠️ Erro ao abrir a marca {marca}: {e}")

        finally:
            driver.quit()
            time.sleep(3)  # dá um tempo entre as marcas

print("Processo concluído.")
