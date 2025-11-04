<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\ProductAttributeOption;

class ProductVariantController extends Controller
{
    /**
     * Otomatik varyant oluşturma
     */
    public function generateMissing(Product $product)
    {
        $attributes = $product->attributes()->with('options')->get();

        if ($attributes->isEmpty()) {
            return response()->json(['message' => 'Hiç özellik seçilmemiş.'], 422);
        }

        // Seçeneklerin kombinasyonlarını oluştur
        $options = [];
        foreach ($attributes as $attr) {
            $ids = $attr->options->pluck('id')->toArray();
            if (!empty($ids)) {
                $options[] = $ids;
            }
        }

        if (empty($options)) {
            return response()->json(['message' => 'Seçenek bulunamadı.'], 422);
        }

        $combinations = $this->cartesian($options);

        foreach ($combinations as $combination) {
            $existing = ProductVariant::where('product_id', $product->id)
                ->whereHas('options', function ($q) use ($combination) {
                    $q->whereIn('option_id', $combination);
                })->exists();

            if (!$existing) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => uniqid('VAR-'),
                    'price' => $product->price,
                    'stock' => 0,
                ]);

                foreach ($combination as $optId) {
                    $opt = ProductAttributeOption::find($optId);
                    if ($opt) {
                        ProductVariantOption::create([
                            'variant_id' => $variant->id,
                            'attribute_id' => $opt->attribute_id,
                            'option_id' => $opt->id,
                        ]);
                    }
                }
            }
        }

        return response()->json(['message' => 'Varyantlar başarıyla oluşturuldu.']);
    }

    /**
     * Manuel varyant ekleme
     */
    public function storeManual(Request $request, Product $product)
    {
        $request->validate([
            'attribute_id' => 'required|exists:product_attributes,id',
            'option_id' => 'required|exists:product_attribute_options,id',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => uniqid('MAN-'),
            'price' => $product->price,
            'stock' => 0,
        ]);

        ProductVariantOption::create([
            'variant_id' => $variant->id,
            'attribute_id' => $request->attribute_id,
            'option_id' => $request->option_id,
        ]);

        return response()->json([
            'message' => 'Varyant başarıyla eklendi.',
            'variant' => $variant,
        ]);
    }

    /**
     * Dizi kombinasyonlarını hesaplar (örneğin renk × beden)
     */
    private function cartesian($input)
    {
        $result = [[]];
        foreach ($input as $property => $propertyValues) {
            $tmp = [];
            foreach ($result as $resultItem) {
                foreach ($propertyValues as $propertyValue) {
                    $tmp[] = array_merge($resultItem, [$propertyValue]);
                }
            }
            $result = $tmp;
        }
        return $result;
    }
}
