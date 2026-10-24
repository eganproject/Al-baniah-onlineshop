{{-- Promo: panel rose lembut berisi ajakan + produk diskon (teks diatur di Admin › Tampilan Beranda) --}}
@use('App\Support\Storefront')
@if ($onSale->isNotEmpty())
    <section class="shop-container section-gap" aria-labelledby="home-sale">
        <div class="relative isolate overflow-hidden rounded-[2rem] bg-brand-50 px-4 py-10 ring-1 ring-brand-100 sm:px-8 lg:px-12 lg:py-14">
            <div class="ornament-bg-light absolute inset-0 -z-10 opacity-60 [mask-image:linear-gradient(to_right,#000,transparent_60%)]" aria-hidden="true"></div>
            <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-12">
                <div class="text-center lg:col-span-4 lg:text-left">
                    <div class="flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                        @if ($eyebrow = Storefront::text('promo_eyebrow'))<p class="eyebrow text-brand-700">{{ $eyebrow }}</p>@endif
                        @if ($badge = Storefront::text('promo_badge'))
                            <span class="tag bg-accent-500 text-white">{{ $badge }}</span>
                        @endif
                    </div>
                    <h2 id="home-sale" class="display mt-3 text-[2.5rem] leading-[1.02] text-ink sm:text-5xl lg:text-6xl">{{ Storefront::text('promo_title', 'Lagi diskon') }}</h2>
                    @if ($text = Storefront::text('promo_text'))<p class="mx-auto mt-4 max-w-sm text-sm leading-relaxed text-stone-600 sm:text-[15px] lg:mx-0">{{ $text }}</p>@endif
                    <a href="{{ Storefront::href(Storefront::text('promo_url', '/produk?promo=1')) }}" class="btn btn-ink btn-pill mt-7 min-h-12 px-7">{{ Storefront::text('promo_button', 'Semua promo') }} <x-icon name="arrow-right" class="size-4" /></a>
                </div>
                <div class="grid grid-cols-2 gap-x-3 gap-y-9 sm:gap-x-5 md:grid-cols-4 lg:col-span-8">
                    @foreach ($onSale as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
