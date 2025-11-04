<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeOption extends Model
{
    protected $table = 'product_attribute_options';

    protected $fillable = [
        'attribute_id',
        'name',
        'value',
        'meta',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id', 'id');
    }
}
