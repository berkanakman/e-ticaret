<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_upload_images_for_a_product()
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $this->post(route('admin.products.update', $product), [
            'name' => 'New Name',
            'images' => [$image],
        ]);

        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->path);
    }

    /** @test */
    public function an_admin_can_delete_images_from_a_product()
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $image = $product->images()->create([
            'path' => UploadedFile::fake()->image('product.jpg')->store('products', 'public'),
            'name' => 'product.jpg'
        ]);

        $this->post(route('admin.products.update', $product), [
            'name' => 'New Name',
            'delete_images' => [$image->id],
        ]);

        $this->assertCount(0, $product->fresh()->images);
        Storage::disk('public')->assertMissing($image->path);
    }
}
