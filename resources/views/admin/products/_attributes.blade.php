<div class="mb-3">
    <label for="attributes" class="form-label">Özellik Seç</label>
    <select id="attributes" class="form-control" multiple name="attributes[]">
        @foreach($allAttributes as $attr)
            <option value="{{ $attr->id }}"
                    {{ in_array($attr->id, $selectedAttributes) ? 'selected' : '' }}>
                {{ $attr->name }}
            </option>
        @endforeach
    </select>
</div>

<div id="attribute-options-container">
    @foreach($selectedAttributes as $attrId)
        @php $options = $attributeOptions[$attrId] ?? []; @endphp
        @if($options)
            <div class="mb-2 attribute-group" data-attr-id="{{ $attrId }}">
                <strong>{{ $attributesMap[$attrId] ?? 'Özellik' }}</strong><br>
                @foreach($options as $opt)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox"
                               name="attribute_values[{{ $attrId }}][]" value="{{ $opt['id'] }}"
                                {{ in_array($opt['id'], $productValues[$attrId] ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $opt['value'] }}</label>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
</div>
