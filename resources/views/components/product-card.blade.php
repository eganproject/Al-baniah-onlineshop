{{--
    Kartu produk NuFit: kartu putih membulat, foto di dalam bingkai, info &
    tombol tambah cepat (+) selalu terlihat (juga di HP). Produk bervarian
    diarahkan ke halaman produk untuk memilih varian dulu.
    rank: nomor peringkat besar di pojok foto (dipakai di Best Seller beranda).
--}}
@props(['product', 'quickAdd' => true, 'eager' => false, 'rank' => null])
<article {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-3xl border border-stone-200/80 bg-white p-2 transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-xl hover:shadow-ink/10']) }}>
    <div class="relative">
        <x-product-image :product="$product" :eager="$eager" class="aspect-square rounded-2xl" />

        <div class="pointer-events-none absolute left-2 top-2 flex flex-col items-start gap-1.5">
            @if ($product->isOnSale())
                <span class="rounded-full bg-accent-500 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white uppercase">-{{ $product->discountPercent() }}%</span>
            @elseif ($product->created_at?->gt(now()->subDays(30)))
                <span class="rounded-full bg-volt px-2.5 py-1 text-[10px] font-bold tracking-wider text-ink uppercase">Baru</span>
            @endif
            @unless ($product->inStock())
                <span class="rounded-full bg-ink/80 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white uppercase backdrop-blur">Stok habis</span>
            @endunless
        </div>

        @if ($rank)
            <span class="pointer-events-none absolute bottom-1 left-3 font-display text-5xl leading-none font-black text-ink italic [paint-order:stroke_fill] [-webkit-text-stroke:6px_#fff] sm:text-6xl" aria-hidden="true">{{ $rank }}</span>
        @endif

        <div class="absolute right-2 top-2 z-10">
            <x-wishlist-button :product="$product" />
        </div>
    </div>

    <div class="flex flex-1 flex-col px-1.5 pb-1.5 pt-3 sm:px-2">
        @if ($product->relationLoaded('category') && $product->category)
            <p class="truncate text-[10px] font-bold tracking-[0.14em] text-brand-700 uppercase sm:text-[11px]">{{ $product->category->name }}</p>
        @endif
        <h3 class="mt-0.5 text-[13px] leading-snug font-semibold text-ink line-clamp-2 sm:text-sm">
            <a href="{{ route('products.show', $product) }}" class="after:absolute after:inset-0 after:rounded-3xl">{{ $product->name }}</a>
        </h3>
        @if ($product->review_count > 0)
            <p class="mt-1 flex items-center gap-1 text-xs text-stone-500">
                <x-icon name="star" class="size-3.5 fill-amber-400 text-amber-400" />
                <span class="font-semibold text-ink">{{ number_format($product->rating_avg, 1, ',', '.') }}</span>
                <span>({{ $product->review_count }})</span>
            </p>
        @endif

        <div class="mt-auto flex items-end justify-between gap-2 pt-2.5">
            <div class="min-w-0">
                @if ($product->isOnSale())
                    <p class="text-[11px] leading-none text-stone-400 line-through">@rupiah($product->compare_at_price)</p>
                @endif
                <p @class(['mt-1 text-sm font-extrabold tracking-tight sm:text-base', 'text-accent-600' => $product->isOnSale(), 'text-ink' => ! $product->isOnSale()])>@rupiah($product->price)</p>
            </div>

            @if ($quickAdd && $product->inStock())
                @php($addClasses = 'relative z-10 grid size-9 shrink-0 place-items-center rounded-full bg-ink text-white transition hover:bg-brand-400 hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 sm:size-10')
                @if ($product->has_variants)
                    <a href="{{ route('products.show', $product) }}" class="{{ $addClasses }}" aria-label="Pilih {{ \Illuminate\Support\Str::lower($product->variant_label ?: 'varian') }} {{ $product->name }}">
                        <x-icon name="plus" class="size-4" />
                    </a>
                @else
                    <form method="POST" action="{{ route('cart.store', $product) }}" class="relative z-10">
                        @csrf
                        <button class="{{ $addClasses }}" aria-label="Tambah {{ $product->name }} ke keranjang">
                            <x-icon name="plus" class="size-4" />
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</article>
