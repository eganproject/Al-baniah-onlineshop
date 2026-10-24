<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Artikel contoh (tanpa gambar sampul; unggah sendiri lewat Admin › Artikel). */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['tips-latihan', 'Tips Latihan', 'Tips lari, yoga, pilates, dan workout supaya latihan lebih efektif dan aman.'],
            ['panduan-belanja', 'Panduan Belanja', 'Bingung memilih? Panduan singkat sebelum membeli perlengkapan olahraga.'],
            ['kabar-nufit', 'Kabar NuFit', 'Promo, koleksi baru, dan info layanan NuFit.'],
        ])->mapWithKeys(fn ($c, $i) => [$c[0] => ArticleCategory::updateOrCreate(['slug' => $c[0]], ['name' => $c[1], 'description' => $c[2], 'sort_order' => $i])]);

        $author = User::where('email', 'admin@nufit.test')->first();

        $articles = [
            [
                'slug' => 'persiapan-lari-pagi-untuk-pemula',
                'category' => 'tips-latihan',
                'title' => '7 Persiapan Lari Pagi untuk Pemula Supaya Tidak Cepat Lelah',
                'excerpt' => 'Baru mulai rutin lari? Persiapan kecil sebelum keluar rumah bikin lari lebih nyaman, aman, dan konsisten.',
                'tags' => ['lari', 'pemula', 'tips'],
                'products' => 'Running',
                'days' => 2,
                'body' => '<p>Lari adalah olahraga paling mudah dimulai: cukup sepatu dan niat. Tapi beberapa persiapan sederhana membuat bedanya terasa sejak kilometer pertama.</p>'
                    .'<h2>1. Pemanasan 5–10 menit</h2><p>Jalan cepat, leg swing, dan high knee membantu otot dan sendi siap bergerak sehingga risiko cedera berkurang.</p>'
                    .'<h2>2. Bawa barang seperlunya</h2><p>HP, kunci, dan kartu cukup disimpan di <strong>running belt</strong> supaya tidak memantul di saku. Tangan bebas, langkah lebih stabil.</p>'
                    .'<h2>3. Lindungi kepala dari matahari</h2><p>Topi visor yang cepat kering menahan keringat agar tidak masuk ke mata dan melindungi wajah dari sinar pagi.</p>'
                    .'<h3>Tanda kamu terlalu memaksakan diri</h3><ul><li>Tidak bisa berbicara sama sekali saat berlari</li><li>Pusing atau mual</li><li>Nyeri tajam di lutut atau tulang kering</li></ul>'
                    .'<blockquote><p>Mulai dengan pola lari-jalan: 1 menit lari, 2 menit jalan, ulangi 8 kali. Tingkatkan perlahan setiap minggu.</p></blockquote>'
                    .'<h2>4. Minum cukup</h2><p>Minum segelas air 30 menit sebelum lari dan bawa botol untuk sesi lebih dari 45 menit.</p>',
            ],
            [
                'slug' => 'panduan-memilih-matras-yoga',
                'category' => 'panduan-belanja',
                'title' => 'Panduan Memilih Matras Yoga: Bahan, Ketebalan, dan Grip',
                'excerpt' => 'TPE, NBR, atau PVC? Tebal 4mm atau 6mm? Kenali perbedaannya sebelum membeli matras yoga pertamamu.',
                'tags' => ['yoga', 'matras', 'panduan'],
                'products' => 'Yoga',
                'days' => 6,
                'body' => '<p>Matras adalah perlengkapan yoga yang paling sering dipakai. Matras yang tepat membuat pose lebih stabil dan sendi lebih nyaman.</p>'
                    .'<h2>Pilih bahan sesuai kebutuhan</h2><ol><li><strong>TPE</strong>: ringan, empuk, ramah lingkungan. Cocok untuk pemula.</li><li><strong>NBR</strong>: paling empuk, pas untuk pilates dan stretching.</li><li><strong>PVC</strong>: awet dan grip kuat, sedikit lebih berat.</li></ol>'
                    .'<h2>Ketebalan</h2><p>6mm nyaman untuk lutut dan cocok dipakai di lantai keras. 4mm lebih stabil untuk pose berdiri dan mudah dibawa.</p>'
                    .'<h2>Perawatan</h2><p>Lap dengan kain lembap setelah dipakai, keringkan di tempat teduh, dan jangan dilipat supaya permukaannya tidak retak.</p>',
            ],
            [
                'slug' => 'nufit-kini-hadir-online',
                'category' => 'kabar-nufit',
                'title' => 'NuFit Kini Hadir Online: Belanja Perlengkapan Olahraga Lebih Mudah',
                'excerpt' => 'Sekarang kamu bisa belanja perlengkapan olahraga NuFit langsung dari website, lengkap dengan pembayaran aman dan lacak pesanan.',
                'tags' => ['info toko', 'promo'],
                'products' => null,
                'days' => 10,
                'body' => '<p>Kabar gembira! Toko online resmi NuFit sudah bisa dipakai untuk berbelanja langsung dari rumah. <strong>Healthy Body, Healthy Mind, Happy Life.</strong></p>'
                    .'<h2>Yang bisa kamu lakukan</h2><ul><li>Bayar lewat transfer bank, e-wallet, QRIS, atau kartu kredit</li><li>Pilih kurir favorit dan lihat ongkir sebelum bayar</li><li>Lacak pesanan langsung dari halaman akun</li><li>Simpan produk incaran di wishlist</li></ul>'
                    .'<p>Pakai kode <strong>HEMAT10</strong> untuk diskon 10% di pesanan pertamamu. Jangan lupa bagikan momen latihanmu dengan tagar <strong>#BEFITWITHNUFIT</strong>!</p>',
            ],
        ];

        foreach ($articles as $data) {
            $article = Article::updateOrCreate(['slug' => $data['slug']], [
                'article_category_id' => $categories[$data['category']]->id,
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'body' => $data['body'],
                'tags' => $data['tags'],
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDays($data['days'])->setTime(9, 0),
                'reading_minutes' => Article::readingMinutes($data['body']),
            ]);

            if ($author && ! $article->author_id) {
                $article->author()->associate($author)->save();
            }

            if ($data['products']) {
                $ids = Product::active()->whereHas('category', fn ($q) => $q->where('name', $data['products'])->orWhereHas('parent', fn ($p) => $p->where('name', $data['products'])))->take(4)->pluck('id');
                $article->products()->sync($ids->values()->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i]])->all());
            }
        }
    }
}
