<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappButtonTest extends TestCase
{
    use RefreshDatabase;

    private const BUTTON = 'data-whatsapp-button';

    public function test_floating_button_links_to_admin_number_on_customer_pages(): void
    {
        config(['shop.whatsapp' => '6281234567890']);

        $this->get(route('home'))->assertOk()
            ->assertSee(self::BUTTON, false)
            ->assertSee('https://wa.me/6281234567890?text=', false);

        $this->get(route('products.index'))->assertOk()->assertSee(self::BUTTON, false);
        $this->get(route('cart.index'))->assertOk()->assertSee(self::BUTTON, false);
        $this->get(route('articles.index'))->assertOk()->assertSee(self::BUTTON, false);
    }

    public function test_product_page_prefills_message_with_product_name(): void
    {
        config(['shop.whatsapp' => '6281234567890']);
        $product = Product::factory()->create(['name' => 'Matras Yoga Pro', 'stock' => 5]);

        $this->get(route('products.show', $product))->assertOk()
            ->assertSee(self::BUTTON, false)
            ->assertSee(rawurlencode('Matras Yoga Pro'), false);
    }

    public function test_button_hidden_without_number(): void
    {
        config(['shop.whatsapp' => null]);

        $this->get(route('home'))->assertOk()->assertDontSee(self::BUTTON, false);
    }

    public function test_button_not_shown_on_admin_pages(): void
    {
        config(['shop.whatsapp' => '6281234567890']);
        $admin = \App\Models\User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertDontSee(self::BUTTON, false);
    }
}
