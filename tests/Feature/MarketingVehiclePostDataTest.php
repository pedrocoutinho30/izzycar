<?php

namespace Tests\Feature;

use App\Marketing\VehiclePostData;
use App\Models\V3Vehicle;
use App\Models\V3VehiclePhoto;
use App\Models\VehicleAttribute;
use App\Models\VehicleAttributeValue;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Instantâneo de uma viatura V3 para um post: só dados existentes, nada inventado. */
class MarketingVehiclePostDataTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private function viatura(array $extra = []): V3Vehicle
    {
        return V3Vehicle::create($extra + [
            'reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4',
            'version' => 'eDrive40', 'year' => 2023, 'kilometers' => 21000, 'fuel' => 'Elétrico',
            'power' => 340, 'asking_price' => 45900, 'status' => 'em_stock',
        ]);
    }

    private function atributo(V3Vehicle $v, string $key, string $name, string $type, string $value): void
    {
        $a = VehicleAttribute::create(['name' => $name, 'key' => $key, 'type' => $type, 'options' => [], 'order' => 1, 'attribute_group' => 'Equipamento']);
        VehicleAttributeValue::create(['v3_vehicle_id' => $v->id, 'attribute_id' => $a->id, 'value' => $value]);
    }

    public function test_instantaneo_inclui_dados_existentes(): void
    {
        $v = $this->viatura();

        $d = VehiclePostData::fromVehicle($v);

        $this->assertSame('BMW', $d['brand']);
        $this->assertSame(45900.0, (float) $d['price']);
        $this->assertSame($v->id, $d['vehicle_id']);
        $this->assertSame('em_stock', $d['status']);
        $this->assertArrayHasKey('captured_at', $d);
    }

    public function test_sem_preco_nao_ha_chave_price(): void
    {
        $d = VehiclePostData::fromVehicle($this->viatura(['asking_price' => null]));

        $this->assertArrayNotHasKey('price', $d);
    }

    public function test_sem_fotos_devolve_lista_vazia(): void
    {
        $d = VehiclePostData::fromVehicle($this->viatura());

        $this->assertSame([], $d['photos']);
    }

    public function test_fotos_vem_ordenadas_com_capa_primeiro(): void
    {
        $v = $this->viatura();
        V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => 'b.jpg', 'order_position' => 2]);
        V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => 'a.jpg', 'order_position' => 1]);
        $capa = V3VehiclePhoto::create(['v3_vehicle_id' => $v->id, 'path' => 'c.jpg', 'order_position' => 3, 'is_cover' => true]);

        $d = VehiclePostData::fromVehicle($v->fresh());

        $this->assertSame(['c.jpg', 'a.jpg', 'b.jpg'], array_column($d['photos'], 'path'));
        $this->assertSame($capa->id, $d['photos'][0]['id']);
    }

    public function test_equipamento_so_com_valores_reais_e_caixa_do_atributo(): void
    {
        $v = $this->viatura();
        $this->atributo($v, 'bluetooth', 'Bluetooth', 'boolean', '1');
        $this->atributo($v, 'apple_carplay', 'Apple CarPlay', 'boolean', '0');
        $this->atributo($v, 'tecto_panoramico', 'Tecto panorâmico', 'select', 'Teto panorâmico');
        $this->atributo($v, 'estofos', 'Estofos', 'select', '');
        $this->atributo($v, 'tipo_caixa', 'Tipo de Caixa', 'select', 'Caixa Automática');

        $d = VehiclePostData::fromVehicle($v->fresh());

        $this->assertEqualsCanonicalizing(['Bluetooth', 'Teto panorâmico', 'Caixa Automática'], $d['equipment']);
        $this->assertSame('Caixa Automática', $d['transmission']);
    }

    public function test_sem_atributo_de_caixa_nao_ha_transmission(): void
    {
        $this->assertArrayNotHasKey('transmission', VehiclePostData::fromVehicle($this->viatura()));
    }

    public function test_vendido_so_com_viatura_vendida(): void
    {
        $this->expectException(\DomainException::class);
        VehiclePostData::assertTemplateAllowed(['status' => 'em_stock'], 'vendido');
    }

    public function test_novo_stock_so_com_viatura_em_stock(): void
    {
        $this->expectException(\DomainException::class);
        VehiclePostData::assertTemplateAllowed(['status' => 'vendido'], 'novo-stock');
    }

    public function test_templates_permitidos_nao_lancam(): void
    {
        VehiclePostData::assertTemplateAllowed(['status' => 'vendido'], 'vendido');
        VehiclePostData::assertTemplateAllowed(['status' => 'em_stock'], 'novo-stock');
        VehiclePostData::assertTemplateAllowed(['status' => 'reservado'], 'premium');
        $this->addToAssertionCount(1);
    }
}
