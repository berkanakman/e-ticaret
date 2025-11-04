@extends('admin.layouts.app')

@section('title', 'Yeni Kategori Oluştur')

@section('content')
    <form action="{{ route('admin.categories.store') }}" method="POST" novalidate>
        @csrf

        @include('admin.categories._form', ['category' => null, 'parents' => $parents])

        <div class="d-flex gap-2">
            <button class="btn btn-success">Oluştur</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
        </div>
    </form>
@endsection
