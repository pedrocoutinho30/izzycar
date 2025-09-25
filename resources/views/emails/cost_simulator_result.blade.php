@component('mail::message')
# Resultado do Simulador de Custos

Aqui estão os detalhes do seu carro e custos associados:

- **Valor do Carro:** €{{ number_format($valorCarro, 2) }}
- **ISV:** €{{ number_format($isv, 2) }}
- **Custo de Serviço:** €{{ number_format($servicos, 2) }}

- **Custo Total:** €{{ number_format($custoTotal, 2) }}

Obrigado por usar o nosso simulador!

@endcomponent