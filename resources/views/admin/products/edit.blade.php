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
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Açıklama</label>
                    <div id="quill-editor">{!! old('description', $product->description) !!}</div>
                    <input type="hidden" name="description" id="description">
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="price" class="form-label">Fiyat</label>
                        <input type="number" name="price" value="{{ old('price', $product->price) }}" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label for="stock" class="form-label">Stok</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control">
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
    <script src="{{ asset('js/admin/vendors/Sortable.min.js') }}"></script>
    <script>
        // Quill Editor
        if (document.getElementById('quill-editor')) {
            var quill = new Quill('#quill-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{'list': 'ordered'}, {'list': 'bullet'}],
                        ['link', 'image', 'video'],
                        ['clean']
                    ]
                }
            });
            quill.on('text-change', function() {
                document.getElementById('description').value = quill.root.innerHTML;
            });
            // Set initial content
             document.getElementById('description').value = quill.root.innerHTML;
        }

    </script>
    <script src="{{ asset('js/admin/variants.js') }}"></script>
    <script>
        $(document).ready(function() {
            var croppie = null;
            var cropModal = new bootstrap.Modal(document.getElementById('cropImageModal'));
            var imageUploadInput = $('#image-upload-input');
            var imageCropContainer = $('#image-crop-container');
            var cropAndUploadBtn = $('#crop-and-upload-btn');
            var uploadedImagesContainer = $('#uploaded-images-container');
            var productId = '{{ $product->id }}';

            function initializeCroppie(imageUrl) {
                if (croppie) {
                    croppie.destroy();
                }
                croppie = new Croppie(imageCropContainer[0], {
                    viewport: { width: 400, height: 400, type: 'square' },
                    boundary: { width: '100%', height: 380 },
                    enableExif: true
                });
                croppie.bind({
                    url: imageUrl
                });
            }

            imageUploadInput.on('change', function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        initializeCroppie(e.target.result);
                        cropModal.show();
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            cropAndUploadBtn.on('click', function(e) {
                croppie.result({
                    type: 'canvas',
                    size: { width: 800, height: 800 },
                    format: 'png',
                    quality: 0.95
                }).then(function(base64Image) {
                    cropModal.hide();
                    cropAndUploadBtn.prop('disabled', true).text('Yükleniyor...');

                    $.ajax({
                        url: '{{ route("admin.products.uploadImage") }}',
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "image": base64Image,
                            "product_id": productId
                        },
                        success: function(response) {
                            if (response.success) {
                                var newImageHtml = `
                                    <div class="uploaded-image-card" data-image-id="${response.image.id}">
                                        <img src="${response.image.path}" alt="Ürün Resmi" class="img-thumbnail">
                                        <button type="button" class="btn btn-danger btn-sm delete-image-btn">&times;</button>
                                    </div>
                                `;
                                uploadedImagesContainer.append(newImageHtml);
                            } else {
                                alert(response.message || 'Bir hata oluştu.');
                            }
                        },
                        error: function() {
                            alert('Resim yüklenirken bir sunucu hatası oluştu.');
                        },
                        complete: function() {
                            cropAndUploadBtn.prop('disabled', false).text('Kırp ve Yükle');
                            imageUploadInput.val('');
                        }
                    });
                });
            });

            uploadedImagesContainer.on('click', '.delete-image-btn', function() {
                if (!confirm('Bu resmi silmek istediğinizden emin misiniz?')) return;
                var card = $(this).closest('.uploaded-image-card');
                var imageId = card.data('image-id');
                var deleteButton = $(this);
                    var url = '{{ route("admin.products.deleteImage", ["imageId" => ":id"]) }}'.replace(':id', imageId);

                deleteButton.prop('disabled', true);

                $.ajax({
                        url: url,
                    type: 'DELETE',
                    data: { "_token": "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            card.fadeOut(300, function() { $(this).remove(); });
                        } else {
                            alert(response.message || 'Resim silinirken bir hata oluştu.');
                            deleteButton.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        alert('Sunucu hatası: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Bilinmeyen hata.'));
                        deleteButton.prop('disabled', false);
                    }
                });
            });

            $('#cropImageModal').on('hidden.bs.modal', function () {
                if (croppie) {
                    croppie.destroy();
                    croppie = null;
                }
            });
        });
    </script>
@endpush
