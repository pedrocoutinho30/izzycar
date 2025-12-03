<div class="modal fade" id="formPropostaModal" tabindex="-1" aria-labelledby="formPropostaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-modern">
            <div class="modal-header-modern">
                <div class="modal-header-content">
                    <div class="modal-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <line x1="10" y1="9" x2="8" y2="9"></line>
                        </svg>
                    </div>
                    <div>
                        <h5 class="modal-title-modern" id="formPropostaLabel">Peça a sua proposta</h5>
                        <p class="modal-subtitle-modern">Preencha o formulário e receba uma proposta personalizada</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modern" data-bs-dismiss="modal" aria-label="Fechar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body-modern">
                @include('frontend.forms.proposal')
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .modal-modern {
        background: white;
        border-radius: 24px;
        border: none;
        overflow: hidden;
    }

    .modal-header-modern {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        padding: 2rem;
        border: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .modal-header-content {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex: 1;
    }

    .modal-icon {
        flex-shrink: 0;
        width: 64px;
        height: 64px;
        background: rgba(255,255,255,0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .modal-title-modern {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0;
    }

    .modal-subtitle-modern {
        font-size: 0.95rem;
        color: rgba(255,255,255,0.9);
        margin: 0;
        margin-top: 0.25rem;
    }

    .btn-close-modern {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.15);
        border: none;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-close-modern:hover {
        background: rgba(255,255,255,0.25);
        transform: rotate(90deg);
    }

    .modal-body-modern {
        padding: 2.5rem;
        max-height: calc(100vh - 250px);
        overflow-y: auto;
    }

    /* Scrollbar personalizada */
    .modal-body-modern::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body-modern::-webkit-scrollbar-track {
        background: #f1f3f5;
        border-radius: 10px;
    }

    .modal-body-modern::-webkit-scrollbar-thumb {
        background: var(--accent-color);
        border-radius: 10px;
    }

    .modal-body-modern::-webkit-scrollbar-thumb:hover {
        background: #990000;
    }

    /* Backdrop com blur */
    .modal-backdrop.show {
        backdrop-filter: blur(10px);
        background-color: rgba(0, 0, 0, 0.6);
    }

    @media (max-width: 1200px) {
        .modal-xl {
            max-width: 90%;
        }
    }

    @media (max-width: 768px) {
        .modal-header-modern {
            padding: 1.5rem;
        }

        .modal-header-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
        }

        .modal-icon svg {
            width: 24px;
            height: 24px;
        }

        .modal-title-modern {
            font-size: 1.5rem;
        }

        .modal-subtitle-modern {
            font-size: 0.9rem;
        }

        .modal-body-modern {
            padding: 1.5rem;
        }
    }
</style>
@endpush

