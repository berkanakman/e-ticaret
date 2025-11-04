@extends('admin.layouts.app')

@section('title', 'Yeni Rol Oluştur')

@section('content')
    <form action="{{ route('admin.roles.store') }}" method="POST" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label">Rol Adı</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <fieldset class="mb-3">
            <legend class="small">İzinler</legend>

            @php $oldPermissions = old('permissions', []); @endphp

            <div class="row g-2">
                @foreach($permissions->chunk(6) as $chunk)
                    <div class="col-12 col-md-6 col-lg-4">
                        @foreach($chunk as $perm)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="permissions[]"
                                       value="{{ $perm->id }}"
                                       id="perm_{{ $perm->id }}"
                                    {{ in_array($perm->id, $oldPermissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $perm->id }}">
                                    {{ $perm->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            @error('permissions') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            @error('permissions.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </fieldset>

        <div class="d-flex gap-2">
            <button class="btn btn-success">Oluştur</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
        </div>
    </form>
@endsection
