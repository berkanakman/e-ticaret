@extends('admin.layouts.app')

@section('title', 'Rol Düzenle')

@section('content')
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Rol Adı</label>
            <input type="text" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $role->name) }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <fieldset class="mb-3">
            <legend class="small">İzinler</legend>

            @php
                $selected = old('permissions', $rolePermissionIds ?? []);
            @endphp

            <div class="d-flex justify-content-between mb-2">
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePermissions(true)">Tümünü Seç</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="togglePermissions(false)">Tümünü Kaldır</button>
                </div>
                <div class="text-muted small align-self-center">Toplam: {{ $permissions->count() }}</div>
            </div>

            <div class="row g-2">
                @foreach($permissions->chunk(6) as $chunk)
                    <div class="col-12 col-md-6 col-lg-4">
                        @foreach($chunk as $perm)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="permissions[]"
                                       id="perm_{{ $perm->id }}"
                                       value="{{ $perm->id }}"
                                    {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
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
            <button class="btn btn-primary">Güncelle</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
        </div>
    </form>

    <script>
        function togglePermissions(check) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = check);
        }
    </script>
@endsection
