@php
    $allAttributes = $allAttributes ?? (\App\Models\ProductAttribute::where('is_active', true)->with('options')->orderBy('sort_order')->get() ?? collect());

    // saved values for edit mode: attribute_id => array(of option ids OR raw values)
    $savedMap = [];
    if (!empty($product)) {
        $product->load('attributeValues.option');
        foreach ($product->attributeValues as $av) {
            if ($av->option_id)
                $savedMap[$av->attribute_id][] = $av->option_id;
            else
                $savedMap[$av->attribute_id][] = $av->value;
        }
    }

    // prepare JS map
    $allForJs = [];
    foreach ($allAttributes as $a) {
        $opts = [];
        foreach ($a->options ?? [] as $o) {
            $opts[] = ['id' => $o->id, 'name' => $o->name, 'value' => $o->value];
        }
        $allForJs[$a->id] = ['id' => $a->id, 'name' => $a->name, 'type' => $a->type, 'options' => $opts];
    }
@endphp

<div class="mb-3">
    <label class="form-label">Kullanılacak Özellikler</label>
    <select id="attributes-select" class="form-select" multiple>
        @foreach($allAttributes as $attr)
            <option value="{{ $attr->id }}" data-type="{{ $attr->type }}" {{ isset($savedMap[$attr->id]) ? 'selected' : '' }}>
                {{ $attr->name }}
            </option>
        @endforeach
    </select>
    <div class="form-text small text-muted">Bir veya daha fazla özellik seçin; seçilen her özellik için alttan
        seçenekler checkbox olarak görünecektir.</div>
</div>

<div id="selected-attributes-list">
    {{-- Server-side render of existing product attributes (edit) --}}
    @foreach($allAttributes as $attr)
        @if(isset($savedMap[$attr->id]))
            <div class="mb-3 attr-block" data-attr-id="{{ $attr->id }}" data-attr-type="{{ $attr->type }}">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label mb-0">{{ $attr->name }}</label>
                </div>

                <div class="options-list">
                    @if(in_array($attr->type, ['select', 'multiselect', 'color']))
                        @foreach($attr->options as $opt)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input attr-option-checkbox" type="checkbox"
                                    name="attributes[{{ $attr->id }}][]" id="attr_{{ $attr->id }}_opt_{{ $opt->id }}"
                                    value="{{ $opt->id }}" {{ in_array($opt->id, $savedMap[$attr->id]) ? 'checked' : '' }}>
                                <label class="form-check-label" for="attr_{{ $attr->id }}_opt_{{ $opt->id }}">
                                    @if($attr->type === 'color')
                                        <span
                                            style="display:inline-block;width:18px;height:18px;border-radius:3px;background:{{ $opt->value ?? '#000' }};border:1px solid #ddd;margin-right:6px;vertical-align:middle"></span>
                                    @endif
                                    {{ $opt->name }}
                                </label>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif
    @endforeach
</div>

@push('scripts')
    <script>
        // Initialize global maps for the external JS file
        window.ATTRIBUTES_MAP = window.ATTRIBUTES_MAP || @json($allForJs);
        window.SAVED_MAP = window.SAVED_MAP || @json($savedMap ?? []);
    </script>
@endpush