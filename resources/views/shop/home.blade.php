@php
    $hero = \App\Support\Storefront::hero();
    $light = $hero['light'];
    // Warna ubin kategori bergantian supaya beranda terasa energik.
    $tones = [
        ['bg-brand-400 text-ink', 'bg-white/30'],
        ['bg-ink text-white', 'bg-brand-400/20'],
        ['bg-volt text-ink', 'bg-white/40'],
        ['bg-accent-500 text-white', 'bg-white/15'],
        ['bg-brand-100 text-brand-900', 'bg-white/60'],
        ['bg-sand-200 text-ink', 'bg-white/60'],
    ];
    $bento = $categories->take(8);
@endphp
<x-layouts.shop :transparent-header="$light">
    <x-slot:head>
        {!! \App\Support\StructuredData::script(\App\Support\StructuredData::website()) !!}
        <link rel="preload" as="image" href="{{ $hero['image'] }}" @if ($hero['mobile_image']) media="(min-width: 768px)" @endif>
        @if ($hero['mobile_image'])
            <link rel="preload" as="image" href="{{ $hero['mobile_image'] }}" media="(max-width: 767px)">
        @endif
    </x-slot:head>

    {{-- Hero layar penuh: navbar transparan menyatu di atasnya (diatur di Admin › Tampilan Beranda) --}}
    <section @class(['relative isolate overflow-hidden', 'bg-ink text-white' => $light, 'bg-sand-100 text-ink' => ! $light])>
        <picture class="absolute inset-0 -z-20">
            @if ($hero['mobile_image'])
                <source media="(max-width: 767px)" srcset="{{ $hero['mobile_image'] }}">
            @endif
            <img src="{{ $hero['image'] }}" alt="" fetchpriority="high" decoding="async" class="size-full object-cover">
        </picture>
        @if ($light)
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink via-ink/35 to-ink/50 md:bg-gradient-to-r md:from-ink/85 md:via-ink/30 md:to-ink/10"></div>
            <div class="absolute inset-x-0 top-0 -z-10 h-40 bg-gradient-to-b from-ink/70 to-transparent"></div>
        @else
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-white/90 via-white/40 to-transparent md:bg-gradient-to-r md:from-white/80 md:via-white/25"></div>
        @endif

        <div @class(['shop-container flex min-h-[calc(100svh-36px)] max-h-[900px] flex-col justify-end pb-6 sm:pb-10', 'pt-28 lg:pt-36' => $light, 'pt-16' => ! $light])>
            <div class="max-w-2xl">
                @if ($hero['eyebrow'])
                    <p @class(['inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-bold tracking-[0.18em] uppercase sm:text-xs', 'bg-white/10 ring-1 ring-white/20 backdrop-blur' => $light, 'bg-ink/5 text-brand-800 ring-1 ring-ink/10' => ! $light])>
                        <span class="size-1.5 rounded-full bg-volt"></span>{{ $hero['eyebrow'] }}
                    </p>
                @endif
                <h1 class="display mt-4 text-[2.6rem] leading-[0.95] text-balance sm:text-6xl lg:text-[5.25rem]">{{ $hero['title'] }}</h1>
                @if ($hero['subtitle'])
                    <p @class(['mt-4 max-w-lg text-[15px] leading-relaxed sm:mt-5 sm:text-lg', 'text-white/80' => $light, 'text-stone-700' => ! $light])>{{ $hero['subtitle'] }}</p>
                @endif
                @if ($hero['primary'] || $hero['secondary'])
                    <div class="mt-7 grid grid-cols-2 gap-3 sm:mt-8 sm:flex sm:flex-wrap">
                        @foreach (array_filter([$hero['primary'], $hero['secondary']]) as $cta)
                            <a href="{{ $cta['url'] }}" @class([
                                'group inline-flex min-h-12 items-center justify-center gap-2 rounded-full px-4 text-center text-[13px] font-bold whitespace-nowrap transition sm:min-w-44 sm:px-7 sm:text-sm',
                                'bg-brand-400 text-ink hover:bg-brand-300' => $loop->first,
                                'ring-1 ring-inset' => ! $loop->first,
                                'text-white ring-white/50 hover:bg-white hover:text-ink' => ! $loop->first && $light,
                                'text-ink ring-ink/40 hover:bg-ink hover:text-white' => ! $loop->first && ! $light,
                                'col-span-2' => ! ($hero['primary'] && $hero['secondary']),
                            ])>{{ $cta['label'] }} @if ($loop->first)<x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" />@endif</a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Rel aktivitas: pintasan kategori berlapis kaca di dasar hero --}}
            @if ($categories->isNotEmpty())
                <div class="mt-10 sm:mt-14">
                    <p @class(['eyebrow', 'text-white/60' => $light, 'text-stone-500' => ! $light])>Pilih aktivitasmu</p>
                    <ul class="no-scrollbar -mx-4 mt-3 flex snap-x scroll-px-4 gap-2.5 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
                        @foreach ($categories as $category)
                            <li class="shrink-0 snap-start">
                                <a href="{{ route('products.index', ['kategori' => $category->slug]) }}"
                                   @class(['group flex items-center gap-3 rounded-2xl py-2.5 pr-5 pl-2.5 ring-1 backdrop-blur-md transition', 'bg-white/10 ring-white/15 hover:bg-white/20' => $light, 'bg-white/70 ring-ink/10 hover:bg-white' => ! $light])>
                                    <span class="grid size-10 place-items-center rounded-xl bg-brand-400 text-ink transition group-hover:bg-volt">
                                        <x-sport-icon :for="$category->name.' '.$category->slug" class="size-5" />
                                    </span>
                                    <span>
                                        <span class="block text-sm leading-tight font-bold">{{ $category->name }}</span>
                                        <span @class(['block text-[11px]', 'text-white/60' => $light, 'text-stone-500' => ! $light])>{{ $category->products_count }} produk</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- Pita slogan --}}
    <section class="overflow-hidden bg-volt py-3 text-ink sm:py-4" aria-label="{{ config('shop.motto') }}">
        <div class="flex w-max animate-marquee motion-reduce:animate-none" aria-hidden="true">
            @foreach (range(1, 2) as $half)
                <div class="flex shrink-0 items-center">
                    @foreach (range(1, 3) as $i)
                        @foreach (array_filter(array_merge(explode('•', (string) config('shop.motto')), [config('shop.hashtag')])) as $word)
                            <span class="px-5 text-lg font-extrabold tracking-tight whitespace-nowrap uppercase italic sm:px-6 sm:text-2xl">{{ trim($word) }}</span>
                            <x-logo variant="mark" mono class="h-4 text-ink sm:h-5" />
                        @endforeach
                    @endforeach
                </div>
            @endforeach
        </div>
        <p class="sr-only">{{ config('shop.motto') }} {{ config('shop.hashtag') }}</p>
    </section>

    {{-- Best Seller: korsel geser dengan nomor peringkat --}}
    @if ($bestSellers->isNotEmpty())
        <section class="mt-14 lg:mt-24" aria-labelledby="home-best"
                 x-data="{ go(d) { const t = this.$refs.track; t.scrollBy({ left: d * t.clientWidth * 0.8, behavior: 'smooth' }) } }">
            <div class="shop-container flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow text-brand-600">Paling diminati</p>
                    <h2 id="home-best" class="display mt-2 text-3xl leading-none sm:text-5xl">Best Seller</h2>
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

    {{-- Bento kategori: satu ubin besar + ubin kecil --}}
    @if ($bento->isNotEmpty())
        <section class="shop-container mt-14 lg:mt-24" aria-labelledby="home-categories">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow text-brand-600">Kategori</p>
                    <h2 id="home-categories" class="display mt-2 text-3xl leading-none sm:text-5xl">Belanja per aktivitas</h2>
                </div>
            </div>
            <ul class="mt-6 grid auto-rows-[150px] grid-cols-2 gap-3 sm:mt-8 sm:auto-rows-[190px] sm:gap-4 lg:grid-cols-4 lg:auto-rows-[210px]">
                @foreach ($bento as $category)
                    @php([$tone, $bubble] = $tones[$loop->index % count($tones)])
                    <li @class(['col-span-2 lg:row-span-2' => $loop->first])>
                        <a href="{{ route('products.index', ['kategori' => $category->slug]) }}"
                           class="group relative flex size-full flex-col justify-between overflow-hidden rounded-3xl p-4 transition duration-300 hover:shadow-xl hover:shadow-ink/10 sm:p-6 {{ $category->imageUrl() ? 'bg-ink text-white' : $tone }}">
                            @if ($category->imageUrl())
                                <img src="{{ $category->imageUrl() }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
                                <span class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/15 to-transparent"></span>
                            @else
                                <span @class(['absolute rounded-full transition duration-700 group-hover:scale-110', $bubble, '-right-16 -bottom-16 size-72 lg:size-96' => $loop->first, '-right-10 -bottom-10 size-36' => ! $loop->first]) aria-hidden="true"></span>
                                <x-sport-icon :for="$category->name.' '.$category->slug" @class(['absolute transition duration-500 group-hover:-rotate-6', 'top-5 right-5 size-20 sm:size-28 lg:top-8 lg:right-8 lg:size-44' => $loop->first, 'top-4 right-4 size-11 sm:size-14' => ! $loop->first]) />
                            @endif
                            <span class="relative grid size-9 place-items-center rounded-full bg-white/90 text-ink transition group-hover:bg-white sm:size-10">
                                <x-icon name="arrow-right" class="size-4 -rotate-45 transition group-hover:rotate-0" />
                            </span>
                            <span class="relative">
                                <span @class(['block leading-[0.95] font-extrabold italic uppercase', 'text-3xl sm:text-4xl lg:text-6xl' => $loop->first, 'text-base sm:text-xl' => ! $loop->first])>{{ $category->name }}</span>
                                <span class="mt-1 block text-xs font-medium opacity-80 sm:text-sm">{{ $category->products_count }} produk</span>
                                @if ($loop->first && $category->children->isNotEmpty())
                                    <span class="mt-3 hidden flex-wrap gap-1.5 sm:flex">
                                        @foreach ($category->children->take(4) as $child)
                                            <span class="rounded-full bg-white/25 px-3 py-1 text-xs font-semibold backdrop-blur">{{ $child->name }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
                <li>
                    <a href="{{ route('products.index') }}" class="group flex size-full flex-col justify-between rounded-3xl border-2 border-dashed border-stone-300 p-4 transition hover:border-ink sm:p-6">
                        <span class="grid size-9 place-items-center rounded-full bg-ink text-white sm:size-10"><x-icon name="arrow-right" class="size-4" /></span>
                        <span>
                            <span class="block text-base leading-tight font-extrabold uppercase italic sm:text-xl">Semua produk</span>
                            <span class="mt-1 block text-xs text-stone-500 sm:text-sm">Jelajahi katalog lengkap</span>
                        </span>
                    </a>
                </li>
            </ul>
        </section>
    @endif

    @if ($banners->isNotEmpty())
        @include('shop.partials.banners')
    @endif

    {{-- Promo: panel ajakan + produk diskon --}}
    @if ($onSale->isNotEmpty())
        <section class="shop-container mt-14 lg:mt-24" aria-labelledby="home-sale">
            <div class="grid gap-4 overflow-hidden rounded-[2rem] bg-sand-100 p-3 sm:gap-5 sm:p-5 lg:grid-cols-12">
                <div class="relative flex min-h-64 flex-col justify-between overflow-hidden rounded-3xl bg-accent-500 p-6 text-white sm:p-8 lg:col-span-4">
                    <span class="pointer-events-none absolute -right-20 -bottom-20 size-64 rounded-full border-[32px] border-white/20" aria-hidden="true"></span>
                    <span class="pointer-events-none absolute top-6 right-6 grid size-20 rotate-12 place-items-center rounded-full bg-volt text-center text-sm leading-none font-black text-ink uppercase italic" aria-hidden="true">Hemat!</span>
                    <div class="relative">
                        <p class="eyebrow text-white/80">Hemat lebih banyak</p>
                        <h2 id="home-sale" class="display mt-3 text-4xl leading-[0.95] sm:text-5xl">Lagi diskon</h2>
                        <p class="mt-3 max-w-xs text-sm leading-relaxed text-white/85">Perlengkapan latihan favorit dengan harga spesial. Stok promo terbatas.</p>
                    </div>
                    <a href="{{ route('products.index', ['promo' => 1]) }}" class="btn btn-pill relative mt-6 w-fit bg-white text-ink hover:bg-ink hover:text-white">Semua promo <x-icon name="arrow-right" class="size-4" /></a>
                </div>
                <div class="no-scrollbar -mx-3 flex snap-x scroll-px-3 gap-3 overflow-x-auto px-3 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-4 sm:overflow-visible sm:px-0 lg:col-span-8 lg:grid-cols-4">
                    @foreach ($onSale as $product)
                        <x-product-card :product="$product" class="w-[47%] shrink-0 snap-start sm:w-auto" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Kenapa NuFit --}}
    <section class="shop-container mt-14 lg:mt-24" aria-labelledby="home-why">
        <div class="grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-14">
            <div class="lg:col-span-5">
                <p class="eyebrow text-brand-600">Kenapa {{ config('shop.name') }}</p>
                <h2 id="home-why" class="display mt-3 text-4xl leading-[0.95] sm:text-6xl">
                    @foreach (array_map('trim', explode('•', (string) config('shop.motto'))) as $line)
                        <span @class(['block', 'text-brand-500' => $loop->iteration === 2])>{{ $line }}</span>
                    @endforeach
                </h2>
                <p class="mt-5 max-w-md leading-relaxed text-stone-600">{{ config('shop.tagline') }}. Kami memilih bahan yang nyaman dan awet supaya kamu fokus ke latihan, bukan ke perlengkapan.</p>
            </div>
            <ul class="grid grid-cols-2 gap-3 sm:gap-4 lg:col-span-7">
                @foreach ([
                    ['truck', 'Kirim se-Indonesia', 'Pilih JNE, SiCepat, J&T, POS, dan lainnya. Ongkir terlihat sebelum bayar.'],
                    ['shield', 'Produk resmi NuFit', 'Kualitas terkontrol, dari bahan sampai jahitan.'],
                    ['refresh', 'Garansi tukar 7 hari', 'Cacat produksi? Kami bantu tukar tanpa ribet.'],
                    ['lock', 'Pembayaran aman', 'Transfer bank, e-wallet, QRIS, dan kartu kredit.'],
                ] as [$icon, $title, $desc])
                    <li @class(['rounded-3xl p-5 sm:p-6', 'bg-ink text-white' => $loop->first, 'bg-sand-100' => ! $loop->first])>
                        <span @class(['grid size-11 place-items-center rounded-2xl', 'bg-brand-400 text-ink' => $loop->first, 'bg-white text-brand-600' => ! $loop->first])><x-icon :name="$icon" class="size-5" /></span>
                        <h3 class="mt-5 text-sm font-bold sm:text-base">{{ $title }}</h3>
                        <p @class(['mt-1.5 text-xs leading-relaxed sm:text-sm', 'text-white/65' => $loop->first, 'text-stone-600' => ! $loop->first])>{{ $desc }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    @include('shop.partials.latest-articles')
</x-layouts.shop>
