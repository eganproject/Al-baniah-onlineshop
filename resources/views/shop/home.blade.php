@php
    $hero = \App\Support\Storefront::hero();
    // Foto hero tampil di dalam bingkai lengkung (potret). Tanpa foto sendiri: ilustrasi versi potret.
    $heroImage = $hero['custom'] ? $hero['image'] : ($hero['mobile_image'] ?? $hero['image']);
    $heroMobile = $hero['custom'] ? $hero['mobile_image'] : null;
    $pick = $bestSellers->first();
@endphp
<x-layouts.shop>
    <x-slot:head>
        {!! \App\Support\StructuredData::script(\App\Support\StructuredData::website()) !!}
        <link rel="preload" as="image" href="{{ $heroImage }}" @if ($heroMobile) media="(min-width: 768px)" @endif>
        @if ($heroMobile)
            <link rel="preload" as="image" href="{{ $heroMobile }}" media="(max-width: 767px)">
        @endif
    </x-slot:head>

    {{-- Hero: latar krem bermotif lengkung, foto dalam bingkai mihrab (diatur di Admin › Tampilan Beranda) --}}
    <section class="relative isolate overflow-hidden bg-sand-100">
        <div class="ornament-bg-light absolute inset-0 -z-10 opacity-70 [mask-image:radial-gradient(ellipse_at_70%_40%,#000,transparent_75%)]" aria-hidden="true"></div>
        <div class="absolute -bottom-40 -left-40 -z-10 size-[28rem] rounded-full bg-brand-200/40 blur-3xl" aria-hidden="true"></div>

        <div class="shop-container grid grid-cols-1 items-center gap-x-10 gap-y-10 pt-6 pb-12 md:grid-cols-12 md:py-14 lg:gap-x-16 lg:py-20">
            {{-- Bingkai foto --}}
            <div class="relative mx-auto w-full max-w-[22rem] sm:max-w-[26rem] md:col-span-6 md:col-start-7 md:row-start-1 md:max-w-none lg:col-span-5 lg:col-start-8">
                <div class="arch absolute inset-0 translate-x-3 -translate-y-3 border border-brand-300 sm:translate-x-4 sm:-translate-y-4" aria-hidden="true"></div>
                <picture class="arch relative block aspect-[5/6] overflow-hidden bg-ink shadow-2xl shadow-ink/15 md:aspect-[4/5]">
                    @if ($heroMobile)
                        <source media="(max-width: 767px)" srcset="{{ $heroMobile }}">
                    @endif
                    <img src="{{ $heroImage }}" alt="" fetchpriority="high" decoding="async" class="size-full object-cover object-[70%_50%]">
                </picture>

                {{-- Stempel lingkar "Temani Ibadahmu" --}}
                <div class="absolute -top-4 -left-3 grid size-[5.5rem] place-items-center rounded-full bg-ink text-brand-300 shadow-lg sm:-left-6 sm:size-28" aria-hidden="true">
                    <svg viewBox="0 0 100 100" class="absolute inset-0 size-full motion-safe:animate-[spin_30s_linear_infinite]">
                        <defs><path id="hero-stamp" d="M50 50m-37 0a37 37 0 1 1 74 0a37 37 0 1 1-74 0"/></defs>
                        <text class="fill-current font-logo text-[9px] uppercase"><textPath href="#hero-stamp" textLength="228" lengthAdjust="spacing">{{ config('shop.motto', 'Temani Ibadahmu') }} • {{ config('shop.name') }} •</textPath></text>
                    </svg>
                    <x-logo variant="mark" class="h-auto w-[38%]" />
                </div>

                {{-- Produk pilihan (best seller teratas) --}}
                @if ($pick)
                    <a href="{{ route('products.show', $pick) }}"
                       class="group absolute -bottom-5 left-1/2 flex w-[calc(100%-2rem)] max-w-72 -translate-x-1/2 items-center gap-3 rounded-2xl bg-white/95 p-2 pr-4 text-left shadow-xl shadow-ink/10 ring-1 ring-sand-200 backdrop-blur transition hover:-translate-y-0.5 md:-left-8 md:translate-x-0 md:hover:-translate-y-0.5">
                        <x-product-image :product="$pick" class="size-14 shrink-0 rounded-xl" />
                        <span class="min-w-0">
                            <span class="block text-[10px] font-semibold tracking-[0.18em] text-brand-700 uppercase">Paling dicari</span>
                            <span class="block truncate text-sm font-medium text-ink">{{ $pick->name }}</span>
                            <span class="block text-sm font-semibold text-ink">@rupiah($pick->price)</span>
                        </span>
                        <x-icon name="arrow-right" class="ml-auto size-4 shrink-0 text-stone-400 transition group-hover:translate-x-0.5 group-hover:text-ink" />
                    </a>
                @endif
            </div>

            {{-- Teks --}}
            <div class="min-w-0 text-center md:col-span-6 md:row-start-1 md:text-left lg:col-span-7">
                @if ($hero['eyebrow'])
                    <p class="eyebrow text-brand-700">{{ $hero['eyebrow'] }}</p>
                @endif
                <h1 class="display mt-3 text-[2.6rem] leading-[1.02] text-ink sm:text-6xl lg:text-7xl xl:text-[5.25rem]">{{ $hero['title'] }}</h1>
                @if ($hero['subtitle'])
                    <p class="mx-auto mt-5 max-w-lg text-[15px] leading-relaxed text-stone-600 sm:text-lg md:mx-0">{{ $hero['subtitle'] }}</p>
                @endif
                @if ($hero['primary'] || $hero['secondary'])
                    <div class="mt-8 flex flex-col gap-3 min-[400px]:flex-row min-[400px]:justify-center md:justify-start">
                        @foreach (array_filter([$hero['primary'], $hero['secondary']]) as $cta)
                            <a href="{{ $cta['url'] }}" @class([
                                'btn btn-pill group min-h-12 px-7 whitespace-nowrap',
                                'btn-ink' => $loop->first,
                                'btn-outline bg-white/60' => ! $loop->first,
                            ])>{{ $cta['label'] }} @if ($loop->first)<x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" />@endif</a>
                        @endforeach
                    </div>
                @endif

                {{-- Pintasan koleksi --}}
                @if ($activities->isNotEmpty() && config('shop.storefront.hero_rail', true))
                    <div class="mt-10 border-t border-ink/10 pt-5 lg:mt-12">
                        <p class="eyebrow text-stone-500">Pilih koleksimu</p>
                        <ul class="no-scrollbar -mx-4 mt-3 flex gap-2 overflow-x-auto px-4 md:mx-0 md:flex-wrap md:overflow-visible md:px-0">
                            @foreach ($activities as $tile)
                                <li class="shrink-0">
                                    <a href="{{ $tile['url'] }}" class="group inline-flex min-h-10 items-center gap-2 rounded-full bg-white/80 py-1.5 pr-4 pl-1.5 text-[13px] font-medium text-ink ring-1 ring-sand-300 transition hover:bg-ink hover:text-white hover:ring-ink">
                                        <span class="grid size-7 place-items-center rounded-full bg-sand-100 text-brand-600 transition group-hover:bg-white/15 group-hover:text-brand-300">
                                            <x-category-icon :name="$tile['icon']" :for="$tile['for']" class="size-4" />
                                        </span>
                                        {{ $tile['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Seksi di bawah hero: urutan & tampil/sembunyi diatur di Admin › Tampilan Beranda --}}
    @foreach (\App\Support\Storefront::sections() as $section)
        @include('shop.home.'.$section, ['first' => $loop->first])
    @endforeach
</x-layouts.shop>
