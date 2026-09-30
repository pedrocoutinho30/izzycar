{{-- Modais "Novo vendedor" e "Novo contacto" do seletor de vendedor
     (_picker). Submetem por AJAX; o _scripts atualiza o seletor que os abriu. --}}
<div class="modal fade" id="sellerQuickModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.v2.sellers.store') }}" method="POST" data-seller-form="quick-seller" data-ajax novalidate>
                @csrf
                <input type="hidden" name="confirm_matches" value="0">
                <input type="hidden" name="existing_seller_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Novo vendedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div data-dup-alert></div>
                    <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.06em">Vendedor</h6>
                    @include('admin.v2.sellers._seller-fields', ['seller' => null, 'useOld' => false, 'compact' => true, 'idPrefix' => 'quickSeller'])
                    <h6 class="text-muted text-uppercase fw-semibold mt-4 mb-2" style="font-size:.7rem;letter-spacing:.06em">Primeiro contacto (opcional)</h6>
                    @include('admin.v2.sellers._contact-fields', ['contact' => null, 'useOld' => false, 'showPrimary' => false, 'required' => false, 'idPrefix' => 'quickSellerContact'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-modern"><i class="bi bi-plus"></i> Criar vendedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="sellerContactQuickModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="" method="POST" data-seller-form="quick-contact" data-ajax novalidate>
                @csrf
                <input type="hidden" name="confirm_matches" value="0">
                <div class="modal-header">
                    <h5 class="modal-title">Novo contacto <small class="text-muted fw-normal">— <span data-seller-name></span></small></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div data-dup-alert></div>
                    @include('admin.v2.sellers._contact-fields', ['contact' => null, 'useOld' => false, 'idPrefix' => 'quickContact'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-modern"><i class="bi bi-plus"></i> Adicionar contacto</button>
                </div>
            </form>
        </div>
    </div>
</div>
