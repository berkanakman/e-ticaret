<div id="variants-container">
    <button type="button" class="btn btn-sm btn-primary mb-2" id="generate-variants">Otomatik Oluştur</button>
    <button type="button" class="btn btn-sm btn-secondary mb-2" data-bs-toggle="modal" data-bs-target="#manualVariantModal">Manuel Varyant Ekle</button>

    <table class="table table-bordered mt-3" id="variant-list">
        <thead>
        <tr>
            <th>SKU</th>
            <th>Fiyat</th>
            <th>Stok</th>
            <th>Özellikler</th>
            <th>Sil</th>
        </tr>
        </thead>
        <tbody>
        @foreach($product->variants as $variant)
            <tr>
                <td><input type="text" name="variants[{{ $loop->index }}][sku]" value="{{ $variant->sku ?? '' }}" class="form-control"></td>
                <td><input type="number" name="variants[{{ $loop->index }}][price]" value="{{ $variant->price ?? '' }}" class="form-control" step="0.01"></td>
                <td><input type="number" name="variants[{{ $loop->index }}][stock]" value="{{ $variant->stock ?? '' }}" class="form-control"></td>
                <td>
                    @php
                        print_r($variant->values);
                    @endphp
                    @foreach($variant->values as $val)
                        <span class="badge bg-secondary">
                            {{ $val->attribute->name ?? 'Özellik' }}: {{ $val->option->name ?? $val->option->value ?? '' }}
                        </span>
                        <input type="hidden" name="variants[{{ $loop->parent->index }}][values][{{ $val->attribute_id ?? '' }}]" value="{{ $val->option_id ?? '' }}">
                    @endforeach
                </td>
                <td><button type="button" class="btn btn-sm btn-danger remove-variant">Sil</button></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
