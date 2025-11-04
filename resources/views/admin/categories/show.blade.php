@extends('admin.layouts.app')

@section('title', 'Kategori: ' . $category->name)

@section('content')
    <div class="mb-3 d-flex justify-content-between">
        <div>
            <h5>{{ $category->name }}</h5>
            <div class="text-muted small">Slug: {{ $category->slug }} · Sıra: {{ $category->sort_order }} · Aktif: {{ $category->is_active ? 'Evet' : 'Hayır' }}</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-warning">Ana Kategoriyi Düzenle</a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">Ana Kategorilere Dön</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Alt Kategoriler ({{ $children->count() }})</span>
                    <small class="text-muted">Sürükle bırak ile sırayı değiştirin</small>
                </div>

                <div class="card-body p-2">
                    @if($children->isEmpty())
                        <div class="text-center text-muted py-3">Bu ana kategorinin alt kategorisi yok.</div>
                    @else
                        <div id="children-list" class="list-group">
                            @foreach($children as $child)
                                <div class="list-group-item d-flex justify-content-between align-items-center" data-id="{{ $child->id }}">
                                    <div>
                                        <strong>{{ $child->name }}</strong>
                                        <div class="small text-muted">Slug: {{ $child->slug }} · Sıra: <span class="js-sort-order">{{ $child->sort_order }}</span></div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.categories.edit', $child) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>

                                        <form action="{{ route('admin.categories.destroy', $child) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Alt kategoriyi silmek istediğinize emin misiniz?')">Sil</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Yeni Alt Kategori Oluştur</div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.store') }}" method="POST" novalidate>
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $category->id }}">

                        <div class="mb-3">
                            <label class="form-label">Ad</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug (opsiyonel)</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active_child" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active_child">Aktif</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-success">Alt Kategori Oluştur</button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Geri</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('children-list');
        if (!list) return;

        // CSRF token
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const sortable = Sortable.create(list, {
            animation: 150,
            handle: '.list-group-item', // tüm item draggablе
            onEnd: function (evt) {
                // build order array of ids in current order
                const ids = Array.from(list.querySelectorAll('[data-id]')).map(el => parseInt(el.getAttribute('data-id')));
                // optimistic UI: update visible sort_order spans
                ids.forEach((id, idx) => {
                    const el = list.querySelector('[data-id="'+id+'"] .js-sort-order');
                    if (el) el.textContent = idx;
                });

                // send AJAX
                fetch('{{ route('admin.categories.children.reorder', $category) }}', {
                    method: 'POST',
                    credentials: 'same-origin', // çerezlerin gönderilmesi için çok önemli
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
                        // success feedback (toast veya küçük mesaj)
                        // örnek: kısa yeşil çerçeve animasyonu
                        list.classList.add('border-success');
                        setTimeout(() => list.classList.remove('border-success'), 800);
                    })
                    .catch(async (err) => {
                        // revert UI (basit yol: reload)
                        console.error('Sıralama güncellenemedi', err);
                        // tercihen server hatası mesajı göster
                        alert('Sıralama sunucuya kaydedilirken hata oluştu. Sayfa yenilenecek.');
                        window.location.reload();
                    });
            }
        });
    });
</script>
