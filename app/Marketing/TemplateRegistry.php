<?php

namespace App\Marketing;

use App\Enums\MarketingFormat as F;
use App\Enums\MarketingPostType as T;

/**
 * Registo dos templates. Para acrescentar um: criar o partial
 * resources/views/admin/marketing/templates/<chave>.blade.php e declará-lo aqui.
 */
class TemplateRegistry
{
    /** @return array<string, TemplateDefinition> */
    public static function all(): array
    {
        $todos = [F::FeedVertical, F::FeedSquare, F::Story];
        $feed = [F::FeedVertical, F::FeedSquare];
        $carro = [T::Veiculo, T::Recomendacao];
        $texto = [T::Educativo, T::Servico];

        $defs = [
            new TemplateDefinition('premium', 'Premium', 'Fotografia dominante e tipografia elegante.', $carro, $todos, ['title', 'subtitle', 'price', 'highlights', 'cta', 'photos']),
            new TemplateDefinition('comercial', 'Comercial', 'Preço, características e chamada à ação em destaque.', $carro, $todos, ['title', 'subtitle', 'price', 'highlights', 'cta', 'photos']),
            new TemplateDefinition('minimalista', 'Minimalista', 'Fotografia, modelo, preço e logótipo.', $carro, $todos, ['title', 'subtitle', 'price', 'photos']),
            new TemplateDefinition('equipamento', 'Destaque de equipamento', 'Características e equipamentos relevantes.', $carro, $todos, ['title', 'subtitle', 'highlights', 'cta', 'photos']),
            new TemplateDefinition('carrossel', 'Carrossel automóvel', 'Capa, exterior, interior, equipamentos, características e chamada à ação.', $carro, $feed, ['title', 'subtitle', 'price', 'highlights', 'cta', 'photos'], 2, 8),
            new TemplateDefinition('novo-stock', 'Novo em stock', 'Anuncia uma viatura recém-disponível.', [T::Veiculo], $feed, ['title', 'subtitle', 'price', 'highlights', 'cta', 'photos']),
            new TemplateDefinition('vendido', 'Vendido / Entregue', 'Divulga uma venda ou entrega concluída.', [T::Veiculo], $feed, ['title', 'subtitle', 'photos']),
            new TemplateDefinition('recomendacao', 'Recomendação', 'Carro recomendado, com preço e poupança quando existirem.', [T::Recomendacao], $todos, ['title', 'subtitle', 'price', 'savings', 'highlights', 'cta', 'photos']),
            new TemplateDefinition('dica-rapida', 'Dica rápida', 'Um título e uma dica curta.', $texto, $todos, ['title', 'subtitle', 'cta', 'items'], 1, 1, 1),
            new TemplateDefinition('mito-verdade', 'Mito ou verdade', 'Carrossel: um mito e a verdade por slide.', $texto, $feed, ['title', 'subtitle', 'cta', 'items'], 2, 10, 8),
            new TemplateDefinition('pergunta-frequente', 'Pergunta frequente', 'Uma pergunta e a resposta.', $texto, $todos, ['title', 'cta', 'items'], 1, 1, 1),
            new TemplateDefinition('passo-a-passo', 'Passo a passo', 'Até 6 passos numerados.', $texto, $todos, ['title', 'subtitle', 'cta', 'items'], 1, 1, 6),
            new TemplateDefinition('erros-comuns', 'Erros comuns', 'Até 5 erros a evitar.', $texto, $todos, ['title', 'subtitle', 'cta', 'items'], 1, 1, 5),
            new TemplateDefinition('educativo-carrossel', 'Carrossel educativo', 'Capa, um ponto por slide e chamada à ação.', $texto, $feed, ['title', 'subtitle', 'cta', 'items'], 2, 10, 8),
            new TemplateDefinition('servico', 'Serviço', 'Apresenta um serviço com até 4 pontos.', [T::Servico], $todos, ['title', 'subtitle', 'cta', 'items'], 1, 1, 4),
            new TemplateDefinition('importacao', 'Importação realizada', 'Mais uma importação entregue, com origem e poupança quando existirem.', [T::Importacao], $todos, ['title', 'subtitle', 'savings', 'highlights', 'cta', 'photos']),
            new TemplateDefinition('testemunho', 'Testemunho', 'Citação de um cliente, com estrelas, nome e origem.', [T::Testemunho], $todos, ['quote', 'cta']),
        ];

        $out = [];
        foreach ($defs as $d) {
            $out[$d->key] = $d;
        }

        return $out;
    }

    public static function get(string $key): TemplateDefinition
    {
        return self::all()[$key] ?? throw new \InvalidArgumentException("Template desconhecido: {$key}");
    }

    /** Template válido para o tipo e formato, ou ValidationException com mensagem em português. */
    public static function resolve(string $key, T $type, F $format): TemplateDefinition
    {
        $all = self::all();
        if (! isset($all[$key]) || ! $all[$key]->supportsType($type)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['template' => 'Template inválido para este tipo de publicação.']);
        }
        if (! $all[$key]->supports($format)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['format' => 'Este template não suporta o formato escolhido.']);
        }

        return $all[$key];
    }

    /** @return array<string, TemplateDefinition> */
    public static function forType(T $type): array
    {
        return array_filter(self::all(), fn (TemplateDefinition $d) => $d->supportsType($type));
    }
}
