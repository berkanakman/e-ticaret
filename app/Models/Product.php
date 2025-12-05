<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id','name','slug','description','price','is_active','stock','sku'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->slug) && !empty($model->name)) {
                $model->slug = Str::slug($model->name) . '-' . uniqid();
            }
        });
    }

    // İlişkiler
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    /**
     * Product ile Attribute ilişkisinin tanımı
     * ProductAttributeValue üzerinden ProductAttribute ile ilişki
     */
    public function attributes()
    {
        return $this->belongsToMany(
            ProductAttribute::class,
            'product_attribute_values', // pivot tablo
            'product_id',               // Product ID
            'attribute_id'              // Attribute ID
        )->withPivot('value'); // Eğer pivot tablosunda değer saklıyorsanız
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
