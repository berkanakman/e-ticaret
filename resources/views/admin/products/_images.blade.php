{{-- Image Upload Section --}}
<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3">Ürün Resimleri</h5>
        <div class="mb-3">
            <input type="file" id="image-upload-input" class="form-control" accept="image/*">
            <small class="form-text text-muted">Yüklemek için bir resim seçin. Resimler 800x800 piksel boyutuna kırpılacaktır.</small>
        </div>
        <div id="uploaded-images-container" class="mt-3 d-flex flex-wrap gap-2">
            {{-- Mevcut resimler buraya eklenecek (backend'den) --}}
            @if(isset($product) && $product->images)
                @foreach($product->images as $image)
                    <div class="uploaded-image-card" data-image-id="{{ $image->id }}">
                        <img src="{{ Storage::url($image->image_path) }}" alt="Ürün Resmi" class="img-thumbnail">
                        <button type="button" class="btn btn-danger btn-sm delete-image-btn">&times;</button>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

{{-- Croppie Modal --}}
<div class="modal fade" id="cropImageModal" tabindex="-1" aria-labelledby="cropImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropImageModalLabel">Resmi Kırp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="image-crop-container" style="width:100%; height:400px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="crop-and-upload-btn">Kırp ve Yükle</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .uploaded-image-card {
        position: relative;
        width: 150px;
        height: 150px;
    }
    .uploaded-image-card .img-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .uploaded-image-card .delete-image-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        line-height: 1;
    }
</style>
@endpush
