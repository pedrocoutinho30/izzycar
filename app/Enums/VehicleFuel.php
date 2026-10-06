<?php

namespace App\Enums;

use Illuminate\Support\Str;

/**
 * Combustível de uma Oportunidade. Os valores são os mesmos do formulário de
 * importação do site (form_proposals.fuel), para que as checklists
 * configuráveis (opportunity_checklists.applies_to_fuels) usem um único
 * vocabulário.
 */
enum VehicleFuel: string
{
    use HasOptions;

    case Petrol = 'gasolina';
    case Diesel = 'diesel';
    case HybridPetrol = 'hibrido_gasolina';
    case HybridDiesel = 'hibrido_diesel';
    case PluginHybridPetrol = 'hibrido_plugin_gasolina';
    case PluginHybridDiesel = 'hibrido_plugin_diesel';
    case Electric = 'eletrico';

    public function label(): string
    {
        return match ($this) {
            self::Petrol => 'Gasolina',
            self::Diesel => 'Diesel',
            self::HybridPetrol => 'Gasolina (HEV)',
            self::HybridDiesel => 'Diesel (HEV)',
            self::PluginHybridPetrol => 'Híbrido Plug-in / Gasolina',
            self::PluginHybridDiesel => 'Híbrido Plug-in / Diesel',
            self::Electric => 'Elétrico',
        };
    }

    /** Valor equivalente em proposals.fuel (a cotação usa os rótulos). */
    public function proposalLabel(): string
    {
        return match ($this) {
            self::PluginHybridPetrol => 'Híbrido Plug-in/Gasolina',
            self::PluginHybridDiesel => 'Híbrido Plug-in/Diesel',
            default => $this->label(),
        };
    }

    /**
     * form_proposals.fuel tem registos antigos com o rótulo em vez da chave
     * ("Elétrico", "Híbrido Plug-in Gasolina"...). Normaliza os dois formatos.
     */
    public static function fromLoose(?string $value): ?self
    {
        if (blank($value)) {
            return null;
        }

        $slug = Str::of($value)->ascii()->lower()->replace(['/', '-'], ' ')->squish()->replace(' ', '_')->toString();

        return self::tryFrom($slug) ?? match ($slug) {
            'hibrido_plug_in_gasolina' => self::PluginHybridPetrol,
            'hibrido_plug_in_diesel' => self::PluginHybridDiesel,
            'gasolina_(hev)' => self::HybridPetrol,
            'diesel_(hev)' => self::HybridDiesel,
            default => null,
        };
    }
}
