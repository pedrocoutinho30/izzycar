<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Page;
use App\Models\PageContent;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Criar a página de consignação
        $page = Page::create([
            'name' => 'Consignação de Automóveis',
            'slug' => 'consignacao-automovel',
            'is_active' => true,
        ]);

        // Adicionar conteúdo da página
        PageContent::create([
            'page_id' => $page->id,
            'field_name' => 'title',
            'field_value' => 'Venda o Seu Carro à Consignação',
        ]);

        PageContent::create([
            'page_id' => $page->id,
            'field_name' => 'subtitle',
            'field_value' => 'Deixe-nos cuidar de todo o processo de venda enquanto você aproveita o seu tempo. Sem complicações, sem stress.',
        ]);

        PageContent::create([
            'page_id' => $page->id,
            'field_name' => 'content',
            'field_value' => '<p>A <strong>Izzycar</strong> oferece um serviço completo de venda à consignação, pensado para tornar o processo de venda do seu veículo simples, rápido e transparente.</p>
            <p>Com a nossa experiência no mercado automóvel, garantimos que o seu carro é apresentado da melhor forma possível aos potenciais compradores, maximizando o seu valor de venda.</p>
            <p>Não perca tempo com negociações intermináveis, visitas inconvenientes ou preocupações com documentação - tratamos de tudo por si!</p>',
        ]);

        // Adicionar passos do processo (enum)
        $steps = [
            [
                'title' => 'Avaliação do Veículo',
                'content' => '<p>Fazemos uma avaliação completa do seu veículo para determinar o melhor preço de venda. A nossa equipa analisa o estado do carro, o mercado atual e as características específicas para garantir uma avaliação justa e competitiva.</p>',
            ],
            [
                'title' => 'Armazenamento Seguro',
                'content' => '<p>O seu veículo fica guardado num espaço seguro e protegido enquanto está à venda. Pode manter o carro consigo até encontrarmos um comprador ou deixá-lo nas nossas instalações - a escolha é sua.</p>',
            ],
            [
                'title' => 'Preparação Profissional',
                'content' => '<p>Realizamos uma limpeza completa e preparação estética do veículo. Pequenos detalhes fazem toda a diferença - certificamo-nos de que o seu carro está impecável para causar a melhor impressão.</p>',
            ],
            [
                'title' => 'Fotografia e Publicidade',
                'content' => '<p>Sessão fotográfica profissional e criação de anúncios atrativos. Publicamos o seu veículo nas principais plataformas online como StandVirtual, AutoScout24 e nas nossas redes sociais, garantindo máxima visibilidade.</p>',
            ],
            [
                'title' => 'Gestão de Contactos',
                'content' => '<p>Tratamos de todos os contactos com potenciais compradores, agendamos visitas e fazemos test-drives. Poupamos o seu tempo e evitamos visitas inconvenientes ou negociações desconfortáveis.</p>',
            ],
            [
                'title' => 'Documentação e Venda',
                'content' => '<p>Quando vendemos o seu carro, tratamos de toda a documentação legal e do registo de propriedade. Garantimos uma transação segura e transparente do início ao fim, recebendo o pagamento antes de entregar o veículo.</p>',
            ],
        ];

        foreach ($steps as $index => $step) {
            PageContent::create([
                'page_id' => $page->id,
                'field_name' => 'enum',
                'field_value' => json_encode([
                    'order' => $index + 1,
                    'title' => $step['title'],
                    'content' => $step['content'],
                ]),
            ]);
        }

        // Adicionar SEO
        PageContent::create([
            'page_id' => $page->id,
            'field_name' => 'meta_title',
            'field_value' => 'Venda à Consignação | Izzycar - Vendemos o Seu Carro',
        ]);

        PageContent::create([
            'page_id' => $page->id,
            'field_name' => 'meta_description',
            'field_value' => 'Venda o seu carro sem complicações com o serviço de consignação da Izzycar. Cuidamos de tudo: fotografia, publicidade, negociação e documentação. Processo simples e transparente.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $page = Page::where('slug', 'consignacao-automovel')->first();
        if ($page) {
            PageContent::where('page_id', $page->id)->delete();
            $page->delete();
        }
    }
};

