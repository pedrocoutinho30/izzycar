<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content share-modal-card">
            <div class="modal-header share-modal-header">
                <h5 class="modal-title" id="shareModalLabel">
                    <i class="bi bi-share-fill me-2"></i>Partilhar viatura
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    {{ $vehicle->brand }} {{ $vehicle->model }}
                    @if($vehicle->version) &mdash; {{ $vehicle->version }} @endif
                </p>

                <!-- Link de cópia -->
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm" id="shareLinkInput"
                           value="{{ urldecode(request()->fullUrl()) }}" readonly>
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyShareLink()" id="copyBtn">
                        <i class="bi bi-clipboard me-1"></i>Copiar
                    </button>
                </div>

                <hr class="my-3">

                <!-- Partilha social -->
                <div class="d-grid gap-2">
                    <a href="https://wa.me/?text={{ urlencode($vehicle->brand . ' ' . $vehicle->model . ' - ' . request()->fullUrl()) }}"
                       target="_blank" class="btn share-btn-whatsapp">
                        <i class="bi bi-whatsapp me-2"></i>Partilhar no WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                       target="_blank" class="btn share-btn-facebook">
                        <i class="bi bi-facebook me-2"></i>Partilhar no Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .share-modal-card {
        background: var(--white-color);
        border-radius: 16px;
        overflow: hidden;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
    }
    .share-modal-header {
        background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
        padding: 1rem 1.25rem;
        border-bottom: none;
    }
    .share-modal-header .modal-title { color: #fff; font-size: 1rem; font-weight: 700; }
    .share-btn-whatsapp {
        background: #25D366;
        color: #fff;
        border-radius: 50px;
        font-weight: 600;
    }
    .share-btn-whatsapp:hover { background: #1ebe5d; color: #fff; }
    .share-btn-facebook {
        background: #1877F2;
        color: #fff;
        border-radius: 50px;
        font-weight: 600;
    }
    .share-btn-facebook:hover { background: #0d65d8; color: #fff; }
    #shareLinkInput { background: #f5f5f5; font-size: .82rem; color: #555; }
</style>
@endpush

@push('scripts')
<script>
function copyShareLink() {
    const input = document.getElementById('shareLinkInput');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('copyBtn');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Copiado!';
        btn.classList.replace('btn-outline-secondary', 'btn-success');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.replace('btn-success', 'btn-outline-secondary'); }, 2000);
    }).catch(() => {
        input.select();
        document.execCommand('copy');
    });
}
</script>
@endpush