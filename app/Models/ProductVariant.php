<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'combination_key',
        'attributes',
        'barcode'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(ProductVariantOption::class, 'product_variant_id', 'id');
    }

    // Basit values accessor - sadece options ilişkisini kullan
    public function getValuesAttribute()
    {
        $values = [];

        foreach ($this->options as $option) {
            $values[] = (object)[
                'attribute_id' => $option->attribute_id,
                'option_id' => $option->option_id,
                'attribute' => (object)[
                    'id' => $option->attribute_id,
                    'name' => $option->attribute->name ?? ''
                ],
                'option' => (object)[
                    'id' => $option->option_id,
                    'value' => $option->option->value ?? '',
                    'name' => $option->option->name ?? ''
                ],
            ];
        }

        return collect($values);
    }
}
