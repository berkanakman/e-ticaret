@extends('admin.layouts.app')

@section('title', 'Yeni İzin Oluştur')

@section('content')
    <form action="{{ route('admin.permissions.store') }}" method="POST" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label">İzin Adı</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Örnek: manage.products veya view.orders</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Açıklama (opsiyonel)</label>
            <input type="text" name="description" class="form-control" value="{{ old('description') }}">
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-success">Oluştur</button>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
        </div>
    </form>
@endsection
