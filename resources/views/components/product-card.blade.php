@props(['product', 'quickAdd' => true])
<article {{ $attributes->merge(['class' => 'group relative flex flex-col']) }}>
    <div class="relative">
        <x-product-image :product="$product" class="aspect-[4/5] rounded-2xl" />

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

        @if ($quickAdd && $product->inStock())
            @php($quickClasses = 'absolute inset-x-3 bottom-3 z-10 hidden translate-y-2 opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:translate-y-0 group-focus-within:opacity-100 lg:block')
            @if ($product->has_variants)
                {{-- Produk bervarian harus dipilih variannya dulu di halaman produk. --}}
                <a href="{{ route('products.show', $product) }}" class="{{ $quickClasses }} btn btn-ink rounded-full py-2.5 shadow-lg shadow-ink/20">
                    Pilih {{ \Illuminate\Support\Str::lower($product->variant_label ?: 'varian') }} <x-icon name="arrow-right" class="size-4" />
                </a>
            @else
                <form method="POST" action="{{ route('cart.store', $product) }}" class="{{ $quickClasses }}">
                    @csrf
                    <button class="btn btn-ink w-full rounded-full py-2.5 shadow-lg shadow-ink/20">
                        <x-icon name="plus" class="size-4" /> Tambah ke keranjang
                    </button>
                </form>
            @endif
        @endif
    </div>

    <div class="flex flex-1 flex-col px-1 pt-3">
        @if ($product->relationLoaded('category') && $product->category)
            <p class="text-[11px] font-semibold tracking-wider text-brand-700 uppercase">{{ $product->category->name }}</p>
        @endif
        <h3 class="mt-0.5 text-sm leading-snug font-semibold text-ink line-clamp-2 sm:text-[15px]">
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
            <p @class(['text-sm font-bold sm:text-[15px]', 'text-accent-600' => $product->isOnSale()])>@rupiah($product->price)</p>
            @if ($product->isOnSale())
                <p class="text-xs text-stone-400 line-through">@rupiah($product->compare_at_price)</p>
            @endif
        </div>
    </div>
</article>
