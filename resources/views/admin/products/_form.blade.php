{{-- resources/views/admin/products/_form.blade.php --}}

@if(isset($product))
    {{-- Product Images --}}
    <div class="card mb-4">
        <div class="card-header">Ürün Resimleri</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="images" class="form-label">Yeni Resimler Yükle</label>
                <input type="file" id="image_upload" class="form-control" accept="image/*">
            </div>

            <div id="uploaded_images" class="row">
                {{-- Existing images will be loaded here --}}
            </div>
        </div>
    </div>

    {{-- Croppie Modal --}}
    <div class="modal fade" id="croppie-modal" tabindex="-1" aria-labelledby="croppie-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="croppie-modal-label">Resmi Kırp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="croppie-container"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" id="crop-and-upload" class="btn btn-primary">Kırp ve Yükle</button>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card mb-4">
        <div class="card-header">Ürün Resimleri</div>
        <div class="card-body">
            <p class="text-muted">Ürünü kaydettikten sonra resim ekleyebilirsiniz.</p>
        </div>
    </div>
@endif


@include('admin.products._attributes')
@include('admin.products._variants')
@include('admin.products._manual_variant_modal')
