@extends('admin.layouts.app')
@section('title','Özellik Düzenle')

@section('content')
    <form action="{{ route('admin.attributes.update', $attribute) }}" method="POST" class="mb-4">
        @csrf @method('PUT')
        @include('admin.attributes._form', ['attribute'=>$attribute])
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Güncelle</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.attributes.index') }}">Geri</a>
        </div>
    </form>
@endsection
