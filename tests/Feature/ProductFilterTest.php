<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_filter_shows_only_matching_products(): void
    {
        $ok = Product::create(['image' => 'x.jpg', 'name' => 'Produk Alpha', 'price' => 1000, 'availability' => 'tersedia']);
        $out = Product::create(['image' => 'x.jpg', 'name' => 'Produk Beta', 'price' => 1000, 'availability' => 'habis']);
        $soon = Product::create(['image' => 'x.jpg', 'name' => 'Produk Gamma', 'price' => 1000, 'availability' => 'segera']);

        $this->get('/products')->assertSee('Produk Alpha')->assertSee('Produk Beta')->assertSee('Produk Gamma');
        $this->get('/products?status=tersedia')->assertSee('Produk Alpha')->assertDontSee('Produk Beta')->assertDontSee('Produk Gamma');
        $this->get('/products?status=habis')->assertDontSee('Produk Alpha')->assertSee('Produk Beta')->assertDontSee('Produk Gamma');
        $this->get('/products?status=segera')->assertDontSee('Produk Alpha')->assertDontSee('Produk Beta')->assertSee('Produk Gamma');
    }

    public function test_invalid_status_is_ignored(): void
    {
        Product::create(['image' => 'x.jpg', 'name' => 'A', 'price' => 1000, 'availability' => 'tersedia']);

        $this->get('/products?status=ngawur-banget')->assertOk()->assertSee('A');
    }

    public function test_search_and_status_combine_with_and_not_or(): void
    {
        Product::create(['image' => 'x.jpg', 'name' => 'Gantungan Laut', 'price' => 1000, 'availability' => 'tersedia']);
        Product::create(['image' => 'x.jpg', 'name' => 'Gantungan Kunci', 'price' => 1000, 'availability' => 'habis']);

        // pencarian "Gantungan" cocok keduanya, tapi status membatasi ke satu
        $r = $this->get('/products?search=Gantungan&status=tersedia')->assertOk();
        $r->assertSee('Gantungan Laut')->assertDontSee('Gantungan Kunci');
    }

    public function test_status_filter_persists_through_search_form(): void
    {
        Product::create(['image' => 'x.jpg', 'name' => 'A', 'price' => 1000, 'availability' => 'habis']);

        $this->get('/products?status=habis')->assertSee('name="status" value="habis"', false);
    }
}
