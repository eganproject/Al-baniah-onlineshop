@php
    $hero = \App\Support\Storefront::hero();
    $sorts = ['terlaris' => 'Terlaris', 'terbaru' => 'Terbaru', 'termurah' => 'Termurah', 'termahal' => 'Termahal'];
    // Peringkat tiap produk untuk setiap urutan (diurutkan di browser tanpa muat ulang).
    $byPrice = $bestSellers->sortBy('price')->pluck('id')->flip();
    $last = $bestSellers->count() - 1;
    // Warna kartu kategori bergantian supaya beranda terasa energik.
    $tones = [
        ['bg-brand-400 text-ink', 'bg-white/30'],
        ['bg-ink text-white', 'bg-white/10'],
        ['bg-volt text-ink', 'bg-white/40'],
        ['bg-accent-500 text-white', 'bg-white/15'],
        ['bg-brand-100 text-brand-900', 'bg-white/60'],
        ['bg-sand-200 text-ink', 'bg-white/60'],
    ];
    $collections = $categories->take(2);
@endphp
<x-layouts.shop>
    <x-slot:head>
        {!! \App\Support\StructuredData::script(\App\Support\StructuredData::website()) !!}
        <link rel="preload" as="image" href="{{ $hero['image'] }}" @if ($hero['mobile_image']) media="(min-width: 768px)" @endif>
        @if ($hero['mobile_image'])
            <link rel="preload" as="image" href="{{ $hero['mobile_image'] }}" media="(max-width: 767px)">
        @endif
    </x-slot:head>

    {{-- Hero: foto besar selebar layar dengan judul & tombol (diatur di Admin › Tampilan Beranda) --}}
    <section @class(['relative isolate flex overflow-hidden bg-ink', 'text-white' => $hero['light'], 'text-ink' => ! $hero['light']])>
        <picture class="absolute inset-0 -z-20">
            @if ($hero['mobile_image'])
                <source media="(max-width: 767px)" srcset="{{ $hero['mobile_image'] }}">
            @endif
            <img src="{{ $hero['image'] }}" alt="" fetchpriority="high" decoding="async" class="size-full object-cover">
        </picture>
        @if ($hero['light'])
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/30 to-transparent md:bg-gradient-to-r md:from-ink/70 md:via-ink/20"></div>
        @else
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-white/85 via-white/30 to-transparent md:bg-gradient-to-r md:from-white/75 md:via-white/20"></div>
        @endif

        {{-- Hampir setinggi layar, disisakan supaya strip keunggulan langsung terlihat. --}}
        <div class="shop-container flex min-h-[calc(100svh-190px)] max-h-[780px] items-end pt-24 pb-10 sm:pb-14 md:min-h-[min(calc(100vh-200px),720px)] md:items-center md:py-20 md:[@media(max-height:760px)]:py-8">
            <div class="w-full max-w-xl">
                @if ($hero['eyebrow'])
                    <p @class(['inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-bold tracking-[0.18em] uppercase sm:text-xs', 'bg-white/10 text-white ring-1 ring-white/20 backdrop-blur' => $hero['light'], 'bg-ink/5 text-brand-800 ring-1 ring-ink/10' => ! $hero['light']])>
                        <span class="size-1.5 rounded-full bg-volt"></span>{{ $hero['eyebrow'] }}
                    </p>
                @endif
                <h1 class="mt-4 text-[2.5rem] leading-[1] font-extrabold tracking-tight text-balance italic sm:text-6xl lg:text-7xl md:[@media(max-height:760px)]:text-5xl">{{ $hero['title'] }}</h1>
                @if ($hero['subtitle'])
                    <p @class(['mt-4 max-w-md text-[15px] leading-relaxed sm:text-lg', 'text-white/80' => $hero['light'], 'text-stone-700' => ! $hero['light']])>{{ $hero['subtitle'] }}</p>
                @endif
                @if ($hero['primary'] || $hero['secondary'])
                    <div class="mt-7 grid grid-cols-2 gap-3 sm:mt-9 sm:flex sm:flex-wrap">
                        @foreach (array_filter([$hero['primary'], $hero['secondary']]) as $cta)
                            <a href="{{ $cta['url'] }}" @class([
                                'group inline-flex min-h-12 items-center justify-center gap-2 rounded-full px-4 text-center text-[13px] font-bold whitespace-nowrap transition sm:min-w-44 sm:px-7 sm:text-sm',
                                'bg-brand-400 text-ink hover:bg-brand-300' => $loop->first,
                                'ring-1 ring-inset' => ! $loop->first,
                                'text-white ring-white/50 hover:bg-white hover:text-ink' => ! $loop->first && $hero['light'],
                                'text-ink ring-ink/40 hover:bg-ink hover:text-white' => ! $loop->first && ! $hero['light'],
                                'col-span-2' => ! ($hero['primary'] && $hero['secondary']),
                            ])>{{ $cta['label'] }} @if ($loop->first)<x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" />@endif</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Keunggulan --}}
    <section class="border-b border-stone-200 bg-white" aria-label="Keunggulan belanja di {{ config('shop.name') }}">
        <ul class="shop-container no-scrollbar flex snap-x scroll-px-4 gap-6 overflow-x-auto py-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:py-5">
            @foreach ([
                ['truck', 'Kirim se-Indonesia', 'JNE, SiCepat, J&T & lainnya'],
                ['shield', 'Produk resmi NuFit', 'Kualitas terkontrol'],
                ['refresh', 'Garansi tukar 7 hari', 'Bila cacat produksi'],
                ['lock', 'Pembayaran aman', 'Transfer, e-wallet, QRIS'],
            ] as [$icon, $title, $desc])
                <li class="flex min-w-[220px] shrink-0 snap-start items-center gap-3 sm:min-w-0">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700"><x-icon :name="$icon" class="size-5" /></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold">{{ $title }}</span>
                        <span class="block truncate text-xs text-stone-500">{{ $desc }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Kategori: belanja sesuai aktivitas --}}
    @if ($categories->isNotEmpty())
        <section class="mt-12 lg:mt-20" aria-labelledby="home-categories">
            <div class="shop-container flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow text-brand-600">Kategori</p>
                    <h2 id="home-categories" class="display mt-2 text-3xl leading-none sm:text-5xl">Pilih aktivitasmu</h2>
                </div>
                <a href="{{ route('products.index') }}" class="link-underline shrink-0 text-sm font-semibold">Semua produk</a>
            </div>
            <div class="shop-container mt-6 sm:mt-8">
                <ul class="no-scrollbar -mx-4 flex snap-x scroll-px-4 gap-3 overflow-x-auto px-4 pb-2 sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-4 sm:overflow-visible sm:px-0 lg:grid-cols-4">
                    @foreach ($categories as $category)
                        @php([$tone, $bubble] = $tones[$loop->index % count($tones)])
                        <li class="w-[42%] shrink-0 snap-start sm:w-auto">
                            <a href="{{ route('products.index', ['kategori' => $category->slug]) }}"
                               class="group relative flex aspect-[4/5] flex-col justify-between overflow-hidden rounded-3xl lg:aspect-[5/4] p-4 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-ink/10 sm:p-5 {{ $category->imageUrl() ? 'bg-ink text-white' : $tone }}">
                                @if ($category->imageUrl())
                                    <img src="{{ $category->imageUrl() }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
                                    <span class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/10 to-transparent"></span>
                                @else
                                    <span class="absolute -right-10 -bottom-10 size-40 rounded-full {{ $bubble }} transition duration-500 group-hover:scale-110" aria-hidden="true"></span>
                                    <x-sport-icon :for="$category->name.' '.$category->slug" class="absolute top-3 right-3 size-12 opacity-90 transition duration-500 group-hover:-rotate-6 sm:top-auto sm:right-4 sm:bottom-4 sm:size-20" />
                                @endif
                                <span class="relative grid size-9 place-items-center rounded-full bg-white/90 text-ink transition group-hover:bg-white sm:size-10">
                                    <x-icon name="arrow-right" class="size-4 -rotate-45 transition group-hover:rotate-0" />
                                </span>
                                <span class="relative">
                                    <span class="block text-base leading-tight font-extrabold italic uppercase sm:text-lg">{{ $category->name }}</span>
                                    <span class="mt-0.5 block text-xs font-medium opacity-80">{{ $category->products_count }} produk</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Best Seller --}}
    @if ($bestSellers->isNotEmpty())
        <section class="shop-container mt-14 lg:mt-24" x-data="{ sort: 'terlaris' }" aria-labelledby="home-best">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow text-brand-600">{{ $bestSellers->count() }} produk paling diminati</p>
                    <h2 id="home-best" class="display mt-2 text-3xl leading-none sm:text-5xl">Best Seller</h2>
                </div>
                <div class="no-scrollbar -mx-4 flex w-[calc(100%+2rem)] gap-2 overflow-x-auto px-4 sm:mx-0 sm:w-auto sm:px-0" role="group" aria-label="Urutkan best seller">
                    @foreach ($sorts as $value => $label)
                        <button type="button" @click="sort = '{{ $value }}'" :aria-pressed="(sort === '{{ $value }}').toString()"
                                :class="sort === '{{ $value }}' && 'chip-active'" class="chip py-1.5 text-[13px]">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-x-3 gap-y-6 sm:mt-8 sm:gap-x-5 sm:gap-y-8 md:grid-cols-3 xl:grid-cols-4">
                @foreach ($bestSellers as $product)
                    @php($ranks = ['terlaris' => $product->rank_best, 'terbaru' => $product->rank_new, 'termurah' => $byPrice[$product->id], 'termahal' => $last - $byPrice[$product->id]])
                    <x-product-tile :product="$product" :eager="$loop->index < 4" x-bind:style="'order: ' + {{ json_encode($ranks) }}[sort]" />
                @endforeach
            </div>
            <div class="mt-10 text-center">
                <a href="{{ route('products.index') }}" x-bind:href="@js(route('products.index')) + '?urut=' + sort"
                   class="btn btn-outline btn-pill">Lihat semua produk <x-icon name="arrow-right" class="size-4" /></a>
            </div>
        </section>
    @else
        <section class="shop-container mt-14">
            <x-empty-state title="Belum ada produk" />
        </section>
    @endif

    @if ($banners->isNotEmpty())
        @include('shop.partials.banners')
    @endif

    {{-- Koleksi unggulan: dua kategori teratas sebagai banner besar --}}
    @if ($collections->count() === 2)
        <section class="shop-container mt-14 grid gap-3 sm:gap-5 md:grid-cols-2 lg:mt-24" aria-label="Koleksi pilihan">
            @foreach ($collections as $category)
                <a href="{{ route('products.index', ['kategori' => $category->slug]) }}"
                   @class(['group relative flex min-h-72 flex-col justify-end overflow-hidden rounded-3xl p-6 sm:min-h-96 sm:p-10', 'bg-ink text-white' => $loop->first, 'bg-brand-400 text-ink' => ! $loop->first])>
                    @if ($category->imageUrl())
                        <img src="{{ $category->imageUrl() }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-[1.03]">
                        <span class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/20 to-transparent"></span>
                    @else
                        <span @class(['absolute -top-16 -right-16 size-72 rounded-full border-[36px] transition duration-700 group-hover:rotate-12 sm:size-96', 'border-brand-400' => $loop->first, 'border-white/50' => ! $loop->first]) aria-hidden="true"></span>
                        <span @class(['absolute right-8 bottom-8 size-12 rounded-full sm:right-12 sm:bottom-12 sm:size-16', 'bg-volt' => $loop->first, 'bg-ink' => ! $loop->first]) aria-hidden="true"></span>
                        <x-sport-icon :for="$category->name.' '.$category->slug" class="absolute top-6 right-6 size-24 opacity-90 transition duration-700 group-hover:-rotate-6 sm:top-10 sm:right-12 sm:size-32" />
                    @endif
                    <span @class(['relative eyebrow', 'text-brand-300' => $loop->first || $category->imageUrl(), 'text-ink/70' => ! $loop->first && ! $category->imageUrl()])>Koleksi</span>
                    <span @class(['relative display mt-2 text-4xl leading-[0.95] sm:text-6xl', 'text-white' => $category->imageUrl()])>{{ $category->name }}</span>
                    <span @class(['relative mt-5 inline-flex w-fit items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold transition', 'bg-white text-ink group-hover:bg-brand-400' => $loop->first || $category->imageUrl(), 'bg-ink text-white group-hover:bg-white group-hover:text-ink' => ! $loop->first && ! $category->imageUrl()])>
                        Belanja sekarang <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" />
                    </span>
                </a>
            @endforeach
        </section>
    @endif

    {{-- Pita slogan --}}
    <section class="mt-14 overflow-hidden bg-volt py-4 text-ink lg:mt-24" aria-label="{{ config('shop.motto') }}">
        <div class="flex w-max animate-marquee motion-reduce:animate-none" aria-hidden="true">
            @foreach (range(1, 2) as $half)
                <div class="flex shrink-0 items-center">
                    @foreach (range(1, 3) as $i)
                        @foreach (array_filter(array_merge(explode('•', (string) config('shop.motto')), [config('shop.hashtag')])) as $word)
                            <span class="px-6 text-xl font-extrabold tracking-tight whitespace-nowrap uppercase italic sm:text-3xl">{{ trim($word) }}</span>
                            <x-logo variant="mark" mono class="h-5 text-ink sm:h-6" />
                        @endforeach
                    @endforeach
                </div>
            @endforeach
        </div>
        <p class="sr-only">{{ config('shop.motto') }} {{ config('shop.hashtag') }}</p>
    </section>

    {{-- Promo --}}
    @if ($onSale->isNotEmpty())
        <section class="shop-container mt-14 lg:mt-24" aria-labelledby="home-sale">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow text-accent-600">Hemat lebih banyak</p>
                    <h2 id="home-sale" class="display mt-2 text-3xl leading-none sm:text-5xl">Lagi diskon</h2>
                </div>
                <a href="{{ route('products.index', ['promo' => 1]) }}" class="link-underline shrink-0 text-sm font-semibold">Semua promo</a>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-x-3 gap-y-8 sm:mt-8 sm:gap-x-5 lg:grid-cols-4">
                @foreach ($onSale as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    @include('shop.partials.latest-articles')
</x-layouts.shop>
