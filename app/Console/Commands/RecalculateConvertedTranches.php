<?php

namespace App\Console\Commands;

use App\Models\ConvertedProposal;
use App\Services\ServiceTranches;
use Illuminate\Console\Command;

/**
 * Recalcula as tranches de cotações convertidas pelas percentagens das
 * Definições (init_payment / final_payment), sobre o total do serviço Izzycar
 * (custos do processo + comissão, sem ISV nem valor do carro).
 *
 * Só mostra o que mudaria; grava com --apply. Ignora as cotações convertidas
 * com alguma tranche já marcada como paga (mexeriam nos movimentos financeiros)
 * e as canceladas.
 */
class RecalculateConvertedTranches extends Command
{
    protected $signature = 'converted-proposals:recalculate-tranches
        {id? : Id da cotação convertida (sem id, todas)}
        {--apply : Grava as alterações (sem esta opção só mostra a diferença)}';

    protected $description = 'Recalcula as tranches das cotações convertidas pelas percentagens das Definições';

    public function handle(): int
    {
        $query = ConvertedProposal::notCancelled()->orderBy('id');
        if ($this->argument('id')) {
            $query->whereKey($this->argument('id'));
        }

        $settings = ServiceTranches::settings();
        $rows = [];
        $changed = 0;

        foreach ($query->get() as $converted) {
            $new = ServiceTranches::split($converted->serviceTotal(), $settings);
            $old1 = (float) $converted->valor_primeira_tranche;
            $old2 = (float) $converted->valor_segunda_tranche;

            if (abs($old1 - $new['tranche1']) < 0.005 && abs($old2 - $new['tranche2']) < 0.005) {
                continue;
            }

            if ($converted->primeira_tranche_pago || $converted->segunda_tranche_pago) {
                $rows[] = [$converted->id, $this->label($converted), $this->fmt($old1) . ' / ' . $this->fmt($old2), $this->fmt($new['tranche1']) . ' / ' . $this->fmt($new['tranche2']), 'ignorada (tranche já paga)'];

                continue;
            }

            $rows[] = [$converted->id, $this->label($converted), $this->fmt($old1) . ' / ' . $this->fmt($old2), $this->fmt($new['tranche1']) . ' / ' . $this->fmt($new['tranche2']), $this->option('apply') ? 'atualizada' : 'a atualizar'];

            if ($this->option('apply')) {
                $converted->update(['valor_primeira_tranche' => $new['tranche1'], 'valor_segunda_tranche' => $new['tranche2']]);
                $changed++;
            }
        }

        if ($rows === []) {
            $this->info('Nada a alterar: as tranches já seguem as percentagens das Definições.');

            return self::SUCCESS;
        }

        $this->table(['Id', 'Veículo', 'Tranches atuais', 'Tranches novas', 'Estado'], $rows);
        $this->line($this->option('apply') ? "{$changed} cotação(ões) convertida(s) atualizada(s)." : 'Nada foi gravado. Volte a correr com --apply para gravar.');

        return self::SUCCESS;
    }

    private function label(ConvertedProposal $converted): string
    {
        return trim(implode(' ', array_filter([$converted->brand, $converted->modelCar]))) ?: '—';
    }

    private function fmt(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }
}
