{{--
    Kartu produk gaya "tile" untuk beranda: gambar di kotak membulat, hati
    wishlist di pojok kanan atas, nama & harga di bawahnya.
--}}
@props(['product', 'eager' => false])
<article {{ $attributes->merge(['class' => 'group relative flex flex-col']) }}>
    <div class="relative">
        <x-product-image :product="$product" :eager="$eager" bg="bg-sand-100" class="aspect-square rounded-2xl" />

        <div class="pointer-events-none absolute left-2.5 top-2.5 flex flex-col items-start gap-1.5 sm:left-3 sm:top-3">
            @if ($product->isOnSale())
                <span class="rounded-full bg-accent-500 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white uppercase">-{{ $product->discountPercent() }}%</span>
            @elseif ($product->created_at?->gt(now()->subDays(30)))
                <span class="rounded-full bg-volt px-2.5 py-1 text-[10px] font-bold tracking-wider text-ink uppercase">Baru</span>
            @endif
            @unless ($product->inStock())
                <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold tracking-wider text-ink uppercase">Stok habis</span>
            @endunless
        </div>

        <div class="absolute right-2 top-2 z-10 sm:right-2.5 sm:top-2.5">
            <x-wishlist-button :product="$product" />
        </div>
    </div>

    <div class="flex flex-1 flex-col px-1 pt-3">
        <h3 class="text-[13px] leading-snug font-semibold text-ink line-clamp-2 sm:text-sm">
            <a href="{{ route('products.show', $product) }}" class="after:absolute after:inset-0 group-hover:text-brand-700">{{ $product->name }}</a>
        </h3>
        @if ($product->review_count > 0)
            <p class="mt-1 flex items-center gap-1 text-xs text-stone-500">
                <x-icon name="star" class="size-3.5 fill-amber-400 text-amber-400" />
                <span class="font-semibold text-ink">{{ number_format($product->rating_avg, 1, ',', '.') }}</span>
                <span>({{ $product->review_count }})</span>
            </p>
        @endif
        <div class="mt-1.5 flex flex-wrap items-baseline gap-x-2">
            <p @class(['text-sm font-bold sm:text-[15px]', 'text-accent-600' => $product->isOnSale(), 'text-ink' => ! $product->isOnSale()])>@rupiah($product->price)</p>
            @if ($product->isOnSale())
                <p class="text-xs text-stone-400 line-through">@rupiah($product->compare_at_price)</p>
            @endif
        </div>
    </div>
</article>
