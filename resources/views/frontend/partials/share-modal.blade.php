<div class="modal fade " id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title " id="shareModalLabel">Partilhar</h5>
                <button type="button" class="btn-close bg-finance" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>




            <div class="modal-body text-center">
                <!-- Botão WhatsApp -->
                <a href="https://wa.me/?text={{ urlencode($vehicle->brand . ' ' . $vehicle->model . ' - ' . request()->fullUrl()) }}"
                    target="_blank"
                    class="btn btn-success w-100 mb-3">
                    <i class="bi bi-whatsapp me-2"></i> Partilhar no WhatsApp
                </a>

                <!-- Botão copiar link -->
                <button class="btn btn-outline-form w-100" onclick="copyShareLink()">
                    <i class="bi bi-clipboard me-2"></i> Copiar link
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #shareModal .modal-content {
        background-color: var(--primary-color);
        color: white;
        /* para texto ficar legível */
    }

    #shareModal .form-label {
        color: white;
        /* labels em branco */
    }

    .modal-backdrop.show {
        backdrop-filter: blur(20px);
        /* Ajuste a intensidade aqui */
        background-color: rgba(0, 0, 0, 0.8);
        /* mantém escurecimento */
    }

    #shareModal .form-control {
        background-color: var(--secondary-color);
        border: 1px solid var(--accent-color);
        color: white;
    }

    #shareModal .form-control:focus {
        background-color: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-color);
        color: white;
    }

    #shareModal .btn-primary {
        background-color: var(--accent-color);
        border: none;
    }
</style>
@endpush