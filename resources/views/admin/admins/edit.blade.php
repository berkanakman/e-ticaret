@extends('admin.layouts.app')

@section('title', 'Admin Düzenle')

@section('content')
    <form action="{{ route('admin.admins.update', $admin) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text" name="username"
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username', $admin->username) }}">
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">E-posta</label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $admin->email) }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Şifre (boş bırakırsanız değişmez)</label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Şifre (Tekrar)</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Roller</label>
                <select name="roles[]" class="form-select" multiple>
                    @foreach($roles as $id => $name)
                        <option value="{{ $id }}" {{ in_array($id, old('roles', $adminRoleIds)) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                @error('roles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('roles.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                <div class="form-text">Birden fazla seçmek için Ctrl/Cmd tuşunu kullanın.</div>
            </div>

            <div class="col-md-3 d-flex align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $admin->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Aktif</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Güncelle</button>
                <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            </div>
        </div>
    </form>
@endsection
