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
            ['inspirasi-ibadah', 'Inspirasi Ibadah', 'Tips dan inspirasi supaya ibadah sehari-hari terasa lebih tenang dan khusyuk.'],
            ['panduan-belanja', 'Panduan Belanja', 'Bingung memilih? Panduan singkat memilih bahan, model, dan ukuran mukena.'],
            ['kabar-albaniah', 'Kabar Albaniah', 'Promo, koleksi baru, dan info layanan Albaniah.'],
        ])->mapWithKeys(fn ($c, $i) => [$c[0] => ArticleCategory::updateOrCreate(['slug' => $c[0]], ['name' => $c[1], 'description' => $c[2], 'sort_order' => $i])]);

        $author = User::where('email', 'admin@albaniah.test')->first();

        $articles = [
            [
                'slug' => 'cara-merawat-mukena-renda-agar-awet',
                'category' => 'inspirasi-ibadah',
                'title' => 'Cara Merawat Mukena Renda agar Tetap Putih dan Awet',
                'excerpt' => 'Mukena renda mudah tersangkut dan menguning bila salah cuci. Ikuti langkah sederhana ini supaya mukena favoritmu tetap cantik bertahun-tahun.',
                'tags' => ['perawatan', 'mukena renda', 'tips'],
                'products' => 'Mukena Dewasa',
                'days' => 2,
                'body' => '<p>Mukena adalah pakaian yang kita kenakan saat menghadap Allah, jadi wajar bila kita ingin selalu bersih dan rapi. Renda dan bordir memang mempercantik mukena, tapi butuh perawatan sedikit lebih teliti.</p>'
                    .'<h2>1. Cuci dengan tangan</h2><p>Rendam dalam air dingin dengan deterjen lembut selama 15 menit, lalu kucek perlahan. Hindari mesin cuci dengan putaran kencang supaya renda tidak sobek.</p>'
                    .'<h2>2. Pisahkan dari pakaian berwarna</h2><p>Mukena putih sebaiknya dicuci terpisah agar tidak kelunturan warna lain.</p>'
                    .'<h2>3. Jemur di tempat teduh</h2><p>Sinar matahari langsung membuat kain cepat menguning. Angin-anginkan di tempat teduh hingga kering.</p>'
                    .'<h3>Yang sebaiknya dihindari</h3><ul><li>Pemutih keras berbahan klorin</li><li>Memeras terlalu kuat</li><li>Menyetrika renda dengan suhu tinggi</li></ul>'
                    .'<blockquote><p>Simpan mukena di tas kain atau tas serutnya setelah dipakai, supaya tidak berdebu dan tetap wangi.</p></blockquote>'
                    .'<h2>4. Setrika suhu rendah</h2><p>Balik mukena dan setrika dari sisi dalam dengan suhu rendah. Untuk bahan crinkle, cukup digantung tanpa disetrika.</p>',
            ],
            [
                'slug' => 'panduan-memilih-bahan-mukena',
                'category' => 'panduan-belanja',
                'title' => 'Katun, Crinkle, Jersey, atau Sutra Velvet? Panduan Memilih Bahan Mukena',
                'excerpt' => 'Setiap bahan punya kelebihan sendiri. Kenali perbedaannya supaya mukena yang kamu pilih pas untuk dipakai di rumah, bepergian, atau hari raya.',
                'tags' => ['bahan', 'panduan', 'mukena travel'],
                'products' => 'Mukena Travel',
                'days' => 6,
                'body' => '<p>Mukena yang nyaman membantu kita lebih fokus beribadah. Bahan adalah hal pertama yang perlu diperhatikan.</p>'
                    .'<h2>Pilih bahan sesuai kebutuhan</h2><ol><li><strong>Katun</strong>: menyerap keringat dan adem. Cocok untuk salat sehari-hari dan tarawih.</li><li><strong>Crinkle airflow</strong>: ringan, tidak mudah kusut, tidak perlu disetrika. Pas untuk dibawa bepergian.</li><li><strong>Jersey</strong>: lembut dan sedikit melar, nyaman untuk model tutup dagu.</li><li><strong>Sutra velvet</strong>: jatuh dan berkilau halus, tampil anggun di hari raya.</li></ol>'
                    .'<h2>Mukena travel</h2><p>Untuk dibawa kerja, mudik, atau umrah, pilih katun mikro atau crinkle yang bisa dilipat ringkas ke dalam tasnya.</p>'
                    .'<h2>Ukuran</h2><p>Mukena dewasa umumnya all size. Bila tinggi badanmu di atas 165 cm, pilih model jumbo supaya bawahan menutup sempurna saat sujud.</p>',
            ],
            [
                'slug' => 'albaniah-kini-hadir-online',
                'category' => 'kabar-albaniah',
                'title' => 'Albaniah Kini Hadir Online: Belanja Mukena Lebih Mudah',
                'excerpt' => 'Sekarang kamu bisa belanja mukena Albaniah langsung dari website, lengkap dengan pembayaran aman dan lacak pesanan.',
                'tags' => ['info toko', 'promo'],
                'products' => null,
                'days' => 10,
                'body' => '<p>Alhamdulillah, toko online resmi Albaniah sudah bisa dipakai untuk berbelanja langsung dari rumah. <strong>Temani ibadahmu</strong> dengan mukena yang adem dan anggun.</p>'
                    .'<h2>Yang bisa kamu lakukan</h2><ul><li>Bayar lewat transfer bank, e-wallet, QRIS, atau kartu kredit</li><li>Pilih kurir favorit dan lihat ongkir sebelum bayar</li><li>Lacak pesanan langsung dari halaman akun</li><li>Simpan mukena incaran di wishlist</li></ul>'
                    .'<p>Pakai kode <strong>BERKAH10</strong> untuk diskon 10% di pesanan pertamamu. Bagikan momen ibadahmu dengan tagar <strong>#TemaniIbadahmu</strong>.</p>',
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
