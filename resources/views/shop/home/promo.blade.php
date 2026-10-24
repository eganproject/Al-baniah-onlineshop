{{-- Promo: panel ajakan + produk diskon (teks diatur di Admin › Tampilan Beranda) --}}
@use('App\Support\Storefront')
@if ($onSale->isNotEmpty())
    <section class="shop-container section-gap" aria-labelledby="home-sale">
        <div class="grid gap-6 lg:grid-cols-12 lg:gap-8">
            <div class="track-lines relative flex min-h-64 flex-col justify-between overflow-hidden rounded-2xl bg-ink p-6 text-white sm:p-8 lg:col-span-4">
                <x-logo variant="mark" mono class="pointer-events-none absolute -right-8 -bottom-6 h-40 text-white/[0.05]" aria-hidden="true" />
                <div class="relative">
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($eyebrow = Storefront::text('promo_eyebrow'))<p class="kicker text-white/60">{{ $eyebrow }}</p>@endif
                        @if ($badge = Storefront::text('promo_badge'))
                            <span class="tag bg-accent-500 text-white">{{ $badge }}</span>
                        @endif
                    </div>
                    <h2 id="home-sale" class="display mt-4 text-4xl leading-[1.02] sm:text-5xl">{{ Storefront::text('promo_title', 'Lagi diskon') }}</h2>
                    @if ($text = Storefront::text('promo_text'))<p class="mt-4 max-w-xs text-sm leading-relaxed text-white/65">{{ $text }}</p>@endif
                </div>
                <a href="{{ Storefront::href(Storefront::text('promo_url', '/produk?promo=1')) }}" class="btn btn-light btn-pill relative mt-8 w-fit">{{ Storefront::text('promo_button', 'Semua promo') }} <x-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="no-scrollbar -mx-4 flex snap-x scroll-px-4 gap-3 overflow-x-auto px-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:px-0 lg:col-span-8 lg:grid-cols-4">
                @foreach ($onSale as $product)
                    <x-product-card :product="$product" class="w-[46%] shrink-0 snap-start sm:w-auto" />
                @endforeach
            </div>
        </div>
    </section>
@endif
