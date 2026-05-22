@extends('layouts.admin-v2')

@section('title', 'Veículos V3')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-car-front-fill', 'label' => 'Veículos V3', 'href' => ''],
    ],
    'title'    => 'Veículos V3',
    'subtitle' => 'Gestão completa de veículos',
    'actionHref' => route('admin.v3.vehicles.create'),
    'actionLabel' => 'Novo Veículo',
])

{{-- Flash --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Filters --}}
<div class="modern-card mb-3">
    <div class="modern-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Pesquisar referência, marca, modelo, matrícula…" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os estados</option>
                    @foreach(\App\Models\V3Vehicle::statusOptions() as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="fuel" class="form-select form-select-sm">
                    <option value="">Todos os combustíveis</option>
                    @foreach(\App\Models\V3Vehicle::fuelOptions() as $f)
                        <option value="{{ $f }}" {{ request('fuel') === $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i> Filtrar</button>
                <a href="{{ route('admin.v3.vehicles.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Limpar</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="modern-card">
    <div class="modern-card-body p-0">
        @if($vehicles->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-car-front fs-1 d-block mb-2"></i>
                Nenhum veículo encontrado.
                <a href="{{ route('admin.v3.vehicles.create') }}" class="d-block mt-2">Criar primeiro veículo</a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px"></th>
                        <th>Referência</th>
                        <th>Veículo</th>
                        <th>Matrícula</th>
                        <th>Combustível</th>
                        <th>Estado</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicles as $v)
                    <tr>
                        <td>
                            @if($v->coverPhoto)
                                <img src="{{ asset('storage/' . $v->coverPhoto->path) }}" class="rounded" width="44" height="44" style="object-fit:cover">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px"><i class="bi bi-car-front text-muted"></i></div>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary font-monospace">{{ $v->reference }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $v->brand }} {{ $v->model }}</div>
                            <small class="text-muted">{{ $v->version }} {{ $v->year ? '· ' . $v->year : '' }} {{ $v->kilometers ? '· ' . number_format($v->kilometers) . ' km' : '' }}</small>
                        </td>
                        <td>{{ $v->registration ?: '—' }}</td>
                        <td>{{ $v->fuel ?: '—' }}</td>
                        <td><span class="badge bg-{{ $v->status_color }}">{{ $v->status_label }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.v3.vehicles.edit', $v->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <button type="button"
                                class="btn btn-sm btn-outline-success"
                                title="Gerar Anúncio"
                                onclick="openAdModal({{ $v->id }}, {{ json_encode(trim($v->brand . ' ' . $v->model . ' ' . $v->sub_model)) }}, {{ $v->ad_text ? 'true' : 'false' }}, {{ json_encode($v->ad_text) }})">
                                <i class="bi bi-megaphone"></i>
                            </button>
                            <form action="{{ route('admin.v3.vehicles.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar este veículo?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2">{{ $vehicles->links() }}</div>
        @endif
    </div>
</div>

@endsection

{{-- ===== MODAL: GERAR ANÚNCIO ===== --}}
<div class="modal fade" id="adModal" tabindex="-1" aria-labelledby="adModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adModalLabel">
                    <i class="bi bi-megaphone me-2 text-success"></i>Gerar Anúncio
                    <small class="ms-2 text-muted fw-normal" id="adModalVehicleName"></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Status badges --}}
                <div id="adStatusBadge" class="mb-2 d-none">
                    <span id="adBadgeAI" class="badge bg-primary d-none"><i class="bi bi-stars me-1"></i>Gerado por IA</span>
                    <span id="adBadgeSaved" class="badge bg-success d-none"><i class="bi bi-floppy me-1"></i>Guardado</span>
                    <span id="adBadgeTemplate" class="badge bg-secondary d-none"><i class="bi bi-file-text me-1"></i>Modelo padrão (IA indisponível)</span>
                </div>

                {{-- Spinner --}}
                <div id="adSpinner" class="text-center py-5 d-none">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="mt-2 text-muted small">A gerar anúncio com IA…</div>
                </div>

                {{-- Textarea --}}
                <textarea id="adTextarea" class="form-control d-none" rows="12" placeholder="O texto do anúncio aparecerá aqui…" style="resize:vertical; font-size:.92rem; line-height:1.6;"></textarea>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="adBtnRegenerate" onclick="adGenerate(true)">
                    <i class="bi bi-arrow-clockwise me-1"></i>Regenerar
                </button>
                <button type="button" class="btn btn-outline-dark btn-sm" id="adBtnCopy" onclick="adCopy()">
                    <i class="bi bi-clipboard me-1"></i>Copiar
                </button>
                <button type="button" class="btn btn-success btn-sm" id="adBtnSave" onclick="adSave()">
                    <i class="bi bi-floppy me-1"></i>Guardar
                </button>
                <button type="button" class="btn btn-secondary btn-sm ms-auto" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let _adVehicleId   = null;
let _adVehicleName = '';
let _adSource      = null; // 'ai' | 'saved' | 'template'

const adModal       = new bootstrap.Modal(document.getElementById('adModal'));
const adTextarea    = document.getElementById('adTextarea');
const adSpinner     = document.getElementById('adSpinner');
const adStatusBadge = document.getElementById('adStatusBadge');
const adBadgeAI     = document.getElementById('adBadgeAI');
const adBadgeSaved  = document.getElementById('adBadgeSaved');
const adBadgeTemplate = document.getElementById('adBadgeTemplate');

function openAdModal(vehicleId, vehicleName, hasSaved, savedText) {
    _adVehicleId   = vehicleId;
    _adVehicleName = vehicleName;
    document.getElementById('adModalVehicleName').textContent = vehicleName || '';

    // Reset UI
    adHideAll();
    adSetBadge(null);

    if (hasSaved && savedText) {
        adShowText(savedText, 'saved');
    } else {
        adGenerate(false);
    }

    adModal.show();
}

function adHideAll() {
    adTextarea.classList.add('d-none');
    adSpinner.classList.add('d-none');
    adStatusBadge.classList.add('d-none');
}

function adSetBadge(source) {
    adBadgeAI.classList.add('d-none');
    adBadgeSaved.classList.add('d-none');
    adBadgeTemplate.classList.add('d-none');
    if (source === 'ai')       { adBadgeAI.classList.remove('d-none'); adStatusBadge.classList.remove('d-none'); }
    if (source === 'saved')    { adBadgeSaved.classList.remove('d-none'); adStatusBadge.classList.remove('d-none'); }
    if (source === 'template') { adBadgeTemplate.classList.remove('d-none'); adStatusBadge.classList.remove('d-none'); }
}

function adShowText(text, source) {
    _adSource = source;
    adSpinner.classList.add('d-none');
    adTextarea.value = text;
    adTextarea.classList.remove('d-none');
    adSetBadge(source);
}

function adGenerate(forceRegenerate) {
    adHideAll();
    adSpinner.classList.remove('d-none');

    const url = `{{ url('gestao/v3/vehicles') }}/${_adVehicleId}/generate-ad`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ regenerate: forceRegenerate }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            adShowText(data.text, data.ai ? 'ai' : 'template');
        } else {
            adShowText('Erro ao gerar o anúncio. Tente novamente.', 'template');
        }
    })
    .catch(() => {
        adShowText('Erro de ligação. Tente novamente.', 'template');
    });
}

function adCopy() {
    const text = adTextarea.value.trim();
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('adBtnCopy');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copiado!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-dark');
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-dark');
        }, 2000);
    });
}

function adSave() {
    const text = adTextarea.value.trim();
    const url  = `{{ url('gestao/v3/vehicles') }}/${_adVehicleId}/save-ad`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ ad_text: text }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            adSetBadge('saved');
            const btn = document.getElementById('adBtnSave');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Guardado!';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        }
    })
    .catch(() => { alert('Erro ao guardar. Tente novamente.'); });
}
</script>
@endpush
