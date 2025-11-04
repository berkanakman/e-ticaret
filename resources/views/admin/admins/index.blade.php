@extends('admin.layouts.app')

@section('title', 'Admin Kullanıcıları')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">Yeni Admin Oluştur</a>

        <form method="GET" action="{{ route('admin.admins.index') }}" class="d-flex">
            <input type="search" name="q" class="form-control form-control-sm me-2" value="{{ request('q') }}" placeholder="Ara...">
            <button class="btn btn-sm btn-outline-secondary">Ara</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Kullanıcı Adı</th>
                <th>Email</th>
                <th>Aktif</th>
                <th>Roller</th>
                <th>Oluşturulma</th>
                <th class="text-end">İşlemler</th>
            </tr>
            </thead>
            <tbody>
            @forelse($admins as $admin)
                <tr>
                    <td>{{ $admin->id }}</td>
                    <td>{{ $admin->username }}</td>
                    <td>{{ $admin->email }}</td>
                    <td>
                        @if($admin->is_active)
                            <span class="badge bg-success">Evet</span>
                        @else
                            <span class="badge bg-secondary">Hayır</span>
                        @endif
                    </td>
                    <td>
                        @foreach($admin->roles as $role)
                            <span class="badge bg-info text-dark">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>{{ $admin->created_at->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-warning">Düzenle</a>

                        <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Bu admini silmek istediğinize emin misiniz?')">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Kayıt bulunamadı.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $admins->withQueryString()->links() }}
    </div>
@endsection
