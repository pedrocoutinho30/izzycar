<form id="form-equipment" autocomplete="off">
    <div class="v3-tab-errors" style="display:none"></div>

    @if($attributes->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-star fs-1 d-block mb-2"></i>
            Nenhum atributo configurado.<br>
            <a href="{{ route('admin.v3.vehicles.index') }}" class="small">Ver Viaturas</a>
        </div>
    @else
        <div class="row g-3">
            @foreach($attributes as $groupName => $groupAttributes)

                {{-- Group header --}}
                <div class="col-12 {{ !$loop->first ? 'mt-2' : '' }}">
                    <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;letter-spacing:.06em">
                        <i class="bi bi-folder2-open me-1"></i>{{ $groupName ?: 'Geral' }}
                    </h6>
                    <hr class="mt-1 mb-0">
                </div>

                @foreach($groupAttributes as $attr)
                    @php
                        $fieldName     = 'attributes[' . $attr->id . ']';
                        $existingValue = $attributeValues[$attr->id]->value ?? null;
                    @endphp

                    <div class="col-md-4 col-sm-6">
                        @if($attr->type === 'boolean')
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       id="attr_{{ $attr->id }}"
                                       name="{{ $fieldName }}"
                                       value="1"
                                       {{ $existingValue ? 'checked' : '' }}>
                                <label class="form-check-label" for="attr_{{ $attr->id }}">
                                    {{ $attr->name }}
                                </label>
                            </div>

                        @elseif($attr->type === 'select')
                            <label for="attr_{{ $attr->id }}" class="form-label fw-semibold small">{{ $attr->name }}</label>
                            <select name="{{ $fieldName }}" id="attr_{{ $attr->id }}" class="form-select form-select-sm">
                                <option value="">—</option>
                                @foreach($attr->options ?? [] as $option)
                                    <option value="{{ $option }}" {{ $existingValue === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>

                        @else
                            <label for="attr_{{ $attr->id }}" class="form-label fw-semibold small">{{ $attr->name }}</label>
                            <input type="{{ $attr->type }}" name="{{ $fieldName }}"
                                   id="attr_{{ $attr->id }}"
                                   class="form-control form-control-sm"
                                   value="{{ $existingValue }}">
                        @endif
                    </div>
                @endforeach

            @endforeach

            {{-- Save button --}}
            <div class="col-12 d-flex gap-2 pt-3">
                <button type="button" class="btn btn-primary" onclick="v3SaveTab('equipment')">
                    <i class="bi bi-check-lg me-1"></i> Guardar Equipamento
                </button>
            </div>
        </div>
    @endif
</form>
