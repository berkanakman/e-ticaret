@php
    $allAttributes = $allAttributes ?? (\App\Models\ProductAttribute::where('is_active', true)->with('options')->orderBy('sort_order')->get() ?? collect());
    $allForJs = [];
    foreach ($allAttributes as $a) {
        $opts = [];
        foreach ($a->options ?? [] as $o) {
            $opts[] = ['id'=>$o->id,'name'=>$o->name,'value'=>$o->value,'attribute_id'=>$a->id];
        }
        $allForJs[$a->id] = ['id'=>$a->id,'name'=>$a->name,'type'=>$a->type,'options'=>$opts];
    }
    $variantsForJs = $variantsForJs ?? [];
    if (!empty($product) && isset($product->variants) && is_iterable($product->variants)) {
        foreach ($product->variants as $v) {
            $opts = [];
            if (isset($v->options) && is_iterable($v->options)) {
                foreach ($v->options as $po) {
                    $opts[] = [
                        'attribute_id' => $po->attribute_id ?? null,
                        'option_id' => $po->option_id ?? null,
                        'attribute_name' => $po->attribute->name ?? null,
                        'option_name' => $po->option->name ?? null,
                    ];
                }
            }
            // Pass price and stock to the JS bootstrap data
            $variantsForJs[] = ['id'=>$v->id ?? null,'sku'=>$v->sku ?? null, 'price' => $v->price ?? 0, 'stock' => $v->stock ?? 0, 'options'=>$opts];
        }
    }
@endphp

<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3">Varyantlar</h5>

        <div class="mb-3">
            <div class="form-text small text-muted">Özellikleri sayfanın üstündeki "Kullanılacak Özellikler" bölümünden seçin. Aşağıda seçilen özelliklere ait seçenekler checkbox olarak görülecek.</div>
        </div>

        <div id="selected-attributes-list" class="mb-3"></div>

        <div class="mb-3 d-flex gap-2 align-items-center">
            <button type="button" id="generate-variants-btn" class="btn btn-success">Otomatik Oluştur</button>
            <div class="ms-2 text-muted small">Seçili seçeneklerin tüm kombinasyonları oluşturulacak</div>
        </div>

        <hr>
        <h6 class="mb-2">Mevcut Varyantlar</h6>
        <div id="variants-list">
            {{-- Variants will be rendered here by JavaScript --}}
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.VARIANTS_BOOTSTRAP = {
            allForJs: window.ATTRIBUTES_MAP || @json($allForJs),
            variantsForJs: @json($variantsForJs ?? []),
            routes: {
                generate: "{{ route('admin.products.variants.generate', ['product' => $product->id ?? 0]) }}"
            }
        };
    </script>
@endpush
