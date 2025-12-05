<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'name',
        'sort_order',
    ];

    protected static function booted()
    {
        static::deleting(function ($image) {
            Storage::disk('public')->delete($image->path);
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
