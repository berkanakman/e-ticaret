@extends('admin.layouts.app')

@section('title', 'İzin Düzenle')

@section('content')
    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">İzin Adı</label>
            <input type="text" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $permission->name) }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Örnek: manage.products veya view.orders</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Açıklama (opsiyonel)</label>
            <input type="text" name="description" class="form-control" value="{{ old('description', $permission->description ?? '') }}">
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">Güncelle</button>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
        </div>
    </form>
@endsection
