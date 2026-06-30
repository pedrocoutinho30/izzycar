<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CmsPage;
use App\Models\CmsBlock;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────────
        // HOMEPAGE
        // ──────────────────────────────────────────────────
        $home = CmsPage::firstOrCreate(['slug' => 'home'], [
            'name'  => 'Homepage',
            'order' => 1,
        ]);

        $this->blocks($home, [

            ['type' => 'hero', 'name' => 'hero', 'order' => 1,
                'title'       => 'O Seu Carro dos Sonhos, Ao Melhor Preço',
                'subtitle'    => 'Especializados em importação de veículos de toda a Europa, oferecemos um serviço completo e transparente. Desde a procura até à entrega, cuidamos de cada detalhe para que o seu carro chegue pronto a conduzir.',
                'body'        => 'Importação Automóvel Chave na Mão', // badge
                'button_text' => 'Quero Importar',
                'button_url'  => '/importacao',
                'button2_text'=> 'Simular Custos',
                'button2_url' => '/simulador-custos',
            ],

            ['type' => 'badges', 'name' => 'trust_badges', 'order' => 2,
                'title' => 'Por que nos escolher',
                'data'  => [
                    ['icon' => 'bi-shield-check',   'title' => '100% Seguro',     'text' => 'Processo transparente e garantido'],
                    ['icon' => 'bi-clock-history',  'title' => 'Entrega Rápida',  'text' => '3-6 semanas em média'],
                    ['icon' => 'bi-tag',            'title' => 'Melhor Preço',    'text' => 'Economize até 30%'],
                    ['icon' => 'bi-headset',        'title' => 'Suporte Total',   'text' => 'Do início ao fim'],
                ],
            ],

            ['type' => 'cards', 'name' => 'services', 'order' => 3,
                'title'    => 'Os Nossos Serviços',
                'subtitle' => 'Soluções completas para importar e legalizar o seu automóvel',
                'data'     => [
                    [
                        'icon'        => 'bi-car-front',
                        'title'       => 'Importação Chave na Mão',
                        'text'        => 'Serviço completo de importação de veículos da Europa. Tratamos de tudo: procura, negociação, transporte, legalização e entrega.',
                        'link'        => '/importacao',
                        'button_text' => 'Saber mais',
                    ],
                    [
                        'icon'        => 'bi-file-earmark-check',
                        'title'       => 'Legalização de Veículos',
                        'text'        => 'Tratamos de todo o processo de legalização do seu veículo importado em Portugal — ISV, IPO, IMT e matrícula.',
                        'link'        => '/legalizacao',
                        'button_text' => 'Saber mais',
                    ],
                ],
            ],

            ['type' => 'cards', 'name' => 'why_us', 'order' => 4,
                'title'    => 'Experiência e Confiança ao Seu Serviço',
                'subtitle' => 'A Izzycar é a sua parceira de confiança para importação de veículos. Trabalhamos com transparência total, sem custos escondidos.',
                'data'     => [
                    ['icon' => 'bi-eye',             'title' => 'Processo Transparente', 'text' => 'Acompanhamento em tempo real de todas as etapas da importação'],
                    ['icon' => 'bi-search',          'title' => 'Inspeção Rigorosa',     'text' => 'Todos os veículos são inspecionados antes da compra'],
                    ['icon' => 'bi-person-check',    'title' => 'Apoio Personalizado',   'text' => 'Equipa dedicada disponível para esclarecer todas as suas dúvidas'],
                ],
            ],

            ['type' => 'steps', 'name' => 'process', 'order' => 5,
                'title'    => 'Como Funciona',
                'subtitle' => 'Em 4 passos simples, o seu carro importado chega às suas mãos',
                'button_text' => 'Ver Processo Detalhado',
                'button_url'  => '/importacao',
                'data' => [
                    ['number' => '01', 'title' => 'Pedido de Cotação',   'text' => 'Preencha o formulário com as características do carro que deseja. Respondemos em 24h com uma cotação detalhada e transparente.'],
                    ['number' => '02', 'title' => 'Procura e Seleção',   'text' => 'Procuramos o veículo perfeito para si nos melhores mercados europeus. Inspecionamos e enviamos relatório fotográfico completo.'],
                    ['number' => '03', 'title' => 'Compra e Transporte', 'text' => 'Após aprovação, compramos e tratamos do transporte seguro até Portugal. Acompanhe todo o processo em tempo real.'],
                    ['number' => '04', 'title' => 'Legalização e Entrega', 'text' => 'Tratamos de toda a papelada, inspeção e matrícula. Recebe o seu carro pronto a conduzir, com documentação completa.'],
                ],
            ],

            ['type' => 'cta', 'name' => 'cta_final', 'order' => 6,
                'title'       => 'Comece a Importar Hoje',
                'subtitle'    => 'Peça uma cotação sem compromisso e descubra quanto pode economizar ao importar o seu próximo carro connosco.',
                'body'        => '+351 928 459 346', // telefone de contacto
                'button_text' => 'Pedir Cotação',
                'button_url'  => '/importacao',
                'button2_text'=> 'Simular Custos',
                'button2_url' => '/simulador-custos',
            ],
        ]);

        // ──────────────────────────────────────────────────
        // IMPORTAÇÃO
        // ──────────────────────────────────────────────────
        $import = CmsPage::firstOrCreate(['slug' => 'importacao'], [
            'name'  => 'Importação',
            'order' => 2,
        ]);

        $this->blocks($import, [

            ['type' => 'hero', 'name' => 'hero', 'order' => 1,
                'title'       => 'Importação Automóvel Chave na Mão',
                'subtitle'    => 'A forma segura e descomplicada de importar o seu próximo carro.',
                'body'        => 'Importação Chave na Mão', // badge
                'button_text' => 'Pedir Cotação',
                'button_url'  => '/importacao/formulario',
                'button2_text'=> 'Simular Custos',
                'button2_url' => '/simulador-custos',
                'image'       => 'files/carros_escala.jpg',
            ],

            ['type' => 'badges', 'name' => 'trust_badges', 'order' => 2,
                'data' => [
                    ['icon' => 'bi-shield-check',  'title' => 'Processo Seguro',     'text' => '100% Transparente'],
                    ['icon' => 'bi-clock',         'title' => '3-6 Semanas',         'text' => 'Entrega Média'],
                    ['icon' => 'bi-emoji-smile',   'title' => 'Zero Preocupações',   'text' => 'Tratamos de Tudo'],
                    ['icon' => 'bi-piggy-bank',    'title' => 'Até 30% Poupança',    'text' => 'Melhor Preço'],
                ],
            ],

            ['type' => 'text', 'name' => 'intro', 'order' => 3,
                'title' => 'O Nosso Processo de Importação',
                'body'  => '<p>Na <strong style="color:#6e0707">Izzycar</strong>, acreditamos que importar um automóvel deve ser uma experiência simples, transparente e sem surpresas. O nosso método de importação é simples e transparente:</p><ul><li><strong>Veículos</strong>: Não importamos carros abaixo de 10.000€ (preço na origem) nem veículos com mais de 10 anos.</li><li><strong>Fornecedores</strong>: Trabalhamos exclusivamente com profissionais e stands automóvel certificados.</li><li><strong>Processo</strong>: Total acompanhamento desde a seleção até à entrega chave na mão em Portugal.</li></ul>',
            ],

            ['type' => 'cards', 'name' => 'why_import', 'order' => 4,
                'title'    => 'Porque Importar com a Izzycar?',
                'subtitle' => 'Razões concretas para escolher a importação',
                'data'     => [
                    ['icon' => 'bi-piggy-bank',    'title' => '01. Poupança face ao mercado nacional',    'text' => 'Importar permite-lhe aceder a veículos até 30% mais económicos, mesmo considerando transporte e legalização.'],
                    ['icon' => 'bi-globe2',        'title' => '02. Maior oferta nos mercados europeus',   'text' => 'Com acesso aos principais mercados da Europa, encontra modelos, versões e motorizações muitas vezes indisponíveis em Portugal.'],
                    ['icon' => 'bi-clipboard-data','title' => '03. Histórico de manutenções rigoroso',    'text' => 'Todos os automóveis que importamos têm histórico detalhado de quilometragem, revisões e sinistralidade, garantindo total confiança.'],
                    ['icon' => 'bi-person-check',  'title' => '04. Acompanhamento pessoal e imparcial',   'text' => 'O nosso compromisso é ajudá-lo a encontrar o carro ideal, sem conflitos de interesse, sempre com acompanhamento personalizado.'],
                ],
            ],

            ['type' => 'steps', 'name' => 'process', 'order' => 5,
                'title'    => 'O Nosso Processo Passo a Passo',
                'subtitle' => 'Da seleção à entrega, tratamos de tudo',
                'data'     => [
                    ['number' => '1', 'title' => 'Seleção do carro',                    'text' => 'Quer já tenha um modelo em mente ou esteja apenas à procura da melhor opção, ajudamos a encontrar a viatura que corresponde às suas expectativas.'],
                    ['number' => '2', 'title' => 'Verificação e controlo de qualidade', 'text' => 'Através do número de chassis (VIN), verificamos quilometragem real, registos de sinistros, antigos proprietários e alertas internacionais, assegurando total confiança.'],
                    ['number' => '3', 'title' => 'Compra do veículo',                   'text' => 'Negociamos as melhores condições de compra, assegurando um bom preço final e um contrato transparente.'],
                    ['number' => '4', 'title' => 'Transporte Seguro',                   'text' => 'Trabalhamos apenas com transportadoras certificadas, que oferecem seguro de carga, de maneira a garantir que o seu automóvel chega a Portugal com total segurança.'],
                    ['number' => '5', 'title' => 'Legalização completa',                'text' => 'Tratamos de todo o processo legal: inspeção obrigatória, matrícula portuguesa, pagamento de impostos e entrega da documentação final.'],
                    ['number' => '6', 'title' => 'Entrega chave na mão',                'text' => 'Entregamos o seu automóvel em perfeitas condições, com transparência durante todo o processo e sem burocracia.'],
                ],
            ],

            ['type' => 'costs', 'name' => 'costs', 'order' => 6,
                'title'    => 'Custos da Importação',
                'subtitle' => 'Transparência total — sem surpresas',
                'data'     => [
                    ['icon' => 'bi-car-front',       'title' => 'Preço da Viatura',              'text' => 'Valor do carro adquirido fora de Portugal, geralmente mais baixo do que no mercado nacional.',    'badge' => 'Direto ao vendedor'],
                    ['icon' => 'bi-truck',           'title' => 'Valor do Transporte',           'text' => 'Inclui o transporte seguro, desde o vendedor até ao destino final.',                                 'badge' => 'Incluído no serviço'],
                    ['icon' => 'bi-receipt',         'title' => 'Imposto Sobre Veículos (ISV)',  'text' => 'Imposto obrigatório pago ao Estado durante a legalização do veículo em Portugal.',                   'badge' => 'Pago às autoridades'],
                    ['icon' => 'bi-calendar-check',  'title' => 'Imposto Único de Circulação (IUC)', 'text' => 'Imposto anual que terá de pagar após a legalização, tal como acontece com qualquer viatura nacional.', 'badge' => 'Pago às autoridades'],
                    ['icon' => 'bi-clipboard-check', 'title' => 'Inspeção, Matrícula e Legalização', 'text' => 'Abrange o custo da inspeção obrigatória, emissão da matrícula nacional e todo o processo administrativo de legalização.', 'badge' => 'Incluído no serviço'],
                    ['icon' => 'bi-briefcase',       'title' => 'Honorários do Serviço',         'text' => 'Valor fixo pelo serviço completo de importação e legalização.',                                      'badge' => 'Incluído no serviço'],
                ],
            ],

            ['type' => 'faq', 'name' => 'faq', 'order' => 7,
                'title'    => 'Perguntas Frequentes',
                'subtitle' => 'Tudo o que precisa de saber sobre importação',
                'data'     => [
                    ['question' => 'Quanto tempo demora o processo de importação?',                 'answer' => 'O processo demora habitualmente entre 3 a 6 semanas, dependendo da disponibilidade do veículo, do transporte e dos prazos administrativos de legalização.'],
                    ['question' => 'Posso escolher qualquer carro ou a Izzycar ajuda-me na seleção?', 'answer' => 'Pode escolher um modelo específico ou apenas indicar as características que procura. A Izzycar apresenta sempre uma proposta detalhada, com preço completo (chave na mão), antes de qualquer compromisso.'],
                    ['question' => 'Como posso ter a certeza de que o carro está em boas condições?', 'answer' => 'É realizada uma vistoria presencial e em paralelo uma análise detalhada do histórico da viatura que é apresentada ao cliente.'],
                    ['question' => 'O transporte é seguro?',                                        'answer' => 'Sim. Trabalhamos apenas com transportadoras certificadas, com seguro incluído. Para além disso, mantemos o cliente informado sobre o progresso do transporte.'],
                    ['question' => 'Que veículos importam?',                                        'answer' => 'Importamos todo o tipo de veículos com preço de origem a partir dos 10.000€, adquiridos exclusivamente a profissionais ou stands automóvel.'],
                    ['question' => 'Como funciona o pagamento?',                                    'answer' => 'A compra requer pagamento integral, sem possibilidade de financiamento ou retomas. O serviço é pago em duas fases: 60% no início e 40% na entrega.'],
                    ['question' => 'E se a compra for em nome de uma empresa?',                     'answer' => 'Quando a compra é feita em nome de uma pessoa coletiva, dependendo da viatura, pode haver lugar à devolução do IVA. Nesse caso, o valor do IVA funciona como um encargo provisório.'],
                    ['question' => 'Posso acompanhar todo o processo de importação do meu carro?',  'answer' => 'Sim. A Izzycar garante total visibilidade em cada etapa. Desde a escolha do veículo até à entrega final, o cliente recebe atualizações regulares sobre o estado do processo.'],
                    ['question' => 'O que acontece quando o carro chega a Portugal?',               'answer' => 'O carro é descarregado no centro de inspeções para a realização da Inspeção obrigatória e depois é guardado nas nossas instalações até à emissão da matrícula.'],
                    ['question' => 'Como é feita a entrega final?',                                 'answer' => 'Após emissão da matrícula, mandamos fazer as chapas, o cliente contrata o seguro automóvel e pode levantar o carro.'],
                    ['question' => 'Como funciona o pagamento do ISV e a matrícula?',               'answer' => 'Depois de toda a documentação ser entregue ao IMT/Alfândega, é emitida uma guia de pagamento do ISV, que deve ser liquidada de imediato. Em 48 a 72h é emitida a matrícula.'],
                ],
            ],

        ]);

        // ──────────────────────────────────────────────────
        // LEGALIZAÇÃO
        // ──────────────────────────────────────────────────
        $legal = CmsPage::firstOrCreate(['slug' => 'legalizacao'], [
            'name'  => 'Legalização',
            'order' => 3,
        ]);

        $this->blocks($legal, [

            ['type' => 'hero', 'name' => 'hero', 'order' => 1,
                'title'       => 'Legalização Automóvel',
                'subtitle'    => 'Tratamos de todo o processo de legalização do seu veículo importado em Portugal — ISV, IPO, IMT e matrícula — de forma rápida, transparente e sem complicações.',
                'body'        => 'Legalização de Veículos', // badge
                'button_text' => 'Pedir Cotação',
                'button_url'  => '/importacao/formulario',
            ],

            ['type' => 'text', 'name' => 'intro', 'order' => 2,
                'title' => 'O Processo de Legalização',
                'body'  => '<p style="text-align:center;">Garantimos o processo de legalização de viaturas adquiridas no estrangeiro de forma segura e descomplicada. A nossa equipa trata de todos os documentos, impostos e registos necessários para que o seu veículo fique legalmente matriculado em Portugal.</p>',
            ],

            ['type' => 'steps', 'name' => 'steps', 'order' => 3,
                'title'    => 'Etapas do Processo',
                'subtitle' => '6 passos para legalizar o seu veículo em Portugal',
                'data'     => [
                    ['number' => '01', 'title' => 'Documentação Necessária',                       'text' => 'Reunimos toda a documentação necessária: Certificado de Conformidade, título de registo do país de origem, fatura de compra, documento de identificação e comprovativo de morada do comprador.'],
                    ['number' => '02', 'title' => 'Inspeção Técnica',                              'text' => 'O veículo é avaliado num Centro de Inspeção Periódica Automóvel (CIPA) para garantir que cumpre todas as normas de segurança e emissões exigidas em Portugal.'],
                    ['number' => '03', 'title' => 'Declaração Aduaneira de Veículos (DAV)',        'text' => 'Submetemos a DAV para que o veículo seja corretamente registado e classificado para efeitos fiscais e aduaneiros.'],
                    ['number' => '04', 'title' => 'Pagamento de Impostos',                         'text' => 'O valor do pagamento é gerado com base nas características do veículo. Tratamos do cálculo e pagamento do ISV (Imposto Sobre Veículos) junto da Autoridade Tributária.'],
                    ['number' => '05', 'title' => 'Pedido e Emissão de Matrículas',                'text' => 'Depois de obtida a matrícula entregamos os documentos do veículo. O registo fica feito em nome do comprador com morada portuguesa.'],
                    ['number' => '06', 'title' => 'Registo na Conservatória do Registo Automóvel', 'text' => 'Transferimos oficialmente a propriedade do veículo para o nome do comprador junto da Conservatória do Registo Automóvel, completando assim o processo de legalização.'],
                ],
            ],

        ]);

        $this->command->info('CMS seeded: ' . CmsPage::count() . ' páginas, ' . CmsBlock::count() . ' blocos.');
    }

    private function blocks(CmsPage $page, array $blocks): void
    {
        foreach ($blocks as $block) {
            CmsBlock::updateOrCreate(
                ['cms_page_id' => $page->id, 'name' => $block['name']],
                array_merge(['cms_page_id' => $page->id], $block)
            );
        }
    }
}
