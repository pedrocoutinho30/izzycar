<?php

namespace Tests\Feature\Mobile;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/** Tarefas na versão mobile: a lógica é a do desktop (TaskService). */
class MobileTasksTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-03-15 12:00:00');
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function tarefa(array $attributes = []): Task
    {
        return Task::create($attributes + ['title' => 'Tarefa', 'due_date' => '2026-03-15', 'status' => 'pendente']);
    }

    private function payload(array $override = []): array
    {
        return $override + ['title' => 'Ligar ao cliente', 'description' => 'Confirmar', 'due_date' => '2026-03-20', 'status' => 'pendente'];
    }

    /** Utilizador não admin, com acesso mobile, com apenas as permissões dadas. */
    private function userWith(array $permissions): User
    {
        $role = Role::findOrCreate('mobile-t-' . md5(implode(',', $permissions)), 'web');
        foreach ($permissions as $nome) {
            $role->givePermissionTo(Permission::findOrCreate($nome, 'web'));
        }
        $user = $this->backofficeUser($role->name, ['email' => uniqid() . '@example.com']);
        $user->forceFill(['access_mobile' => true])->save();

        return $user;
    }

    public function test_lista_mostra_cartoes_e_filtros(): void
    {
        $admin = $this->mobileUser();
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);
        $this->tarefa(['title' => 'Hoje A', 'user_id' => $admin->id, 'due_date' => '2026-03-15']);
        $this->tarefa(['title' => 'Atrasada B', 'user_id' => $admin->id, 'due_date' => '2026-03-10', 'status' => 'em_progresso']);
        $this->tarefa(['title' => 'Futura C', 'user_id' => $admin->id, 'due_date' => '2026-03-30']);
        $this->tarefa(['title' => 'Feita D', 'user_id' => $admin->id, 'status' => 'concluida']);
        $this->tarefa(['title' => 'Cancelada E', 'user_id' => $admin->id, 'status' => 'cancelada']);
        $this->tarefa(['title' => 'Alheia F', 'user_id' => $outro->id, 'due_date' => '2026-03-01']);

        $ver = fn (array $q) => $this->actingAs($admin)->get(route('admin.mobile.tasks.index', $q))->assertOk();

        // Por omissão: para tratar (hoje + atraso), só as minhas.
        $ver([])->assertSee('Hoje A')->assertSee('Atrasada B')->assertDontSee('Futura C')->assertDontSee('Feita D')->assertDontSee('Alheia F')
            ->assertSee('Em atraso', false)->assertSee('text-danger', false);
        $ver(['filtro' => 'hoje'])->assertSee('Hoje A')->assertDontSee('Atrasada B');
        $ver(['filtro' => 'atraso'])->assertSee('Atrasada B')->assertDontSee('Hoje A');
        $ver(['filtro' => 'pendentes'])->assertSee('Hoje A')->assertSee('Futura C')->assertDontSee('Feita D')->assertDontSee('Cancelada E');
        $ver(['filtro' => 'concluidas'])->assertSee('Feita D')->assertDontSee('Hoje A');
        // Todas (não só as minhas), como no desktop.
        $ver(['filtro' => 'atraso', 'minhas' => '0'])->assertSee('Alheia F')->assertSee('Atrasada B');
        // Filtro desconhecido volta ao omissão.
        $ver(['filtro' => 'xpto'])->assertSee('Hoje A')->assertDontSee('Futura C');
    }

    public function test_pesquisa_e_literal_para_percent_e_underscore(): void
    {
        $admin = $this->mobileUser();
        $this->tarefa(['title' => '50% desconto', 'user_id' => $admin->id]);
        $this->tarefa(['title' => 'Outra tarefa', 'user_id' => $admin->id]);
        $this->tarefa(['title' => 'a_b', 'user_id' => $admin->id]);
        $this->tarefa(['title' => 'axb', 'user_id' => $admin->id]);

        $this->actingAs($admin)->get(route('admin.mobile.tasks.index', ['search' => '%']))->assertOk()->assertSee('50% desconto')->assertDontSee('Outra tarefa');
        $this->actingAs($admin)->get(route('admin.mobile.tasks.index', ['search' => 'a_b']))->assertOk()->assertSee('a_b')->assertDontSee('axb');
    }

    public function test_parametros_em_array_nao_dao_500(): void
    {
        $admin = $this->mobileUser();
        $this->tarefa(['user_id' => $admin->id]);

        $this->actingAs($admin)->get(route('admin.mobile.tasks.index', ['search' => ['x'], 'filtro' => ['hoje'], 'minhas' => ['0'], 'page' => ['1']]))->assertOk();
        $this->actingAs($admin)->get(route('admin.mobile.tasks.index') . '?page[]=2&page[x]=1')->assertOk();
    }

    public function test_titulo_e_escapado(): void
    {
        $admin = $this->mobileUser();
        $this->tarefa(['title' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=1>', 'user_id' => $admin->id]);

        $html = $this->actingAs($admin)->get(route('admin.mobile.tasks.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_carregar_mais_pagina_a_lista(): void
    {
        $admin = $this->mobileUser();
        for ($i = 1; $i <= 16; $i++) {
            $this->tarefa(['title' => "Tarefa $i", 'user_id' => $admin->id]);
        }

        $this->actingAs($admin)->get(route('admin.mobile.tasks.index'))->assertOk()->assertSee('id="m-load-more"', false)->assertSee('Tarefa 15')->assertDontSee('Tarefa 16');
        $this->actingAs($admin)->get(route('admin.mobile.tasks.index', ['page' => 2]))->assertOk()->assertSee('Tarefa 16')->assertDontSee('id="m-load-more"', false);
    }

    public function test_criar_atribui_e_notifica_como_no_desktop(): void
    {
        Notification::fake();
        $admin = $this->mobileUser();
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);

        $this->actingAs($admin)->get(route('admin.mobile.tasks.create'))->assertOk()->assertSee('Nova tarefa');

        $this->actingAs($admin)->post(route('admin.mobile.tasks.store'), $this->payload(['user_id' => (string) $outro->id]))
            ->assertRedirect(route('admin.mobile.tasks.index'))->assertSessionHas('success', 'Tarefa criada com sucesso!');
        Notification::assertSentTo($outro, TaskAssignedNotification::class);

        // A si próprio: sem notificação (mesmo com o id como texto, como vem do formulário).
        $this->actingAs($admin)->post(route('admin.mobile.tasks.store'), $this->payload(['user_id' => (string) $admin->id]))->assertRedirect();
        Notification::assertNotSentTo($admin, TaskAssignedNotification::class);

        $task = Task::where('user_id', $admin->id)->firstOrFail();
        $this->assertSame('Ligar ao cliente', $task->title);
        $this->assertSame('2026-03-20', $task->due_date->toDateString());
    }

    public function test_criar_valida_com_as_mensagens_do_desktop(): void
    {
        $admin = $this->mobileUser();

        $this->actingAs($admin)->post(route('admin.mobile.tasks.store'), [])
            ->assertSessionHasErrors(['title' => 'O título é obrigatório.', 'due_date' => 'A data limite é obrigatória.', 'status' => 'O estado é obrigatório.']);
        $this->actingAs($admin)->post(route('admin.mobile.tasks.store'), $this->payload(['reminder_date' => '2026-03-25']))
            ->assertSessionHasErrors(['reminder_date' => 'A data do lembrete deve ser anterior ou igual à data limite.']);
        $this->actingAs($admin)->post(route('admin.mobile.tasks.store'), $this->payload(['status' => 'xpto']))->assertSessionHasErrors('status');
        $this->actingAs($admin)->post(route('admin.mobile.tasks.store'), $this->payload(['user_id' => 99999]))->assertSessionHasErrors('user_id');
        $this->assertSame(0, Task::count());
    }

    public function test_arrays_nos_campos_dao_erro_de_validacao_e_nao_500(): void
    {
        $admin = $this->mobileUser();

        foreach (['title', 'description', 'due_date', 'user_id', 'status'] as $campo) {
            $this->actingAs($admin)->post(route('admin.mobile.tasks.store'), $this->payload([$campo => [$admin->id]]))
                ->assertRedirect()->assertSessionHasErrors($campo);
        }
        $this->assertSame(0, Task::count());
    }

    public function test_so_os_campos_validados_chegam_ao_modelo(): void
    {
        $admin = $this->mobileUser();

        $this->actingAs($admin)->post(route('admin.mobile.tasks.store'), $this->payload(['user_name' => 'Fulano', 'reminder_sent' => 1, 'vehicle_id' => 5, 'id' => 77]))->assertRedirect();

        $task = Task::firstOrFail();
        $this->assertNotSame(77, $task->id);
        $this->assertNull($task->user_name);
        $this->assertFalse((bool) $task->reminder_sent);
        $this->assertNull($task->vehicle_id);
    }

    public function test_concluir_so_muda_o_estado_como_o_desktop(): void
    {
        Notification::fake();
        $admin = $this->mobileUser();
        $task = $this->tarefa(['user_id' => $admin->id, 'title' => 'Manter', 'due_date' => '2026-03-10']);

        $this->actingAs($admin)->from(route('admin.mobile.tasks.index'))->post(route('admin.mobile.tasks.complete', $task->id))
            ->assertRedirect(route('admin.mobile.tasks.index'))->assertSessionHas('success', 'Tarefa concluída.');

        $fresh = $task->fresh();
        $this->assertSame('concluida', $fresh->status);
        $this->assertSame('Manter', $fresh->title);
        $this->assertSame('2026-03-10', $fresh->due_date->toDateString());
        $this->assertSame($admin->id, $fresh->user_id);
        $this->assertSame(1, Task::count()); // sem recorrência: não nasce outra tarefa
        Notification::assertNothingSent();

        // O resultado é o mesmo que o desktop (PATCH do estado) produz.
        $outra = $this->tarefa(['title' => 'Desktop']);
        $this->actingAs($admin)->patchJson(route('admin.tasks.update-status', $outra), ['status' => 'concluida'])->assertOk();
        $this->assertEquals($outra->fresh()->only(['status', 'user_id', 'reminder_sent']), $this->tarefa(['status' => 'concluida'])->only(['status', 'user_id', 'reminder_sent']));

        // Concluir outra vez não faz nada.
        $this->actingAs($admin)->post(route('admin.mobile.tasks.complete', $task->id))->assertSessionHas('info', 'A tarefa já estava concluída.');
    }

    public function test_editar_atualiza_sem_mexer_na_viatura_e_no_nome_livre(): void
    {
        Notification::fake();
        $admin = $this->mobileUser();
        $task = $this->tarefa(['user_name' => 'Livre']);

        $this->actingAs($admin)->get(route('admin.mobile.tasks.edit', $task->id))->assertOk()->assertSee('Editar tarefa');
        $this->actingAs($admin)->put(route('admin.mobile.tasks.update', $task->id), $this->payload(['title' => 'Novo', 'status' => 'em_progresso', 'user_id' => (string) $admin->id]))
            ->assertRedirect(route('admin.mobile.tasks.index'))->assertSessionHas('success', 'Tarefa atualizada com sucesso!');

        $task->refresh();
        $this->assertSame('Novo', $task->title);
        $this->assertSame('em_progresso', $task->status);
        $this->assertSame($admin->id, $task->user_id);
        $this->assertSame('Livre', $task->user_name);
        Notification::assertNothingSent();

        $this->actingAs($admin)->put(route('admin.mobile.tasks.update', $task->id), $this->payload(['title' => '']))->assertSessionHasErrors('title');
    }

    public function test_inexistente_da_404(): void
    {
        $admin = $this->mobileUser();

        $this->actingAs($admin)->get(route('admin.mobile.tasks.edit', 999999))->assertNotFound();
        $this->actingAs($admin)->post(route('admin.mobile.tasks.complete', 999999))->assertNotFound();
        $this->actingAs($admin)->put(route('admin.mobile.tasks.update', 999999), $this->payload())->assertNotFound();
    }

    /** @return array<string, array{bool}> */
    public static function enforce(): array
    {
        return ['enforce ligado' => [true], 'enforce desligado' => [false]];
    }

    #[DataProvider('enforce')]
    public function test_sem_permissao_de_tarefas_da_403_em_todas_as_rotas(bool $enforce): void
    {
        config(['permissions.enforce' => $enforce]);
        $user = $this->userWith(['leads.view.all']);
        $task = $this->tarefa();

        $this->actingAs($user)->get(route('admin.mobile.tasks.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.tasks.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.tasks.store'), $this->payload())->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.tasks.edit', $task->id))->assertForbidden();
        $this->actingAs($user)->put(route('admin.mobile.tasks.update', $task->id), $this->payload(['title' => 'X']))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.tasks.complete', $task->id))->assertForbidden();

        $this->assertSame(0, Task::where('title', '!=', 'Tarefa')->count());
        $this->assertSame('pendente', $task->fresh()->status);
    }

    #[DataProvider('enforce')]
    public function test_so_ver_nao_cria_nem_edita_nem_conclui_tarefas_de_outros(bool $enforce): void
    {
        config(['permissions.enforce' => $enforce]);
        $user = $this->userWith(['tasks.view']);
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);
        $task = $this->tarefa(['title' => 'Do outro', 'user_id' => $outro->id]);

        $this->actingAs($user)->get(route('admin.mobile.tasks.index', ['minhas' => '0']))->assertOk()->assertSee('Do outro')
            ->assertDontSee('Concluir')->assertDontSee('Editar')->assertDontSee('Nova tarefa');
        $this->actingAs($user)->get(route('admin.mobile.tasks.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.tasks.store'), $this->payload())->assertForbidden();
        $this->actingAs($user)->get(route('admin.mobile.tasks.edit', $task->id))->assertForbidden();
        $this->actingAs($user)->put(route('admin.mobile.tasks.update', $task->id), $this->payload(['title' => 'Hackeada']))->assertForbidden();
        $this->actingAs($user)->post(route('admin.mobile.tasks.complete', $task->id))->assertForbidden();

        $task->refresh();
        $this->assertSame('Do outro', $task->title);
        $this->assertSame('pendente', $task->status);
    }

    #[DataProvider('enforce')]
    public function test_com_editar_conclui_e_edita_como_o_desktop_permite(bool $enforce): void
    {
        config(['permissions.enforce' => $enforce]);
        $user = $this->userWith(['tasks.view', 'tasks.update', 'tasks.create']);
        $outro = $this->backofficeUser('admin', ['email' => 'outro@example.com']);
        $task = $this->tarefa(['user_id' => $outro->id]);

        $this->actingAs($user)->get(route('admin.mobile.tasks.index', ['minhas' => '0']))->assertOk()->assertSee('Concluir')->assertSee('Nova tarefa');
        $this->actingAs($user)->post(route('admin.mobile.tasks.complete', $task->id))->assertRedirect();
        $this->assertSame('concluida', $task->fresh()->status);
        $this->actingAs($user)->post(route('admin.mobile.tasks.store'), $this->payload())->assertRedirect(route('admin.mobile.tasks.index'));
    }

    public function test_sem_access_mobile_e_angariador_nao_entram(): void
    {
        $this->actingAs($this->mobileUser(false))->get(route('admin.mobile.tasks.index'))->assertRedirect(route('admin.v2.dashboard'));

        $angariador = $this->backofficeUser('angariador');
        $angariador->forceFill(['access_mobile' => true])->save();
        $this->actingAs($angariador)->get(route('admin.mobile.tasks.index'))->assertForbidden();
    }

    public function test_menu_mais_mostra_tarefas_so_a_quem_as_pode_ver(): void
    {
        config(['permissions.enforce' => true]);
        $com = $this->userWith(['tasks.view']);
        $sem = $this->userWith(['leads.view.all']);

        $this->actingAs($com)->get(route('admin.mobile.tasks.index'))->assertOk()
            ->assertSee('href="' . route('admin.mobile.tasks.index') . '"', false);
        $this->actingAs($sem)->get(route('admin.mobile.leads.index'))->assertOk()
            ->assertDontSee('href="' . route('admin.mobile.tasks.index') . '"', false);
    }

    public function test_formularios_levam_csrf_e_ligacao_ao_desktop(): void
    {
        $admin = $this->mobileUser();
        $task = $this->tarefa(['user_id' => $admin->id]);

        $lista = $this->actingAs($admin)->get(route('admin.mobile.tasks.index'))->getContent();
        $this->assertStringContainsString('name="_token"', $lista);
        $this->assertStringContainsString(route('admin.tasks.index'), $lista);
        $this->assertStringContainsString('name="_token"', $this->actingAs($admin)->get(route('admin.mobile.tasks.edit', $task->id))->getContent());
        $this->assertStringContainsString('name="_token"', $this->actingAs($admin)->get(route('admin.mobile.tasks.create'))->getContent());
    }
}
