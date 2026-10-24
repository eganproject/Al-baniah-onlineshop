<x-layouts.shop :title="$product->name" :description="$product->metaDescription()" :image="$product->imageUrl()" type="product" :bottom-nav="! $product->inStock()">
    <x-slot:head>
        <meta property="product:price:amount" content="{{ $product->price }}">
        <meta property="product:price:currency" content="IDR">
        <meta property="product:availability" content="{{ $product->inStock() ? 'in stock' : 'out of stock' }}">
        {!! \App\Support\StructuredData::script(
            \App\Support\StructuredData::product($product, $reviews->getCollection()),
            \App\Support\StructuredData::breadcrumbs(array_values(array_filter([
                ['Beranda', route('home')],
                ['Produk', route('products.index')],
                $product->category?->parent ? [$product->category->parent->name, route('products.index', ['kategori' => $product->category->parent->slug])] : null,
                $product->category ? [$product->category->name, route('products.index', ['kategori' => $product->category->slug])] : null,
                [$product->name, route('products.show', $product)],
            ]))),
        ) !!}
    </x-slot:head>
    @php
        $variants = $product->activeVariants;
        $images = $product->images;
        $pageConfig = [
            'images' => $images->map(fn ($i) => ['id' => $i->id, 'url' => $i->url()])->values(),
            'variants' => $variants->map(fn ($v) => [
                'id' => $v->id, 'name' => $v->name, 'price' => $v->price, 'compare_at_price' => $v->compare_at_price,
                'stock' => $v->stock, 'image_id' => $v->product_image_id, 'sku' => $v->sku,
            ])->values(),
            // Pilih otomatis bila hanya ada satu varian yang tersedia.
            'selectedId' => old('variant_id') ? (int) old('variant_id') : ($variants->where('stock', '>', 0)->count() === 1 ? $variants->firstWhere('stock', '>', 0)->id : null),
            'price' => $product->price,
            'compareAt' => $product->compare_at_price,
            'stock' => $product->stock,
        ];
        $priceRange = $variants->isNotEmpty() && $variants->pluck('price')->unique()->count() > 1;
        $lowStock = config('shop.low_stock_threshold');
        $sportFor = $product->category ? $product->category->name.' '.($product->category->parent?->name) : $product->name;

        $tabs = array_filter([
            'desc' => $product->description ? 'Deskripsi' : null,
            'detail' => 'Detail',
            'ship' => 'Pengiriman',
            'warranty' => 'Garansi',
        ]);
    @endphp

    <div x-data="productPage(@js($pageConfig))">
        {{-- Panggung produk: latar abu dingin melengkung --}}
        <section class="relative isolate overflow-hidden bg-sand-100/70 pb-10 lg:rounded-b-[3rem] lg:pb-16">
            <span class="pointer-events-none absolute -top-40 -right-48 -z-10 hidden size-[38rem] rounded-full border-[64px] border-brand-400/15 lg:block" aria-hidden="true"></span>

            <div class="shop-container pt-5 lg:pt-8">
                <nav class="flex flex-wrap items-center gap-1.5 text-xs text-stone-500" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-ink">Beranda</a>
                    <x-icon name="chevron-right" class="size-3" />
                    <a href="{{ route('products.index') }}" class="hover:text-ink">Produk</a>
                    @if ($product->category?->parent)
                        <x-icon name="chevron-right" class="size-3" />
                        <a href="{{ route('products.index', ['kategori' => $product->category->parent->slug]) }}" class="hover:text-ink">{{ $product->category->parent->name }}</a>
                    @endif
                    @if ($product->category)
                        <x-icon name="chevron-right" class="size-3" />
                        <a href="{{ route('products.index', ['kategori' => $product->category->slug]) }}" class="hover:text-ink">{{ $product->category->name }}</a>
                    @endif
                </nav>

                <div class="mt-5 grid gap-6 lg:mt-8 lg:grid-cols-12 lg:gap-10 xl:gap-14">
                    {{-- Galeri --}}
                    <div class="min-w-0 lg:col-span-7">
                        <div class="lg:flex lg:gap-4">
                            {{-- Thumbnail vertikal (desktop) --}}
                            @if ($images->count() > 1)
                                <div class="no-scrollbar hidden max-h-[640px] w-[88px] shrink-0 flex-col gap-3 overflow-y-auto p-1 lg:flex">
                                    @foreach ($images as $index => $image)
                                        <button type="button" @click="active = {{ $index }}"
                                                class="aspect-square w-full shrink-0 overflow-hidden rounded-2xl bg-white ring-2 transition"
                                                :class="active === {{ $index }} ? 'ring-brand-400' : 'ring-transparent opacity-60 hover:opacity-100'"
                                                aria-label="Lihat foto {{ $index + 1 }}">
                                            <img src="{{ $image->url() }}" alt="" loading="lazy" class="size-full object-cover">
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="relative min-w-0 flex-1">
                                @if ($images->isEmpty())
                                    <x-product-image :product="$product" :eager="true" class="aspect-square overflow-hidden rounded-[2rem] bg-white" />
                                @else
                                    {{-- Mobile: geser dengan jari --}}
                                    <div x-ref="swipe" class="no-scrollbar -mx-4 flex snap-x snap-mandatory overflow-x-auto sm:mx-0 sm:rounded-[2rem] lg:hidden"
                                         @scroll.debounce.80ms="active = Math.round($el.scrollLeft / $el.clientWidth)"
                                         x-effect="const i = active; $nextTick(() => { if (Math.round($el.scrollLeft / $el.clientWidth) !== i) $el.scrollTo({ left: i * $el.clientWidth, behavior: 'smooth' }) })">
                                        @foreach ($images as $index => $image)
                                            <img src="{{ $image->url() }}" alt="{{ $product->name }} — foto {{ $index + 1 }}" @if ($index > 0) loading="lazy" @endif decoding="async"
                                                 class="aspect-square w-full shrink-0 snap-center bg-white object-cover">
                                        @endforeach
                                    </div>

                                    {{-- Desktop: foto utama --}}
                                    <div class="relative hidden aspect-[5/6] overflow-hidden rounded-[2rem] bg-white lg:block">
                                        @foreach ($images as $index => $image)
                                            <img src="{{ $image->url() }}" alt="{{ $product->name }} — foto {{ $index + 1 }}"
                                                 @if ($index > 0) loading="lazy" x-cloak @endif decoding="async"
                                                 x-show="active === {{ $index }}" x-transition.opacity.duration.200ms
                                                 class="absolute inset-0 size-full object-cover">
                                        @endforeach
                                        @if ($images->count() > 1)
                                            <div class="absolute right-5 bottom-5 flex gap-2">
                                                <button type="button" @click="next(-1)" class="grid size-11 place-items-center rounded-full bg-white text-ink shadow-lg transition hover:bg-ink hover:text-white" aria-label="Foto sebelumnya"><x-icon name="arrow-left" class="size-5" /></button>
                                                <button type="button" @click="next(1)" class="grid size-11 place-items-center rounded-full bg-white text-ink shadow-lg transition hover:bg-ink hover:text-white" aria-label="Foto berikutnya"><x-icon name="arrow-right" class="size-5" /></button>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($images->count() > 1)
                                        <div class="mt-3 flex justify-center gap-1.5 lg:hidden" aria-hidden="true">
                                            @foreach ($images as $index => $image)
                                                <span class="h-1.5 rounded-full transition-all" :class="active === {{ $index }} ? 'w-6 bg-ink' : 'w-1.5 bg-stone-300'"></span>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif

                                <div class="pointer-events-none absolute top-4 left-4 flex flex-col items-start gap-2 lg:top-5 lg:left-5">
                                    <template x-if="discount">
                                        <span class="rounded-full bg-accent-500 px-3 py-1 text-[11px] font-bold tracking-wider text-white uppercase" x-text="`Hemat ${discount}%`"></span>
                                    </template>
                                    @if ($product->created_at?->gt(now()->subDays(30)))
                                        <span class="rounded-full bg-volt px-3 py-1 text-[11px] font-bold tracking-wider text-ink uppercase">Baru</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kartu info (lengket di desktop) --}}
                    <div class="lg:col-span-5">
                        <div class="rounded-[2rem] bg-white p-5 shadow-[0_30px_80px_-40px] shadow-ink/30 ring-1 ring-stone-200/70 sm:p-7 lg:sticky lg:top-24 xl:p-8">
                            <div class="flex items-center justify-between gap-3">
                                @if ($product->category)
                                    <a href="{{ route('products.index', ['kategori' => $product->category->slug]) }}" class="inline-flex items-center gap-2 rounded-full bg-ink py-1 pr-3 pl-1 text-xs font-semibold text-white hover:bg-brand-900">
                                        <span class="grid size-6 place-items-center rounded-full bg-brand-400 text-ink"><x-sport-icon :for="$sportFor" class="size-3.5" /></span>
                                        {{ $product->category->name }}
                                    </a>
                                @endif
                                @if ($product->hasReviews())
                                    <a href="#ulasan" class="inline-flex items-center gap-1.5 text-sm">
                                        <x-icon name="star" class="size-4 fill-amber-400 text-amber-400" />
                                        <span class="font-semibold">{{ number_format($product->rating_avg, 1, ',', '.') }}</span>
                                        <span class="text-stone-500 underline underline-offset-4">({{ $product->review_count }})</span>
                                    </a>
                                @endif
                            </div>

                            <h1 class="mt-4 font-display text-[1.75rem] leading-[1.08] font-extrabold tracking-tight sm:text-4xl">{{ $product->name }}</h1>

                            {{-- Harga --}}
                            <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2 border-y border-dashed border-stone-200 py-4">
                                <p class="font-display text-3xl font-extrabold tracking-tight sm:text-[2.1rem]" :class="compareAt && 'text-accent-600'">
                                    @if ($priceRange)<span x-show="!selected" class="text-sm font-medium text-stone-500">Mulai</span>@endif
                                    <span x-text="rupiah(price)">@rupiah($product->price)</span>
                                </p>
                                <p x-show="compareAt" @unless ($product->isOnSale()) x-cloak @endunless class="text-base text-stone-400 line-through" x-text="rupiah(compareAt)">@rupiah($product->compare_at_price)</p>
                                <span x-show="compareAt" @unless ($product->isOnSale()) x-cloak @endunless class="rounded-full bg-accent-50 px-2.5 py-1 text-xs font-bold text-accent-700"
                                      x-text="`Hemat ${rupiah(compareAt - price)}`"></span>
                            </div>

                            <p class="mt-4 flex items-center gap-2 text-sm">
                                <span class="relative flex size-2.5">
                                    <span class="absolute inset-0 animate-ping rounded-full opacity-60" :class="stock < 1 ? 'hidden' : (stock <= {{ $lowStock }} ? 'bg-amber-400' : 'bg-emerald-400')"></span>
                                    <span class="relative size-2.5 rounded-full" :class="stock < 1 ? 'bg-stone-400' : (stock <= {{ $lowStock }} ? 'bg-amber-500' : 'bg-emerald-500')"></span>
                                </span>
                                <span class="font-medium" x-text="stock < 1 ? 'Stok habis' : (stock <= {{ $lowStock }} ? `Stok terbatas, sisa ${stock}` : 'Stok tersedia, siap dikirim')">
                                    {{ ! $product->inStock() ? 'Stok habis' : ($product->isLowStock() ? 'Stok terbatas, sisa '.$product->stock : 'Stok tersedia, siap dikirim') }}
                                </span>
                            </p>

                            {{-- Varian --}}
                            @if ($variants->isNotEmpty())
                                <div class="mt-6">
                                    <p class="text-sm font-semibold">
                                        {{ $product->variant_label ?: 'Varian' }}:
                                        <span class="font-normal text-stone-500" x-text="selected ? selected.name : 'pilih salah satu'"></span>
                                    </p>
                                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                        @foreach ($variants as $variant)
                                            <button type="button" @click="select(@js(['id' => $variant->id, 'stock' => $variant->stock, 'image_id' => $variant->product_image_id]))"
                                                    @disabled(! $variant->inStock())
                                                    :aria-pressed="selectedId === {{ $variant->id }}"
                                                    :class="selectedId === {{ $variant->id }} ? 'border-ink bg-ink text-white' : 'border-stone-200 hover:border-brand-400'"
                                                    class="relative flex min-h-12 items-center gap-2 rounded-2xl border px-3 py-2 text-left text-sm font-medium transition disabled:cursor-not-allowed disabled:border-dashed disabled:text-stone-300">
                                                @if ($variant->image)
                                                    <img src="{{ $variant->image->url() }}" alt="" class="size-8 shrink-0 rounded-lg object-cover">
                                                @endif
                                                <span class="min-w-0 leading-tight">
                                                    <span @class(['block truncate', 'line-through' => ! $variant->inStock()])>{{ $variant->name }}</span>
                                                    @unless ($variant->inStock())<span class="block text-[11px] font-normal no-underline">Habis</span>@endunless
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($product->inStock())
                                <form id="add-to-cart" method="POST" action="{{ route('cart.store', $product) }}" class="mt-6"
                                      @submit="if (needsVariant) { $event.preventDefault(); $refs.variantHint.classList.remove('hidden') }">
                                    @csrf
                                    @if ($variants->isNotEmpty())
                                        <input type="hidden" name="variant_id" :value="selectedId ?? ''">
                                    @endif
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold">Jumlah</p>
                                        <div class="inline-flex items-center rounded-full bg-sand-100 p-1">
                                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="grid size-10 place-items-center rounded-full bg-white shadow-xs transition hover:bg-ink hover:text-white" aria-label="Kurangi"><x-icon name="minus" class="size-4" /></button>
                                            <input type="number" name="quantity" x-model.number="qty" min="1" :max="max" value="1" aria-label="Jumlah"
                                                   class="w-12 border-0 bg-transparent p-0 text-center text-base font-bold focus:ring-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">
                                            <button type="button" @click="qty = Math.min(max, qty + 1)" class="grid size-10 place-items-center rounded-full bg-white shadow-xs transition hover:bg-ink hover:text-white" aria-label="Tambah"><x-icon name="plus" class="size-4" /></button>
                                        </div>
                                    </div>
                                    <p x-ref="variantHint" x-show="needsVariant" class="mt-4 hidden text-sm font-medium text-accent-600">Pilih {{ mb_strtolower($product->variant_label ?: 'varian') }} terlebih dahulu.</p>
                                    <div class="mt-5 flex gap-2">
                                        <button class="btn btn-brand btn-pill min-h-14 flex-1 text-[15px]"><x-icon name="bag" class="size-5" /> Tambah ke keranjang</button>
                                        <x-wishlist-button :product="$product" variant="outline" class="shrink-0" />
                                    </div>
                                    <button name="buy_now" value="1" class="btn btn-ink btn-pill mt-2 min-h-14 w-full text-[15px]">Beli sekarang <x-icon name="arrow-right" class="size-4" /></button>
                                </form>
                            @else
                                <div class="mt-6 rounded-2xl bg-sand-100 p-5 text-sm text-stone-600">
                                    Produk ini sedang habis.
                                    @if (config('shop.whatsapp'))
                                        <a href="https://wa.me/{{ config('shop.whatsapp') }}?text={{ urlencode('Halo, kapan '.$product->name.' tersedia lagi?') }}" target="_blank" rel="noopener" class="link-underline font-semibold text-ink">Tanya ketersediaan</a>
                                    @endif
                                </div>
                                <x-wishlist-button :product="$product" variant="button" class="mt-3" />
                            @endif

                            <ul class="mt-6 grid grid-cols-3 gap-2 text-[11px] leading-tight font-medium text-stone-600 sm:text-xs">
                                @foreach ([['shield', 'Produk resmi'], ['truck', 'Kirim se-Indonesia'], ['refresh', 'Tukar 7 hari']] as [$icon, $label])
                                    <li class="flex items-center gap-2 rounded-2xl bg-sand-50 p-2.5"><x-icon :name="$icon" class="size-5 shrink-0 text-brand-600" /> {{ $label }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tab informasi --}}
        <section class="shop-container mt-12 lg:mt-20" x-data="{ tab: @js(array_key_first($tabs)) }">
            <div class="grid gap-8 lg:grid-cols-12 lg:gap-14">
                <div class="min-w-0 lg:col-span-8">
                    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:px-0" role="tablist" aria-label="Informasi produk">
                        @foreach ($tabs as $key => $label)
                            <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}" @click="tab = '{{ $key }}'"
                                    :aria-selected="tab === '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-ink text-white' : 'bg-sand-100 text-stone-600 hover:bg-sand-200'"
                                    @class(['shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold transition max-lg:min-h-11', 'bg-ink text-white' => $loop->first, 'bg-sand-100 text-stone-600' => ! $loop->first])>{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="mt-6 text-[15px] leading-relaxed text-stone-600">
                        @foreach ($tabs as $key => $label)
                            <div id="panel-{{ $key }}" role="tabpanel" aria-labelledby="tab-{{ $key }}" x-show="tab === '{{ $key }}'" @if (! $loop->first) x-cloak @endif>
                                @if ($key === 'desc')
                                    <div class="max-w-3xl whitespace-pre-line">{{ $product->description }}</div>
                                @elseif ($key === 'detail')
                                    <dl class="grid max-w-xl overflow-hidden rounded-2xl border border-stone-200 text-sm sm:grid-cols-[180px_1fr]">
                                        @if ($product->sku)
                                            <dt class="bg-sand-50 px-4 py-3 font-semibold text-ink">SKU</dt><dd class="px-4 py-3 max-sm:border-b max-sm:border-stone-200"><span x-text="selected?.sku || @js($product->sku)">{{ $product->sku }}</span></dd>
                                        @endif
                                        @if ($product->category)
                                            <dt class="border-t border-stone-200 bg-sand-50 px-4 py-3 font-semibold text-ink">Kategori</dt><dd class="px-4 py-3 sm:border-t sm:border-stone-200">{{ $product->category->parent ? $product->category->parent->name.' › ' : '' }}{{ $product->category->name }}</dd>
                                        @endif
                                        @if ($product->collections->isNotEmpty())
                                            <dt class="border-t border-stone-200 bg-sand-50 px-4 py-3 font-semibold text-ink">Cocok untuk</dt>
                                            <dd class="flex flex-wrap gap-1.5 px-4 py-3 sm:border-t sm:border-stone-200">
                                                @foreach ($product->collections as $collection)
                                                    <a href="{{ $collection->url() }}" class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-800 ring-1 ring-brand-200 hover:bg-brand-100">{{ $collection->name }}</a>
                                                @endforeach
                                            </dd>
                                        @endif
                                        @if ($variants->isNotEmpty())
                                            <dt class="border-t border-stone-200 bg-sand-50 px-4 py-3 font-semibold text-ink">{{ $product->variant_label ?: 'Varian' }}</dt><dd class="px-4 py-3 sm:border-t sm:border-stone-200">{{ $variants->pluck('name')->join(', ') }}</dd>
                                        @endif
                                        <dt class="border-t border-stone-200 bg-sand-50 px-4 py-3 font-semibold text-ink">Berat</dt><dd class="px-4 py-3 sm:border-t sm:border-stone-200">{{ $product->weight }} gr</dd>
                                    </dl>
                                @elseif ($key === 'ship')
                                    <div class="grid max-w-3xl gap-3 sm:grid-cols-2">
                                        <div class="rounded-2xl bg-sand-50 p-5"><x-icon name="truck" class="size-6 text-brand-600" /><p class="mt-3 font-semibold text-ink">Pengiriman</p><p class="mt-1 text-sm">Dikirim dari gudang kami dengan kurir pilihanmu (JNE, SiCepat, J&amp;T, POS, dan lainnya). Ongkir dihitung otomatis saat checkout.</p></div>
                                        <div class="rounded-2xl bg-sand-50 p-5"><x-icon name="credit-card" class="size-6 text-brand-600" /><p class="mt-3 font-semibold text-ink">Pembayaran</p><p class="mt-1 text-sm">Bayar dengan transfer bank, e-wallet, atau QRIS. Pesanan diproses setelah pembayaran terkonfirmasi.</p></div>
                                    </div>
                                @else
                                    <div class="max-w-3xl rounded-2xl bg-sand-50 p-5"><x-icon name="refresh" class="size-6 text-brand-600" /><p class="mt-3 text-sm">Produk resmi NuFit. Produk cacat produksi bisa ditukar dalam 7 hari setelah diterima, cukup hubungi CS kami dengan menyertakan nomor pesanan.</p></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="relative overflow-hidden rounded-[2rem] bg-ink p-6 text-white lg:col-span-4 lg:self-start lg:p-8">
                    <span class="pointer-events-none absolute -right-12 -bottom-12 size-40 rounded-full border-[18px] border-brand-400/30" aria-hidden="true"></span>
                    <span class="grid size-12 place-items-center rounded-2xl bg-brand-400 text-ink"><x-sport-icon :for="$sportFor" class="size-6" /></span>
                    <p class="display mt-5 text-2xl leading-tight">Butuh saran perlengkapan?</p>
                    <p class="mt-2 text-sm text-white/65">Tim NuFit siap bantu memilih yang pas untuk latihanmu.</p>
                    @if (config('shop.whatsapp'))
                        <a href="https://wa.me/{{ config('shop.whatsapp') }}?text={{ urlencode('Halo NuFit, saya mau tanya tentang '.$product->name) }}" target="_blank" rel="noopener" class="btn btn-brand btn-pill relative mt-5"><x-icon name="chat" class="size-4" /> Chat WhatsApp</a>
                    @else
                        <a href="{{ route('articles.index') }}" class="btn btn-brand btn-pill relative mt-5">Baca tips latihan <x-icon name="arrow-right" class="size-4" /></a>
                    @endif
                    <p class="eyebrow relative mt-6 text-brand-300">{{ config('shop.hashtag') }}</p>
                </aside>
            </div>
        </section>

        <div class="shop-container">
            @include('shop.products.partials.reviews')
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-20 pb-28 lg:mt-28 lg:pb-0" aria-labelledby="related-title"
                     x-data="{ go(d) { const t = this.$refs.track; t.scrollBy({ left: d * t.clientWidth * 0.8, behavior: 'smooth' }) } }">
                <div class="shop-container flex items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow text-brand-600">{{ $product->category?->name ?? 'Rekomendasi' }}</p>
                        <h2 id="related-title" class="display mt-2 text-3xl leading-none sm:text-5xl">Lengkapi latihanmu</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($product->category)
                            <a href="{{ route('products.index', ['kategori' => $product->category->slug]) }}" class="link-underline mr-2 text-sm font-semibold">Lihat semua</a>
                        @endif
                        <button type="button" @click="go(-1)" class="hidden size-11 place-items-center rounded-full border border-stone-200 transition hover:border-ink hover:bg-ink hover:text-white sm:grid" aria-label="Geser ke kiri"><x-icon name="arrow-left" class="size-5" /></button>
                        <button type="button" @click="go(1)" class="hidden size-11 place-items-center rounded-full border border-stone-200 transition hover:border-ink hover:bg-ink hover:text-white sm:grid" aria-label="Geser ke kanan"><x-icon name="arrow-right" class="size-5" /></button>
                    </div>
                </div>
                <div class="shop-container mt-6 sm:mt-8">
                    <div x-ref="track" class="no-scrollbar -mx-4 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto px-4 pb-4 sm:mx-0 sm:scroll-px-0 sm:gap-5 sm:px-0">
                        @foreach ($related as $item)
                            <x-product-card :product="$item" class="w-[47%] shrink-0 snap-start sm:w-[31%] lg:w-[23.5%]" />
                        @endforeach
                    </div>
                </div>
            </section>
        @else
            <div class="pb-28 lg:pb-0"></div>
        @endif

        {{-- Bar beli di mobile --}}
        @if ($product->inStock())
            <div class="fixed inset-x-0 bottom-0 z-30 border-t border-stone-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden">
                <div class="flex items-center gap-3 px-4 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs text-stone-500">{{ $product->name }}</p>
                        <p class="text-base font-extrabold" :class="compareAt && 'text-accent-600'">
                            @if ($priceRange)<span x-show="!selected" class="text-xs font-medium text-stone-500">Mulai </span>@endif<span x-text="rupiah(price)">@rupiah($product->price)</span>
                        </p>
                    </div>
                    <button form="add-to-cart" class="btn btn-brand rounded-full px-5 py-3"><x-icon name="bag" class="size-4" /> Tambah</button>
                    <button form="add-to-cart" name="buy_now" value="1" class="btn btn-ink rounded-full px-5 py-3">Beli</button>
                </div>
            </div>
        @endif
    </div>
</x-layouts.shop>
