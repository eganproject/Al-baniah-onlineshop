{{-- Best Seller: korsel geser dengan nomor peringkat --}}
@if ($bestSellers->isNotEmpty())
    <section @class(['section-gap' => true, '!mt-10 lg:!mt-16' => $first]) aria-labelledby="home-best"
             x-data="{ go(d) { const t = this.$refs.track; t.scrollBy({ left: d * t.clientWidth * 0.8, behavior: 'smooth' }) } }">
        <div class="shop-container flex items-end justify-between gap-4">
            <div>
                @if ($eyebrow = \App\Support\Storefront::text('best_eyebrow'))<p class="kicker">{{ $eyebrow }}</p>@endif
                <h2 id="home-best" class="section-title">{{ \App\Support\Storefront::text('best_title', 'Best Seller') }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.best-sellers') }}" class="link-arrow mr-1 sm:mr-3">Lihat semua <x-icon name="arrow-right" class="size-4" /></a>
                <button type="button" @click="go(-1)" class="hidden size-11 place-items-center rounded-full border border-stone-200 transition hover:border-ink sm:grid" aria-label="Geser ke kiri"><x-icon name="arrow-left" class="size-[18px]" /></button>
                <button type="button" @click="go(1)" class="hidden size-11 place-items-center rounded-full border border-stone-200 transition hover:border-ink sm:grid" aria-label="Geser ke kanan"><x-icon name="arrow-right" class="size-[18px]" /></button>
            </div>
        </div>
        <div class="shop-container mt-7 sm:mt-9">
            <div x-ref="track" class="no-scrollbar -mx-4 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto px-4 pb-2 sm:mx-0 sm:scroll-px-0 sm:gap-6 sm:px-0">
                @foreach ($bestSellers as $product)
                    <x-product-tile :product="$product" :eager="$loop->index < 4" :rank="$loop->iteration <= 10 && (\App\Services\Storefront\BestSellers::isManual() || ($product->sold_count ?? 0) > 0) ? $loop->iteration : null"
                                    class="w-[46%] shrink-0 snap-start sm:w-[31%] lg:w-[calc((100%-4.5rem)/4)]" />
                @endforeach
            </div>
        </div>
    </section>
@else
    <section class="shop-container section-gap">
        <x-empty-state title="Belum ada produk" />
    </section>
@endif
