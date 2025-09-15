@props([
'label' => 'Imagens',
'images' => '', // string separada por vírgula
'multi' => true,
])

<div class="form-group image-manager" data-multiselect="{{ $multi ? 'true' : 'false' }}">
    <label>{{ $label }}</label>

    {{-- Imagens existentes --}}
    <div class="d-flex flex-wrap mb-2" id="existing-images-preview">
        @if(!empty($images))
        @foreach(explode(',', $images) as $img)
        <div class="img-thumb me-2 mb-2">
            <img src="{{ $img }}" style="width:100px;height:100px;object-fit:cover;border-radius:6px;">
            <button type="button" class="btn btn-sm btn-danger mt-1 w-100 remove-image">Remover</button>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Novas imagens --}}
    <div class="d-flex flex-wrap mb-2" id="new-images-preview"></div>

    {{-- Inputs hidden para enviar ao backend --}}
    <input type="hidden" name="images_existing" value="{{ $images ?? '' }}">
    <input type="hidden" id="images_new_input" name="images_new" value="">
    <input type="hidden" name="images_removed" value="">

    <div class="input-group mt-2">
        <button data-input="images_new_input" data-preview="new-images-preview" class="btn btn-primary lfm" type="button">Escolher Imagens</button>
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const manager = document.querySelector('.image-manager');
        const multi = manager.dataset.multiselect === 'true';
        const existingPreview = manager.querySelector('#existing-images-preview');
        const newPreview = manager.querySelector('#new-images-preview');

        const inputExisting = manager.querySelector('input[name="images_existing"]');
        const inputNew = manager.querySelector('input[name="images_new"]');
        const inputRemoved = manager.querySelector('input[name="images_removed"]');

        let existingImages = inputExisting.value.split(',').map(i => i.trim()).filter(i => i);
        let newImages = [];
        let removedImages = [];

        // Remove imagem existente
        function removeExistingImage(src, div) {
            existingImages = existingImages.filter(i => i !== src);
            removedImages.push(src);
            inputExisting.value = existingImages.join(',');
            inputRemoved.value = removedImages.join(',');
            div.remove();
        }

        // Remove imagem nova
        function removeNewImage(src, div) {
            newImages = newImages.filter(i => i !== src);
            inputNew.value = newImages.join(',');
            div.remove();
        }

        // Atualiza eventos das imagens existentes
        function bindExistingRemove() {
            Array.from(existingPreview.querySelectorAll('.remove-image')).forEach(btn => {
                btn.onclick = function() {
                    const div = btn.parentElement;
                    const src = div.querySelector('img').src;
                    removeExistingImage(src, div);
                }
            });
        }

        bindExistingRemove();

        // Atualiza preview das novas imagens
        function updateNewPreview() {
            newPreview.innerHTML = '';
            newImages.forEach(src => {
                const div = document.createElement('div');
                div.classList.add('img-thumb', 'me-2', 'mb-2');

                const img = document.createElement('img');
                img.src = src;
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '6px';
                div.appendChild(img);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.classList.add('btn', 'btn-sm', 'btn-danger', 'mt-1', 'w-100', 'remove-image');
                btn.textContent = 'Remover';
                btn.onclick = () => removeNewImage(src, div);
                div.appendChild(btn);

                newPreview.appendChild(div);
            });

            inputNew.value = newImages.join(',');
        }

        // Inicializa FileManager
        const lfmBtn = manager.querySelector('.lfm');
        $(lfmBtn).filemanager('image');

        // Callback global do FileManager
        window.SetUrl = function(url) {
            const urls = url.split(',');
            if (multi) {
                newImages = [...new Set([...newImages, ...urls])];
            } else {
                newImages = [urls[0]];
            }
            updateNewPreview();
        }
    });
</script>
@endonce