# 🚀 Sistema de Gestão Izzycar - V2

## 📋 Índice
- [Visão Geral](#visão-geral)
- [Arquitetura](#arquitetura)
- [Ficheiros Criados](#ficheiros-criados)
- [Como Usar](#como-usar)
- [Componentes Reutilizáveis](#componentes-reutilizáveis)
- [Expandir para Outros Módulos](#expandir-para-outros-módulos)
- [FAQ](#faq)

---

## 🎯 Visão Geral

Este é o **novo sistema de gestão (Backoffice V2)** da Izzycar, completamente redesenhado com foco em:

✅ **Mobile-First**: Funciona perfeitamente em qualquer dispositivo  
✅ **Componentes Reutilizáveis**: Código modular que pode ser usado em outros módulos  
✅ **Clean Code**: Código bem comentado e organizado para facilitar manutenção  
✅ **Design Moderno**: Interface limpa, profissional e intuitiva  
✅ **Performance**: Otimizado para velocidade e experiência do utilizador  

### 🆕 O que foi criado?

- Sistema completo de gestão de propostas (V2)
- Layout base reutilizável para todo o BO
- Componentes Blade reutilizáveis (filtros, cards, stats)
- Controller bem documentado e organizado
- Sistema de rotas organizado (V1 e V2 coexistem)

---

## 🏗️ Arquitetura

### Estrutura de Pastas

```
app/
└── Http/
    └── Controllers/
        └── Admin/
            └── ProposalV2Controller.php       # Controller do módulo V2

resources/
└── views/
    ├── layouts/
    │   └── admin-v2.blade.php                 # Layout master reutilizável
    ├── components/
    │   └── admin/
    │       ├── filter-bar.blade.php           # Componente de filtros
    │       ├── item-card.blade.php            # Componente de card de item
    │       └── stats-cards.blade.php          # Componente de estatísticas
    └── admin/
        └── v2/
            └── proposals/
                ├── index.blade.php            # Listagem
                └── form.blade.php             # Criar/Editar

routes/
└── web.php                                    # Rotas V2 adicionadas
```

### Fluxo de Dados

```
[Utilizador] 
    ↓
[Rota: /gestao/v2/proposals]
    ↓
[ProposalV2Controller]
    ↓
[View: admin.v2.proposals.index]
    ↓
[Components: filter-bar, item-card, stats-cards]
    ↓
[Layout: admin-v2]
```

---

## 📁 Ficheiros Criados

### 1. Layout Master
**Ficheiro**: `resources/views/layouts/admin-v2.blade.php`

**O que faz**: Layout base para todo o backoffice V2

**Características**:
- Topbar fixa com logo, search e user menu
- Sidebar lateral com navegação
- Sistema de notificações (toasts)
- Totalmente responsivo (mobile-first)
- Variáveis CSS reutilizáveis
- JavaScript para toggle de sidebar mobile

**Como usar**:
```blade
@extends('layouts.admin-v2')

@section('title', 'Nome da Página')

@section('content')
    <!-- Seu conteúdo aqui -->
@endsection
```

---

### 2. Componentes Reutilizáveis

#### 2.1. Filter Bar
**Ficheiro**: `resources/views/components/admin/filter-bar.blade.php`

**O que faz**: Barra de filtros adaptativa com múltiplos tipos de input

**Como usar**:
```blade
@include('components.admin.filter-bar', [
    'action' => route('algo.index'),
    'filters' => [
        [
            'name' => 'search',
            'label' => 'Pesquisar',
            'type' => 'text',
            'placeholder' => 'Digite algo...',
            'value' => request('search'),
            'col' => 12
        ],
        [
            'name' => 'status',
            'label' => 'Estado',
            'type' => 'select',
            'options' => ['Ativo', 'Inativo'],
            'value' => request('status'),
            'col' => 6
        ],
        [
            'name' => 'date',
            'label' => 'Data',
            'type' => 'date',
            'value' => request('date'),
            'col' => 6
        ]
    ]
])
```

**Tipos de input suportados**:
- `text`: Campo de texto
- `select`: Dropdown
- `date`: Seletor de data
- `number`: Campo numérico

---

#### 2.2. Item Card
**Ficheiro**: `resources/views/components/admin/item-card.blade.php`

**O que faz**: Card moderno para exibir items em listas

**Como usar**:
```blade
@include('components.admin.item-card', [
    'title' => 'Título do Item',
    'subtitle' => 'Subtítulo opcional',
    'image' => 'caminho/para/imagem.jpg',
    'badges' => [
        ['text' => 'Novo', 'color' => 'success', 'icon' => 'star'],
        ['text' => '2024', 'color' => 'secondary']
    ],
    'meta' => [
        ['icon' => 'person', 'text' => 'João Silva'],
        ['icon' => 'calendar', 'text' => '15/12/2025']
    ],
    'actions' => [
        [
            'icon' => 'pencil',
            'href' => route('edit', $id),
            'color' => 'primary',
            'label' => 'Editar'
        ],
        [
            'icon' => 'trash',
            'href' => route('destroy', $id),
            'color' => 'danger',
            'label' => 'Eliminar',
            'method' => 'DELETE',
            'confirm' => 'Tem certeza?'
        ]
    ]
])
```

---

#### 2.3. Stats Cards
**Ficheiro**: `resources/views/components/admin/stats-cards.blade.php`

**O que faz**: Cards de estatísticas para dashboard

**Como usar**:
```blade
@include('components.admin.stats-cards', [
    'stats' => [
        [
            'title' => 'Total Vendas',
            'value' => '1.543',
            'icon' => 'cart',
            'color' => 'success',
            'change' => '+12%',
            'changeType' => 'positive'
        ],
        [
            'title' => 'Pendentes',
            'value' => '23',
            'icon' => 'clock',
            'color' => 'warning'
        ]
    ]
])
```

**Cores disponíveis**: `primary`, `success`, `warning`, `danger`, `info`

---

### 3. Controller
**Ficheiro**: `app/Http/Controllers/Admin/ProposalV2Controller.php`

**O que faz**: Gere todas as operações CRUD de propostas

**Métodos disponíveis**:
- `index()` - Listagem com filtros e paginação
- `create()` - Form de criação
- `store()` - Guardar nova proposta
- `edit($id)` - Form de edição
- `update($id)` - Atualizar proposta
- `destroy($id)` - Eliminar proposta

**Características**:
- Código extremamente comentado (cada linha explicada)
- Validação completa
- Upload de imagens
- Filtros avançados
- Tratamento de erros

---

### 4. Views

#### 4.1. Listagem
**Ficheiro**: `resources/views/admin/v2/proposals/index.blade.php`

**Características**:
- Stats cards no topo
- Barra de filtros colapsável (mobile)
- Cards de items adaptativos
- Paginação
- Estado vazio (quando não há dados)
- Animações suaves

#### 4.2. Formulário
**Ficheiro**: `resources/views/admin/v2/proposals/form.blade.php`

**Características**:
- Secções organizadas
- Layout 2 colunas (info principal + sidebar)
- Validação client-side
- Cálculo automático de totais
- Upload de múltiplas imagens
- Dicas de preenchimento
- Marca/Modelo cascade (dependente)

---

### 5. Rotas
**Ficheiro**: `routes/web.php`

**Rotas criadas**:
```php
GET    /gestao/v2/proposals              # Listagem
GET    /gestao/v2/proposals/create       # Form criar
POST   /gestao/v2/proposals              # Guardar
GET    /gestao/v2/proposals/{id}/edit    # Form editar
PUT    /gestao/v2/proposals/{id}         # Atualizar
DELETE /gestao/v2/proposals/{id}         # Eliminar
```

**Names das rotas**:
- `admin.v2.proposals.index`
- `admin.v2.proposals.create`
- `admin.v2.proposals.store`
- `admin.v2.proposals.edit`
- `admin.v2.proposals.update`
- `admin.v2.proposals.destroy`

---

## 🎮 Como Usar

### Aceder ao Novo Sistema

1. **Via URL direta**:
   ```
   https://seusite.com/gestao/v2/proposals
   ```

2. **Via Dashboard** (redireciona automaticamente):
   ```
   https://seusite.com/gestao/dashboard
   ```

3. **Via Sidebar** (se já estiver no BO):
   - Clicar em "Propostas" no menu lateral

### Sistema Antigo vs Novo

| Característica | V1 (Antigo) | V2 (Novo) |
|---|---|---|
| URL | `/gestao/proposals` | `/gestao/v2/proposals` |
| Layout | Bootstrap básico | Layout moderno V2 |
| Mobile | Limitado | Totalmente otimizado |
| Componentes | Inline | Reutilizáveis |
| Código | Disperso | Bem organizado |
| Documentação | Mínima | Extensa |

**⚠️ IMPORTANTE**: O sistema antigo continua a funcionar! Nada foi apagado.

---

## 🧩 Componentes Reutilizáveis

### Como Criar um Novo Componente

1. **Criar ficheiro em** `resources/views/components/admin/`
2. **Estrutura básica**:
```blade
{{--
    COMPONENTE: Nome do Componente
    
    USO:
    @include('components.admin.nome-componente', [
        'param1' => 'valor'
    ])
    
    DESCRIÇÃO:
    O que o componente faz
--}}

@props([
    'param1' => 'default'
])

<div class="component">
    {{ $param1 }}
</div>

@push('styles')
<style>
    /* Estilos do componente */
</style>
@endpush
```

3. **Usar em qualquer view**:
```blade
@include('components.admin.nome-componente', ['param1' => 'teste'])
```

---

## 🔄 Expandir para Outros Módulos

### Exemplo: Criar Módulo de Clientes V2

#### 1. Criar Controller
```php
// app/Http/Controllers/Admin/ClientV2Controller.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientV2Controller extends Controller
{
    /**
     * Listagem de clientes
     */
    public function index(Request $request)
    {
        $query = Client::query();
        
        // Filtros
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        $clients = $query->paginate(15);
        
        // Stats
        $stats = [
            ['title' => 'Total Clientes', 'value' => Client::count(), 'icon' => 'people', 'color' => 'primary']
        ];
        
        return view('admin.v2.clients.index', compact('clients', 'stats'));
    }
    
    // ... outros métodos (create, store, edit, update, destroy)
}
```

#### 2. Criar View de Listagem
```blade
{{-- resources/views/admin/v2/clients/index.blade.php --}}
@extends('layouts.admin-v2')

@section('title', 'Clientes')

@section('content')

<div class="page-header">
    <h1 class="page-title">Clientes</h1>
</div>

@include('components.admin.stats-cards', ['stats' => $stats])

@include('components.admin.filter-bar', [
    'action' => route('admin.v2.clients.index'),
    'filters' => [
        [
            'name' => 'search',
            'label' => 'Pesquisar',
            'type' => 'text',
            'placeholder' => 'Nome do cliente...',
            'value' => request('search'),
            'col' => 12
        ]
    ]
])

<div class="modern-card">
    @foreach($clients as $client)
        @include('components.admin.item-card', [
            'title' => $client->name,
            'subtitle' => $client->email,
            'meta' => [
                ['icon' => 'telephone', 'text' => $client->phone]
            ],
            'actions' => [
                ['icon' => 'pencil', 'href' => route('admin.v2.clients.edit', $client), 'color' => 'primary', 'label' => 'Editar']
            ]
        ])
    @endforeach
</div>

@endsection
```

#### 3. Adicionar Rotas
```php
// routes/web.php
Route::prefix('v2/clients')->name('admin.v2.clients.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\ClientV2Controller::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admin\ClientV2Controller::class, 'create'])->name('create');
    // ... etc
});
```

#### 4. Adicionar ao Menu
```blade
{{-- resources/views/layouts/admin-v2.blade.php --}}
<div class="nav-item">
    <a href="{{ route('admin.v2.clients.index') }}" class="nav-link {{ request()->routeIs('admin.v2.clients.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Clientes</span>
    </a>
</div>
```

---

## 🎨 Personalização

### Variáveis CSS Disponíveis

No layout `admin-v2.blade.php`, tens estas variáveis CSS reutilizáveis:

```css
:root {
    --admin-primary: #6e0707;          /* Cor principal */
    --admin-primary-dark: #4a0505;     /* Variante escura */
    --admin-secondary: #111111;        /* Cor secundária */
    --admin-success: #28a745;          /* Verde sucesso */
    --admin-danger: #dc3545;           /* Vermelho perigo */
    --admin-warning: #ffc107;          /* Amarelo aviso */
    --admin-info: #17a2b8;             /* Azul info */
    --sidebar-width: 280px;            /* Largura do menu */
    --topbar-height: 70px;             /* Altura da topbar */
    --border-radius: 12px;             /* Raio dos cards */
}
```

**Para mudar a cor principal**:
```css
:root {
    --admin-primary: #0066cc;  /* Muda para azul */
}
```

### Classes Utilitárias

```blade
<!-- Botões -->
<button class="btn btn-primary-modern">Primary</button>
<button class="btn btn-secondary-modern">Secondary</button>
<button class="btn btn-danger-modern">Danger</button>
<button class="btn btn-icon">Icon</button>

<!-- Cards -->
<div class="modern-card">Conteúdo</div>

<!-- Cores -->
<span class="text-primary-admin">Texto vermelho</span>
<div class="bg-primary-admin">Fundo vermelho</div>
<div class="gradient-primary">Gradiente</div>
```

---

## ❓ FAQ

### P: O sistema antigo vai parar de funcionar?
**R**: Não! O sistema V1 continua a funcionar normalmente em `/gestao/proposals`. Ambos coexistem.

### P: Posso usar os componentes noutros projetos?
**R**: Sim! Os componentes são independentes e podem ser copiados para outros projetos Laravel.

### P: Como adiciono mais filtros à listagem?
**R**: Basta adicionar mais items ao array `filters` no componente `filter-bar`. Suporta text, select, date e number.

### P: Como personalizo as cores?
**R**: Muda as variáveis CSS no `:root` do ficheiro `admin-v2.blade.php`.

### P: Preciso migrar dados do V1 para V2?
**R**: Não! Ambos usam a mesma base de dados. O V2 é apenas uma interface diferente.

### P: Como adiciono novos items ao menu lateral?
**R**: Edita o ficheiro `layouts/admin-v2.blade.php` na secção `<aside class="admin-sidebar">`.

### P: O V2 funciona em mobile?
**R**: Sim! Foi desenvolvido com abordagem mobile-first. Testa redimensionando o browser.

### P: Posso adicionar mais secções ao formulário?
**R**: Sim! Copia a estrutura de uma secção existente (`.modern-card`) e personaliza.

---

## 🎓 Para Estagiários

### Conceitos Importantes

1. **Blade Components**: São "pedaços" de código reutilizáveis. Como LEGO blocks.

2. **Controller**: É o "cérebro" que decide o que fazer com os dados.

3. **View**: É o que o utilizador vê no browser.

4. **Route**: É o "caminho" que liga uma URL a um Controller.

5. **Mobile-First**: Desenvolver primeiro para mobile, depois adaptar para desktop.

### Exercícios Práticos

1. **Fácil**: Adiciona um novo filtro à listagem de propostas
2. **Médio**: Cria um novo módulo de Veículos V2 usando os componentes
3. **Difícil**: Adiciona funcionalidade de "quick edit" (editar direto da listagem)

### Recursos de Aprendizagem

- **Laravel Docs**: https://laravel.com/docs
- **Bootstrap Icons**: https://icons.getbootstrap.com
- **CSS Flexbox**: https://css-tricks.com/snippets/css/a-guide-to-flexbox/

---

## 📞 Suporte

Se tiveres dúvidas ou problemas:

1. Lê primeiro esta documentação completa
2. Verifica os comentários no código (estão MUITO bem explicados)
3. Testa no browser e vê a consola de erros (F12)
4. Se persistir, documenta o erro com screenshots

---

## 🚀 Roadmap Futuro

Funcionalidades planeadas para implementar:

- [ ] Sistema de notificações em tempo real
- [ ] Export de dados para Excel/PDF
- [ ] Filtros salvos (favoritos)
- [ ] Dark mode
- [ ] Drag & drop para reordenar items
- [ ] Búlk actions (ações em massa)
- [ ] Timeline de atividades
- [ ] Sistema de permissões granular
- [ ] API REST para integrações
- [ ] PWA (Progressive Web App)

---

## 📝 Changelog

### v2.0.0 (15/12/2025)
- ✨ Lançamento inicial do sistema V2
- ✨ Layout admin-v2 criado
- ✨ 3 componentes reutilizáveis criados
- ✨ Módulo de propostas completamente funcional
- ✨ Sistema de rotas organizado
- ✨ Documentação completa

---

**Desenvolvido com ❤️ para Izzycar**

Este sistema foi criado para ser fácil de usar, manter e expandir. Diverte-te a desenvolver! 🎉
