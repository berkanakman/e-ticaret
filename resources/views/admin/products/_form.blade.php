{{-- resources/views/admin/products/_form.blade.php --}}

{{-- Product Images --}}
<div class="card mb-4">
    <div class="card-header">Ürün Resimleri</div>
    <div class="card-body">
        <div class="mb-3">
            <label for="images" class="form-label">Yeni Resimler Yükle</label>
            <input type="file" name="images[]" id="images" class="form-control" multiple>
        </div>

        @if(isset($product) && $product->images->count() > 0)
            <div class="row">
                @foreach($product->images as $image)
                    <div class="col-md-3">
                        <div class="card">
                            <img src="{{ asset('storage/' . $image->path) }}" class="card-img-top" alt="{{ $image->name }}">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="delete_images[]" value="{{ $image->id }}" id="delete_image_{{ $image->id }}">
                                    <label class="form-check-label" for="delete_image_{{ $image->id }}">
                                        Sil
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>


@include('admin.products._attributes')
@include('admin.products._variants')
@include('admin.products._manual_variant_modal')
