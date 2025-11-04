@extends('admin.layouts.app')

@section('title', 'Kategori Düzenle')

@section('content')
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        @include('admin.categories._form', ['category' => $category, 'parents' => $parents])

        <div class="d-flex gap-2">
            <button class="btn btn-primary">Güncelle</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
        </div>
    </form>
@endsection
