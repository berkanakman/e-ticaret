@php
    $a = $attribute ?? null;
    $opts = old('options', $a?->options?->toArray() ?? []);
    $isColorDefault = ($a?->type ?? old('type')) === 'color';
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Ad</label>
        <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $a->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $a->slug ?? '') }}">
        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Tip</label>
        <select name="type" id="attribute-type" class="form-select">
            @foreach(['select'=>'Tek seçim','multiselect'=>'Çoklu','text'=>'Metin','color'=>'Renk','number'=>'Sayı','boolean'=>'Evet/Hayır'] as $k=>$v)
                <option value="{{ $k }}" {{ (string)old('type', $a->type ?? '') === (string)$k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2 d-flex align-items-center">
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="is_filterable" name="is_filterable" value="1" {{ old('is_filterable', $a->is_filterable ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_filterable">Filtre</label>
        </div>
    </div>

    <div class="col-md-2 d-flex align-items-center">
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $a->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Sıra</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $a->sort_order ?? 0) }}">
    </div>

    <div class="col-12">
        <hr>
        <h6>Seçenekler</h6>

        <div id="options-rows" class="list-group mb-2">
            @foreach($opts as $i => $opt)
                <div class="list-group-item option-row d-flex justify-content-between align-items-start" data-index="{{ $i }}" data-id="{{ $opt['id'] ?? '' }}">
                    <div class="w-100">
                        <input type="hidden" data-field="id" name="options[{{ $i }}][id]" value="{{ $opt['id'] ?? '' }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <input data-field="name" name="options[{{ $i }}][name]" class="form-control" placeholder="Ad" value="{{ $opt['name'] ?? '' }}" required>
                            </div>

                            <div class="col-md-3 d-flex align-items-center">
                                <input data-field="value" name="options[{{ $i }}][value]" class="form-control value-input" placeholder="Değer (örn #ff0000)" value="{{ $opt['value'] ?? '' }}">
                                @if($isColorDefault)
                                    <input type="color" class="form-control form-control-color ms-2 color-picker" value="{{ $opt['value'] ?? '#ff0000' }}">
                                @endif
                            </div>

                            <div class="col-md-2">
                                <input data-field="sort_order" name="options[{{ $i }}][sort_order]" type="number" class="form-control sort-order-input" value="{{ $opt['sort_order'] ?? $i }}">
                            </div>

                            <div class="col-md-1">
                                <input type="hidden" data-field="is_active" name="options[{{ $i }}][is_active]" value="0">
                                <div class="form-check">
                                    <input data-field="is_active" type="checkbox" name="options[{{ $i }}][is_active]" class="form-check-input" value="1" {{ (!isset($opt['is_active']) || $opt['is_active']) ? 'checked' : '' }}>
                                </div>
                            </div>

                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-danger btn-remove-option">Sil</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-2 d-flex gap-2 align-items-center">
            <button type="button" id="btn-add-option" class="btn btn-sm btn-outline-primary">Seçenek Ekle</button>
            <small class="text-muted">Sürükle bırak ile sırayı değiştirin</small>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('options-rows');
            const addBtn = document.getElementById('btn-add-option');
            const attributeTypeSelect = document.getElementById('attribute-type');

            function replaceOptionsIndex(name, newIndex) {
                const token = 'options[';
                const first = name.indexOf(token);
                if (first === -1) return name;
                const start = first + token.length;
                const end = name.indexOf(']', start);
                if (end === -1) return name;
                return name.slice(0, first) + token + newIndex + name.slice(end + 1);
            }

            function reindex() {
                const rows = Array.from(container.querySelectorAll('.option-row'));
                rows.forEach((row, i) => {
                    row.dataset.index = i;

                    // for all inputs/selects/textareas inside the row: rebuild name from data-field
                    row.querySelectorAll('input, select, textarea').forEach(el => {
                        const field = el.getAttribute('data-field');
                        if (!field) return; // if no data-field, leave as is
                        const newName = `options[${i}][${field}]`;
                        el.setAttribute('name', newName);
                    });

                    // ensure numeric sort_order input value equals index
                    const sortInput = row.querySelector('.sort-order-input') || row.querySelector('input[data-field="sort_order"]');
                    if (sortInput) sortInput.value = i;
                });
            }

            function attachColorToRow(row) {
                if (!row) return;
                const valueInput = row.querySelector('.value-input');
                if (!valueInput) return;
                if (row.querySelector('.color-picker')) return;
                const picker = document.createElement('input');
                picker.type = 'color';
                picker.className = 'form-control form-control-color ms-2 color-picker';
                picker.value = valueInput.value || '#ff0000';
                valueInput.after(picker);
                picker.addEventListener('input', function () { valueInput.value = picker.value; });
                valueInput.addEventListener('input', function () { try { picker.value = valueInput.value || '#ff0000'; } catch(e){} });
            }

            // Attach color pickers on load if attribute type is color
            const isColorOnLoad = (attributeTypeSelect && attributeTypeSelect.value === 'color') || @json($isColorDefault);
            if (isColorOnLoad) {
                container.querySelectorAll('.option-row').forEach(r => attachColorToRow(r));
            }

            // react to type change: toggle color pickers
            attributeTypeSelect?.addEventListener('change', function () {
                if (this.value === 'color') {
                    container.querySelectorAll('.option-row').forEach(r => attachColorToRow(r));
                } else {
                    container.querySelectorAll('.option-row .color-picker').forEach(p => p.remove());
                }
            });

            addBtn?.addEventListener('click', function () {
                const idx = container.querySelectorAll('.option-row').length;
                const div = document.createElement('div');
                div.className = 'list-group-item option-row d-flex justify-content-between align-items-start';
                div.dataset.index = idx;
                div.innerHTML = `
                    <div class="w-100">
                        <input data-field="id" type="hidden" name="options[${idx}][id]" value="">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5"><input data-field="name" name="options[${idx}][name]" class="form-control" placeholder="Ad" required></div>
                            <div class="col-md-3 d-flex align-items-center">
                                <input data-field="value" name="options[${idx}][value]" class="form-control value-input" placeholder="Değer (örn #ff0000)">
                            </div>
                            <div class="col-md-2"><input data-field="sort_order" name="options[${idx}][sort_order]" type="number" class="form-control sort-order-input" value="${idx}"></div>
                            <div class="col-md-1">
                                <input data-field="is_active" type="hidden" name="options[${idx}][is_active]" value="0">
                                <div class="form-check">
                                    <input data-field="is_active" type="checkbox" name="options[${idx}][is_active]" class="form-check-input" value="1" checked>
                                </div>
                            </div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-danger btn-remove-option">Sil</button></div>
                        </div>
                    </div>`;
                container.appendChild(div);

                // if attribute type is color, attach color picker to the new row
                const currentType = attributeTypeSelect ? attributeTypeSelect.value : @json($isColorDefault ? 'color' : '');
                if (currentType === 'color') {
                    attachColorToRow(div);
                }

                reindex();
            });

            container.addEventListener('click', function (e) {
                if (e.target.matches('.btn-remove-option')) {
                    const row = e.target.closest('.option-row');
                    if (row) { row.remove(); reindex(); }
                }
            });

            // Sortable
            Sortable.create(container, {
                animation: 150,
                // handle: '.drag-handle', // eğer özel bir handle kullanıyorsanız
                onEnd: function () {
                    reindex();
                }
            });

        });
    </script>
@endpush
