@php
    $c = $category ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Ad</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $c->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Slug (opsiyonel)</label>
        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $c->slug ?? '') }}">
        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">Boş bırakırsanız sistem adı slug haline getirir.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Üst Kategori</label>
        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
            <option value="">— Yok —</option>
            @foreach($parents as $p)
                <option value="{{ $p->id }}" {{ (string)old('parent_id', $c->parent_id ?? '') === (string)$p->id ? 'selected' : '' }}>
                    {{ $p->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Sıra (sort_order)</label>
        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $c->sort_order ?? 0) }}">
        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Açıklama</label>
        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $c->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 d-flex align-items-center">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                {{ old('is_active', $c->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>
