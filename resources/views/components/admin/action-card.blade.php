    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title">
                <i class="bi bi-check-circle"></i>
                Ações
            </h5>
        </div>

        <div class="d-grid gap-2">
            @if($submitButtonLabel)
            <button type="submit" class="btn btn-primary-modern btn-lg">
                <i class="bi bi-check-lg"></i>
                {{ $submitButtonLabel }}
            </button>
            @endif

            <a href="{{ $cancelButtonHref }}" class="btn btn-secondary-modern">
                <i class="bi bi-x-lg"></i>
                Cancelar
            </a>
            
        </div>

        @if($timestamps)
        <hr>
        <div class="text-muted small">
            @if(isset($timestamps['created_at']))
            <div class="mb-2">
                <i class="bi bi-clock-history"></i>
                Criada: {{ $timestamps['created_at']->format('d/m/Y H:i') }}
            </div>
            @endif
            @if(isset($timestamps['updated_at']))
            <div>
                <i class="bi bi-pencil"></i>
                Última atualização: {{ $timestamps['updated_at']->format('d/m/Y H:i') }}
            </div>
            @endif
        </div>
        @endif
    </div>