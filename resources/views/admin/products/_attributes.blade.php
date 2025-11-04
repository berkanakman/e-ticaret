@php
    $allAttributes = $allAttributes ?? (\App\Models\ProductAttribute::where('is_active', true)->with('options')->orderBy('sort_order')->get() ?? collect());
    $savedMap = [];
    if(!empty($product)) {
        $product->load('attributeValues.option');
        foreach($product->attributeValues as $av) {
            if ($av->option_id) $savedMap[$av->attribute_id][] = $av->option_id;
            else $savedMap[$av->attribute_id][] = $av->value;
        }
    }

    $allForJs = [];
    foreach ($allAttributes as $a) {
        $opts = [];
        foreach ($a->options ?? [] as $o) {
            $opts[] = ['id'=>$o->id,'name'=>$o->name,'value'=>$o->value];
        }
        $allForJs[$a->id] = ['id'=>$a->id,'name'=>$a->name,'type'=>$a->type,'options'=>$opts];
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
    <div class="form-text small text-muted">Bir veya daha fazla özellik seçin; seçilen her özellik için alttan seçenekler checkbox olarak görünecektir.</div>
</div>

<div id="selected-attributes-list">
    {{-- Server-side render mevcutsa bunu koruruz (edit modunda) --}}
    @foreach($allAttributes as $attr)
        @if(isset($savedMap[$attr->id]))
            <div class="mb-3 attr-block" data-attr-id="{{ $attr->id }}" data-attr-type="{{ $attr->type }}">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label mb-0">{{ $attr->name }}</label>
                </div>
                <div class="options-list">
                    @if(in_array($attr->type, ['select','multiselect','color']))
                        @foreach($attr->options as $opt)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input attr-option-checkbox" type="checkbox" name="attributes[{{ $attr->id }}][]" id="attr_{{ $attr->id }}_opt_{{ $opt->id }}" value="{{ $opt->id }}"
                                    {{ in_array($opt->id, $savedMap[$attr->id]) ? 'checked' : '' }}>
                                <label class="form-check-label" for="attr_{{ $attr->id }}_opt_{{ $opt->id }}">
                                    @if($attr->type === 'color')
                                        <span style="display:inline-block;width:18px;height:18px;border-radius:3px;background:{{ $opt->value ?? '#000' }};border:1px solid #ddd;margin-right:6px;vertical-align:middle"></span>
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
    {{-- Eğer select2 kullanmak isterseniz CDN yüklemesi layout veya sayfa tarafından yapılmış olmalı --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.ATTRIBUTES_MAP = window.ATTRIBUTES_MAP || @json($allForJs);

            const sel = document.getElementById('attributes-select');
            if (!sel) return;

            // init Select2 if available (keeps server-side selected visible)
            try {
                if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
                    setTimeout(() => {
                        $('#attributes-select').select2({ placeholder: 'Özellik seçin', width: '100%' });
                        $('#attributes-select').trigger('change.select2');
                    }, 20);
                }
            } catch (e) { console.warn('select2 init failed', e); }

            // render selected attributes' options as checkbox blocks
            function renderSelectedAttributes(selectedIds) {
                const container = document.getElementById('selected-attributes-list');
                if (!container) return;

                // preserve checked values
                const preserve = {};
                container.querySelectorAll('.attr-block').forEach(b => {
                    const aid = b.dataset.attrId;
                    const checked = Array.from(b.querySelectorAll('.attr-option-checkbox:checked')).map(i => i.value);
                    if (checked.length) preserve[aid] = new Set(checked);
                });

                container.innerHTML = '';

                selectedIds.forEach(id => {
                    const attr = (window.ATTRIBUTES_MAP || {})[String(id)];
                    if (!attr) return;

                    const block = document.createElement('div');
                    block.className = 'mb-3 attr-block';
                    block.dataset.attrId = attr.id;
                    block.dataset.attrType = attr.type;

                    const header = document.createElement('div');
                    header.className = 'd-flex align-items-center justify-content-between mb-1';
                    header.innerHTML = `<label class="form-label mb-0">${attr.name}</label>`;

                    const optionsList = document.createElement('div');
                    optionsList.className = 'options-list';

                    (attr.options || []).forEach(opt => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'form-check form-check-inline';

                        const input = document.createElement('input');
                        input.className = 'form-check-input attr-option-checkbox';
                        input.type = 'checkbox';
                        input.name = `attributes[${attr.id}][]`;
                        input.id = `attr_${attr.id}_opt_${opt.id}`;
                        input.value = opt.id;
                        if (preserve[String(attr.id)] && preserve[String(attr.id)].has(String(opt.id))) {
                            input.checked = true;
                        }

                        const label = document.createElement('label');
                        label.className = 'form-check-label';
                        label.htmlFor = input.id;

                        if (attr.type === 'color') {
                            const span = document.createElement('span');
                            span.style.cssText = `display:inline-block;width:18px;height:18px;border-radius:3px;background:${opt.value||'#000'};border:1px solid #ddd;margin-right:6px;vertical-align:middle`;
                            label.appendChild(span);
                        }

                        label.appendChild(document.createTextNode(opt.name));
                        wrapper.appendChild(input);
                        wrapper.appendChild(label);
                        optionsList.appendChild(wrapper);
                    });

                    block.appendChild(header);
                    block.appendChild(optionsList);
                    container.appendChild(block);
                });
            }

            // initial render if server didn't provide blocks
            if (!document.querySelectorAll('.attr-block').length) {
                const initial = Array.from(sel.selectedOptions).map(o => o.value);
                if (initial.length) renderSelectedAttributes(initial);
            }

            // respond to selection changes
            sel.addEventListener('change', function () {
                const selected = Array.from(sel.selectedOptions).map(o => o.value);
                renderSelectedAttributes(selected);
            });
        });
    </script>
@endpush
