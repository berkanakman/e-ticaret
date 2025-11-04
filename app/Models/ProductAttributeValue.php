<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $table = 'product_attribute_values';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'option_id',
        'value'
    ];

    public $timestamps = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ProductAttribute::class, 'attribute_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ProductAttributeOption::class, 'option_id');
    }
}
