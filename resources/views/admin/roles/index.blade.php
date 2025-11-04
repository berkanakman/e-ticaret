@extends('admin.layouts.app')

@section('title', 'Roller')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5>Roller</h5>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Yeni Rol</a>
    </div>

    <form method="GET" action="{{ route('admin.roles.index') }}" class="mb-3">
        <div class="input-group">
            <input type="search" name="q" class="form-control" placeholder="Rol ara..." value="{{ request('q') }}">
            <button class="btn btn-outline-secondary">Ara</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Ad</th>
                <th>Guard</th>
                <th>İzin Sayısı</th>
                <th class="text-end">İşlemler</th>
            </tr>
            </thead>
            <tbody>
            @forelse($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->guard_name }}</td>
                    <td>{{ $role->permissions()->count() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-warning">Düzenle</a>

                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Bu rolü silmek istediğinize emin misiniz?')">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Kayıt yok</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $roles->withQueryString()->links() }}</div>
@endsection
