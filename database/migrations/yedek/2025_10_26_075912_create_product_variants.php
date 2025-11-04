<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->json('attributes')->nullable(); // {"color":3,"size":5} attribute_id => option_id or value
            $table->string('barcode')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
