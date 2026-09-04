<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RadarEquipment;
use App\Models\RadarEquipmentAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Curadoria do equipamento descoberto automaticamente pelo scraper (ver
 * scarperAutoscout/scraper/equipment_client.py + Database.get_or_create_equipment).
 * Cada item novo entra aqui ESCONDIDO dos filtros por omissão - o utilizador decide
 * quais mostrar, renomeia labels feias, e funde aliases equivalentes entre sites
 * (ex.: o alemão "Elektrische Sitze" e o português "Banco do condutor com regulação
 * eléctrica" podem ser o mesmo item "Bancos elétricos").
 */
class RadarEquipmentController extends Controller
{
    public function index()
    {
        $equipment = RadarEquipment::withCount(['listings', 'aliases'])
            ->with('aliases')
            ->orderByDesc('listings_count')
            ->orderBy('label')
            ->get();

        return view('admin.v2.radar-equipment.index', compact('equipment'));
    }

    public function toggleFilter(RadarEquipment $radarEquipment, Request $request)
    {
        $radarEquipment->update(['show_in_filters' => $request->boolean('show')]);

        return response()->json(['show_in_filters' => $radarEquipment->show_in_filters]);
    }

    public function update(RadarEquipment $radarEquipment, Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
        ]);

        $radarEquipment->update(['label' => $validated['label']]);

        return back()->with('success', 'Equipamento atualizado.');
    }

    /**
     * Funde este item de equipamento noutro: todos os aliases, associações a
     * anúncios e a pesquisas passam a apontar para o item destino, e este item
     * (agora órfão) é apagado. Usado para juntar o mesmo equipamento descoberto
     * com textos diferentes em sites diferentes.
     */
    public function merge(RadarEquipment $radarEquipment, Request $request)
    {
        $validated = $request->validate([
            'target_id' => ['required', 'integer', 'exists:radar_equipment,id', 'not_in:'.$radarEquipment->id],
        ]);

        $target = RadarEquipment::findOrFail($validated['target_id']);

        DB::transaction(function () use ($radarEquipment, $target) {
            $radarEquipment->aliases()->update(['radar_equipment_id' => $target->id]);

            // whereNotIn evita violar a PK composta se o mesmo anúncio/pesquisa já
            // estiver associado aos dois itens.
            DB::table('radar_listing_equipment')
                ->where('radar_equipment_id', $radarEquipment->id)
                ->whereNotIn('radar_listing_id', function ($query) use ($target) {
                    $query->select('radar_listing_id')->from('radar_listing_equipment')->where('radar_equipment_id', $target->id);
                })
                ->update(['radar_equipment_id' => $target->id]);

            DB::table('radar_search_equipment')
                ->where('radar_equipment_id', $radarEquipment->id)
                ->whereNotIn('radar_search_id', function ($query) use ($target) {
                    $query->select('radar_search_id')->from('radar_search_equipment')->where('radar_equipment_id', $target->id);
                })
                ->update(['radar_equipment_id' => $target->id]);

            $radarEquipment->delete();
        });

        return back()->with('success', "Equipamento fundido em \"{$target->label}\".");
    }

    /**
     * O inverso de merge(): tira este alias específico do item onde está (que pode
     * ter aliases de outros sites juntos) e dá-lhe de volta um item só para ele.
     * Não mexe nas associações a anúncios/pesquisas já feitas com o item de onde saiu
     * (essas ficam lá - não há forma fiável de saber, a posteriori, quais desses
     * anúncios "pertenciam" a este alias em concreto, já que a ligação anúncio↔
     * equipamento não guarda por qual alias foi feita) - só anúncios novos, a partir
     * de agora, é que vão passar a associar-se ao item novo em vez do antigo.
     */
    public function detachAlias(RadarEquipmentAlias $radarEquipmentAlias)
    {
        if ($radarEquipmentAlias->equipment->aliases()->count() <= 1) {
            return back()->with('success', 'Este alias já está sozinho no seu próprio item - não há nada para desacoplar.');
        }

        $label = $radarEquipmentAlias->raw_label ?: $radarEquipmentAlias->raw_key;

        $newEquipment = RadarEquipment::create([
            'label' => $label,
            'slug' => Str::slug($label),
            'show_in_filters' => false,
        ]);

        $radarEquipmentAlias->update(['radar_equipment_id' => $newEquipment->id]);

        return back()->with('success', "\"{$label}\" desacoplado para um item novo.");
    }

    public function destroy(RadarEquipment $radarEquipment)
    {
        $radarEquipment->delete();

        return back()->with('success', 'Equipamento apagado.');
    }
}
