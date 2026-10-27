<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class FormulariosPublicosLimitesTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    /** @dataProvider rotasLimitadas */
    public function test_rota_publica_limita_pedidos(string $metodo, string $uri): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->call($metodo, $uri, ['x' => 'y']);
        }

        $this->call($metodo, $uri, ['x' => 'y'])->assertStatus(429);
    }

    public static function rotasLimitadas(): array
    {
        return [
            'contacto de viatura' => ['POST', '/contact/vehicle'],
            'retoma' => ['POST', '/retomas'],
            'consignação' => ['POST', '/consignacao/avaliacao'],
            'recuperar password' => ['POST', '/password/email'],
            'repor password' => ['POST', '/password/reset'],
            'definir password' => ['POST', '/definir-password'],
        ];
    }

    public function test_consignacao_ignora_ficheiros_que_nao_sao_imagens(): void
    {
        Storage::fake('public');

        // Ficheiro real (o fake do Laravel adivinha o tipo pela extensão).
        $tmp = tempnam(sys_get_temp_dir(), 'foto');
        file_put_contents($tmp, '<?php echo 1;');
        $falsaFoto = new UploadedFile($tmp, 'foto.jpg', 'image/jpeg', null, true);

        $this->post('/consignacao/avaliacao', [
            'brand' => 'Opel', 'model' => 'Astra', 'year' => 2020, 'kilometers' => 1000,
            'plate' => 'AA-00-BB', 'fuel' => 'Gasolina', 'gearbox' => 'Manual', 'condition' => 'Bom',
            'name' => 'Teste', 'phone' => '910000000', 'email' => 'teste@example.com',
            'privacy_consent' => '1',
            // Um PHP disfarçado de imagem não pode ficar guardado.
            'photos' => [$falsaFoto],
        ]);

        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
