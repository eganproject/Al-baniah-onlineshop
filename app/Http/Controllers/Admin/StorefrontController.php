<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Settings\StoreSettings;
use App\Services\Storefront\ActivityTiles;
use App\Services\Storefront\BestSellers;
use App\Support\Storefront;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → Tampilan Beranda: foto & teks hero, pengumuman di topbar, dan isi tab Best Seller. */
class StorefrontController extends Controller
{
    /** Nama kolom form => kunci config (teks). */
    private const TEXT = [
        'hero_eyebrow' => 'shop.storefront.hero_eyebrow',
        'hero_title' => 'shop.storefront.hero_title',
        'hero_subtitle' => 'shop.storefront.hero_subtitle',
        'hero_primary_label' => 'shop.storefront.hero_primary_label',
        'hero_primary_url' => 'shop.storefront.hero_primary_url',
        'hero_secondary_label' => 'shop.storefront.hero_secondary_label',
        'hero_secondary_url' => 'shop.storefront.hero_secondary_url',
        'hero_text' => 'shop.storefront.hero_text',
    ];

    /** Kolom unggahan => kunci config. */
    private const IMAGES = [
        'hero_image' => 'shop.storefront.hero_image',
        'hero_image_mobile' => 'shop.storefront.hero_image_mobile',
    ];

    /** Teks seksi beranda: nama kolom form => [kunci config, maks. karakter]. */
    private const SECTION_TEXT = [
        'ribbon_text' => ['shop.storefront.ribbon_text', 200],
        'best_eyebrow' => ['shop.storefront.best_eyebrow', 40],
        'best_title' => ['shop.storefront.best_title', 60],
        'activities_eyebrow' => ['shop.storefront.activities_eyebrow', 40],
        'activities_title' => ['shop.storefront.activities_title', 60],
        'promo_eyebrow' => ['shop.storefront.promo_eyebrow', 40],
        'promo_title' => ['shop.storefront.promo_title', 60],
        'promo_text' => ['shop.storefront.promo_text', 200],
        'promo_badge' => ['shop.storefront.promo_badge', 14],
        'promo_button' => ['shop.storefront.promo_button', 30],
        'promo_url' => ['shop.storefront.promo_url', 255],
        'why_eyebrow' => ['shop.storefront.why_eyebrow', 40],
        'why_title' => ['shop.storefront.why_title', 90],
        'why_text' => ['shop.storefront.why_text', 300],
        'articles_title' => ['shop.storefront.articles_title', 60],
    ];

    public function __construct(private readonly StoreSettings $settings) {}

    public function edit(): View
    {
        $keys = self::TEXT + self::IMAGES;

        return view('admin.storefront', [
            'values' => collect($keys)->map(fn (string $key) => config($key))->all()
                + ['announcements' => implode("\n", Storefront::announcements())],
            'defaults' => collect(self::TEXT)->map(fn (string $key) => $this->settings->default($key))->all(),
            'images' => collect(self::IMAGES)->map(fn (string $key) => Storefront::url(config($key)))->all(),
            'sections' => $this->sectionItems(),
            'sectionText' => collect(self::SECTION_TEXT)->map(fn (array $def) => config($def[0]))->all()
                + [
                    'why_points' => implode("\n", (array) config('shop.storefront.why_points', [])),
                    'activities_source' => config('shop.storefront.activities_source', 'auto'),
                    'hero_rail' => (bool) config('shop.storefront.hero_rail', true),
                ],
            'sectionDefaults' => array_merge(
                collect(self::SECTION_TEXT)->map(fn (array $def) => $this->settings->default($def[0]))->all(),
                ['ribbon_text' => implode(' • ', array_filter([config('shop.motto'), config('shop.hashtag')]))],
            ),
            'textLimits' => collect(self::SECTION_TEXT)->map(fn (array $def) => $def[1])->all(),
            'bestSeller' => [
                'mode' => BestSellers::isManual() ? 'manual' : 'auto',
                'fill' => (bool) config('shop.storefront.best_seller_fill', true),
                'products' => $this->pickedProducts(BestSellers::pickedIds()),
            ],
        ]);
    }

    /** Simpan urutan, tampil/sembunyi, dan teks seksi beranda. */
    public function updateSections(Request $request): RedirectResponse
    {
        $rules = collect(self::SECTION_TEXT)->map(fn (array $def) => ['nullable', 'string', 'max:'.$def[1]])->all();
        $rules['promo_url'][] = 'regex:#^(/(?!/)|https?://)#';

        $data = $request->validate($rules + [
            'sections' => ['nullable', 'array'],
            'sections.*' => ['string', 'distinct', Rule::in(array_keys(Storefront::SECTIONS))],
            'activities_source' => ['required', Rule::in(array_keys(ActivityTiles::SOURCES))],
            'hero_rail' => ['boolean'],
            'why_points' => ['nullable', 'string', 'max:1000'],
        ], [
            'promo_url.regex' => 'Awali dengan "/" untuk halaman toko (mis. /produk?promo=1) atau https:// untuk link luar.',
        ], [
            'sections' => 'seksi', 'promo_url' => 'link tombol promo', 'why_points' => 'poin keunggulan', 'ribbon_text' => 'teks pita',
        ]);

        $values = collect(self::SECTION_TEXT)->mapWithKeys(fn (array $def, string $field) => [$def[0] => $data[$field] ?? null])->all();

        $values['shop.storefront.sections'] = array_values($data['sections'] ?? []);
        $values['shop.storefront.activities_source'] = $data['activities_source'];
        $values['shop.storefront.hero_rail'] = $request->boolean('hero_rail');
        $values['shop.storefront.why_points'] = collect(preg_split('/\R/', (string) ($data['why_points'] ?? '')))
            ->map(fn (string $line) => Str::limit(trim($line), 200, ''))
            ->filter()->take(count(Storefront::WHY_ICONS))->values()->all() ?: null;

        $changes = $this->settings->save($values);

        return redirect()->to(route('admin.storefront.edit').'#seksi')
            ->with('success', $changes ? 'Seksi beranda disimpan.' : 'Tidak ada perubahan.');
    }

    /** @return list<array{key: string, label: string, on: bool}> seksi tampil (berurutan) lalu yang disembunyikan */
    private function sectionItems(): array
    {
        $on = Storefront::sections();
        $off = array_diff(array_keys(Storefront::SECTIONS), $on);

        return array_map(fn (string $key) => [
            'key' => $key,
            'label' => Storefront::SECTIONS[$key],
            'on' => in_array($key, $on, true),
        ], [...$on, ...$off]);
    }

    /** Simpan mode & urutan produk Best Seller (form terpisah dari hero). */
    public function updateBestSellers(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'best_seller_mode' => ['required', 'in:auto,manual'],
            'best_seller_fill' => ['boolean'],
            'best_seller_products' => ['nullable', 'required_if:best_seller_mode,manual', 'array', 'max:'.BestSellers::MAX_PICKS],
            'best_seller_products.*' => ['integer', 'distinct', Rule::exists('products', 'id')],
        ], [
            'best_seller_products.max' => 'Pilih maksimal '.BestSellers::MAX_PICKS.' produk.',
            'best_seller_products.required_if' => 'Pilih minimal 1 produk untuk mode manual.',
        ], [
            'best_seller_products' => 'produk best seller',
        ]);

        $ids = array_map('intval', $data['best_seller_products'] ?? []);

        $changes = $this->settings->save([
            'shop.storefront.best_seller_mode' => $data['best_seller_mode'],
            'shop.storefront.best_seller_fill' => $request->boolean('best_seller_fill'),
            'shop.storefront.best_seller_products' => $ids,
        ]);

        return redirect()->to(route('admin.storefront.edit').'#best-seller')
            ->with('success', $changes ? 'Best Seller disimpan.' : 'Tidak ada perubahan.');
    }

    /** Pencarian produk untuk pemilih Best Seller (JSON). */
    public function searchProducts(Request $request): JsonResponse
    {
        $term = Str::limit(trim((string) $request->query('q')), 100, '');

        $products = Product::query()
            ->search($term)
            ->orderByDesc('is_active')->orderBy('name')
            ->take(20)->get();

        return response()->json($products->map(fn (Product $p) => $p->pickerPayload())->all());
    }

    /** @param  list<int>  $ids */
    private function pickedProducts(array $ids): array
    {
        $products = Product::whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)->map(fn (int $id) => $products->get($id))->filter()
            ->map(fn (Product $p) => $p->pickerPayload())->values()->all();
    }

    public function update(Request $request): RedirectResponse
    {
        $link = ['nullable', 'string', 'max:255', 'regex:#^(/(?!/)|https?://)#'];

        $data = $request->validate([
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=1200'],
            'hero_image_mobile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=600'],
            'remove_hero_image' => ['boolean'],
            'remove_hero_image_mobile' => ['boolean'],
            'hero_eyebrow' => ['nullable', 'string', 'max:60'],
            'hero_title' => ['nullable', 'string', 'max:90'],
            'hero_subtitle' => ['nullable', 'string', 'max:200'],
            'hero_primary_label' => ['nullable', 'string', 'max:30'],
            'hero_primary_url' => $link,
            'hero_secondary_label' => ['nullable', 'string', 'max:30'],
            'hero_secondary_url' => $link,
            'hero_text' => ['required', 'in:light,dark'],
            'announcements' => ['nullable', 'string', 'max:1000'],
        ], [
            'hero_primary_url.regex' => 'Awali dengan "/" untuk halaman toko (mis. /produk) atau https:// untuk link luar.',
            'hero_secondary_url.regex' => 'Awali dengan "/" untuk halaman toko (mis. /produk) atau https:// untuk link luar.',
            'hero_image.dimensions' => 'Foto hero minimal lebar 1200 px supaya tetap tajam di layar besar.',
            'hero_image_mobile.dimensions' => 'Foto versi ponsel minimal lebar 600 px.',
        ], [
            'hero_image' => 'foto hero', 'hero_image_mobile' => 'foto versi ponsel', 'hero_title' => 'judul',
            'hero_primary_url' => 'link tombol utama', 'hero_secondary_url' => 'link tombol kedua', 'announcements' => 'pengumuman',
        ]);

        $values = collect(self::TEXT)->mapWithKeys(fn (string $key, string $field) => [$key => $data[$field] ?? null])->all();

        $values['shop.storefront.announcements'] = collect(preg_split('/\R/', (string) ($data['announcements'] ?? '')))
            ->map(fn (string $line) => Str::limit(trim($line), 120, ''))
            ->filter()->take(6)->values()->all() ?: null;

        $obsolete = [];
        foreach (self::IMAGES as $field => $key) {
            $current = config($key);
            $uploaded = $current && Str::startsWith($current, Storefront::UPLOAD_DIR.'/');

            if ($request->hasFile($field)) {
                $values[$key] = $request->file($field)->store(Storefront::UPLOAD_DIR, 'public');
                $uploaded && $obsolete[] = $current;
            } elseif ($request->boolean('remove_'.$field)) {
                $values[$key] = null;
                $uploaded && $obsolete[] = $current;
            }
        }

        $changes = $this->settings->save($values);

        // Hapus file lama setelah pengaturan baru tersimpan.
        Storage::disk('public')->delete($obsolete);

        return back()->with('success', $changes ? 'Tampilan beranda disimpan.' : 'Tidak ada perubahan.');
    }
}
