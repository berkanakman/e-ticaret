@extends('admin.layouts.app')
@section('title', 'Ürün Düzenle')

@section('content')
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="product-edit-form">
        @csrf @method('PUT')

        {{-- Genel Bilgiler --}}
        <div class="card mb-4">
            <div class="card-header">Genel Bilgiler</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Ürün Adı</label>
                        <input type="text" name="name" value="{{ $product->name }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" name="slug" value="{{ $product->slug }}" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Açıklama</label>
                    <div id="quill-editor">{!! $product->description !!}</div>
                    <input type="hidden" name="description" id="description">
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="price" class="form-label">Fiyat</label>
                        <input type="number" name="price" value="{{ $product->price }}" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label for="stock" class="form-label">Stok</label>
                        <input type="number" name="stock" value="{{ $product->stock }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" name="sku" value="{{ $product->sku }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        @include('admin.products._form')

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4">Kaydet</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Geri</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var croppie = null;
        var croppieModal = $("#croppie-modal");
        var croppieContainer = $("#croppie-container");
        var imageUpload = $("#image_upload");
        var cropAndUpload = $("#crop-and-upload");
        var uploadedImages = $("#uploaded_images");
        var productId = {{ $product->id }};

        function setupCroppie(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if (croppie) {
                    croppie.destroy();
                }
                croppie = new Croppie(croppieContainer[0], {
                    viewport: { width: 400, height: 400 },
                    boundary: { width: 500, height: 500 },
                    enableExif: true
                });
                croppie.bind({
                    url: e.target.result
                });
                croppieModal.modal('show');
            }
            reader.readAsDataURL(file);
        }

        imageUpload.on("change", function() {
            if (this.files && this.files[0]) {
                setupCroppie(this.files[0]);
            }
        });

        cropAndUpload.on("click", function() {
            croppie.result('base64').then(function(base64) {
                croppieModal.modal('hide');
                $.ajax({
                    url: "{{ route('admin.products.uploadImage') }}",
                    type: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "image": base64,
                        "product_id": productId
                    },
                    success: function(response) {
                        if (response.success) {
                            renderImage(response.image);
                        }
                    }
                });
            });
        });

        function renderImage(image) {
            var html = `
                <div class="col-md-3" id="image-${image.id}">
                    <div class="card">
                        <img src="/storage/${image.path}" class="card-img-top">
                        <div class="card-body">
                            <button type="button" class="btn btn-danger btn-sm delete-image" data-id="${image.id}">Sil</button>
                        </div>
                    </div>
                </div>
            `;
            uploadedImages.append(html);
        }

        $(document).on('click', '.delete-image', function() {
            var imageId = $(this).data('id');
            $.ajax({
                url: `/admin/products/delete-image/${imageId}`,
                type: "DELETE",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        $(`#image-${imageId}`).remove();
                    }
                }
            });
        });

        // Initial load
        @foreach($product->images as $image)
            renderImage(@json($image));
        @endforeach
    });
</script>
<script src="{{ asset('js/admin/variants.js') }}"></script>
<script src="{{ asset('js/admin/product_features_variants.js') }}"></script>
@endpush
