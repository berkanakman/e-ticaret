<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="{{ route('admin.dashboard') }}">Admin</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Menüyü aç/kapat">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {{-- Global quick links --}}
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                @auth('admin')
                    @if(auth('admin')->user()->hasRole('superadmin'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.admins.index') }}">Adminler</a></li>
                    @endif
                @endauth
            </ul>

            {{-- Right side --}}
            <ul class="navbar-nav">
                @auth('admin')
                    <li class="nav-item d-flex align-items-center me-2">
                        <span class="navbar-text text-white-50">Hoş geldin, {{ auth('admin')->user()->username }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="btn btn-outline-light btn-sm" type="submit">Çıkış</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="{{ route('admin.login') }}">Giriş</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
