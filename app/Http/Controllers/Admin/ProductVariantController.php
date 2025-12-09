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
     * Receives selected options from the frontend and generates combinations.
     */
    public function generateMissing(Request $request, Product $product)
    {
        $selectedOptions = $request->validate([
            'options' => 'required|array',
            'options.*' => 'array',
            'options.*.*' => 'integer|exists:product_attribute_options,id'
        ]);

        $optionGroups = array_values($selectedOptions['options']);

        if (empty($optionGroups)) {
            return response()->json([], 200); // Return empty if no options selected
        }

        $combinations = $this->cartesian($optionGroups);
        $createdVariants = [];

        foreach ($combinations as $combination) {
            if (!is_array($combination)) {
                $combination = [$combination];
            }
            sort($combination);
            $uniqueCombination = array_unique($combination);

            // Check if a variant with the exact same options already exists
            $existingVariant = $product->variants()
                ->whereHas('options', function ($query) use ($uniqueCombination) {
                    $query->whereIn('option_id', $uniqueCombination);
                }, '=', count($uniqueCombination))
                ->first();

            if (!$existingVariant) {
                $variant = $product->variants()->create([
                    'sku' => 'AUTO-' . uniqid(),
                    'price' => $product->price ?? 0,
                    'stock' => 0,
                ]);

                $optionsForVariant = [];
                foreach ($uniqueCombination as $optId) {
                    $opt = ProductAttributeOption::with('attribute')->find($optId);
                    if ($opt) {
                        $variant->options()->create([
                            'attribute_id' => $opt->attribute_id,
                            'option_id' => $opt->id,
                        ]);
                        // Prepare data for the JSON response
                        $optionsForVariant[] = [
                            'attribute_id' => $opt->attribute_id,
                            'option_id' => $opt->id,
                            'attribute_name' => $opt->attribute->name,
                            'option_name' => $opt->name,
                        ];
                    }
                }
                // Add the newly created variant with its details to the list to be returned
                $createdVariants[] = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'options' => $optionsForVariant,
                ];
            }
        }
        // Return the array of newly created variants
        return response()->json($createdVariants);
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
