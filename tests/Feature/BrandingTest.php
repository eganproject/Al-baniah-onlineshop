<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_uses_nufit_branding(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('<title>NuFit Indonesia', false)
            ->assertSee(config('shop.motto'))
            ->assertSee('#BEFITWITHNUFIT')
            ->assertDontSee('MGA')
            ->assertDontSee('Media Gudang');
    }

    public function test_header_mega_menu_lists_subcategories(): void
    {
        $running = Category::factory()->create(['name' => 'Running', 'slug' => 'running']);
        Category::factory()->create(['name' => 'Running Belt', 'slug' => 'running-belt', 'parent_id' => $running->id]);

        $this->get(route('home'))->assertOk()
            ->assertSee('id="mega-menu"', false)
            ->assertSeeInOrder(['Running', 'Running Belt'])
            ->assertSee(route('products.index', ['kategori' => 'running-belt']), false);
    }
}
