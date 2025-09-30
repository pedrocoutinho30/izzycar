import sys
import requests

def fetch_html(url):
    try:
        response = requests.get(url)
        response.raise_for_status()  # Gera exceção se houver erro HTTP
        return response.text
    except requests.RequestException as e:
        return f"Erro ao acessar a URL: {e}"

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Nenhuma URL fornecida")
        sys.exit(1)

    url = sys.argv[1]  # Recebe o URL passado pelo Laravel
    html = fetch_html(url)
    print(html)
