<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->unsignedBigInteger('option_id')->nullable();
            $table->timestamps();

            $table->index('product_variant_id');
            $table->index('attribute_id');
            $table->index('option_id');

            $table->foreign('product_variant_id')
                ->references('id')->on('product_variants')
                ->onDelete('cascade');

            // Karşı tablolarınız farklı isimdeyse bu foreign keyleri uyarlayın
            $table->foreign('attribute_id')
                ->references('id')->on('product_attributes')
                ->onDelete('set null');

            $table->foreign('option_id')
                ->references('id')->on('product_attribute_options')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variant_options');
    }
};
