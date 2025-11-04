@extends('admin.layouts.app')
@section('title','Özellikler')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="mb-0">Özellikler</h5>
        <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">Yeni Özellik</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="row g-2">
            <div class="col-auto">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Ara...">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Ara</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Ad</th>
                <th>Slug</th>
                <th>Tip</th>
                <th>Seçenek</th>
                <th>Sıra</th>
                <th class="text-end">İşlemler</th>
            </tr>
            </thead>
            <tbody>
            @forelse($attributes as $attr)
                <tr>
                    <td>{{ $attr->id }}</td>
                    <td><a href="{{ route('admin.attributes.edit', $attr) }}">{{ $attr->name }}</a></td>
                    <td>{{ $attr->slug }}</td>
                    <td>{{ $attr->type }}</td>
                    <td>{{ $attr->options_count }}</td>
                    <td>{{ $attr->sort_order }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.attributes.edit', $attr) }}" class="btn btn-sm btn-warning">Düzenle</a>
                        <form action="{{ route('admin.attributes.destroy', $attr) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Silinsin mi?')">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Kayıt yok</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $attributes->withQueryString()->links() }}
    </div>
@endsection
