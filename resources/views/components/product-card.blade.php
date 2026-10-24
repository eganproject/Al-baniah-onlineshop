{{--
    Kartu produk NuFit: foto di atas latar abu lembut, info ringkas di bawah.
    Tombol tambah cepat (+) di pojok foto: selalu terlihat di layar sentuh,
    muncul saat hover di desktop. Produk bervarian diarahkan ke halaman produk.
    rank: nomor peringkat di pojok foto (dipakai di Best Seller beranda).
--}}
@props(['product', 'quickAdd' => true, 'eager' => false, 'rank' => null])
<article {{ $attributes->merge(['class' => 'group relative flex flex-col']) }}>
    <div class="relative overflow-hidden rounded-2xl bg-sand-100">
        <x-product-image :product="$product" :eager="$eager" class="aspect-square" />

        <div class="pointer-events-none absolute top-2.5 left-2.5 flex flex-col items-start gap-1.5 sm:top-3 sm:left-3">
            @if ($product->isOnSale())
                <span class="tag bg-accent-500 text-white">-{{ $product->discountPercent() }}%</span>
            @elseif ($product->created_at?->gt(now()->subDays(30)))
                <span class="tag bg-white text-ink shadow-xs">Baru</span>
            @endif
            @unless ($product->inStock())
                <span class="tag bg-ink/85 text-white">Stok habis</span>
            @endunless
        </div>

        @if ($rank)
            <span class="pointer-events-none absolute bottom-2 left-3 font-display text-4xl leading-none font-extrabold tracking-tighter text-ink/90 italic [paint-order:stroke_fill] [-webkit-text-stroke:5px_#fff] sm:text-5xl" aria-hidden="true">{{ $rank }}</span>
        @endif

        <div class="absolute top-2 right-2 z-10">
            <x-wishlist-button :product="$product" />
        </div>

        @if ($quickAdd && $product->inStock())
            @php($addClasses = 'grid size-10 place-items-center rounded-full bg-white text-ink shadow-md shadow-ink/10 transition hover:bg-ink hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500')
            <div class="absolute right-2.5 bottom-2.5 z-10 transition duration-200 lg:translate-y-1 lg:opacity-0 lg:group-hover:translate-y-0 lg:group-hover:opacity-100 lg:group-focus-within:translate-y-0 lg:group-focus-within:opacity-100 [@media(pointer:coarse)]:translate-y-0 [@media(pointer:coarse)]:opacity-100">
                @if ($product->has_variants)
                    <a href="{{ route('products.show', $product) }}" class="{{ $addClasses }}" aria-label="Pilih {{ \Illuminate\Support\Str::lower($product->variant_label ?: 'varian') }} {{ $product->name }}">
                        <x-icon name="plus" class="size-[18px]" />
                    </a>
                @else
                    <form method="POST" action="{{ route('cart.store', $product) }}" data-cart-add>
                        @csrf
                        <button class="{{ $addClasses }}" aria-label="Tambah {{ $product->name }} ke keranjang">
                            <x-icon name="plus" class="size-[18px]" />
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col pt-3 sm:pt-3.5">
        @if ($product->relationLoaded('category') && $product->category)
            <p class="truncate text-[11px] font-medium text-stone-500 sm:text-xs">{{ $product->category->name }}</p>
        @endif
        <h3 class="mt-0.5 text-[13px] leading-snug font-medium text-ink line-clamp-2 sm:text-sm">
            <a href="{{ route('products.show', $product) }}" class="after:absolute after:inset-0 after:rounded-2xl">{{ $product->name }}</a>
        </h3>
        <div class="mt-auto flex flex-wrap items-baseline gap-x-2 pt-2">
            <p class="text-sm font-semibold tracking-tight text-ink sm:text-[15px]">@rupiah($product->price)</p>
            @if ($product->isOnSale())
                <p class="text-xs text-stone-400 line-through">@rupiah($product->compare_at_price)</p>
            @endif
        </div>
        @if ($product->review_count > 0)
            <p class="mt-1 flex items-center gap-1 text-xs text-stone-500">
                <x-icon name="star" class="size-3.5 fill-ink text-ink" />
                <span class="font-medium text-ink">{{ number_format($product->rating_avg, 1, ',', '.') }}</span>
                <span>({{ $product->review_count }})</span>
            </p>
        @endif
    </div>
</article>
