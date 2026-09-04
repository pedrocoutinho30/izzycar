# Radar de preços AutoScout24

Scraper de aquisição de dados: corre pesquisas parametrizadas contra a
AutoScout24.de e grava anúncios + histórico de preços na mesma base de dados
MySQL usada pela app Laravel do izzycar (`radar_searches`, `radar_listings`,
`radar_price_history`, `radar_search_runs` — ver
`izzycar/database/migrations/2026_09_01_*`).

Esta fase cobre apenas a obtenção de dados. Visualização/análise fica para depois.

## Como funciona

- Cada página de resultados da AutoScout24 (`/lst`) embute os dados completos num
  bloco `<script id="__NEXT_DATA__">` (Next.js) — não é preciso renderizar
  JavaScript, um `GET` HTTP normal com um User-Agent de browser chega
  (confirmado empiricamente e também usado hoje em produção por
  `app/Services/VehicleListingImportService.php`).
- Uma pesquisa é definida por marca + modelo (vão no path do URL,
  `/lst/{make}/{model}`) e um dicionário de filtros que são passados
  diretamente como query params da AutoScout24 (ver `scraper/filters.py` para
  os nomes confirmados).
- Cada vez que uma pesquisa corre: os anúncios são upsert-ados por
  `external_id` (UUID da AutoScout24, é o identificador estável), acrescenta-se
  uma linha ao histórico de preços, e os anúncios que desapareceram da
  pesquisa ficam marcados com `removed_at` (sinal de vendido/removido).

## Setup

```bash
cd scarperAutoscout
python3 -m venv venv
./venv/bin/pip install -r requirements.txt
```

As credenciais da BD são lidas diretamente do `.env` do izzycar (um nível acima)
— não há segredos duplicados aqui.

## Uso

```bash
# Define pesquisas em searches/*.yaml (ver searches/example.yaml), depois:
./venv/bin/python -m scraper.cli sync-searches

# Corre uma pesquisa (pagina tudo, escreve na BD):
./venv/bin/python -m scraper.cli run audi-a4-diesel-auto

# Corre todas as pesquisas guardadas:
./venv/bin/python -m scraper.cli run-all

# Debug: inspeciona o JSON real de um URL de pesquisa (para confirmar/ajustar
# os nomes dos campos em scraper/autoscout_client.py se a AutoScout24 mudar o
# formato):
./venv/bin/python -m scraper.cli inspect-json "https://www.autoscout24.de/lst/audi/a4?...&page=1"
```

Sem agendamento automático por agora — corre-se à mão sempre que se quiser
acrescentar um novo ponto ao histórico de preços.

## Definir uma pesquisa nova

Cria um ficheiro em `searches/<nome>.yaml`:

```yaml
name: nome-unico-da-pesquisa
make: audi          # vai para o path /lst/audi
model: a4            # opcional; vai para o path /lst/audi/a4
filters:
  fregfrom: 2018      # ano de matrícula, de
  fregto: 2022        # ano de matrícula, até
  kmto: 100000         # quilómetros, até
  powerfrom: 150        # potência, de (junto com powertype)
  powertype: hp          # hp ou kw
  fuel: D                  # D = Diesel (confirmado); outros códigos por confirmar
  gear: A                    # A = automática, M = manual
  priceto: 30000               # preço, até
```

Depois corre `sync-searches` para criar/atualizar a pesquisa na BD.

### Como saber o `make`/`model` certos

Sim — tens de usar literalmente o mesmo "slug" (identificador de URL) que a
própria AutoScout24 usa, que **não é sempre igual ao nome comercial**. Por
exemplo, Mercedes-Benz não é `mercedes`, é `mercedes-benz`; a Classe E não é
`classe-e` nem `e-class`, é `e-klasse` (em alemão, porque o `.de` é o
mercado alemão). Confirmado empiricamente (2026-09-02):

```
https://www.autoscout24.de/lst/mercedes-benz/e-klasse
```

devolve mesmo os anúncios da Mercedes-Benz Classe E (10.745 resultados sem
mais filtros — ultrapassa o limite de 4000, por isso esta pesquisa em
concreto precisa de `fregfrom`/`priceto`/etc. para ficar viável).

**Forma mais fiável de descobrir o slug de uma marca/modelo nova:** abre
`autoscout24.de`, usa os dropdowns de marca/modelo da própria pesquisa (não
tentes adivinhar) e copia o que aparece no URL depois de `/lst/`. Está
guardado um segundo exemplo já confirmado em `searches/mercedes-e-klasse.yaml`.

### Filtros ainda não confirmados

Só os parâmetros documentados em `scraper/filters.py` foram testados
diretamente contra o site. Coisas como tipo de vendedor (particular vs
profissional), código postal + raio, tipo de carroçaria, cor e equipamento
ainda não têm nome de parâmetro confirmado. Para os descobrir: aplica o
filtro na própria UI em autoscout24.de/lst e copia o URL resultante — os
parâmetros novos podem ser passados directamente no `filters:` do YAML, não é
preciso alterar código.

## Limitações conhecidas (v1)

- `body_type` fica sempre vazio — não está presente no JSON da página de
  lista, só na página de detalhe de cada anúncio (implicaria um pedido extra
  por anúncio; fora do âmbito desta fase).
- Se duas pesquisas guardadas se sobrepuserem (mesmo anúncio aparece em
  ambas), o anúncio fica associado à última pesquisa que o viu.
- A AutoScout24 limita cada pesquisa a 4000 resultados (200 páginas × 20). Se
  `numberOfResults` de uma pesquisa aproximar desse limite, convém dividi-la
  em pesquisas mais estreitas (por ano ou preço, por exemplo).
- Sem proxies/rotação de IP — pedidos sequenciais com um delay aleatório de
  2–5s entre páginas. Se começar a aparecer `status=blocked` em
  `radar_search_runs`, é sinal para abrandar ainda mais ou repensar a
  abordagem.
