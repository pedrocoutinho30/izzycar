<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Caracterização das rotas desktop de tarefas (admin.tasks.*): fixa o
 * comportamento antes de a lógica passar para o TaskService.
 */
class TaskDesktopTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Carbon::setTestNow('2026-03-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function payload(array $override = []): array
    {
        return $override + [
            'title' => 'Ligar ao cliente',
            'description' => 'Confirmar proposta',
            'due_date' => '2026-03-20',
            'status' => 'pendente',
        ];
    }

    private function tarefa(array $attributes = []): Task
    {
        return Task::create($attributes + ['title' => 'Tarefa', 'due_date' => '2026-03-15', 'status' => 'pendente']);
    }

    public function test_criar_guarda_e_redireciona_com_mensagem(): void
    {
        Notification::fake();
        $admin = $this->backofficeUser();

        $this->actingAs($admin)->post(route('admin.tasks.store'), $this->payload(['reminder_date' => '2026-03-18']))
            ->assertRedirect(route('admin.tasks.index'))
            ->assertSessionHas('success', 'Tarefa criada com sucesso!');

        $task = Task::firstOrFail();
        $this->assertSame('Ligar ao cliente', $task->title);
        $this->assertSame('2026-03-20', $task->due_date->toDateString());
        $this->assertSame('2026-03-18', $task->reminder_date->toDateString());
        $this->assertSame('pendente', $task->status);
        $this->assertNull($task->user_id);
        Notification::assertNothingSent();
    }

    public function test_criar_notifica_o_atribuido_mas_nao_quem_se_auto_atribui(): void
    {
        Notification::fake();
        $admin = $this->backofficeUser();
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);

        $this->actingAs($admin)->post(route('admin.tasks.store'), $this->payload(['user_id' => $outro->id]))->assertRedirect();
        Notification::assertSentTo($outro, TaskAssignedNotification::class);

        $this->actingAs($admin)->post(route('admin.tasks.store'), $this->payload(['user_id' => $admin->id]))->assertRedirect();
        Notification::assertNotSentTo($admin, TaskAssignedNotification::class);
        $this->assertSame(2, Task::count());
    }

    public function test_criar_valida_com_as_mensagens_do_desktop(): void
    {
        $admin = $this->backofficeUser();

        $this->actingAs($admin)->post(route('admin.tasks.store'), [])
            ->assertSessionHasErrors([
                'title' => 'O título é obrigatório.',
                'due_date' => 'A data limite é obrigatória.',
                'status' => 'O estado é obrigatório.',
            ]);

        $this->actingAs($admin)->post(route('admin.tasks.store'), $this->payload(['reminder_date' => '2026-03-25']))
            ->assertSessionHasErrors(['reminder_date' => 'A data do lembrete deve ser anterior ou igual à data limite.']);

        $this->actingAs($admin)->post(route('admin.tasks.store'), $this->payload(['status' => 'inventado']))
            ->assertSessionHasErrors('status');
        $this->actingAs($admin)->post(route('admin.tasks.store'), $this->payload(['user_id' => 99999]))
            ->assertSessionHasErrors('user_id');

        $this->assertSame(0, Task::count());
    }

    public function test_atualizar_guarda_e_nao_notifica(): void
    {
        Notification::fake();
        $admin = $this->backofficeUser();
        $task = $this->tarefa();

        $this->actingAs($admin)->put(route('admin.tasks.update', $task), $this->payload(['title' => 'Novo título', 'status' => 'em_progresso', 'user_id' => $admin->id]))
            ->assertRedirect(route('admin.tasks.index'))
            ->assertSessionHas('success', 'Tarefa atualizada com sucesso!');

        $task->refresh();
        $this->assertSame('Novo título', $task->title);
        $this->assertSame('em_progresso', $task->status);
        $this->assertSame($admin->id, $task->user_id);
        Notification::assertNothingSent();
    }

    public function test_atualizar_valida(): void
    {
        $task = $this->tarefa();

        $this->actingAs($this->backofficeUser())->put(route('admin.tasks.update', $task), $this->payload(['title' => '']))
            ->assertSessionHasErrors(['title' => 'O título é obrigatório.']);
        $this->assertSame('Tarefa', $task->fresh()->title);
    }

    public function test_estado_por_ajax_devolve_json_e_valida(): void
    {
        $admin = $this->backofficeUser();
        $task = $this->tarefa();

        $this->actingAs($admin)->patchJson(route('admin.tasks.update-status', $task), ['status' => 'concluida'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Estado atualizado com sucesso!')
            ->assertJsonPath('task.status', 'concluida');
        $this->assertSame('concluida', $task->fresh()->status);

        $this->actingAs($admin)->patchJson(route('admin.tasks.update-status', $task), ['status' => 'xpto'])->assertStatus(422);
        $this->assertSame('concluida', $task->fresh()->status);
    }

    public function test_eliminar(): void
    {
        $task = $this->tarefa();

        $this->actingAs($this->backofficeUser())->delete(route('admin.tasks.destroy', $task))
            ->assertRedirect(route('admin.tasks.index'))
            ->assertSessionHas('success', 'Tarefa eliminada com sucesso!');
        $this->assertSame(0, Task::count());
    }

    public function test_paginas_do_desktop_abrem(): void
    {
        $admin = $this->backofficeUser();
        $task = $this->tarefa(['title' => 'Visível no calendário']);

        $this->actingAs($admin)->get(route('admin.tasks.index'))->assertOk()->assertSee('Visível no calendário');
        $this->actingAs($admin)->get(route('admin.tasks.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.tasks.show', $task))->assertOk()->assertSee('Visível no calendário');
        $this->actingAs($admin)->get(route('admin.tasks.edit', $task))->assertOk();
    }
}
