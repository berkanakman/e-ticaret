<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->string('name');
            $table->string('value')->nullable(); // e.g. hex code or code
            $table->json('meta')->nullable(); // extensible JSON
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_options');
    }
};
