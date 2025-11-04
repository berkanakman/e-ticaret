@extends('admin.layouts.app')
@section('title', 'Ürünler')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h1>Ürünler</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Yeni Ürün</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Adı</th>
            <th>Fiyat</th>
            <th>Stok</th>
            <th>Durum</th>
            <th>İşlemler</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->price }}</td>
                <td>{{ $product->stock }}</td>
                <td>{{ $product->is_active ? 'Aktif' : 'Pasif' }}</td>
                <td>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning">Düzenle</a>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
