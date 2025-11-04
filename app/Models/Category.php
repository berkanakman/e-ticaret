<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Parent-children ilişkileri
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    // Eğer Product modeli varsa ilişki
    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    // Slug mutator: eğer slug verilmemişse name'den üretir
    public function setSlugAttribute($value)
    {
        $slug = $value ?: Str::slug($this->attributes['name'] ?? $value);
        $base = $slug;
        $i = 1;
        while (self::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->withTrashed()->exists()) {
            $slug = $base . '-' . $i++;
        }
        $this->attributes['slug'] = $slug;
    }
}
