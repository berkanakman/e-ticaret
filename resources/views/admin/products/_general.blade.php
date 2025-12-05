<div class="card mb-4">
    <div class="card-header">Genel Bilgiler</div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Ürün Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}"
                    class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug ?? '') }}"
                    class="form-control" placeholder="Otomatik oluşturulur">
            </div>
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Kategori</label>
            <select name="category_id" id="category_id" class="form-select">
                <option value="">Seçiniz...</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (old('category_id', $product->category_id ?? '') == $cat->id) ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Açıklama</label>
            {{-- Quill editor container --}}
            <div id="editor" style="height: 200px;">{!! old('description', $product->description ?? '') !!}</div>
            <input type="hidden" name="description" id="description"
                value="{{ old('description', $product->description ?? '') }}">
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="price" class="form-label">Fiyat</label>
                <input type="number" name="price" id="price" value="{{ old('price', $product->price ?? '') }}"
                    class="form-control" step="0.01">
            </div>
            <div class="col-md-4">
                <label for="stock" class="form-label">Stok</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock ?? 0) }}"
                    class="form-control">
            </div>
            <div class="col-md-4">
                <label for="sku" class="form-label">SKU</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku ?? '') }}"
                    class="form-control">
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Aktif mi?</label>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Quill Editor Setup
            if (document.getElementById('editor')) {
                var quill = new Quill('#editor', {
                    theme: 'snow'
                });
                var form = document.querySelector('form');
                form.onsubmit = function () {
                    var description = document.querySelector('input[name=description]');
                    description.value = quill.root.innerHTML;
                };
            }
        });
    </script>
@endpush