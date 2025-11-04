<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    protected $table = 'product_attributes';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_filterable',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function options()
    {
        return $this->hasMany(ProductAttributeOption::class, 'attribute_id', 'id')->orderBy('sort_order');
    }
    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }
}
