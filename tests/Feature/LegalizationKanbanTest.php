<?php

namespace Tests\Feature;

use App\Models\Legalization;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class LegalizationKanbanTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function legalization(string $model, array $steps): Legalization
    {
        return Legalization::create(['marca' => 'Tesla', 'modelo' => $model, 'combustivel' => 'Elétrico', 'steps_completed' => $steps]);
    }

    public function test_current_step_is_the_first_one_not_completed(): void
    {
        $this->assertSame(1, $this->legalization('A', [])->currentStep());
        $this->assertSame(3, $this->legalization('B', [1, 2])->currentStep());
        $this->assertSame(2, $this->legalization('C', [1, 3])->currentStep());   // desmarcar o 2 não limpa o 3
        $this->assertNull($this->legalization('D', [1, 2, 3, 4, 5, 6, 7])->currentStep());
    }

    public function test_kanban_shows_in_progress_cards_by_step_and_hides_the_completed(): void
    {
        $this->legalization('ModeloNova', []);
        $this->legalization('ModeloDav', [1, 2]);
        $this->legalization('ModeloFeita', [1, 2, 3, 4, 5, 6, 7]);

        $response = $this->actingAs($this->backofficeUser())->get(route('admin.legalizations.kanban'))->assertOk();
        $grouped = $response->viewData('grouped');

        $this->assertSame(['ModeloNova'], $grouped[1]->pluck('modelo')->all());
        $this->assertSame(['ModeloDav'], $grouped[3]->pluck('modelo')->all());
        $this->assertSame(1, $response->viewData('doneCount'));
        $this->assertCount(7, $response->viewData('columns'));
        $response->assertSee('Homologação IMT')->assertSee('ModeloDav')->assertSee('Largue aqui')
            ->assertDontSee('ModeloFeita');
    }

    private function move(Legalization $legalization, string $column)
    {
        return $this->actingAs($this->backofficeUser())->postJson(route('admin.legalizations.move', $legalization), ['column' => $column]);
    }

    public function test_dragging_to_a_column_sets_the_steps_before_it_as_completed(): void
    {
        $legalization = $this->legalization('Mover', [1, 2]);

        $this->move($legalization, '4')->assertOk()->assertJson(['steps' => 3, 'done' => false]);
        $this->assertSame([1, 2, 3], $legalization->fresh()->steps_completed);
        $this->assertSame(4, $legalization->fresh()->currentStep());

        // Para trás: desmarca os seguintes.
        $this->move($legalization, '2')->assertOk()->assertJson(['steps' => 1]);
        $this->assertSame([1], $legalization->fresh()->steps_completed);

        $this->move($legalization, '1')->assertOk();
        $this->assertSame([], $legalization->fresh()->steps_completed);
    }

    public function test_dragging_to_completed_marks_all_steps(): void
    {
        $legalization = $this->legalization('Concluir', [1]);

        $this->move($legalization, 'done')->assertOk()->assertJson(['steps' => 7, 'progress' => 100, 'done' => true]);
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], $legalization->fresh()->steps_completed);
        $this->assertNull($legalization->fresh()->currentStep());
    }

    public function test_moving_past_step_five_creates_the_modelo9_task_only_once(): void
    {
        $legalization = $this->legalization('Tarefa', [1, 2, 3]);

        $this->move($legalization, '5')->assertOk()->assertJson(['task_created' => false]);
        $this->assertSame(0, \App\Models\Task::count());

        $this->move($legalization, '6')->assertOk()->assertJson(['task_created' => true]);
        $this->assertSame(1, \App\Models\Task::count());

        $this->move($legalization, 'done')->assertOk()->assertJson(['task_created' => false]);
        $this->assertSame(1, \App\Models\Task::count());
    }

    public function test_move_rejects_an_invalid_column(): void
    {
        $legalization = $this->legalization('Inválida', [1]);

        $this->move($legalization, '9')->assertStatus(422);
        $this->assertSame([1], $legalization->fresh()->steps_completed);
    }

    public function test_list_view_is_kept_and_links_to_the_kanban(): void
    {
        $this->legalization('ModeloLista', []);

        $this->actingAs($this->backofficeUser())->get(route('admin.legalizations.index'))->assertOk()
            ->assertSee('ModeloLista')
            ->assertSee('Lista de Legalizações')
            ->assertSee(route('admin.legalizations.kanban'));
    }
}
