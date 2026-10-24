{{-- Best Seller: korsel geser dengan nomor peringkat --}}
@if ($bestSellers->isNotEmpty())
    <section class="mt-14 lg:mt-24" aria-labelledby="home-best"
             x-data="{ go(d) { const t = this.$refs.track; t.scrollBy({ left: d * t.clientWidth * 0.8, behavior: 'smooth' }) } }">
        <div class="shop-container flex items-end justify-between gap-4">
            <div>
                @if ($eyebrow = \App\Support\Storefront::text('best_eyebrow'))<p class="eyebrow text-brand-600">{{ $eyebrow }}</p>@endif
                <h2 id="home-best" class="display mt-2 text-3xl leading-none sm:text-5xl">{{ \App\Support\Storefront::text('best_title', 'Best Seller') }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.index', ['urut' => 'terlaris']) }}" class="link-underline mr-2 text-sm font-semibold">Lihat semua</a>
                <button type="button" @click="go(-1)" class="hidden size-11 place-items-center rounded-full border border-stone-200 transition hover:border-ink hover:bg-ink hover:text-white sm:grid" aria-label="Geser ke kiri"><x-icon name="arrow-left" class="size-5" /></button>
                <button type="button" @click="go(1)" class="hidden size-11 place-items-center rounded-full border border-stone-200 transition hover:border-ink hover:bg-ink hover:text-white sm:grid" aria-label="Geser ke kanan"><x-icon name="arrow-right" class="size-5" /></button>
            </div>
        </div>
        <div class="shop-container mt-6 sm:mt-8">
            <div x-ref="track" class="no-scrollbar -mx-4 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto px-4 pb-4 sm:mx-0 sm:scroll-px-0 sm:gap-5 sm:px-0">
                @foreach ($bestSellers as $product)
                    <x-product-tile :product="$product" :eager="$loop->index < 4" :rank="($product->sold_count ?? 0) > 0 && $loop->iteration <= 10 ? $loop->iteration : null"
                                    class="w-[47%] shrink-0 snap-start sm:w-[31%] lg:w-[23.5%]" />
                @endforeach
            </div>
        </div>
    </section>
@else
    <section class="shop-container mt-14">
        <x-empty-state title="Belum ada produk" />
    </section>
@endif
