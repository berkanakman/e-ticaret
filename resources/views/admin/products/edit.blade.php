@extends('admin.layouts.app')
@section('title', 'Ürün Düzenle')

@section('content')
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data"
        id="product-edit-form">
        @csrf @method('PUT')

        @include('admin.products._general')

        @include('admin.products._form')

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4">Kaydet</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Geri</a>
        </div>
    </form>
@endsection

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