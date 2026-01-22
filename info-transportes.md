# Módulo de Orçamentos de Transporte

## Configuração

### Google Maps API Key

Para que o mapa funcione corretamente, é necessário adicionar uma chave de API do Google Maps.

1. Obter uma chave API em: https://console.cloud.google.com/google/maps-apis
2. Ativar as seguintes APIs:
   - Maps JavaScript API
   - Geocoding API (opcional, para conversão de endereços)

3. Substituir `YOUR_API_KEY` no arquivo:
   - `resources/views/admin/v2/transport-quotes/map.blade.php`
   
   Linha 207:
   ```html
   <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
   ```

## Funcionalidades

### Gestão de Orçamentos
- ✅ Criar, editar e eliminar orçamentos
- ✅ Listar orçamentos com filtros (data, transportadora)
- ✅ Armazenar informações completas: marca, modelo, origem, destino, preço, prazo

### Visualização no Mapa
- ✅ Mapa interativo com Google Maps
- ✅ Marcadores para origens dos transportes
- ✅ Marcador fixo para destino (Oliveira de Azeméis)
- ✅ Clustering de marcadores
- ✅ Info windows com detalhes do orçamento
- ✅ Cálculo de distância aproximada

### Dados Armazenados
- Veículo: Marca e Modelo
- Origem: Cidade, País, Código Postal, Latitude, Longitude
- Destino: Fixo - Oliveira de Azeméis, Portugal (40.8397, -8.4775)
- Transportadora: Relação com tabela suppliers
- Valores: Preço, Data do Orçamento
- Informações Adicionais: Prazo de Entrega (dias), Observações

## Acesso

Menu de Administração: **Transportes** (ícone de camião)

Rotas:
- Listagem: `/gestao/v2/transport-quotes`
- Criar: `/gestao/v2/transport-quotes/create`
- Editar: `/gestao/v2/transport-quotes/{id}/edit`
- Mapa: `/gestao/v2/transport-quotes/map`
- API Dados Mapa: `/gestao/v2/transport-quotes/map-data`

## Base de Dados

Tabela: `transport_quotes`

```sql
- id
- brand (varchar)
- model (varchar)
- origin_city (varchar)
- origin_country (varchar)
- origin_postal_code (varchar, nullable)
- origin_latitude (decimal, nullable)
- origin_longitude (decimal, nullable)
- destination_city (varchar, default: 'Oliveira de Azeméis')
- destination_country (varchar, default: 'Portugal')
- destination_latitude (decimal, nullable)
- destination_longitude (decimal, nullable)
- supplier_id (foreign key)
- price (decimal)
- quote_date (date, default: today)
- estimated_delivery_days (integer, nullable)
- observations (text, nullable)
- created_at
- updated_at
```

## Próximas Melhorias (Opcionais)

- [ ] Integração automática com API de geocoding para preencher coordenadas
- [ ] Cálculo automático de distância real por estrada (não em linha reta)
- [ ] Filtro de raio de distância customizável (não apenas 200km)
- [ ] Estatísticas de preços médios por região/país
- [ ] Exportação de relatórios em PDF/Excel
- [ ] Notificações quando orçamento fica antigo (> X dias)
