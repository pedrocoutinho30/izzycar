<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content contact-modal-card">

            <div class="modal-header contact-modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="contactModalLabel">
                        <i class="bi bi-envelope-fill me-2"></i>Pedir mais informações
                    </h5>
                    <p class="mb-0 mt-1 contact-modal-subtitle">Resposta em menos de 24 horas</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4">
                <form action="{{ route('contact.vehicle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="url" value="{{ request()->fullUrl() }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="contact-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control contact-input" name="name"
                                   placeholder="O seu nome" required>
                        </div>
                        <div class="col-md-6">
                            <label class="contact-label">Telefone / Telemóvel <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control contact-input" name="phone"
                                   placeholder="9XX XXX XXX" required>
                        </div>
                        <div class="col-12">
                            <label class="contact-label">Email</label>
                            <input type="email" class="form-control contact-input" name="email"
                                   placeholder="email@exemplo.pt">
                        </div>
                        <div class="col-12">
                            <label class="contact-label">Mensagem <span class="text-danger">*</span></label>
                            <textarea class="form-control contact-input" name="message" rows="3"
                                      placeholder="Olá, gostaria de saber mais sobre este veículo…" required></textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-grid">
                        <button type="submit" class="btn contact-submit-btn" id="contactSubmitBtn">
                            <i class="bi bi-send me-2"></i>Enviar pedido
                        </button>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const form = document.querySelector('#contactModal form');
                            const btn  = document.getElementById('contactSubmitBtn');
                            if (form && btn) {
                                form.addEventListener('submit', function () {
                                    btn.disabled = true;
                                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>A enviar…';
                                });
                            }
                        });
                    </script>
                </form>
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    .contact-modal-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
    }
    .contact-modal-header {
        background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: none;
        align-items: flex-start;
    }
    .contact-modal-header .modal-title {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 700;
    }
    .contact-modal-subtitle {
        color: rgba(255,255,255,.75);
        font-size: .82rem;
    }
    .contact-label {
        display: block;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #555;
        margin-bottom: 5px;
    }
    .contact-input {
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        font-size: .92rem;
        transition: border-color .2s, box-shadow .2s;
        color: #333;
    }
    .contact-input:focus {
        border-color: #6e0707;
        box-shadow: 0 0 0 3px rgba(110,7,7,.1);
        outline: none;
    }
    .contact-input::placeholder { color: #aaa; }
    .contact-submit-btn {
        background: linear-gradient(135deg, #6e0707 0%, #990000 100%);
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 12px;
        font-weight: 600;
        font-size: .95rem;
        transition: opacity .2s, transform .2s;
    }
    .contact-submit-btn:hover  { opacity: .9; transform: translateY(-1px); color: #fff; }
    .contact-submit-btn:active { transform: translateY(0); }
    .contact-submit-btn:disabled { opacity: .65; }
    .modal-backdrop.show {
        backdrop-filter: blur(8px);
        background-color: rgba(0,0,0,.55);
    }
</style>
@endpush
