<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageContent;
use App\Models\PageType;
use Illuminate\Support\Str;

class NewsArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $pageType = PageType::where('name', 'Notícias')->firstOrFail();

        foreach ($this->articles() as $article) {
            $slug = $article['slug'];

            if (Page::where('slug', $slug)->exists()) {
                $this->command->info("Artigo já existe: {$slug}");
                continue;
            }

            $page = Page::create([
                'page_type_id' => $pageType->id,
                'title'        => $slug,
                'slug'         => $slug,
                'is_active'    => true,
            ]);

            foreach ($article['contents'] as $field => $value) {
                PageContent::create([
                    'page_id'     => $page->id,
                    'field_name'  => $field,
                    'field_value' => $value,
                ]);
            }

            $page->updateSeo([
                'title'            => $article['seo_title'],
                'meta_description' => $article['seo_description'],
                'meta_keywords'    => $article['seo_keywords'],
                'og_title'         => $article['seo_title'],
                'og_description'   => $article['seo_description'],
                'og_type'          => 'article',
                'canonical_url'    => 'https://izzycar.pt/noticias/' . $slug,
            ]);

            $this->command->info("Criado: {$slug}");
        }
    }

    private function articles(): array
    {
        return [

            // ─────────────────────────────────────────────────────────────────
            // ARTIGO 1 — ISV guia completo 2025
            // ─────────────────────────────────────────────────────────────────
            [
                'slug'            => 'como-calcular-isv-importar-carro-portugal-2025',
                'seo_title'       => 'Como Calcular o ISV ao Importar um Carro para Portugal (Guia 2025)',
                'seo_description' => 'Descubra como é calculado o ISV em Portugal: componente cilindrada, componente ambiental, reduções por idade e casos especiais para eléctricos e híbridos. Guia completo 2025.',
                'seo_keywords'    => 'ISV Portugal, calcular ISV carro, imposto sobre veículos, ISV importação, ISV 2025',
                'contents' => [
                    'title'    => 'Como é calculado o ISV ao importar um carro para Portugal — Guia Completo 2025',
                    'subtitle' => '<p>O <strong>Imposto Sobre Veículos (ISV)</strong> é um dos custos mais relevantes quando se importa um automóvel. Saber antecipadamente quanto vai pagar evita surpresas e ajuda a tomar decisões mais informadas. Neste guia explicamos, em linguagem simples, como funciona o cálculo do ISV em 2025.</p>',
                    'date'     => '2025-06-01T09:00',
                    'status'   => 'Publicado',
                    'image'    => '',
                    'content'  => '
<h2>O que é o ISV?</h2>
<p>O ISV — Imposto Sobre Veículos — é o imposto obrigatório a pagar quando um veículo é matriculado pela primeira vez em Portugal. É cobrado pela Autoridade Tributária (AT) e representa, na maioria dos casos, o maior custo fiscal de uma importação.</p>
<p>Ao contrário do que muitos pensam, o ISV não é um valor fixo. É calculado com base em <strong>dois critérios principais</strong>: a cilindrada do motor e as emissões de CO₂.</p>

<h2>Os dois pilares do cálculo do ISV</h2>

<h3>1. Componente da Cilindrada</h3>
<p>Esta componente é calculada com base na cilindrada do motor, em centímetros cúbicos (cc), usando a seguinte tabela para ligeiros de passageiros:</p>
<ul>
  <li><strong>Até 1 000 cc:</strong> taxa de 1,09 € por cc, menos uma parcela de 849 €</li>
  <li><strong>1 001 a 1 250 cc:</strong> taxa de 1,18 € por cc, menos 851 €</li>
  <li><strong>Mais de 1 250 cc:</strong> taxa de 5,61 € por cc, menos 6 195 €</li>
</ul>
<p>Exemplo prático: um motor de 2 000 cc pagaria <em>(2000 × 5,61) − 6195 = 5 025 €</em> nesta componente.</p>

<h3>2. Componente Ambiental (CO₂)</h3>
<p>A segunda componente baseia-se nas emissões de dióxido de carbono do veículo (g/km) e varia consoante o tipo de combustível e o método de medição (WLTP ou NEDC).</p>
<p>Para um veículo a gasolina com medição WLTP a emitir 130 g/km, a componente ambiental é calculada por escalões, sendo o valor final subtraído de uma parcela de abatimento.</p>

<h2>Reduções por idade do veículo</h2>
<p>Veículos provenientes da União Europeia beneficiam de uma redução no ISV consoante a sua antiguidade. As reduções em vigor são:</p>
<ul>
  <li>Menos de 1 ano: redução de 10%</li>
  <li>1 a 2 anos: 20%</li>
  <li>2 a 3 anos: 28%</li>
  <li>3 a 4 anos: 35%</li>
  <li>4 a 5 anos: 43%</li>
  <li>5 a 6 anos: 52%</li>
  <li>6 a 7 anos: 60%</li>
  <li>7 a 8 anos: 65%</li>
  <li>8 a 9 anos: 70%</li>
  <li>9 a 10 anos: 75%</li>
  <li>Mais de 10 anos: 80%</li>
</ul>
<p>Isto significa que um carro com 6 anos de matrícula paga apenas 40% do ISV base — uma diferença muito significativa em relação a um modelo mais recente.</p>

<h2>Casos especiais: eléctricos e híbridos</h2>
<p><strong>Veículos 100% eléctricos</strong> estão isentos de ISV. Esta é uma das maiores vantagens da importação de eléctricos, especialmente de mercados como a Alemanha onde os preços são consideravelmente mais baixos.</p>
<p><strong>Híbridos plug-in</strong> com autonomia eléctrica igual ou superior a 50 km e emissões abaixo de 50 g/km beneficiam de uma taxa de redução de 75% — pagam apenas 25% do ISV calculado.</p>
<p><strong>Híbridos convencionais</strong> têm uma redução de 40%.</p>

<h2>Agravamento por partículas (diesel)</h2>
<p>Veículos a diesel com emissões de partículas superiores a 0,0001 g/km têm um agravamento de <strong>500 €</strong> no ISV. É um factor a considerar ao comparar alternativas a gasóleo.</p>

<h2>Valor mínimo de ISV</h2>
<p>Independentemente do cálculo, o ISV nunca pode ser inferior a <strong>100 €</strong>. Na prática, este mínimo só se aplica a veículos muito antigos com reduções elevadas.</p>

<h2>Simule o seu ISV gratuitamente</h2>
<p>Antes de tomar qualquer decisão, utilize o nosso <a href="/simulador-custos"><strong>Simulador de Custos de Importação</strong></a>. Em menos de 2 minutos obtém uma estimativa detalhada do ISV e de todos os outros custos associados à importação — sem compromisso e sem qualquer custo.</p>
',
                    'summary'  => '<p>O ISV é calculado com base na cilindrada e nas emissões de CO₂, com reduções progressivas por idade do veículo. Veículos eléctricos são isentos. Híbridos plug-in com boa autonomia beneficiam de reduções de até 75%. Simule já os seus custos no nosso simulador gratuito.</p>',
                ],
            ],

            // ─────────────────────────────────────────────────────────────────
            // ARTIGO 2 — Processo completo de importação
            // ─────────────────────────────────────────────────────────────────
            [
                'slug'            => 'importar-carro-alemanha-portugal-processo-completo',
                'seo_title'       => 'Importar Carro da Alemanha para Portugal: O Processo Completo Passo a Passo',
                'seo_description' => 'Tudo o que precisa de saber para importar um carro da Alemanha para Portugal: pesquisa, negociação, transporte, ISV, IPO, matrícula. Guia prático e detalhado.',
                'seo_keywords'    => 'importar carro Alemanha Portugal, importação automóvel, como importar carro, processo importação, carro importado Alemanha',
                'contents' => [
                    'title'    => 'Importar Carro da Alemanha para Portugal: O Processo Completo Passo a Passo',
                    'subtitle' => '<p>A Alemanha é o mercado de referência para a importação automóvel em Portugal. Os preços são em média <strong>15 a 30% mais baixos</strong> do que em Portugal para a mesma viatura, e a oferta é vastíssima. Mas o processo tem várias etapas — e conhecê-las antecipadamente faz toda a diferença.</p>',
                    'date'     => '2025-05-15T09:00',
                    'status'   => 'Publicado',
                    'image'    => '',
                    'content'  => '
<h2>Porquê importar da Alemanha?</h2>
<p>A Alemanha é o maior mercado automóvel da Europa. A combinação de elevada rotatividade de frota (muitos alemães mudam de carro a cada 2-3 anos), impostos mais baixos sobre veículos e uma cultura de manutenção exemplar faz com que o mercado de usados alemão seja o mais apetecível da Europa.</p>
<p>Para além do preço, os carros alemães têm em geral histórico de serviço documentado, inspecções regulares e quilometragem verificável — factores que reduzem o risco na compra.</p>

<h2>Etapa 1 — Pesquisa e identificação do veículo</h2>
<p>O processo começa na pesquisa. As plataformas mais usadas são o <strong>AutoScout24</strong> e o <strong>Mobile.de</strong>, ambas com versões em inglês e vasta oferta de viaturas alemãs.</p>
<p>Na pesquisa, tenha em atenção:</p>
<ul>
  <li><strong>Histórico de serviço:</strong> procure viaturas com "Scheckheftgepflegt" (livrete de revisões completo)</li>
  <li><strong>TÜV (inspecção alemã):</strong> quanto mais recente, melhor — significa menos custos imediatos</li>
  <li><strong>Primeira matrícula:</strong> determina o ISV a pagar em Portugal</li>
  <li><strong>Emissões CO₂ e cilindrada:</strong> são as variáveis do cálculo do ISV</li>
</ul>

<h2>Etapa 2 — Negociação e aquisição</h2>
<p>Depois de identificar a viatura, é necessário contactar o stand ou o particular. Na Izzycar tratamos desta comunicação para si, verificando o estado real do carro, confirmando os dados técnicos e negociando em nome do cliente.</p>
<p>O pagamento ao stand é feito por <strong>transferência bancária directa</strong> — o dinheiro vai directamente do cliente para o vendedor, sem intermediários financeiros.</p>

<h2>Etapa 3 — Inspecção de origem</h2>
<p>Antes do transporte, é realizada uma inspecção técnica ao veículo no país de origem. Esta inspecção verifica o estado mecânico, elétrico e de carroçaria, e é obrigatória para o processo de ISV em Portugal.</p>

<h2>Etapa 4 — Transporte até Portugal</h2>
<p>O transporte é feito por camião transportador especializado. O tempo médio desde a Alemanha é de <strong>7 a 12 dias úteis</strong>. O veículo viaja segurado e o cliente recebe actualizações do percurso.</p>

<h2>Etapa 5 — Legalização em Portugal</h2>
<p>Depois de chegar a Portugal, começa o processo de legalização que inclui:</p>
<ol>
  <li><strong>IPO (Inspecção Periódica Obrigatória):</strong> o veículo é sujeito a inspecção técnica portuguesa</li>
  <li><strong>Cálculo e pagamento do ISV:</strong> entregue o processo na AT e pago o imposto</li>
  <li><strong>IMT (Imposto Municipal sobre Transmissões):</strong> pago no registo de propriedade</li>
  <li><strong>Emissão de matrícula portuguesa:</strong> entregue a documentação no IMT</li>
  <li><strong>Registo de propriedade:</strong> a viatura fica oficialmente em nome do proprietário português</li>
</ol>

<h2>Quanto tempo demora o processo completo?</h2>
<p>Da aprovação da proposta à entrega do veículo, o processo demora tipicamente <strong>20 a 25 dias úteis</strong>. O maior factor de variação é o calendário da AT para o processamento do ISV.</p>

<h2>Quanto custa no total?</h2>
<p>Além do preço do veículo, os custos a contar são:</p>
<ul>
  <li>ISV (variável conforme o carro)</li>
  <li>IUC (Imposto Único de Circulação) — pago anualmente</li>
  <li>Transporte desde a Alemanha</li>
  <li>Inspecção de origem</li>
  <li>IPO em Portugal</li>
  <li>IMT e emissão de matrícula</li>
  <li>Honorários de gestão do processo</li>
</ul>
<p>Use o nosso <a href="/simulador-custos"><strong>simulador de custos</strong></a> para ter uma estimativa completa em minutos, sem necessidade de falar com ninguém.</p>

<h2>Posso fazer este processo sozinho?</h2>
<p>Tecnicamente sim, mas a maioria dos clientes que tentam sozinhos deparam-se com barreiras linguísticas na comunicação com os stands alemães, dificuldade em verificar o estado real dos carros à distância e burocracia na legalização portuguesa. Um erro num destes pontos pode custar muito mais do que o serviço de um especialista.</p>
<p>Na Izzycar gerimos todo o processo, com comunicação constante e preço fechado desde o início. <a href="/formulario-importacao"><strong>Peça já a sua proposta gratuita.</strong></a></p>
',
                    'summary'  => '<p>Importar da Alemanha pode poupar 15 a 30% face ao mercado português. O processo tem 5 etapas principais — pesquisa, aquisição, inspecção, transporte e legalização — e demora em média 20 a 25 dias úteis. A Izzycar trata de tudo, com preço fechado e transparente desde o primeiro dia.</p>',
                ],
            ],

            // ─────────────────────────────────────────────────────────────────
            // ARTIGO 3 — ISV vs IUC
            // ─────────────────────────────────────────────────────────────────
            [
                'slug'            => 'isv-vs-iuc-diferenca-calculo-portugal',
                'seo_title'       => 'ISV vs IUC: Qual a Diferença e Como São Calculados em Portugal',
                'seo_description' => 'Confunde ISV com IUC? Explicamos a diferença entre estes dois impostos automóvel em Portugal, como são calculados e quando cada um é pago. Tudo que precisa de saber.',
                'seo_keywords'    => 'ISV vs IUC, diferença ISV IUC, imposto único circulação, imposto sobre veículos, impostos carro Portugal',
                'contents' => [
                    'title'    => 'ISV vs IUC: Qual a Diferença e Como São Calculados em Portugal',
                    'subtitle' => '<p>É uma das dúvidas mais frequentes de quem está a pensar importar um carro: <em>qual a diferença entre ISV e IUC?</em> São dois impostos distintos, com naturezas completamente diferentes, e é essencial compreender cada um antes de tomar decisões.</p>',
                    'date'     => '2025-04-20T09:00',
                    'status'   => 'Publicado',
                    'image'    => '',
                    'content'  => '
<h2>ISV — Imposto Sobre Veículos: paga-se uma vez</h2>
<p>O ISV é um <strong>imposto de matrícula</strong>. Paga-se uma única vez, quando o veículo é matriculado pela primeira vez em Portugal. Se comprar um carro já matriculado em Portugal, não paga ISV — esse imposto já foi liquidado quando o carro foi registado inicialmente.</p>
<p>Quando importa um carro do estrangeiro, está a introduzir em Portugal um veículo que ainda não foi tributado cá. Por isso tem de pagar o ISV antes de poder emitir matrícula portuguesa.</p>

<h3>Como é calculado o ISV?</h3>
<p>O ISV resulta da soma de duas componentes:</p>
<ul>
  <li><strong>Componente cilindrada:</strong> depende do tamanho do motor (cc)</li>
  <li><strong>Componente ambiental:</strong> depende das emissões de CO₂ (g/km)</li>
</ul>
<p>A este valor aplicam-se reduções progressivas por idade do veículo (até 80% para carros com mais de 10 anos) e benefícios fiscais para eléctricos (isenção total) e híbridos plug-in.</p>

<h3>Quem paga o ISV?</h3>
<p>Paga quem regista o veículo pela primeira vez em Portugal — no caso da importação, é o importador (a pessoa que traz o carro do estrangeiro).</p>

<hr>

<h2>IUC — Imposto Único de Circulação: paga-se todos os anos</h2>
<p>O IUC é um <strong>imposto anual</strong>, pago pelo proprietário do veículo enquanto este estiver matriculado. É independente de ter sido importado ou comprado em Portugal — qualquer carro com matrícula portuguesa paga IUC.</p>
<p>O vencimento do IUC coincide com o mês de matrícula do veículo. Se o seu carro tem matrícula de Março, paga o IUC em Março de cada ano.</p>

<h3>Como é calculado o IUC?</h3>
<p>O IUC varia consoante:</p>
<ul>
  <li>A cilindrada do motor (cc)</li>
  <li>O ano de matrícula (veículos mais antigos pagam menos)</li>
  <li>As emissões de CO₂ (para viaturas matriculadas após 2007)</li>
  <li>O tipo de combustível</li>
</ul>
<p>A título indicativo, um carro a gasolina de 2 000 cc matriculado em 2020 paga entre 150 e 250 € de IUC por ano. Um eléctrico paga significativamente menos.</p>

<hr>

<h2>Tabela comparativa: ISV vs IUC</h2>
<table style="width:100%; border-collapse:collapse; margin:1.5rem 0;">
  <thead>
    <tr style="background:#6e0707; color:#fff;">
      <th style="padding:.75rem 1rem; text-align:left;">Característica</th>
      <th style="padding:.75rem 1rem; text-align:left;">ISV</th>
      <th style="padding:.75rem 1rem; text-align:left;">IUC</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background:#f9fafb;">
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Frequência</td>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Uma vez (na matrícula)</td>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Anual</td>
    </tr>
    <tr>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Quem paga</td>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Quem importa/matricula</td>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Proprietário actual</td>
    </tr>
    <tr style="background:#f9fafb;">
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Base de cálculo</td>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Cilindrada + CO₂</td>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Cilindrada + CO₂ + ano</td>
    </tr>
    <tr>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Eléctricos</td>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Isentos</td>
      <td style="padding:.75rem 1rem; border-bottom:1px solid #e5e7eb;">Taxa muito reduzida</td>
    </tr>
    <tr style="background:#f9fafb;">
      <td style="padding:.75rem 1rem;">Onde pagar</td>
      <td style="padding:.75rem 1rem;">Autoridade Tributária (AT)</td>
      <td style="padding:.75rem 1rem;">Portal das Finanças (online)</td>
    </tr>
  </tbody>
</table>

<h2>E o IMT? Não confundir com ISV ou IUC</h2>
<p>Existe ainda o <strong>IMT — Imposto Municipal sobre Transmissões</strong>, que se paga quando há uma transacção de propriedade (compra e venda). O IMT é calculado sobre o valor do veículo e está tabelado. Na importação, é pago quando se faz o registo de propriedade em nome do comprador português.</p>

<h2>Resumo para quem quer importar</h2>
<p>Ao importar um carro do estrangeiro, os impostos a considerar são:</p>
<ol>
  <li><strong>ISV</strong> — pago uma vez, antes da matrícula (o maior custo fiscal)</li>
  <li><strong>IMT</strong> — pago no registo de propriedade</li>
  <li><strong>IUC</strong> — pago anualmente pelo proprietário</li>
</ol>
<p>Para saber exactamente quanto vai pagar de ISV antes de avançar, utilize o nosso <a href="/simulador-custos"><strong>simulador gratuito</strong></a>.</p>
',
                    'summary'  => '<p>O ISV paga-se uma única vez na matrícula, o IUC paga-se todos os anos. São impostos distintos com bases de cálculo diferentes. Ao importar, conta com os dois — mais o IMT no registo de propriedade. Use o simulador da Izzycar para calcular tudo antecipadamente.</p>',
                ],
            ],

            // ─────────────────────────────────────────────────────────────────
            // ARTIGO 4 — Carros mais importados e porquê
            // ─────────────────────────────────────────────────────────────────
            [
                'slug'            => 'carros-mais-importados-alemanha-portugal-2025',
                'seo_title'       => 'Os Carros Mais Importados da Alemanha para Portugal em 2025 (e Porquê)',
                'seo_description' => 'Descubra quais os modelos mais procurados na importação automóvel da Alemanha para Portugal em 2025 e as razões por detrás de cada escolha. Análise detalhada com preços.',
                'seo_keywords'    => 'carros importados Alemanha Portugal, melhores carros importar, modelos importação, BMW importar, Mercedes importar, importação automóvel 2025',
                'contents' => [
                    'title'    => 'Os Carros Mais Importados da Alemanha para Portugal em 2025 (e Porquê)',
                    'subtitle' => '<p>Ano após ano, certos modelos destacam-se nas encomendas de importação. A combinação de <strong>preço competitivo</strong>, <strong>baixo ISV</strong> e <strong>elevada fiabilidade</strong> define as escolhas mais populares. Analisámos os pedidos da Izzycar e identificámos os cinco modelos mais solicitados — e os motivos concretos por detrás de cada um.</p>',
                    'date'     => '2025-03-10T09:00',
                    'status'   => 'Publicado',
                    'image'    => '',
                    'content'  => '
<h2>Por que razão os portugueses importam tanto da Alemanha?</h2>
<p>A diferença de preços entre o mercado alemão e o português para o mesmo modelo pode atingir os <strong>20 000 €</strong> em segmentos premium. A isso acrescem inspecções regulares obrigatórias na Alemanha (TÜV bienal), histórico de serviço documentado e oferta abundante — factores que tornam a importação uma opção sólida para quem procura qualidade com orçamento controlado.</p>

<h2>1. BMW Série 3 e Série 4</h2>
<p>A família BMW Série 3/4 lidera consistentemente os pedidos. O motivo é simples: <strong>preço, eficiência e valor de revenda</strong>. Um 320d com 50 000 km pode ser encontrado na Alemanha por 22 000 a 28 000 €, quando em Portugal equivalentes custam 30 000 a 36 000 €.</p>
<p>O diesel 2.0 de 190 cv apresenta emissões moderadas e beneficia de uma boa redução de ISV para carros com 3-5 anos. O resultado final chave na mão em Portugal fica frequentemente <strong>8 000 a 12 000 € abaixo</strong> do preço de mercado nacional.</p>
<p><em>ISV estimado para um 320d de 2021:</em> entre 3 500 e 4 500 €, dependendo das emissões exactas.</p>

<h2>2. Mercedes-Benz Classe C e GLC</h2>
<p>A Mercedes ocupa o segundo lugar. O GLC em particular tem tido uma procura crescente — é um SUV premium com bom nível de equipamento e preços alemães muito atractivos. Um GLC 220d de 2020 pode chegar a Portugal chave na mão por menos de 38 000 €, contra os 48 000 a 52 000 € que custaria num stand português.</p>
<p>Os híbridos plug-in desta marca (GLC 300e, C 300e) são especialmente interessantes: as reduções de ISV para PHEVs com autonomia ≥50 km podem baixar o imposto em 75%, tornando a importação ainda mais vantajosa.</p>

<h2>3. Volkswagen Golf e Tiguan</h2>
<p>O Golf mantém a sua popularidade pela <strong>fiabilidade comprovada e custos de manutenção previsíveis</strong>. É o carro de família por excelência — e o mercado alemão de usados tem uma oferta enorme de versões com pouco uso.</p>
<p>O Tiguan, na versão a gasóleo ou híbrida, é a segunda opção mais solicitada neste grupo. Destaca-se pela versatilidade e pelo facto de os preços alemães estarem, em média, 25% abaixo dos preços portugueses para o mesmo estado de conservação.</p>

<h2>4. Audi A4 e Q5</h2>
<p>A Audi completa o quarteto dos mais procurados, com o A4 a liderar pelas mesmas razões do BMW: relação preço/equipamento muito superior ao mercado nacional. O Q5, especialmente nas versões híbridas plug-in (Q5 55 TFSI e), tem despertado interesse crescente pelos benefícios fiscais.</p>
<p>Um Audi Q5 40 TDI de 2021 com 60 000 km pode chegar a Portugal chave na mão por cerca de 36 000 €, um valor impossível de encontrar no mercado nacional para o mesmo nível de equipamento.</p>

<h2>5. Veículos Eléctricos (BMW iX, Mercedes EQC, VW ID.4)</h2>
<p>Os eléctricos cresceram exponencialmente nas encomendas de importação. A razão é directa: <strong>isenção total de ISV</strong>. Um BMW iX xDrive40 pode ser comprado na Alemanha por 50 000 a 55 000 € e chegado a Portugal fica pelo mesmo valor — sem ISV a acrescentar.</p>
<p>No mercado nacional, o mesmo modelo custa entre 68 000 e 75 000 €. A poupança é significativa e torna a importação de eléctricos uma das opções com melhor rácio benefício/esforço.</p>

<h2>O que pesa na escolha?</h2>
<p>Além do preço, os factores decisivos são:</p>
<ul>
  <li><strong>ISV:</strong> um motor menor ou híbrido plug-in pode poupar 3 000 a 8 000 € só neste imposto</li>
  <li><strong>Historial do veículo:</strong> TÜV recente e livrete de serviço completo valem mais do que alguns quilómetros extra</li>
  <li><strong>Disponibilidade:</strong> a Alemanha tem stocks de determinadas configurações que não existem no mercado português</li>
  <li><strong>Prazo de entrega:</strong> ao contrário de encomenda de novo, um usado alemão chega em 3-4 semanas</li>
</ul>

<h2>Quer importar um destes modelos?</h2>
<p>Na Izzycar pesquisamos no mercado alemão exactamente o que procura — com o historial verificado, negociação incluída e processo chave na mão. <a href="/formulario-importacao"><strong>Peça já a sua proposta gratuita</strong></a> ou use o <a href="/simulador-custos"><strong>simulador de custos</strong></a> para perceber o valor total antes de decidir.</p>
',
                    'summary'  => '<p>BMW Série 3/4, Mercedes GLC, VW Golf/Tiguan, Audi A4/Q5 e eléctricos são os modelos mais importados da Alemanha. As poupanças vão de 8 000 a 20 000 € por viatura, dependendo do modelo e do ISV. Os híbridos plug-in e os 100% eléctricos têm benefícios fiscais adicionais que tornam a importação ainda mais atrativa.</p>',
                ],
            ],

        ];
    }
}
