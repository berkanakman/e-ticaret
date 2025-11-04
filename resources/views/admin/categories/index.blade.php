@extends('admin.layouts.app')

@section('title', 'Kategoriler')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Yeni Ana Kategori</a>
        </div>

        <form method="GET" action="{{ route('admin.categories.index') }}" class="d-flex">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm me-2" placeholder="Ara...">
            <button class="btn btn-sm btn-outline-secondary">Ara</button>
        </form>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Ana Kategoriler (Sürükle bırakarak sırala)</span>
            <small class="text-muted">Değişiklikler otomatik kaydedilir</small>
        </div>

        <div class="card-body p-0">
            <div id="top-level-list" class="list-group">
                @foreach($categories as $cat)
                    <div class="list-group-item d-flex justify-content-between align-items-center" data-id="{{ $cat->id }}">
                        <div>
                            <a href="{{ route('admin.categories.show', $cat) }}"><strong>{{ $cat->name }}</strong></a>
                            <div class="small text-muted">Slug: {{ $cat->slug }} · Sıra: <span class="js-sort-order">{{ $cat->sort_order }}</span></div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>

                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Kategoriyi silmek istediğinize emin misiniz?')">Sil</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $categories->withQueryString()->links() }}</div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('top-level-list');
        if (!list) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const sortable = Sortable.create(list, {
            animation: 150,
            handle: '.list-group-item',
            onEnd: function () {
                const ids = Array.from(list.querySelectorAll('[data-id]')).map(el => parseInt(el.getAttribute('data-id')));
                ids.forEach((id, idx) => {
                    const el = list.querySelector('[data-id="'+id+'"] .js-sort-order');
                    if (el) el.textContent = idx;
                });

                fetch('{{ route('admin.categories.reorder') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ order: ids })
                })
                    .then(resp => {
                        if (!resp.ok) throw resp;
                        return resp.json();
                    })
                    .then(data => {
                        list.classList.add('border-success');
                        setTimeout(() => list.classList.remove('border-success'), 800);
                    })
                    .catch(err => {
                        console.error('Sıralama güncellenemedi', err);
                        alert('Sıralama sunucuya kaydedilirken hata oluştu. Sayfayı yenileyin.');
                        window.location.reload();
                    });
            }
        });
    });
</script>
