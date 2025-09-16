<div class="card mt-4">
    <div class="card-header">
        <h5>SEO & Redes Sociais</h5>
    </div>
    <div class="card-body">
        {{-- SEO Básico --}}
        <div class="mb-3">
            <label for="seo_title" class="form-label">Título (SEO)</label>
            <input type="text" class="form-control" id="seo_title" name="seo[title]" value="{{ old('seo.title', $model->seo->title ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="seo_meta_description" class="form-label">Meta Description</label>
            <textarea class="form-control" id="seo_meta_description" name="seo[meta_description]" rows="3">{{ old('seo.meta_description', $model->seo->meta_description ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <x-image-manager :label="'Meta Image'" :images="$model->seo->meta_image ?? ''" name="seo[meta_image]" :multi="false" id="meta_image" />


        </div>

        <div class="mb-3">
            <label for="seo_meta_keywords" class="form-label">Palavras-chave</label>
            <input type="text" class="form-control" id="seo_meta_keywords" name="seo[meta_keywords]" value="{{ old('seo.meta_keywords', $model->seo->meta_keywords ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="seo_meta_secundary_keywords" class="form-label">Palavras-chave Secundárias</label>
            <input type="text" class="form-control" id="seo_meta_secundary_keywords" name="seo[meta_secundary_keywords]" value="{{ old('seo.meta_secundary_keywords', $model->seo->meta_secundary_keywords ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="seo_canonical_url" class="form-label">Canonical URL</label>
            <input type="text" class="form-control" id="seo_canonical_url" name="seo[canonical_url]" value="{{ old('seo.canonical_url', $model->seo->canonical_url ?? '') }}">
        </div>

        {{-- Open Graph --}}
        <h6 class="mt-4 bold">Open Graph (Facebook, LinkedIn)</h6>
        <div class="mb-3">
            <label for="og_title" class="form-label">OG Title</label>
            <input type="text" class="form-control" id="og_title" name="seo[og_title]" value="{{ old('seo.og_title', $model->seo->og_title ?? '') }}">
        </div>
        <div class="mb-3">
            <label for="og_description" class="form-label">OG Description</label>
            <textarea class="form-control" id="og_description" name="seo[og_description]" rows="3">{{ old('seo.og_description', $model->seo->og_description ?? '') }}</textarea>
        </div>
        <div class="mb-3">
            <!-- <label for="og_image" class="form-label">OG Image URL</label> -->
            <x-image-manager :label="'OG Image'" :images="$model->seo->og_image ?? ''" name="seo[og_image]" :multi="false" id="ogImage" />
            <!-- <input type="text" class="form-control" id="og_image" name="seo[og_image]" value="{{ old('seo.og_image', $model->seo->og_image ?? '') }}"> -->
        </div>
        <div class="mb-3">
            <label for="og_url" class="form-label">OG URL</label>
            <input type="text" class="form-control" id="og_url" name="seo[og_url]" value="{{ old('seo.og_url', $model->seo->og_url ?? '') }}">
        </div>
        <div class="mb-3">
            <label for="og_type" class="form-label">OG Type</label>
            <label for="og_type">OG Type</label>
            <select name="seo[og_type]" id="og_type" class="form-control">
                @php
                $types = ['website', 'article', 'product', 'video', 'profile'];
                $current = $model->seo->og_type ?? '';
                @endphp
                @foreach($types as $type)
                <option value="{{ $type }}" @selected($current===$type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            <!-- <input type="text" class="form-control" id="og_type" name="seo[og_type]" value="{{ old('seo.og_type', $model->seo->og_type ?? '') }}"> -->
        </div>

        {{-- Twitter --}}
        <h6 class="mt-4">Twitter Card</h6>
        <div class="mb-3">
            <label for="twitter_card" class="form-label">Card Type</label>
            <input type="text" class="form-control" id="twitter_card" name="seo[twitter_card]" value="{{ old('seo.twitter_card', $model->seo->twitter_card ?? '') }}">
        </div>
        <div class="mb-3">
            <label for="twitter_title" class="form-label">Title</label>
            <input type="text" class="form-control" id="twitter_title" name="seo[twitter_title]" value="{{ old('seo.twitter_title', $model->seo->twitter_title ?? '') }}">
        </div>
        <div class="mb-3">
            <label for="twitter_description" class="form-label">Description</label>
            <textarea class="form-control" id="twitter_description" name="seo[twitter_description]" rows="3">{{ old('seo.twitter_description', $model->seo->twitter_description ?? '') }}</textarea>
        </div>
        <div class="mb-3">
            <x-image-manager :label="'Twitter Image'" :images="$model->seo->twitter_image ?? ''" name="seo[twitter_image]" :multi="false" id="twitter_image" />
            <!-- <label for="twitter_image" class="form-label">Image URL</label>
            <input type="text" class="form-control" id="twitter_image" name="seo[twitter_image]" value="{{ old('seo.twitter_image', $model->seo->twitter_image ?? '') }}"> -->
        </div>
    </div>
</div>