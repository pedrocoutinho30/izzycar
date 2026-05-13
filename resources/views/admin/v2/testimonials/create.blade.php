@extends('layouts.admin-v2')

@section('title', 'Novo Testemunho')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door',  'label' => 'Dashboard',    'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-chat-quote',  'label' => 'Testemunhos',  'href' => route('admin.testimonials.index')],
        ['icon' => 'bi bi-plus-circle', 'label' => 'Novo',         'href' => ''],
    ],
    'title'    => 'Novo Testemunho',
    'subtitle' => 'Adicionar avaliação de cliente',
])

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.testimonials.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-person me-1"></i> Dados do Testemunho</h5>
                </div>
                <div class="modern-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Ex: João Silva" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Origem <span class="text-danger">*</span></label>
                            <select name="origin" class="form-select @error('origin') is-invalid @enderror" required>
                                @foreach($origins as $key => $label)
                                    <option value="{{ $key }}" {{ old('origin', 'google') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('origin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Avaliação <span class="text-danger">*</span></label>
                            <select name="rating" id="ratingInput" class="form-select @error('rating') is-invalid @enderror" style="max-width:200px">
                                @foreach([0,0.5,1,1.5,2,2.5,3,3.5,4,4.5,5] as $val)
                                    <option value="{{ $val }}" {{ old('rating', 0) == $val ? 'selected' : '' }}>{{ $val }} ★</option>
                                @endforeach
                            </select>
                            @error('rating')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Comentário <span class="text-danger">*</span></label>
                            <textarea name="comment" rows="5" class="form-control @error('comment') is-invalid @enderror"
                                      placeholder="Escreve a opinião do cliente..." >{{ old('comment') }}</textarea>
                            @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Data da avaliação</label>
                            <input type="date" name="review_date" class="form-control @error('review_date') is-invalid @enderror"
                                   value="{{ old('review_date') }}">
                            <div class="form-text">Data em que o cliente deixou a avaliação (opcional).</div>
                            @error('review_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-gear me-1"></i> Opções</h5>
                </div>
                <div class="modern-card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="published" id="published" value="1"
                               {{ old('published') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="published">Publicar imediatamente</label>
                        <div class="form-text">Testemunhos publicados ficam visíveis no site.</div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-check-lg me-1"></i> Guardar
                </button>
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
(function () {
    const stars   = document.querySelectorAll('.star-icon');
    const input   = document.getElementById('ratingInput');
    const label   = document.getElementById('ratingLabel');
    const labels  = ['', '1 — Mau', '2 — Razoável', '3 — Bom', '4 — Muito Bom', '5 — Excelente'];
    let current   = parseInt(input.value) || 0;

    function paint(val) {
        stars.forEach((s, i) => {
            s.classList.toggle('bi-star-fill', i < val);
            s.classList.toggle('bi-star', i >= val);
            s.style.color = i < val ? '#ffc107' : '#ccc';
        });
        label.textContent = labels[val] || 'Seleciona uma avaliação';
    }

    paint(current);

    stars.forEach((s, i) => {
        s.addEventListener('mouseover', () => paint(i + 1));
        s.addEventListener('mouseleave', () => paint(current));
        s.addEventListener('click', () => {
            current = i + 1;
            input.value = current;
            paint(current);
        });
    });
})();
</script>
@endpush
