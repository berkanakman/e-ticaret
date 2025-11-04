<?php
namespace App\Helpers;

use Illuminate\Support\Str;
use App\Models\Product;

class SlugHelper
{
    /**
     * Create a unique slug for Product based on $base.
     * If $ignoreId provided, exclude that product (for update).
     */
    public static function uniqueProductSlug(string $base, $ignoreId = null): string
    {
        $baseSlug = Str::slug($base);
        if ($baseSlug === '') {
            $baseSlug = Str::slug(Str::random(8));
        }
        // collect existing slugs that start with baseSlug
        $query = Product::query()->where('slug', 'LIKE', $baseSlug . '%');
        if ($ignoreId) {
            $query->where('id', '<>', $ignoreId);
        }
        // exclude soft-deleted rows if model uses SoftDeletes (default behavior of where)
        $rows = $query->pluck('slug')->toArray();

        // if exact slug not found, return it
        if (!in_array($baseSlug, $rows)) {
            return $baseSlug;
        }

        // determine highest numeric suffix
        $max = 0;
        $pattern = '/^' . preg_quote($baseSlug, '/') . '(?:-(\d+))?$/';
        foreach ($rows as $existing) {
            if (preg_match($pattern, $existing, $m)) {
                $n = isset($m[1]) && $m[1] !== '' ? (int)$m[1] : 0;
                if ($n > $max) $max = $n;
            }
        }

        return $baseSlug . '-' . ($max + 1);
    }
}
