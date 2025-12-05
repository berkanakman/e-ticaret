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
    public function an_admin_can_upload_an_image_via_ajax()
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $imageData = 'data:image/png;base64,' . base64_encode(file_get_contents(UploadedFile::fake()->image('test.png')));

        $this->postJson(route('admin.products.uploadImage'), [
            'image' => $imageData,
            'product_id' => $product->id,
        ])
        ->assertSuccessful()
        ->assertJson(['success' => true]);

        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->path);
    }

    /** @test */
    public function an_admin_can_delete_an_image_via_ajax()
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $image = $product->images()->create([
            'path' => UploadedFile::fake()->image('product.jpg')->store('products', 'public'),
            'name' => 'product.jpg'
        ]);

        $this->deleteJson(route('admin.products.deleteImage', $image))
            ->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertCount(0, $product->fresh()->images);
        Storage::disk('public')->assertMissing($image->path);
    }
}
