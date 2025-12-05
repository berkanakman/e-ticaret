<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Helpers\SlugHelper;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ProductAttributeOption;
use App\Models\ProductVariant;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $query = Product::with('category')->orderBy('created_at', 'desc');
        if ($q) $query->where('name', 'like', "%{$q}%");
        $products = $query->paginate(25);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $attributes = ProductAttribute::where('is_active', true)->with('options')->orderBy('sort_order')->get();
        return view('admin.products.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {

        \Illuminate\Support\Facades\Log::info('PRODUCT STORE PAYLOAD', $request->only(['name','slug','description']));

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'sku' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            // variants, attributes handled separately
        ]);

        // ensure booleans are normalized
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool)$data['is_active'];
        } else {
            $data['is_active'] = false;
        }

        $base = $data['slug'] ?? $data['name'];
        $maxAttempts = 8;
        $attempt = 0;

        do {
            $attempt++;

            // generate candidate slug (helper returns base or base-<n>)
            $candidate = SlugHelper::uniqueProductSlug($base);

            // If repeated attempts, force incremental suffix to avoid looping same candidate
            if ($attempt > 1 && !Str::endsWith($candidate, '-' . ($attempt - 1))) {
                $candidate = $candidate . '-' . ($attempt - 1);
            }

            $data['slug'] = $candidate;

            try {
                // create product (ensure Product::$fillable contains these keys)
                $product = Product::create($data);

                // sync related data AFTER product created
                if (method_exists($this, 'syncProductAttributes')) {
                    $this->syncProductAttributes($product, $request);
                }
                if (method_exists($this, 'syncProductVariants')) {
                    $this->syncProductVariants($product, $request);
                }
                if (method_exists($this, 'syncProductImages')) {
                    $this->syncProductImages($product, $request);
                }

                return redirect()->route('admin.products.index')->with('success', 'Ürün oluşturuldu');
            } catch (QueryException $ex) {
                $errorCode = $ex->errorInfo[1] ?? null;

                // MySQL duplicate key kodu 1062
                if ($errorCode === 1062) {
                    if ($attempt >= $maxAttempts) {
                        // fallback: garantili benzersiz slug
                        $data['slug'] = $candidate . '-' . time() . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
                        try {
                            $product = Product::create($data);
                            if (method_exists($this, 'syncProductAttributes')) {
                                $this->syncProductAttributes($product, $request);
                            }
                            if (method_exists($this, 'syncProductVariants')) {
                                $this->syncProductVariants($product, $request);
                            }
                            if (method_exists($this, 'syncProductImages')) {
                                $this->syncProductImages($product, $request);
                            }
                            return redirect()->route('admin.products.index')->with('success', 'Ürün oluşturuldu');
                        } catch (QueryException $ex2) {
                            throw $ex2;
                        }
                    }

                    // yarış durumunda başka bir suffix denemesi için base'i candidate olarak ayarla
                    $base = $candidate;
                    continue;
                }

                // başka DB hatası ise yeniden fırlat
                throw $ex;
            }
        } while ($attempt < $maxAttempts);

        throw new \RuntimeException('Ürün oluşturulamadı; lütfen tekrar deneyin.');
    }

    public function edit(Product $product)
    {
        // Tüm özellikleri ve seçenekleri getir
        $attributes = ProductAttribute::with(['options' => function($q){
            $q->orderBy('sort_order');
        }])->where('is_active',1)->orderBy('sort_order')->get();

        $attributeOptions = [];
        foreach ($attributes as $attr) {
            $attributeOptions[$attr->id] = $attr->options->map(function($opt){
                return [
                    'id' => $opt->id,
                    'name' => $opt->name,
                    'value' => $opt->value,
                ];
            })->toArray();
        }

        // Ürünün varyantlarını getir
        $variantsWithValues = $product->variants->map(function($variant) {
            return $variant; // ProductVariant modeli getValuesAttribute() ile values sağlar
        });

        // Ürünün özellik değerlerini getir
        $productValues = [];
        $attributeValues = \DB::table('product_attribute_values')->where('product_id', $product->id)->get();

        // Eğer ürün değerleri boşsa, varsayılan değerler ekle
        if ($attributeValues->isEmpty()) {
            // İlk özelliği ve seçeneğini al
            $firstAttribute = $attributes->first();
            if ($firstAttribute && $firstAttribute->options->isNotEmpty()) {
                $firstOption = $firstAttribute->options->first();
                if ($firstOption) {
                    // Veritabanına kaydet
                    \DB::table('product_attribute_values')->insert([
                        'product_id' => $product->id,
                        'attribute_id' => $firstAttribute->id,
                        'option_id' => $firstOption->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Değişkenlere ekle
                    $productValues[$firstAttribute->id] = [(int)$firstOption->id];
                    $attributeValues = \DB::table('product_attribute_values')->where('product_id', $product->id)->get();
                }
            }
        }

        foreach ($attributeValues as $val) {
            $productValues[$val->attribute_id][] = (int)$val->option_id;
        }

        $selectedAttributes = array_keys($productValues);
        $attributesMap = $attributes->pluck('name','id')->toArray();

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'attributes' => $attributes,
            'attributeOptions' => $attributeOptions,
            'productValues' => $productValues,
            'selectedAttributes' => $selectedAttributes,
            'attributesMap' => $attributesMap,
            'variantsWithValues' => $variantsWithValues
        ]);
    }

    public function update(Request $request, Product $product)
    {
        \Illuminate\Support\Facades\Log::info('PRODUCT UPDATE PAYLOAD', $request->only(['id','name','slug','description']));

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'sku' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            // variants, attributes handled separately
        ]);

        // normalize boolean
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool)$data['is_active'];
        }

        // decide base for slug: if user provided slug use it, otherwise use name
        $base = $data['slug'] ?? $data['name'];
        $maxAttempts = 8;
        $attempt = 0;

        do {
            $attempt++;

            // generate candidate slug, ignoring current product id
            $candidate = SlugHelper::uniqueProductSlug($base, $product->id);

            // ensure incremental candidate on repeated attempts
            if ($attempt > 1 && !Str::endsWith($candidate, '-' . ($attempt - 1))) {
                $candidate = $candidate . '-' . ($attempt - 1);
            }

            $data['slug'] = $candidate;

            try {
                // update product (ensure $fillable allows these keys)
                $product->update($data);

                // sync related data AFTER update
                if (method_exists($this, 'syncProductAttributes')) {
                    $this->syncProductAttributes($product, $request);
                }
                if (method_exists($this, 'syncProductVariants')) {
                    $this->syncProductVariants($product, $request);
                }
                if (method_exists($this, 'syncProductImages')) {
                    $this->syncProductImages($product, $request);
                }

                return redirect()->route('admin.products.index')->with('success', 'Ürün güncellendi');
            } catch (QueryException $ex) {
                $errorCode = $ex->errorInfo[1] ?? null;

                // MySQL duplicate key kodu 1062 -> yarış koşulu
                if ($errorCode === 1062) {
                    if ($attempt >= $maxAttempts) {
                        // fallback guaranteed unique slug
                        $data['slug'] = $candidate . '-' . time() . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
                        try {
                            $product->update($data);
                            if (method_exists($this, 'syncProductAttributes')) {
                                $this->syncProductAttributes($product, $request);
                            }
                            if (method_exists($this, 'syncProductVariants')) {
                                $this->syncProductVariants($product, $request);
                            }
                            if (method_exists($this, 'syncProductImages')) {
                                $this->syncProductImages($product, $request);
                            }
                            return redirect()->route('admin.products.index')->with('success', 'Ürün güncellendi');
                        } catch (QueryException $ex2) {
                            throw $ex2;
                        }
                    }

                    // set base to candidate to allow numeric suffix growth and retry
                    $base = $candidate;
                    continue;
                }

                // diğer DB hatalarını tekrar fırlat
                throw $ex;
            }
        } while ($attempt < $maxAttempts);

        throw new \RuntimeException('Ürün güncellenemedi; lütfen tekrar deneyin.');
    }

    protected function makeUniqueSlug($slugCandidate, $name = null, $ignoreId = null)
    {
        $base = $slugCandidate ?: Str::slug($name ?? '');
        if (empty($base)) {
            $base = 'product';
        }

        $slug = $base;
        $i = 0;
        while (true) {
            $q = Product::where('slug', $slug);
            if ($ignoreId) $q->where('id', '!=', $ignoreId);
            $exists = $q->exists();
            if (!$exists) break;
            $i++;
            $slug = $base . '_' . $i;
        }

        return $slug;
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Ürün silindi.');
    }

    protected function syncProductAttributes(Product $product, Request $request)
    {
        $submitted = $request->input('attributes', []);
        $newAttrs = $request->input('attributes_new', []);

        if (!empty($submitted) || !empty($newAttrs)) {
            $attrIds = array_map('intval', array_keys($submitted));
            $newAttrIds = array_map('intval', array_keys($newAttrs));
            $merged = array_unique(array_merge($attrIds, $newAttrIds));
            ProductAttributeValue::where('product_id', $product->id)->whereIn('attribute_id', $merged)->delete();
        }

        foreach ($submitted as $attrId => $val) {
            $attrId = (int)$attrId;
            $attribute = ProductAttribute::find($attrId);
            if (!$attribute) continue;

            // single types
            if (in_array($attribute->type, ['select','color','number','text','boolean'])) {
                if (is_array($val)) continue;
                if (ctype_digit((string)$val)) {
                    ProductAttributeValue::create([
                        'product_id'=>$product->id,
                        'attribute_id'=>$attrId,
                        'option_id'=> (int)$val,
                        'value'=>null
                    ]);
                } else {
                    ProductAttributeValue::create([
                        'product_id'=>$product->id,
                        'attribute_id'=>$attrId,
                        'option_id'=> null,
                        'value'=> $val
                    ]);
                }
                continue;
            }

            // multiselect
            if ($attribute->type === 'multiselect') {
                if (!is_array($val)) $val = [$val];
                foreach ($val as $v) {
                    if (!$v) continue;
                    ProductAttributeValue::create([
                        'product_id'=>$product->id,
                        'attribute_id'=>$attrId,
                        'option_id'=> (ctype_digit((string)$v) ? (int)$v : null),
                        'value'=> (ctype_digit((string)$v) ? null : $v)
                    ]);
                }
            }
        }

        // attributes_new: create options and attach
        foreach ($newAttrs as $attrId => $data) {
            $attrId = (int)$attrId;
            if (empty($data['name']) && empty($data['hex']) && empty($data['value'])) continue;
            $attribute = ProductAttribute::find($attrId);
            if (!$attribute) continue;
            $opt = $attribute->options()->create([
                'name' => $data['name'] ?? ($data['hex'] ?? 'Yeni'),
                'value' => $data['hex'] ?? ($data['value'] ?? null),
                'sort_order' => ($attribute->options()->max('sort_order') ?? 0) + 1,
                'is_active' => true,
            ]);
            ProductAttributeValue::create([
                'product_id'=>$product->id,
                'attribute_id'=>$attrId,
                'option_id'=>$opt->id,
                'value'=> null
            ]);
        }
    }

    protected function syncProductVariants(Product $product, Request $request)
    {
        $variants = $request->input('variants', []);
        if (empty($variants)) return;

        // Önce mevcut varyantları al
        $existingVariants = $product->variants()->get()->keyBy('combination_key');

        foreach ($variants as $variantData) {
            if (empty($variantData['values'])) continue;

            // Varyant değerlerini sırala ve kombinasyon anahtarı oluştur
            $values = $variantData['values'];
            $normalizedValues = [];
            foreach ($values as $k => $v) {
                $normalizedValues[(int)$k] = (int)$v;
            }
            ksort($normalizedValues);
            $combinationKey = json_encode($normalizedValues);

            // Varyant verilerini hazırla
            $variantAttributes = [
                'sku' => $variantData['sku'] ?? null,
                'price' => $variantData['price'] ?? null,
                'stock' => $variantData['stock'] ?? null,
                'values' => $combinationKey,
                'combination_key' => $combinationKey
            ];

            // Eğer bu kombinasyon zaten varsa güncelle, yoksa oluştur
            if (isset($existingVariants[$combinationKey])) {
                $existingVariants[$combinationKey]->update($variantAttributes);
                $existingVariants->forget($combinationKey);
            } else {
                $product->variants()->create($variantAttributes);
            }
        }

        // Formda olmayan varyantları sil
        foreach ($existingVariants as $variant) {
            $variant->delete();
        }
    }

    protected function syncProductImages(Product $product, Request $request)
    {
        // Eğer resim yüklenmişse işle
        if ($request->hasFile('images')) {
            $images = $request->file('images');

            foreach ($images as $image) {
                // Resim doğrulama
                if (!$image->isValid()) continue;

                // Resim adını oluştur
                $fileName = 'product_' . $product->id . '_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Resmi kaydet
                $path = $image->storeAs('products', $fileName, 'public');

                // Resim kaydını oluştur
                $product->images()->create([
                    'path' => $path,
                    'name' => $fileName,
                    'sort_order' => 0
                ]);
            }
        }

        // Silinecek resimleri işle
        $deleteImages = $request->input('delete_images', []);
        if (!empty($deleteImages)) {
            $product->images()->whereIn('id', $deleteImages)->delete();
        }
    }

    public function storeManual(Request $request, Product $product)
    {
        $data = $request->validate([
            'attribute_id' => 'required|integer|exists:product_attributes,id',
            'option_id' => 'required|integer|exists:product_attribute_options,id',
        ]);

        // Normalize combination key (for single-option variant case)
        $combKey = json_encode([[ 'attribute_id' => $data['attribute_id'], 'option_id' => $data['option_id'] ]]);

        // check existing: implement your variant->options relationship
        $exists = $product->variants()->whereHas('options', function ($q) use ($data) {
            $q->where('option_id', $data['option_id']);
        })->exists();

        if ($exists) {
            return response()->json(['message' => 'Bu seçenek zaten bir varyanta ait'], 409);
        }

        // create variant (adjust fields as your model requires)
        $variant = $product->variants()->create([
            'sku' => 'GEN-' . Str::random(6),
            'price' => 0,
            'stock' => 0,
            'combination_key' => $combKey,
        ]);

        // attach option relation (pivot)
        $variant->options()->create([
            'attribute_id' => $data['attribute_id'],
            'option_id' => $data['option_id'],
        ]);

        return response()->json(['ok' => true, 'variant' => $variant->load('options')], 201);
    }

    public function generateMissing(Request $request, Product $product)
    {
        // receive arrays of attribute ids to combine or compute from product
        $attributes = $request->input('attribute_ids', []); // or compute from product selection
        // build all combinations (cartesian product) of options for provided attributes
        $optionSets = [];
        foreach ($attributes as $aid) {
            $optionSets[] = ProductAttribute::find($aid)->options()->pluck('id')->toArray();
        }
        $combinations = $this->cartesian($optionSets); // returns array of arrays of option ids

        $created = [];
        foreach ($combinations as $combo) {
            // determine key or normalized representation to check
            sort($combo);
            $key = implode(',', $combo);

            // check if a variant exists that has exactly these option ids (or includes them depending on model)
            $exists = $product->variants()->whereHas('options', function($q) use ($combo) {
                $q->whereIn('option_id', $combo);
            })->count();

            // better: check equality via sets; implement precise check for your schema
            if ($exists) continue;

            // create variant
            $variant = $product->variants()->create([
                'sku' => 'GEN-' . Str::random(6),
                'price' => 0,
                'stock' => 0,
                'combination_key' => $key,
            ]);
            foreach ($combo as $optId) {
                // find attribute id for opt
                $opt = ProductAttributeOption::find($optId);
                $variant->options()->create(['attribute_id' => $opt->attribute_id, 'option_id' => $optId]);
            }
            $created[] = $variant->load('options');
        }

        return response()->json(['created' => $created, 'count' => count($created)]);
    }

// helper cartesian implementation...
    protected function cartesian(array $sets) {
        $result = [[]];
        foreach ($sets as $set) {
            $append = [];
            foreach ($result as $product) {
                foreach ($set as $item) {
                    $append[] = array_merge($product, [$item]);
                }
            }
            $result = $append;
        }
        return $result;
    }
}
