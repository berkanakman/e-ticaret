<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // sütun yoksa ekle
            if (! Schema::hasColumn('product_variants', 'combination_key')) {
                $table->string('combination_key', 255)->nullable()->after('id');
            }

            // benzersiz index yoksa eklemeye çalış
            // Laravel'in native hasIndex yok; try-catch kullanıyoruz
            try {
                $table->unique(['product_id', 'combination_key'], 'uniq_product_variant_combination');
            } catch (\Exception $e) {
                // index zaten varsa veya başka bir hata varsa yoksay
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // index'i düşür (varsa)
            try {
                $table->dropUnique('uniq_product_variant_combination');
            } catch (\Exception $e) {
                // yoksa yoksay
            }

            // sütun varsa sil
            if (Schema::hasColumn('product_variants', 'combination_key')) {
                $table->dropColumn('combination_key');
            }
        });
    }
};
