<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tambah ke keranjang lewat JS (tanpa reload) dan pesan jumlah yang tepat. */
class CartAddTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_add_returns_message_and_total_count(): void
    {
        $product = Product::factory()->create(['name' => 'Yoga Block', 'stock' => 10]);

        $this->postJson(route('cart.store', $product), ['quantity' => 1])
            ->assertOk()
            ->assertJson(['type' => 'success', 'message' => '1 pcs Yoga Block ditambahkan ke keranjang.', 'added' => 1, 'line_quantity' => 1, 'count' => 1]);

        $this->postJson(route('cart.store', $product), ['quantity' => 2])
            ->assertOk()
            ->assertJson(['message' => '2 pcs Yoga Block ditambahkan. Di keranjang sekarang 3 pcs.', 'added' => 2, 'line_quantity' => 3, 'count' => 3]);
    }

    public function test_add_beyond_stock_says_how_many_were_added(): void
    {
        $product = Product::factory()->create(['name' => 'Botol', 'stock' => 2]);

        $this->postJson(route('cart.store', $product), ['quantity' => 5])
            ->assertOk()
            ->assertJson(['type' => 'warning', 'added' => 2, 'line_quantity' => 2]);

        $this->postJson(route('cart.store', $product))
            ->assertOk()
            ->assertJson(['type' => 'warning', 'added' => 0, 'line_quantity' => 2]);
    }

    public function test_json_errors_for_missing_variant_and_out_of_stock(): void
    {
        $withVariants = Product::factory()->create(['has_variants' => true]);
        ProductVariant::factory()->for($withVariants)->create(['is_active' => true]);
        $soldOut = Product::factory()->create(['stock' => 0]);

        $this->postJson(route('cart.store', $withVariants))->assertStatus(422)->assertJson(['type' => 'error']);
        $this->postJson(route('cart.store', $soldOut))->assertStatus(422)->assertJson(['message' => 'Maaf, stok produk ini sedang habis.']);
    }

    public function test_plain_form_still_redirects_back_with_toast(): void
    {
        $product = Product::factory()->create(['name' => 'Handuk', 'stock' => 5]);

        $this->from(route('products.show', $product))
            ->post(route('cart.store', $product))
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHas('success', '1 pcs Handuk ditambahkan ke keranjang.');
    }

    public function test_add_forms_opt_into_ajax_and_badge_is_live(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->get(route('products.show', $product))->assertOk()
            ->assertSee('data-cart-add', false)
            ->assertSee('@cart-updated.window', false);

        $this->get(route('products.index'))->assertOk()->assertSee('data-cart-add', false);
    }
}
