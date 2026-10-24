<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Enums\StaffRole;
use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Enums\VoucherType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\Voucher;
use App\Services\Catalog\StockService;
use App\Services\Reviews\ReviewService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function __construct(private readonly StockService $stock) {}

    public function run(): void
    {
        // Data contoh (akun berpassword "password", pesanan palsu) tidak boleh masuk server produksi.
        if (app()->isProduction()) {
            $this->command?->error('Seeder contoh tidak dijalankan di produksi. Buat akun Superadmin dengan: php artisan shop:create-admin');

            return;
        }

        User::updateOrCreate(['email' => 'admin@nufit.test'], [
            'name' => 'Admin NuFit',
            'password' => 'password',
            'phone' => '081200000000',
            'role' => UserRole::Admin,
            'staff_role' => StaffRole::Owner,
            'email_verified_at' => now(),
        ]);

        // Contoh staf dengan akses terbatas.
        foreach ([
            ['gudang@nufit.test', 'Rudi Hartono', StaffRole::Warehouse],
            ['cs@nufit.test', 'Sari Wulandari', StaffRole::CustomerService],
        ] as [$email, $name, $staffRole]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => 'password',
                'role' => UserRole::Admin,
                'staff_role' => $staffRole,
                'email_verified_at' => now(),
            ]);
        }

        $customer = User::updateOrCreate(['email' => 'pelanggan@nufit.test'], [
            'name' => 'Budi Santoso',
            'password' => 'password',
            'phone' => '081311112222',
            'role' => UserRole::Customer,
            'email_verified_at' => now(),
        ]);

        if (! $customer->addresses()->exists()) {
            $customer->addresses()->create([
                'label' => 'Rumah',
                'recipient_name' => 'Budi Santoso',
                'phone' => '081311112222',
                'province_id' => '2',
                'province' => 'Jawa Barat',
                'city_id' => '201',
                'city' => 'Kota Bandung',
                'district_id' => '20101',
                'district' => 'Coblong',
                'postal_code' => '40132',
                'address_line' => 'Jl. Dago No. 10, RT 02/RW 05',
                'is_default' => true,
            ]);
        }

        $this->seedCatalog();
        // Tanpa event model supaya ratusan pesanan contoh tidak mengantrekan email.
        Order::withoutEvents(fn () => $this->call(DemoOrderSeeder::class));
        $this->seedReviews();
        $this->seedVouchers();
        $this->call(ArticleSeeder::class);
    }

    /** Voucher contoh (banner perlu gambar, jadi diunggah sendiri lewat admin). */
    private function seedVouchers(): void
    {
        $vouchers = [
            ['code' => 'HEMAT10', 'name' => 'Diskon 10% semua produk', 'type' => VoucherType::Percent, 'value' => 10, 'max_discount' => 25000, 'min_spend' => 100000, 'per_user_limit' => 1],
            ['code' => 'ONGKIRGRATIS', 'name' => 'Gratis ongkir s.d. Rp20.000', 'type' => VoucherType::FreeShipping, 'value' => 20000, 'min_spend' => 75000, 'per_user_limit' => 3],
            ['code' => 'NUFIT50K', 'name' => 'Potongan Rp50.000', 'type' => VoucherType::Fixed, 'value' => 50000, 'min_spend' => 500000, 'quota' => 100, 'per_user_limit' => 1],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(['code' => $voucher['code']], $voucher + ['is_active' => true, 'is_public' => true]);
        }
    }

    /** Contoh ulasan yang sudah tayang supaya tampilan rating terlihat saat demo. */
    private function seedReviews(): void
    {
        $samples = [
            [5, 'Mantap, sesuai deskripsi', 'Barang original, packing rapi pakai bubble wrap. Pengiriman cepat, besoknya sudah sampai.'],
            [4, 'Bagus', 'Kualitas oke untuk harganya. Pengiriman agak lama karena kurir, tapi seller responsif.'],
            [5, 'Recommended seller', 'Sudah beli kedua kalinya di sini, selalu puas. Admin ramah dan membantu pilih tipe yang cocok.'],
        ];

        $reviewers = collect(['Rina Wulandari', 'Agus Pratama', 'Dewi Lestari'])->map(fn ($name) => User::firstOrCreate(
            ['email' => Str::slug($name, '.').'@contoh.test'],
            ['name' => $name, 'password' => 'password', 'email_verified_at' => now()],
        ));

        $reviewService = app(ReviewService::class);

        Product::where('is_featured', true)->get()->each(function (Product $product) use ($reviewers, $samples, $reviewService) {
            foreach ($reviewers as $i => $reviewer) {
                [$rating, $title, $body] = $samples[($product->id + $i) % count($samples)];

                ProductReview::firstOrNew(['user_id' => $reviewer->id, 'product_id' => $product->id])->forceFill([
                    'user_id' => $reviewer->id,
                    'product_id' => $product->id,
                    'rating' => $rating,
                    'title' => $title,
                    'body' => $body,
                    'status' => ReviewStatus::Approved,
                    'approved_at' => now()->subDays($i * 3),
                ])->save();
            }

            $reviewService->syncSummary($product);
        });
    }

    private function seedCatalog(): void
    {
        // Kategori => [subkategori => [[nama, harga, harga coret|null, berat gram, unggulan]]].
        // Kunci subkategori '' = produk langsung di kategori induk.
        $catalog = [
            'Running' => [
                'Running Belt' => [
                    ['Versa Running Belt', 78000, 99000, 120, true],
                    ['Running Belt Hydro dengan Botol 250ml', 135000, 159000, 260, false],
                ],
                'Topi & Visor' => [
                    ['Topi Visor Hat Quick Dry', 63000, 79000, 80, true],
                    ['Running Cap Ultralight', 85000, null, 70, false],
                ],
                'Kaos Kaki & Manset' => [
                    ['Kaos Kaki Lari Anti Lecet', 39000, null, 60, false],
                    ['Manset Lengan UV Protection', 45000, 59000, 50, false],
                ],
            ],
            'Yoga & Pilates' => [
                '' => [
                    ['Nu Shape Aura Flare Yoga Pants', 112000, 149000, 250, true],
                    ['Matras Yoga TPE 6mm', 189000, 239000, 900, true],
                    ['Pilates Ring Magic Circle', 99000, null, 450, false],
                    ['Yoga Block EVA', 55000, null, 200, false],
                ],
            ],
            'Workout' => [
                '' => [
                    ['Wrist & Ankle Weight Bangles', 209000, 249000, 1100, true],
                    ['Skipping Rope Premium', 69000, 89000, 250, false],
                    ['Resistance Band Set 5 Level', 129000, 169000, 400, true],
                    ['Gym Gloves Grip Pro', 89000, null, 150, false],
                ],
            ],
            'Padel' => [
                '' => [
                    ['Bola Padel Isi 3', 90000, null, 200, true],
                    ['Headband Padel Sweat Band', 35000, 45000, 40, false],
                    ['Overgrip Raket Padel (3 pcs)', 49000, null, 60, false],
                ],
            ],
            'Swimming' => [
                '' => [
                    ['Silicone Swimming Cap', 42000, 49000, 80, true],
                    ['Kacamata Renang Anti Fog', 79000, 99000, 120, false],
                    ['Earplug & Nose Clip Renang', 29000, null, 30, false],
                ],
            ],
            'Support & Recovery' => [
                '' => [
                    ['Kinesiology Tape 5cm x 5m', 41000, null, 90, true],
                    ['Cohesive Bandage Elastis', 19000, null, 40, false],
                    ['Knee Support Compression', 95000, 119000, 150, false],
                ],
            ],
            'Essentials' => [
                '' => [
                    ['Botol Minum Sport 750ml BPA Free', 75000, 95000, 200, true],
                    ['Travel Pouch Serbaguna', 19000, null, 60, false],
                    ['Handuk Microfiber Quick Dry', 59000, 75000, 180, false],
                ],
            ],
            'Apparel' => [
                '' => [
                    ['Nu Shape Aura Jacket with Hoodie', 228000, 289000, 350, true],
                    ['Sport Bra Medium Support', 135000, null, 150, false],
                    ['Rok Olahraga Skort 2-in-1', 149000, 179000, 220, false],
                ],
            ],
        ];

        $order = 0;
        foreach ($catalog as $categoryName => $groups) {
            $category = Category::updateOrCreate(['slug' => Str::slug($categoryName)], [
                'name' => $categoryName,
                'description' => "Perlengkapan {$categoryName} pilihan NuFit untuk latihan yang lebih nyaman.",
                'is_active' => true,
                'sort_order' => $order++,
            ]);

            foreach ($groups as $subName => $products) {
                $target = $subName === '' ? $category : Category::updateOrCreate(['slug' => Str::slug($subName)], [
                    'parent_id' => $category->id,
                    'name' => $subName,
                    'is_active' => true,
                ]);

                foreach ($products as [$name, $price, $compare, $weight, $featured]) {
                    $this->seedProduct($target, $name, $price, $compare, $weight, $featured);
                }
            }
        }
    }

    private function seedProduct(Category $category, string $name, int $price, ?int $compare, int $weight, bool $featured): void
    {
        $product = Product::updateOrCreate(['slug' => Str::slug($name)], [
            'category_id' => $category->id,
            'name' => $name,
            'sku' => 'NF-'.Str::upper(Str::substr(md5($name), 0, 6)),
            'description' => "{$name} dari NuFit, nyaman dipakai untuk latihan rutin maupun aktivitas harian.\n\n• Bahan berkualitas & awet\n• Garansi tukar 7 hari bila cacat produksi\n• Siap kirim hari ini",
            'price' => $price,
            'compare_at_price' => $compare,
            'weight' => $weight,
            'is_active' => true,
            'is_featured' => $featured,
        ]);

        if (! $product->wasRecentlyCreated) {
            return;
        }

        if (isset(self::VARIANTS[$name])) {
            [$label, $variants] = self::VARIANTS[$name];
            $product->update(['variant_label' => $label]);

            foreach ($variants as $position => [$variantName, $variantPrice]) {
                $variant = $product->variants()->create([
                    'name' => $variantName,
                    'sku' => $product->sku.'-'.Str::upper(Str::slug($variantName, '')),
                    'price' => $variantPrice ?? $price,
                    'compare_at_price' => $compare ? $compare + (($variantPrice ?? $price) - $price) : null,
                    'is_active' => true,
                    'sort_order' => $position,
                ]);
                $this->stock->adjust($product, $variant, random_int(0, 5) === 0 ? 0 : random_int(5, 40), StockMovementType::Initial);
            }
        } else {
            $this->stock->adjust($product, null, random_int(0, 4) === 0 ? random_int(1, 4) : random_int(10, 80), StockMovementType::Initial);
        }
    }

    /** Contoh varian: nama produk => [judul pilihan, [[nama, harga|null]]]. */
    private const VARIANTS = [
        'Nu Shape Aura Flare Yoga Pants' => ['Ukuran', [['S', null], ['M', null], ['L', null], ['XL', 119000]]],
        'Nu Shape Aura Jacket with Hoodie' => ['Warna', [['Black', null], ['Navy', null], ['Mocca', null], ['Smoky Grey', null]]],
        'Wrist & Ankle Weight Bangles' => ['Berat', [['0,5 kg', 121000], ['1 kg', null], ['1,5 kg', 259000]]],
        'Silicone Swimming Cap' => ['Warna', [['Hitam', null], ['Biru', null], ['Pink', null]]],
        'Matras Yoga TPE 6mm' => ['Warna', [['Purple', null], ['Grey', null], ['Blue', null]]],
    ];
}
