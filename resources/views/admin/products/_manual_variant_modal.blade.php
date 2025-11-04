<div class="modal fade" id="manualVariantModal" tabindex="-1" aria-labelledby="manualVariantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manualVariantModalLabel">Manuel Varyant Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="manual-attribute" class="form-label">Özellik Seç</label>
                    <select id="manual-attribute" class="form-control">
                        <option value="">Seçiniz</option>
                        @foreach($attributes as $attr)
                            <option value="{{ $attr->id }}">{{ $attr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="manual-attribute-options-container"></div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="manual-sku" class="form-label">SKU</label>
                        <input type="text" id="manual-sku" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="manual-price" class="form-label">Fiyat</label>
                        <input type="number" id="manual-price" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label for="manual-stock" class="form-label">Stok</label>
                        <input type="number" id="manual-stock" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="add-manual-variant">Ekle</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>
