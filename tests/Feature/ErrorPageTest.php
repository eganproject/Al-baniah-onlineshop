<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_url_shows_branded_404_page_instead_of_500(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee(config('shop.name'));
    }

    public function test_unknown_nested_url_shows_404(): void
    {
        $this->get('/tidak/ada/halaman-ini')->assertNotFound();
    }

    public function test_missing_model_shows_404(): void
    {
        $this->get('/produk/produk-tidak-ada')->assertNotFound();
    }

    public function test_common_english_paths_redirect_to_indonesian_routes(): void
    {
        $this->get('/login')->assertRedirect('/masuk')->assertStatus(301);
        $this->get('/register')->assertRedirect('/daftar');
        $this->get('/cart')->assertRedirect('/keranjang');
        $this->get('/products')->assertRedirect('/produk');
    }
}
