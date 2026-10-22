{{-- Confirmação de mudança de estado, com a escolha de avisar ou não o cliente
     por email. Partilhado pelo detalhe e pelo kanban:
     askStatusChange({status, from, hasEmail, title, askNotify = true, message, notifyLabel, hint}) => Promise<{confirmed, notify}>
     askNotify=false esconde a opção do email; message substitui o texto da pergunta. --}}
<div class="modal fade" id="statusChangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scmTitle">Mudar estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="scmText" class="mb-3"></p>
                <div class="form-check" id="scmNotifyRow">
                    <input class="form-check-input" type="checkbox" id="scmNotify">
                    <label class="form-check-label" for="scmNotify" id="scmNotifyLabel">Enviar email ao cliente</label>
                </div>
                <div class="form-text" id="scmHint"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary-modern" id="scmConfirm">Confirmar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // Etapas em que, por omissão, não se propõe avisar o cliente.
    const SILENT = @json(\App\Models\ConvertedProposal::SILENT_BY_DEFAULT);

    window.askStatusChange = function ({ status, from, hasEmail, title, askNotify = true, message, notifyLabel, hint }) {
        return new Promise(resolve => {
            const el = document.getElementById('statusChangeModal');
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            const notify = document.getElementById('scmNotify');
            const cancelling = status === 'Cancelada';

            document.getElementById('scmTitle').textContent = cancelling ? 'Cancelar o processo' : 'Mudar o estado';
            const text = document.getElementById('scmText');
            if (message) {
                text.textContent = message;
                if (title) text.insertAdjacentHTML('afterbegin', '<strong></strong><br>');
            } else {
                text.innerHTML = (title ? '<strong></strong><br>' : '') +
                    (cancelling ? 'Cancelar este processo?' : (from ? 'De <strong class="from"></strong> para <strong class="to"></strong>?' : 'Mudar para <strong class="to"></strong>?'));
                text.querySelector('.from')?.append(from);
                text.querySelector('.to')?.append(status);
            }
            if (title) text.querySelector('strong').textContent = title;

            document.getElementById('scmNotifyRow').classList.toggle('d-none', !askNotify);
            document.getElementById('scmNotifyLabel').textContent = notifyLabel || 'Enviar email ao cliente';
            notify.disabled = !hasEmail;
            notify.checked = askNotify && hasEmail && !SILENT.includes(status);
            document.getElementById('scmHint').textContent = !askNotify ? (hint || '') : (hasEmail
                ? (hint || (SILENT.includes(status) ? 'Etapa de burocracia interna: por omissão não se avisa o cliente.' : ''))
                : 'O cliente não tem email registado.');

            let confirmed = false;
            const confirmBtn = document.getElementById('scmConfirm');
            const onConfirm = () => { confirmed = true; modal.hide(); };
            const onHidden = () => {
                confirmBtn.removeEventListener('click', onConfirm);
                el.removeEventListener('hidden.bs.modal', onHidden);
                resolve({ confirmed, notify: confirmed && askNotify && notify.checked });
            };
            confirmBtn.addEventListener('click', onConfirm);
            el.addEventListener('hidden.bs.modal', onHidden);
            modal.show();
        });
    };
})();
</script>
@endpush
