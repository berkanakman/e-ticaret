@extends('admin.layouts.app')
@section('title', 'Ürün Ekle')

@section('content')
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-create-form">
        @csrf
        @include('admin.products._general')
        @include('admin.products._form')

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4">Kaydet</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Geri</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/product_features_variants.js') }}"></script>
@endpush