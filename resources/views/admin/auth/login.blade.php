@extends('admin.layouts.app')

@section('title', 'Admin Giriş')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header text-center bg-dark text-white">
                    <h4>Admin Panel Giriş</h4>
                </div>
                <div class="card-body">
                    {{-- Hata ve status mesajları --}}
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Login Form --}}
                    <form method="POST" action="{{ route('admin.login.submit') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus>
                            @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required>
                            @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Beni Hatırla</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark">Giriş Yap</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">© {{ date('Y') }} Admin Panel</small>
                </div>
            </div>
        </div>
    </div>
@endsection
