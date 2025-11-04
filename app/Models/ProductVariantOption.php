<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantOption extends Model
{
    protected $table = 'product_variant_options';
    protected $fillable = ['product_variant_id', 'attribute_id', 'option_id'];




    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }

    public function option()
    {
        return $this->belongsTo(ProductAttributeOption::class, 'option_id', 'id');
    }

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id', 'id');
    }
}
