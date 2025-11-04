@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Genel Bakış')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
    <div class="row g-3">
        {{-- İstatistik kartları --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold">Kullanıcılar</h6>
                    <p class="display-6">{{ $stats['total_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold">Yöneticiler</h6>
                    <p class="display-6">{{ $stats['total_admins'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold">Ürünler</h6>
                    <p class="display-6">{{ $stats['total_products'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold">Siparişler</h6>
                    <p class="display-6">{{ $stats['total_orders'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Son kullanıcılar --}}
    <div class="mt-4">
        <h5>Son Kullanıcılar</h5>
        <ul class="list-group">
            @foreach($latestUsers as $user)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $user->name }} ({{ $user->email }})</span>
                    <small class="text-muted">{{ $user->created_at->format('d.m.Y H:i') }}</small>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Son siparişler --}}
    <div class="mt-4">
        <h5>Son Siparişler</h5>
        <ul class="list-group">
            @foreach($latestOrders as $order)
                <li class="list-group-item d-flex justify-content-between">
                    <span>#{{ $order->id }} - {{ $order->user->name }}</span>
                    <small class="text-muted">{{ $order->created_at->format('d.m.Y H:i') }}</small>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
