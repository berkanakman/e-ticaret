@extends('admin.layouts.app')

@section('title', 'İzinler')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5>İzinler</h5>
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">Yeni İzin</a>
    </div>

    <form method="GET" action="{{ route('admin.permissions.index') }}" class="mb-3">
        <div class="input-group">
            <input type="search" name="q" class="form-control" placeholder="İzin ara..." value="{{ request('q') }}">
            <button class="btn btn-outline-secondary">Ara</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>İsim</th>
                <th>Guard</th>
                <th>Oluşturulma</th>
                <th class="text-end">İşlemler</th>
            </tr>
            </thead>
            <tbody>
            @forelse($permissions as $perm)
                <tr>
                    <td>{{ $perm->id }}</td>
                    <td>{{ $perm->name }}</td>
                    <td>{{ $perm->guard_name }}</td>
                    <td>{{ optional($perm->created_at)->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.permissions.edit', $perm) }}" class="btn btn-sm btn-warning">Düzenle</a>

                        <form action="{{ route('admin.permissions.destroy', $perm) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Bu izini silmek istediğinize emin misiniz?')">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Kayıt yok</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $permissions->withQueryString()->links() }}</div>
@endsection
