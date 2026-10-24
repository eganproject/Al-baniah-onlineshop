{{-- Promo: panel ajakan + produk diskon (teks diatur di Admin › Tampilan Beranda) --}}
@use('App\Support\Storefront')
@if ($onSale->isNotEmpty())
    <section class="shop-container mt-14 lg:mt-24" aria-labelledby="home-sale">
        <div class="grid gap-4 overflow-hidden rounded-[2rem] bg-sand-100 p-3 sm:gap-5 sm:p-5 lg:grid-cols-12">
            <div class="relative flex min-h-64 flex-col justify-between overflow-hidden rounded-3xl bg-accent-500 p-6 text-white sm:p-8 lg:col-span-4">
                <span class="pointer-events-none absolute -right-20 -bottom-20 size-64 rounded-full border-[32px] border-white/20" aria-hidden="true"></span>
                @if ($badge = Storefront::text('promo_badge'))
                    <span class="pointer-events-none absolute top-6 right-6 grid size-20 rotate-12 place-items-center rounded-full bg-volt p-2 text-center text-sm leading-none font-black text-ink uppercase italic" aria-hidden="true">{{ $badge }}</span>
                @endif
                <div class="relative">
                    @if ($eyebrow = Storefront::text('promo_eyebrow'))<p class="eyebrow text-white/80">{{ $eyebrow }}</p>@endif
                    <h2 id="home-sale" class="display mt-3 text-4xl leading-[0.95] sm:text-5xl">{{ Storefront::text('promo_title', 'Lagi diskon') }}</h2>
                    @if ($text = Storefront::text('promo_text'))<p class="mt-3 max-w-xs text-sm leading-relaxed text-white/85">{{ $text }}</p>@endif
                </div>
                <a href="{{ Storefront::href(Storefront::text('promo_url', '/produk?promo=1')) }}" class="btn btn-pill relative mt-6 w-fit bg-white text-ink hover:bg-ink hover:text-white">{{ Storefront::text('promo_button', 'Semua promo') }} <x-icon name="arrow-right" class="size-4" /></a>
            </div>
            <div class="no-scrollbar -mx-3 flex snap-x scroll-px-3 gap-3 overflow-x-auto px-3 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-4 sm:overflow-visible sm:px-0 lg:col-span-8 lg:grid-cols-4">
                @foreach ($onSale as $product)
                    <x-product-card :product="$product" class="w-[47%] shrink-0 snap-start sm:w-auto" />
                @endforeach
            </div>
        </div>
    </section>
@endif
