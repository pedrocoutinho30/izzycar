from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
import time
import os
import requests
from PIL import Image
from io import BytesIO

# Receber o URL como argumento
url = input("Insere o URL do anúncio do carro: ").strip()

# Pasta para guardar imagens
folder = "imagens_carro"
os.makedirs(folder, exist_ok=True)

# Configurações do Chrome
options = Options()
options.headless = False  # Mude para True se não quiser abrir o navegador
options.add_argument("--window-size=1920,1080")

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
driver.get(url)
time.sleep(5)

# Aceitar cookies se aparecer
try:
    accept_button = driver.find_element(By.XPATH, "//button[contains(text(),'Accept')]")
    accept_button.click()
    print("Aceitou cookies.")
    time.sleep(2)
except:
    print("Nenhum botão de cookies encontrado.")

# Função para capturar imagens de um carrossel e fazer o download
def capture_and_download_images_from_carousel():
    image_urls = []
    try:
        # Encontrar o carrossel
        carousel_div = driver.find_element(By.CLASS_NAME, "image-gallery")
        
        # Tente usar seletores mais genéricos
        try:
            next_button = driver.find_element(By.XPATH, "//button[contains(@aria-label, 'Next')]")
        except:
            print("Botão de navegação 'Next' não encontrado.")
            return []

        for i in range(30):  # Ajuste conforme necessário
            images = carousel_div.find_elements(By.TAG_NAME, "img")
            for img in images:
                src = img.get_attribute("src")
                if src and src not in image_urls:
                    if is_large_image(src):  # Verificar se a imagem é grande
                        image_urls.append(src)
                        download_image(src, len(image_urls))  # Index atualizado
            next_button.click()
            time.sleep(2)
    except Exception as e:
        print(f"Erro ao capturar imagens do carrossel: {e}")

    return image_urls

# Função para verificar se a imagem é grande
def is_large_image(url):
    try:
        response = requests.get(url)
        if response.status_code == 200:
            img = Image.open(BytesIO(response.content))
            width, height = img.size
            print(f"Imagem encontrada com tamanho: {width}x{height}")
            return width > 800 and height > 600  # Critério para imagens grandes
        else:
            print(f"Falha ao verificar a imagem. Status code: {response.status_code}")
            return False
    except Exception as e:
        print(f"Erro ao verificar a imagem: {e}")
        return False

# Função para fazer o download da imagem
def download_image(url, index):
    try:
        response = requests.get(url)
        if response.status_code == 200:
            image_name = f"image_{index}.jpg"
            image_path = os.path.join(folder, image_name)
            with open(image_path, 'wb') as f:
                f.write(response.content)
            print(f"Imagem {index} baixada: {image_name}")
        else:
            print(f"Falha ao baixar a imagem {index}. Status code: {response.status_code}")
    except Exception as e:
        print(f"Erro ao tentar baixar a imagem: {e}")

# Capturar imagens
image_urls = capture_and_download_images_from_carousel()

print(f"Encontradas {len(image_urls)} imagens grandes.")
for url in image_urls:
    print(url)

driver.quit()