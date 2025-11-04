<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mevcut attributes verilerini JSON formatına dönüştür
        $variants = DB::table('product_variants')->get();

        foreach ($variants as $variant) {
            if ($variant->attributes && !json_validate($variant->attributes)) {
                // Eski formatı yeni formata dönüştür
                $attributes = $variant->attributes;

                // Eğer boş değilse ve JSON değilse, temizle
                if (!empty($attributes)) {
                    try {
                        // JSON decode etmeyi dene
                        $decoded = json_decode($attributes, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // Zaten JSON ise olduğu gibi bırak
                            continue;
                        }
                    } catch (\Exception $e) {
                        // JSON değilse, boş dizi olarak ayarla
                        $attributes = '{}';
                    }
                } else {
                    $attributes = '{}';
                }

                DB::table('product_variants')
                    ->where('id', $variant->id)
                    ->update(['attributes' => $attributes]);
            }
        }
    }

    public function down(): void
    {
        // Geri alma işlemi gerekmiyor
    }
};
