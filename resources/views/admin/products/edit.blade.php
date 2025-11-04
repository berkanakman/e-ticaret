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

@section('scripts')
    @push('scripts')
        <script>
            window.attributeOptions = @json($attributeOptions ?? []);
            window.productValues = @json($productValues ?? []);
            window.selectedAttributes = @json($selectedAttributes ?? []);
            window.variantsWithValues = @json($variantsWithValues ?? []);
            window.attributesMap = @json($attributesMap ?? []);
            
            // Debug için
            console.log('Debug bilgileri:');
            console.log('attributeOptions:', window.attributeOptions);
            console.log('productValues:', window.productValues);
            console.log('selectedAttributes:', window.selectedAttributes);
            console.log('variantsWithValues:', window.variantsWithValues);
        </script>
        <script src="{{ asset('js/admin/product_features_variants.js') }}"></script>
    @endpush
@endsection


