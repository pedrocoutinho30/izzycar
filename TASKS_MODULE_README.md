# Módulo de Tarefas - IzzyCar

## Visão Geral

Sistema completo de gestão de tarefas com calendário integrado e sistema de lembretes automáticos.

## Funcionalidades

### 📅 Calendário de Tarefas
- **Vista Mensal**: Visualização completa do mês com todas as tarefas
- **Vista Semanal**: Visualização de 7 dias com detalhes das tarefas
- **Vista Diária**: Visualização detalhada de um dia específico

### ✅ Gestão de Tarefas
- Criar, editar e eliminar tarefas
- Estados disponíveis:
  - **Pendente**: Tarefa aguardando início
  - **Em Progresso**: Tarefa sendo executada
  - **Concluída**: Tarefa finalizada
  - **Cancelada**: Tarefa cancelada

### 🔗 Associações
- **Usuário Responsável**: Associar tarefa a um usuário do sistema
- **Nome de Responsável**: Adicionar nome de responsável externo
- **Veículo**: Vincular tarefa a um veículo específico

### 🔔 Sistema de Lembretes
- Definir data de lembrete para cada tarefa
- Email automático enviado na data do lembrete
- Execução diária às 00:00 via cron job
- Notificação apenas para tarefas não concluídas

## Estrutura do Módulo

### Models
- **Task** (`app/Models/Task.php`)
  - Campos: title, description, due_date, reminder_date, user_id, user_name, vehicle_id, status, reminder_sent
  - Relacionamentos: User, Vehicle
  - Scopes: pending, completed, needsReminderToday

### Controllers
- **TaskController** (`app/Http/Controllers/Admin/TaskController.php`)
  - CRUD completo de tarefas
  - Métodos especiais:
    - `updateStatus()`: Atualizar apenas o status via AJAX
    - `getTasksJson()`: Retornar tarefas em JSON para calendário

### Views
- `resources/views/admin/v2/tasks/`
  - **index.blade.php**: Calendário principal
  - **form.blade.php**: Formulário de criação/edição
  - **show.blade.php**: Detalhes da tarefa
  - **partials/calendar-month.blade.php**: Vista mensal
  - **partials/calendar-week.blade.php**: Vista semanal
  - **partials/calendar-day.blade.php**: Vista diária

### Commands
- **SendTaskReminders** (`app/Console/Commands/SendTaskReminders.php`)
  - Comando: `php artisan tasks:send-reminders`
  - Agendado para rodar diariamente às 00:00
  - Envia emails para tarefas com lembrete do dia

### Emails
- **task-reminder.blade.php** (`resources/views/emails/task-reminder.blade.php`)
  - Template responsivo
  - Informações completas da tarefa
  - Alertas para tarefas atrasadas
  - Botão de ação para ver detalhes

## Rotas

```php
// Listar tarefas (calendário)
GET /admin/tasks

// Criar nova tarefa
GET /admin/tasks/create
POST /admin/tasks

// Ver detalhes
GET /admin/tasks/{task}

// Editar tarefa
GET /admin/tasks/{task}/edit
PUT /admin/tasks/{task}

// Eliminar tarefa
DELETE /admin/tasks/{task}

// Atualizar status (AJAX)
PATCH /admin/tasks/{task}/status

// Obter tarefas em JSON
GET /admin/tasks/json/tasks
```

## Menu

O módulo foi adicionado ao menu lateral da área administrativa:

```
Dashboard
├── Propostas
├── Clientes
├── Veículos
├── Vendas
├── Despesas
├── Tarefas ← NOVO
└── Newsletter
```

## Configuração do Cron

Para que os lembretes sejam enviados automaticamente, adicione ao crontab do servidor:

```bash
* * * * * cd /caminho/para/izzycar && php artisan schedule:run >> /dev/null 2>&1
```

## Comandos Úteis

### Executar migration
```bash
php artisan migrate
```

### Testar envio de lembretes manualmente
```bash
php artisan tasks:send-reminders
```

### Verificar tarefas agendadas
```bash
php artisan schedule:list
```

## Validações

### Criação/Edição de Tarefas
- **title**: Obrigatório, máximo 255 caracteres
- **description**: Opcional
- **due_date**: Obrigatório, formato data
- **reminder_date**: Opcional, deve ser anterior ou igual à due_date
- **user_id**: Opcional, deve existir na tabela users
- **user_name**: Opcional, máximo 255 caracteres
- **vehicle_id**: Opcional, deve existir na tabela vehicles
- **status**: Obrigatório, valores: pendente, em_progresso, concluida, cancelada

## Permissões

O módulo está integrado com o sistema de permissões existente. As rotas estão protegidas pelo middleware `auth`.

## Responsividade

Todas as views são totalmente responsivas e adaptadas para:
- Desktop (telas grandes)
- Tablet (telas médias)
- Mobile (telas pequenas)

## Cores e Estados

| Estado | Cor | Badge |
|--------|-----|-------|
| Pendente | Amarelo (#ffc107) | warning |
| Em Progresso | Azul (#17a2b8) | info |
| Concluída | Verde (#28a745) | success |
| Cancelada | Cinza (#6c757d) | secondary |

## Notificações

### Email de Lembrete
- Enviado para o email do usuário responsável
- Se não houver usuário, envia para o email padrão do sistema
- Conteúdo:
  - Título da tarefa
  - Descrição
  - Estado atual
  - Data limite
  - Veículo relacionado (se houver)
  - Alerta se estiver atrasada
  - Link para ver detalhes

## Boas Práticas

1. **Sempre definir data limite**: Essencial para organização
2. **Usar lembretes**: Configure 1-2 dias antes da data limite
3. **Atualizar status**: Manter o status sempre atualizado
4. **Associar responsável**: Facilita o acompanhamento
5. **Vincular veículo**: Quando a tarefa for específica de um veículo

## Troubleshooting

### Lembretes não são enviados
1. Verificar se o cron está configurado corretamente
2. Testar comando manualmente: `php artisan tasks:send-reminders`
3. Verificar logs: `storage/logs/laravel.log`
4. Confirmar configurações de email em `.env`

### Calendário não carrega
1. Verificar se as rotas estão registradas
2. Limpar cache: `php artisan route:clear`
3. Verificar permissões de acesso

### Erro ao criar tarefa
1. Verificar se a migration foi executada
2. Confirmar que os relacionamentos existem (users, vehicles)
3. Validar formato das datas

## Melhorias Futuras

- [ ] Filtros avançados no calendário
- [ ] Exportação de tarefas para PDF
- [ ] Notificações push
- [ ] Tarefas recorrentes
- [ ] Subtarefas
- [ ] Comentários nas tarefas
- [ ] Anexos de arquivos
- [ ] Integração com Google Calendar

## Suporte

Para dúvidas ou problemas, consultar a documentação Laravel ou contactar a equipa de desenvolvimento.

---

**Versão**: 1.0.0  
**Data**: Abril 2026  
**Desenvolvido para**: IzzyCar
