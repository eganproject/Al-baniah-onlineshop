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
use App\Models\ProductCollection;
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

        User::updateOrCreate(['email' => 'admin@albaniah.test'], [
            'name' => 'Admin Albaniah',
            'password' => 'password',
            'phone' => '081200000000',
            'role' => UserRole::Admin,
            'staff_role' => StaffRole::Owner,
            'email_verified_at' => now(),
        ]);

        // Contoh staf dengan akses terbatas.
        foreach ([
            ['gudang@albaniah.test', 'Rudi Hartono', StaffRole::Warehouse],
            ['cs@albaniah.test', 'Sari Wulandari', StaffRole::CustomerService],
        ] as [$email, $name, $staffRole]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => 'password',
                'role' => UserRole::Admin,
                'staff_role' => $staffRole,
                'email_verified_at' => now(),
            ]);
        }

        $customer = User::updateOrCreate(['email' => 'pelanggan@albaniah.test'], [
            'name' => 'Aisyah Putri',
            'password' => 'password',
            'phone' => '081311112222',
            'role' => UserRole::Customer,
            'email_verified_at' => now(),
        ]);

        if (! $customer->addresses()->exists()) {
            $customer->addresses()->create([
                'label' => 'Rumah',
                'recipient_name' => 'Aisyah Putri',
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
        $this->seedCollections();
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
            ['code' => 'BERKAH10', 'name' => 'Diskon 10% pembelian pertama', 'type' => VoucherType::Percent, 'value' => 10, 'max_discount' => 30000, 'min_spend' => 100000, 'per_user_limit' => 1],
            ['code' => 'ONGKIRGRATIS', 'name' => 'Gratis ongkir s.d. Rp20.000', 'type' => VoucherType::FreeShipping, 'value' => 20000, 'min_spend' => 150000, 'per_user_limit' => 3],
            ['code' => 'ALBANIAH50K', 'name' => 'Potongan Rp50.000', 'type' => VoucherType::Fixed, 'value' => 50000, 'min_spend' => 400000, 'quota' => 100, 'per_user_limit' => 1],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(['code' => $voucher['code']], $voucher + ['is_active' => true, 'is_public' => true]);
        }
    }

    /** Contoh ulasan yang sudah tayang supaya tampilan rating terlihat saat demo. */
    private function seedReviews(): void
    {
        $samples = [
            [5, 'Adem dan jatuh, MasyaAllah', 'Bahannya adem, tidak menerawang, dan jahitannya rapi. Dipakai tarawih tidak gerah sama sekali. Packing juga rapi pakai plastik klip.'],
            [4, 'Cantik, warnanya sesuai foto', 'Warna sesuai foto, ukurannya pas untuk tinggi 160 cm. Pengiriman agak lama karena kurir, tapi admin responsif.'],
            [5, 'Beli lagi untuk hadiah ibu', 'Sudah beli kedua kalinya, kali ini untuk kado ibu. Dapat tas kecil, praktis dibawa ke masjid. Recommended!'],
        ];

        $reviewers = collect(['Rina Wulandari', 'Nur Aisyah', 'Dewi Lestari'])->map(fn ($name) => User::firstOrCreate(
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
        // Kategori => [subkategori => [nama produk, ...]]. Kunci subkategori '' = produk langsung di kategori induk.
        $catalog = [
            'Mukena Dewasa' => [
                'Mukena Renda' => ['Mukena Dewasa Renda Susun Dayana Series Sutra Velvet Premium', 'Mukena Renda Crinkle Airflow 3 in 1 Jumbo Inaya', 'Mukena Renda Mewah Safa Dewasa Resleting Sutra Velvet'],
                'Mukena Bordir' => ['Mukena Katun Bordir Dewasa Khadijah'],
                'Mukena Polos & Premium' => ['Mukena Dewasa Muslim Premium', 'Mukena Albaniah Basic'],
            ],
            'Mukena Tutup Dagu' => [
                '' => ['Mukena Tutup Dagu Polos Malaysia Jersey Lembut Nyaman Adiba Dewasa', 'Mukena Tutup Dagu Motif Printing Malaysia Royal Adem Nyaman Lembut Azeela'],
            ],
            'Mukena Travel' => [
                '' => ['Mukena Dewasa Katun Travelling Polos Almira', 'Mukena Travel Katun Mikro 2 In 1 Alifa', 'Mukena Travel Katun Mikro 2in1', 'Mukena Crinkle 3in1'],
            ],
            'Perlengkapan Ibadah' => [
                '' => ['Kotak Kado Gift Box dan Paper Bag Hadiah'],
            ],
        ];

        $descriptions = [
            'Mukena Dewasa' => 'Mukena dewasa Albaniah dengan bahan adem pilihan dan jahitan rapi, nyaman dipakai untuk salat sehari-hari maupun hari raya.',
            'Mukena Tutup Dagu' => 'Mukena model Malaysia dengan penutup dagu, lebih rapat dan rapi tanpa perlu peniti.',
            'Mukena Travel' => 'Mukena ringkas yang mudah dilipat ke dalam tas, teman ibadah saat bepergian, mudik, dan umrah.',
            'Perlengkapan Ibadah' => 'Pelengkap ibadah dan hadiah: kotak kado, tas mukena, dan perlengkapan lainnya.',
        ];

        $order = 0;
        foreach ($catalog as $categoryName => $groups) {
            $category = Category::updateOrCreate(['slug' => Str::slug($categoryName)], [
                'name' => $categoryName,
                'description' => $descriptions[$categoryName],
                'is_active' => true,
                'sort_order' => $order++,
            ]);

            foreach ($groups as $subName => $products) {
                $target = $subName === '' ? $category : Category::updateOrCreate(['slug' => Str::slug($subName)], [
                    'parent_id' => $category->id,
                    'name' => $subName,
                    'is_active' => true,
                ]);

                foreach ($products as $name) {
                    $this->seedProduct($target, $name, self::PRODUCTS[$name]);
                }
            }
        }
    }

    /** Koleksi contoh: satu produk bisa masuk beberapa koleksi (mis. mukena travel di "Travel & Umrah" dan "Sehari-hari"). */
    private function seedCollections(): void
    {
        $collections = [
            ['Renda & Bordir', 'lace', 'ink', 'Detail renda dan bordir yang anggun untuk hari istimewa', ['mukena-renda', 'mukena-bordir']],
            ['Travel & Umrah', 'travel', null, 'Ringkas dibawa, tetap nyaman dipakai', ['mukena-travel']],
            ['Tutup Dagu Malaysia', 'veil', null, null, ['mukena-tutup-dagu']],
            ['Premium Velvet', 'premium', null, null, ['mukena-dewasa-renda-susun-dayana-series-sutra-velvet-premium', 'mukena-renda-mewah-safa-dewasa-resleting-sutra-velvet', 'mukena-dewasa-muslim-premium']],
            ['Hadiah Spesial', 'gift', null, 'Kado penuh makna untuk ibu, istri, dan sahabat', ['perlengkapan-ibadah', 'mukena-katun-bordir-dewasa-khadijah', 'mukena-dewasa-renda-susun-dayana-series-sutra-velvet-premium', 'mukena-renda-crinkle-airflow-3-in-1-jumbo-inaya']],
            ['Sehari-hari', 'mukena', null, null, ['mukena-albaniah-basic', 'mukena-dewasa-katun-travelling-polos-almira', 'mukena-tutup-dagu-polos-malaysia-jersey-lembut-nyaman-adiba-dewasa', 'mukena-crinkle-3in1']],
        ];

        foreach ($collections as $order => [$name, $icon, $tone, $tagline, $members]) {
            $collection = ProductCollection::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'tagline' => $tagline,
                'description' => "Koleksi {$name} pilihan Albaniah, dari berbagai kategori.",
                'icon' => $icon,
                'tone' => $tone,
                'is_active' => true,
                'show_on_home' => true,
                'sort_order' => $order,
            ]);

            // Slug kategori = semua produk di kategori itu (termasuk subkategori); selain itu slug produk.
            $ids = collect($members)->flatMap(function (string $slug) {
                $category = Category::where('slug', $slug)->first();

                return $category
                    ? Product::whereIn('category_id', $category->familyIds())->pluck('id')
                    : Product::where('slug', $slug)->pluck('id');
            })->unique()->all();

            $collection->products()->sync($ids);
        }
    }

    /** @param  array{price: int, compare: ?int, weight: int, featured: bool, sku: string, text: string, images: list<string>, variants?: array{0: string, 1: list<array{0: string, 1: ?int}>}}  $data */
    private function seedProduct(Category $category, string $name, array $data): void
    {
        $product = Product::updateOrCreate(['slug' => Str::slug($name)], [
            'category_id' => $category->id,
            'name' => $name,
            'sku' => $data['sku'],
            'description' => $data['text']."\n\n• Jahitan rapi, dicek satu per satu sebelum dikirim\n• Garansi tukar 7 hari bila ada cacat produksi\n• Bisa request dibungkus kado (tulis di catatan pesanan)\n\nWarna di layar bisa sedikit berbeda dengan aslinya karena pencahayaan foto.",
            'price' => $data['price'],
            'compare_at_price' => $data['compare'],
            'weight' => $data['weight'],
            'is_active' => true,
            'is_featured' => $data['featured'],
        ]);

        if (! $product->wasRecentlyCreated) {
            return;
        }

        // Foto contoh = URL CDN marketplace (Shopee) Albaniah; ganti dengan unggahan sendiri lewat admin.
        foreach ($data['images'] as $position => $url) {
            $product->images()->create(['path' => $url, 'sort_order' => $position]);
        }

        if (isset($data['variants'])) {
            [$label, $variants] = $data['variants'];
            $product->update(['variant_label' => $label]);

            foreach ($variants as $position => [$variantName, $variantPrice]) {
                $price = $variantPrice ?? $data['price'];
                $variant = $product->variants()->create([
                    'name' => $variantName,
                    'sku' => $product->sku.'-'.Str::upper(Str::slug($variantName, '')),
                    'price' => $price,
                    'compare_at_price' => $data['compare'] ? $data['compare'] + ($price - $data['price']) : null,
                    'is_active' => true,
                    'sort_order' => $position,
                ]);
                $this->stock->adjust($product, $variant, random_int(0, 6) === 0 ? 0 : random_int(5, 40), StockMovementType::Initial);
            }
        } else {
            $this->stock->adjust($product, null, random_int(0, 4) === 0 ? random_int(1, 4) : random_int(15, 90), StockMovementType::Initial);
        }
    }

    private const CDN = 'https://down-id.img.susercontent.com/file/';

    /**
     * Produk contoh dari toko Albaniah di marketplace (nama & foto asli; harga = harga
     * coret dan harga jual yang tampil di listing, sebagian diperkirakan).
     */
    private const PRODUCTS = [
        'Mukena Dewasa Renda Susun Dayana Series Sutra Velvet Premium' => [
            'price' => 189000, 'compare' => 400000, 'weight' => 800, 'featured' => true, 'sku' => 'ALB-DAYANA',
            'text' => 'Mukena dewasa seri Dayana dengan renda susun bertingkat di bagian wajah dan bawahan. Bahan sutra velvet premium yang jatuh, lembut, dan tidak menerawang, membuat tampilan anggun saat salat maupun hari raya.',
            'images' => [self::CDN.'id-11134201-7qul4-lev7osw6qo1p57', self::CDN.'id-11134201-7quky-lev7ox16omg0de', self::CDN.'id-11134201-7qul5-lev7p0epqru9a3', self::CDN.'id-11134201-7qul7-lev7p3lv29o170', self::CDN.'id-11134201-7qula-lev7p5uls471dc'],
            'variants' => ['Warna', [['Putih', null], ['Silver', null], ['Lavender', null], ['Nude', null], ['Mocca', null]]],
        ],
        'Mukena Renda Crinkle Airflow 3 in 1 Jumbo Inaya' => [
            'price' => 165000, 'compare' => 300000, 'weight' => 700, 'featured' => true, 'sku' => 'ALB-INAYA',
            'text' => 'Mukena Inaya ukuran jumbo dari bahan crinkle airflow yang ringan, adem, dan tidak perlu disetrika. Set 3 in 1: atasan, bawahan, dan tas serut, dihiasi renda manis di sekeliling wajah.',
            'images' => [self::CDN.'id-11134207-7r990-lp1osxu91ibb75', self::CDN.'id-11134207-7r98z-lp1osxu8xalza5', self::CDN.'id-11134207-7r98t-lp1osxu8yp6fbf', self::CDN.'id-11134207-7r98z-lp1osxu903qv12', self::CDN.'id-11134207-7r98w-lp1osxu92wvre2'],
            'variants' => ['Warna', [['Putih', null], ['Cream', null], ['Dusty Pink', null], ['Sage', null], ['Lilac', null]]],
        ],
        'Mukena Renda Mewah Safa Dewasa Resleting Sutra Velvet' => [
            'price' => 174200, 'compare' => 350000, 'weight' => 750, 'featured' => false, 'sku' => 'ALB-SAFA',
            'text' => 'Mukena Safa berbahan sutra velvet dengan renda mewah dan resleting praktis, jadi mudah dipakai dan dilipat. Kain lembut, jatuh, dan berkilau halus untuk tampilan yang anggun.',
            'images' => [],
            'variants' => ['Warna', [['Wardah', null], ['Silver', null], ['Lavender', null], ['Nude', null], ['Broken White', null], ['Putih', null]]],
        ],
        'Mukena Katun Bordir Dewasa Khadijah' => [
            'price' => 124600, 'compare' => 250000, 'weight' => 650, 'featured' => true, 'sku' => 'ALB-KHADIJAH',
            'text' => 'Mukena Khadijah dari katun yang menyerap keringat dengan bordir halus di bagian dahi dan ujung bawahan. Adem dipakai lama, cocok untuk tarawih dan ibadah sehari-hari.',
            'images' => [self::CDN.'id-11134207-7qul3-ljxuddvq58ve4b', self::CDN.'id-11134207-7qul6-ljxuddvq6nfuea', self::CDN.'id-11134207-7qukw-ljxuddvq820ad4', self::CDN.'6cdea1c07e6ff1e38c517efd55357051', self::CDN.'0610e8f9c68f1db554eaba4a8391d522'],
            'variants' => ['Warna', [['Putih', null], ['Broken White', null], ['Mocca', null], ['Dusty Pink', null]]],
        ],
        'Mukena Dewasa Muslim Premium' => [
            'price' => 185000, 'compare' => 293000, 'weight' => 700, 'featured' => false, 'sku' => 'ALB-PREMIUM',
            'text' => 'Mukena dewasa premium dengan potongan lebar dan bahan lembut yang tidak menerawang. Pilihan tepat untuk dipakai sendiri maupun dijadikan hadiah.',
            'images' => [self::CDN.'sg-11134275-82580-mq90b824wa9u5a', self::CDN.'sg-11134275-8259v-mq90b7qz30ge9f', self::CDN.'sg-11134275-8258b-mq90b82tzhfld1', self::CDN.'sg-11134275-825au-mq90b7x5x1q825'],
        ],
        'Mukena Albaniah Basic' => [
            'price' => 80000, 'compare' => null, 'weight' => 550, 'featured' => false, 'sku' => 'ALB-BASIC',
            'text' => 'Mukena dewasa model basic dengan potongan simpel dan bahan adem. Nyaman untuk salat lima waktu di rumah maupun di kantor.',
            'images' => [self::CDN.'id-11134207-7rbkd-mapq2k3x7uhq20'],
        ],
        'Mukena Tutup Dagu Polos Malaysia Jersey Lembut Nyaman Adiba Dewasa' => [
            'price' => 136770, 'compare' => 350000, 'weight' => 600, 'featured' => true, 'sku' => 'ALB-ADIBA',
            'text' => 'Mukena Adiba model Malaysia dengan tutup dagu, dari bahan jersey yang lembut, stretch, dan nyaman. Wajah tertutup rapi tanpa perlu peniti, terlaris di toko kami.',
            'images' => [self::CDN.'sg-11134275-824i6-meec5uj1nzeobd', self::CDN.'sg-11134275-824j9-meec5uioy3ur76', self::CDN.'sg-11134275-824gb-meec5uhn6cjp85', self::CDN.'sg-11134275-824g0-meec5ut1knbb3e', self::CDN.'sg-11134275-824ie-meec5uy1hibr39'],
            'variants' => ['Warna', [['Putih', null], ['Dark Chocolate', null], ['Navy', null], ['Mocca', null], ['Sage', null], ['Maroon', null]]],
        ],
        'Mukena Tutup Dagu Motif Printing Malaysia Royal Adem Nyaman Lembut Azeela' => [
            'price' => 159000, 'compare' => 350000, 'weight' => 600, 'featured' => false, 'sku' => 'ALB-AZEELA',
            'text' => 'Mukena Azeela model Malaysia dengan tutup dagu dan motif printing yang manis. Bahan royal yang adem dan lembut, cocok untuk dipakai setiap hari.',
            'images' => [self::CDN.'id-11134207-7rasj-m4g19ooph4mb87', self::CDN.'id-11134207-7rasf-m4g19oopfq1v80', self::CDN.'id-11134207-7ras8-m4g19oop8op8d0', self::CDN.'id-11134207-7rasd-m4g19oopjxr762', self::CDN.'id-11134207-7rasg-m4g19oopij6r0d'],
            'variants' => ['Motif', [['Motif A', null], ['Motif B', null], ['Motif C', null]]],
        ],
        'Mukena Dewasa Katun Travelling Polos Almira' => [
            'price' => 95000, 'compare' => 230000, 'weight' => 450, 'featured' => true, 'sku' => 'ALB-ALMIRA',
            'text' => 'Mukena travelling Almira dari katun polos yang ringan dan cepat kering. Dilipat ringkas ke dalam tas kecil, praktis dibawa kerja, mudik, maupun umrah.',
            'images' => [self::CDN.'id-11134207-7r98q-ln4498d0z0e941', self::CDN.'id-11134207-7qul4-ljapp6e1oz97b8', self::CDN.'id-11134207-7qul2-ljapp6e1ozhfa4', self::CDN.'id-11134207-7qul6-ljapp6e1rsmb07', self::CDN.'sg-11134201-22100-abkvet2sfjiv6c'],
            'variants' => ['Warna', [['Navy', null], ['Mocca', null], ['Sage', null], ['Lilac', null], ['Hitam', null]]],
        ],
        'Mukena Travel Katun Mikro 2 In 1 Alifa' => [
            'price' => 79000, 'compare' => 150000, 'weight' => 400, 'featured' => false, 'sku' => 'ALB-ALIFA',
            'text' => 'Mukena travel Alifa dari katun mikro yang tipis, halus, dan tidak gerah. Set 2 in 1: mukena terusan dan tas, muat di tas tangan.',
            'images' => [self::CDN.'id-11134201-81ztq-mq9je49zkdfxbb', self::CDN.'id-11134201-81ztf-mq9je604c4xu04', self::CDN.'id-11134201-81ztj-mq9je6z1bdvv65', self::CDN.'id-11134201-81ztf-mq9je9fp3pc27a'],
        ],
        'Mukena Travel Katun Mikro 2in1' => [
            'price' => 65000, 'compare' => 99000, 'weight' => 380, 'featured' => false, 'sku' => 'ALB-MIKRO',
            'text' => 'Mukena travel katun mikro 2in1 yang ringan dan ringkas. Pilihan hemat untuk disimpan di mobil, kantor, atau tas sehari-hari.',
            'images' => [self::CDN.'id-11134207-7rasl-m3h99rga3jps63', self::CDN.'id-11134207-7rash-m3h8cclso73i1b', self::CDN.'id-11134207-7rasa-m3h99rga255ceb', self::CDN.'id-11134207-7rasg-m3h8cclsldym6b'],
            'variants' => ['Warna', [['Purple', null], ['Navy', null], ['Mocca', null], ['Grey', null]]],
        ],
        'Mukena Crinkle 3in1' => [
            'price' => 65000, 'compare' => 120000, 'weight' => 450, 'featured' => false, 'sku' => 'ALB-CRINKLE',
            'text' => 'Mukena crinkle 3in1 (atasan, bawahan, tas) dari bahan crinkle yang tidak mudah kusut, jadi tetap rapi meski dibawa bepergian.',
            'images' => [self::CDN.'id-11134207-82252-mivb7rpyo3ye3b', self::CDN.'id-11134207-8224w-mivb7rq5ys5dbc', self::CDN.'id-11134207-8224q-mivb7rq61la929', self::CDN.'id-11134207-8224x-mivb7rq64ef553'],
        ],
        'Kotak Kado Gift Box dan Paper Bag Hadiah' => [
            'price' => 46000, 'compare' => null, 'weight' => 300, 'featured' => false, 'sku' => 'ALB-GIFTBOX',
            'text' => 'Kotak kado eksklusif Albaniah untuk membungkus mukena sebagai hadiah lebaran, ulang tahun, atau seserahan. Tersedia pilihan dengan paper bag.',
            'images' => [self::CDN.'sg-11134275-824iz-meec5uwzveva5b', self::CDN.'sg-11134275-824gi-meec5v5swb2af4', self::CDN.'sg-11134275-824iz-meec5v487bwm53', self::CDN.'sg-11134275-824gf-meec5vb6mgox89'],
            'variants' => ['Pilihan', [['Gift Box', null], ['Gift Box + Paper Bag', 53000]]],
        ],
    ];
}
