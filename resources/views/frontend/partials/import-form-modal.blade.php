<div class="modal fade" id="formPropostaModal" tabindex="-1" aria-labelledby="formPropostaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formPropostaLabel">Peça já a sua proposta</h5>
                <button type="button" class="btn-close bg-finance" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                {{-- Formulário --}}
                @include('frontend.forms.proposal')

            </div>

            <!-- <div id="formResponse" class="mt-3" style="display:none;"></div> -->

        </div>
    </div>
</div>

