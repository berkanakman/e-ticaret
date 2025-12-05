<!doctype html>
<html lang="tr" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>@yield('title', 'Admin Panel')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap CSS (CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Quill CSS --}}
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

    {{-- Özel CSS (varsa) --}}
    <link rel="stylesheet" href="{{ asset('css/admin-custom.css') }}">

    {{-- Vite CSS (development veya production build) --}}
    @env('local')
        @vite(['resources/css/app.css'])
        @else
            {{-- production: yüklemek isterseniz build sonucu public/css/app.css kullanılır --}}
            <link rel="stylesheet" href="{{ asset('css/app.css') }}">
            @endenv

            @stack('head')
</head>
<body class="d-flex flex-column min-vh-100 admin-body">

@include('admin.layouts._navbar')

<div class="container-fluid">
    <div class="row">
        <aside class="col-12 col-md-3 col-xl-2 p-0 border-end bg-body-tertiary position-sticky" style="top:0; height:100vh; overflow:auto;">
            @include('admin.layouts._sidebar')
        </aside>

        <main class="col-12 col-md-9 col-xl-10 px-4 py-4">
            @yield('content')
        </main>
    </div>
</div>

@include('admin.layouts._footer')

{{-- JS Kütüphaneleri (CDN) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

{{-- Mevcut ortak admin scripti (panel.js) korunuyor --}}
<script src="{{ asset('js/admin/panel.js') }}"></script>

{{-- Vite dev server veya build JS yüklemesi (defer ile çakışmaları azalt) --}}
@env('local')
    @if (str_contains(app()->environmentFilePath() ? file_get_contents(app()->environmentFilePath()) : '', 'APP_ENV=local') || true)
        {{-- development: Vite client + bundle --}}
        @vite(['resources/js/admin/app.js'])
    @endif
    @else
        {{-- production fallback --}}
        <script src="{{ asset('js/admin/app.js') }}" defer></script>
        @endenv

        @stack('scripts')
</body>
</html>
