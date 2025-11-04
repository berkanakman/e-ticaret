@extends('admin.layouts.app')
@section('title','Yeni Özellik')

@section('content')
    <form action="{{ route('admin.attributes.store') }}" method="POST" class="mb-4">
        @csrf
        @include('admin.attributes._form', ['attribute'=>null])
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-success">Oluştur</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.attributes.index') }}">İptal</a>
        </div>
    </form>
@endsection
