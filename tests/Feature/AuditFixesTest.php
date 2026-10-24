<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Perbaikan dari audit fitur & alur toko (Oktober 2026). */
class AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_buy_buttons_are_not_inside_a_nested_form_for_customers(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $html = $this->actingAs(User::factory()->create())
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('form="add-to-cart" name="buy_now"', false)
            ->getContent();

        // Form wishlist di dalam form keranjang membuat browser menutup form keranjang lebih awal,
        // sehingga tombol "Beli sekarang" tidak mengirim apa pun.
        $start = strpos($html, '<form id="add-to-cart"');
        $this->assertNotFalse($start);
        $inner = substr($html, $start + 5, strpos($html, '</form>', $start) - $start - 5);
        $this->assertStringNotContainsString('<form', $inner);
    }

    public function test_rate_limits_do_not_share_one_counter(): void
    {
        $this->actingAs(User::factory()->create());

        // 25 permintaan wilayah (batas 120/menit) tidak boleh menghabiskan kuota voucher (20/menit).
        for ($i = 0; $i < 25; $i++) {
            $this->getJson(route('account.regions.provinces'))->assertOk();
        }

        $this->postJson(route('checkout.voucher.apply'), ['code' => 'TIDAKADA'])->assertStatus(422);
    }

    public function test_dashboard_and_report_link_products_by_slug(): void
    {
        $product = Product::factory()->create(['name' => 'Botol Sport', 'slug' => 'botol-sport', 'price' => 50000]);
        $order = Order::factory()->status(OrderStatus::Completed)->paidAt(now()->subDay())->create();
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'price' => 50000, 'quantity' => 1, 'subtotal' => 50000,
        ]);

        $this->actingAs(User::factory()->admin()->create());
        $editUrl = route('admin.products.edit', 'botol-sport');

        $this->get(route('admin.dashboard'))->assertOk()->assertSee($editUrl, false);
        $this->get(route('admin.reports.index', ['tab' => 'produk']))->assertOk()->assertSee($editUrl, false);
        $this->get($editUrl)->assertOk();
    }

    public function test_array_query_parameters_do_not_crash_public_lists(): void
    {
        foreach (['products.index', 'products.best-sellers', 'articles.index'] as $route) {
            $this->get(route($route, ['q' => ['a'], 'urut' => ['x'], 'kategori' => ['y'], 'koleksi' => ['z'], 'harga_min' => ['1']]))->assertOk();
        }
    }

    public function test_swapped_price_range_still_finds_products(): void
    {
        Product::factory()->create(['name' => 'Matras Tengah', 'price' => 150000]);

        $this->get(route('products.index', ['harga_min' => 500000, 'harga_max' => 100000]))
            ->assertOk()
            ->assertSee('Matras Tengah');
    }

    public function test_collection_pages_keep_their_canonical_and_are_in_sitemap(): void
    {
        $collection = ProductCollection::factory()->create(['slug' => 'running', 'is_active' => true]);

        $this->get(route('products.index', ['koleksi' => 'running']))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.e(route('products.index', ['koleksi' => 'running'])).'">', false);

        $this->get(route('sitemap'))->assertOk()->assertSee(e($collection->url()), false);
    }

    public function test_footer_order_link_does_not_send_admin_to_customer_area(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('href="'.route('account.orders.index').'"', false);
    }
}
