{{-- Beranda: 3 artikel terbaru (tidak tampil bila belum ada artikel terbit). --}}
@if ($latestArticles->isNotEmpty())
    <section class="shop-container section-gap" aria-labelledby="artikel-terbaru">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="kicker">Tips &amp; panduan</p>
                <h2 id="artikel-terbaru" class="section-title">{{ \App\Support\Storefront::text('articles_title', 'Artikel terbaru') }}</h2>
            </div>
            <a href="{{ route('articles.index') }}" class="link-arrow shrink-0">Semua artikel <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <div class="no-scrollbar -mx-4 mt-8 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:gap-y-10 sm:overflow-visible sm:px-0 lg:grid-cols-3">
            @foreach ($latestArticles as $article)
                <x-article-card :article="$article" class="w-[82%] shrink-0 snap-start sm:w-auto" />
            @endforeach
        </div>
    </section>
@endif
