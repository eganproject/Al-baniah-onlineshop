@php
    $hero = \App\Support\Storefront::hero();
    $light = $hero['light'];
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

            {{-- Rel aktivitas: pintasan koleksi/kategori berlapis kaca di dasar hero --}}
            @if ($activities->isNotEmpty() && config('shop.storefront.hero_rail', true))
                <div class="mt-10 sm:mt-14">
                    <p @class(['eyebrow', 'text-white/60' => $light, 'text-stone-500' => ! $light])>Pilih aktivitasmu</p>
                    <ul class="no-scrollbar -mx-4 mt-3 flex snap-x scroll-px-4 gap-2.5 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
                        @foreach ($activities as $tile)
                            <li class="shrink-0 snap-start">
                                <a href="{{ $tile['url'] }}"
                                   @class(['group flex items-center gap-3 rounded-2xl py-2.5 pr-5 pl-2.5 ring-1 backdrop-blur-md transition', 'bg-white/10 ring-white/15 hover:bg-white/20' => $light, 'bg-white/70 ring-ink/10 hover:bg-white' => ! $light])>
                                    <span class="grid size-10 place-items-center rounded-xl bg-brand-400 text-ink transition group-hover:bg-volt">
                                        <x-sport-icon :name="$tile['icon']" :for="$tile['for']" class="size-5" />
                                    </span>
                                    <span>
                                        <span class="block text-sm leading-tight font-bold">{{ $tile['name'] }}</span>
                                        <span @class(['block text-[11px]', 'text-white/60' => $light, 'text-stone-500' => ! $light])>{{ $tile['count'] }} produk</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- Seksi di bawah hero: urutan & tampil/sembunyi diatur di Admin › Tampilan Beranda --}}
    @foreach (\App\Support\Storefront::sections() as $section)
        @include('shop.home.'.$section, ['first' => $loop->first])
    @endforeach
</x-layouts.shop>
