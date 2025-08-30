import pandas as pd
import re
from datetime import datetime, timedelta

# Função para determinar região a partir da Localidade
def classificar_regiao(localidade):
    norte = ['Porto', 'Braga', 'Viana do Castelo', 'Penafiel', 'Vermoim', 'Lousada', 'Madalena', 'Sintra (Lisboa)']
    centro = ['Aveiro', 'Santarém', 'Fátima', 'Castanheira do Ribatejo', 'Neiva', 'Canedo']
    sul = ['Lisboa', 'Setúbal', 'Alverca do Ribatejo', 'Corroios', 'Cernadelo', 'Alcabideche', 'Carnaxide', 'Oeiras', 'Laranjeiro', 'Ponte do Rol', 'São João das Lampas', 'Terrugem', 'Avenidas Novas', 'Parque das Nações']

    # Verifica se alguma palavra chave está na localidade
    for cidade in norte:
        if cidade in localidade:
            return 'Norte'
    for cidade in centro:
        if cidade in localidade:
            return 'Centro'
    for cidade in sul:
        if cidade in localidade:
            return 'Sul'
    return 'Desconhecida'

# Função para converter preços para float (remover espaços e símbolo € se houver)
def parse_preco(preco_str):
    preco_str = preco_str.replace('\xa0', '').replace(' ', '').replace('€', '').replace(',', '.')
    try:
        return float(preco_str)
    except:
        return None

# Função para converter quilometragem para int (remove km e espaços)
def parse_km(km_str):
    km_str = km_str.lower().replace('km', '').replace(' ', '').replace('.', '')
    try:
        return int(km_str)
    except:
        return None

# Função para converter tempo publicação para minutos
def parse_tempo_publicacao(tempo_str):
    tempo_str = tempo_str.lower()

    if 'minuto' in tempo_str:
        minutos = re.findall(r'(\d+)', tempo_str)
        if minutos:
            return int(minutos[0])
    elif 'hora' in tempo_str:
        horas = re.findall(r'(\d+)', tempo_str)
        if horas:
            return int(horas[0]) * 60
    elif 'dia' in tempo_str:
        dias = re.findall(r'(\d+)', tempo_str)
        if dias:
            return int(dias[0]) * 60 * 24
    elif 'semana' in tempo_str:
        semanas = re.findall(r'(\d+)', tempo_str)
        if semanas:
            return int(semanas[0]) * 60 * 24 * 7
    elif 'mês' in tempo_str or 'mes' in tempo_str:
        meses = re.findall(r'(\d+)', tempo_str)
        if meses:
            return int(meses[0]) * 60 * 24 * 30
    elif 'ano' in tempo_str:
        anos = re.findall(r'(\d+)', tempo_str)
        if anos:
            return int(anos[0]) * 60 * 24 * 365
    elif 'topo' in tempo_str:
        # Exemplo: "Para o topo há 42 minutos"
        minutos = re.findall(r'(\d+)', tempo_str)
        if minutos:
            return int(minutos[0])
    return None

# Lê o ficheiro CSV
df = pd.read_csv('anuncios.csv', sep=None, engine='python')

# Mostrar os nomes reais das colunas para validação
print("Colunas encontradas:", df.columns.tolist())


# Mostrar os nomes reais das colunas para validação
# Limpa e converte colunas relevantes
df['Preço'] = df['Preço'].apply(parse_preco)
df['Quilometragem'] = df['Quilometragem'].apply(parse_km)
df['Ano'] = df['Ano'].astype(int)
df['Tempo Publicação Min'] = df['Tempo Publicação'].apply(parse_tempo_publicacao)
df['Região'] = df['Localidade'].apply(classificar_regiao)

# Quantidade de anúncios por região
contagem_regiao = df['Região'].value_counts()

# Carro mais caro e mais barato
carro_mais_caro = df.loc[df['Preço'].idxmax()]
carro_mais_barato = df.loc[df['Preço'].idxmin()]

# Média de preços
media_precos = df['Preço'].mean()

# Média do tempo dos anúncios em minutos
media_tempo_dias = df['Tempo Publicação Min'].dropna().mean() / 1440

# Melhor compra - balancear preço baixo, km baixo e ano recente
# Vamos criar um score simples: menor preço, menor km, ano maior = melhor score
# Normalizamos cada critério entre 0 e 1 e somamos ponderado
df['Preco_norm'] = (df['Preço'] - df['Preço'].min()) / (df['Preço'].max() - df['Preço'].min())
df['Km_norm'] = (df['Quilometragem'] - df['Quilometragem'].min()) / (df['Quilometragem'].max() - df['Quilometragem'].min())
df['Ano_norm'] = (df['Ano'] - df['Ano'].min()) / (df['Ano'].max() - df['Ano'].min())

# Score = 0.5*Preco_norm + 0.3*Km_norm - 0.2*Ano_norm (queremos preço e km baixos e ano alto)
df['Score'] = 0.5*df['Preco_norm'] + 0.3*df['Km_norm'] - 0.2*df['Ano_norm']

melhor_compra = df.loc[df['Score'].idxmin()]

# Resultados
print("Contagem de anúncios por região:")
print(contagem_regiao.to_string(), end="\n\n")

print(f"Carro mais caro:\nID: {carro_mais_caro['ID']}\nTítulo: {carro_mais_caro['Título']}\nPreço: {carro_mais_caro['Preço']} €\nAno: {carro_mais_caro['Ano']}\nKm: {carro_mais_caro['Quilometragem']}\nLocalidade: {carro_mais_caro['Localidade']}\nURL: {carro_mais_caro['URL']}\n")

print(f"Carro mais barato:\nID: {carro_mais_barato['ID']}\nTítulo: {carro_mais_barato['Título']}\nPreço: {carro_mais_barato['Preço']} €\nAno: {carro_mais_barato['Ano']}\nKm: {carro_mais_barato['Quilometragem']}\nLocalidade: {carro_mais_barato['Localidade']}\nURL: {carro_mais_barato['URL']}\n")

print(f"Média de preços: {media_precos:.2f} €")
print(f"Média de tempo dos anúncios (em dias): {media_tempo_dias:.2f}\n")

print(f"Melhor compra considerando preço, kms e ano:\nID: {melhor_compra['ID']}\nTítulo: {melhor_compra['Título']}\nPreço: {melhor_compra['Preço']} €\nAno: {melhor_compra['Ano']}\nKm: {melhor_compra['Quilometragem']}\nLocalidade: {melhor_compra['Localidade']}\nURL: {melhor_compra['URL']}")
