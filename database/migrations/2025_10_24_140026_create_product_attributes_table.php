<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->enum('type', ['select','multiselect','text','color','number','boolean'])->default('select');
            $table->boolean('is_filterable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->string('name');
            $table->string('value')->nullable();
            $table->json('meta')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('product_attribute_options')->nullOnDelete();
            $table->text('value')->nullable();
            $table->unique(['product_id','attribute_id','option_id']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->json('attributes')->nullable();
            $table->string('barcode')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attribute_options');
        Schema::dropIfExists('product_attributes');
    }
};
