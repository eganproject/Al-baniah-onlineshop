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
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/30 to-ink/40 md:bg-gradient-to-r md:from-ink/80 md:via-ink/25 md:to-transparent"></div>
            <div class="absolute inset-x-0 top-0 -z-10 h-36 bg-gradient-to-b from-ink/60 to-transparent"></div>
        @else
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-white/90 via-white/40 to-transparent md:bg-gradient-to-r md:from-white/85 md:via-white/30"></div>
        @endif

        <div @class(['shop-container flex min-h-[calc(100svh-100px)] max-h-[860px] lg:min-h-[calc(100svh-36px)] flex-col justify-end pb-8 sm:pb-12', 'pt-28 lg:pt-36' => $light, 'pt-16' => ! $light])>
            <div class="max-w-[44rem]">
                @if ($hero['eyebrow'])
                    <p @class(['kicker', 'text-white/70' => $light, 'text-stone-600' => ! $light])>{{ $hero['eyebrow'] }}</p>
                @endif
                <h1 class="display mt-5 text-[2.6rem] leading-[1.02] sm:text-6xl lg:text-[4.75rem] lg:leading-[0.98]">{{ $hero['title'] }}</h1>
                @if ($hero['subtitle'])
                    <p @class(['mt-5 max-w-lg text-[15px] leading-relaxed sm:text-lg', 'text-white/75' => $light, 'text-stone-600' => ! $light])>{{ $hero['subtitle'] }}</p>
                @endif
                @if ($hero['primary'] || $hero['secondary'])
                    <div class="mt-8 grid grid-cols-2 gap-3 sm:flex sm:flex-wrap">
                        @foreach (array_filter([$hero['primary'], $hero['secondary']]) as $cta)
                            <a href="{{ $cta['url'] }}" @class([
                                'btn btn-pill group min-h-12 px-5 text-[13px] whitespace-nowrap sm:min-w-44 sm:px-7 sm:text-sm',
                                'btn-brand' => $loop->first,
                                'btn-glass' => ! $loop->first && $light,
                                'btn-outline' => ! $loop->first && ! $light,
                                'col-span-2' => ! ($hero['primary'] && $hero['secondary']),
                            ])>{{ $cta['label'] }} @if ($loop->first)<x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" />@endif</a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Rel koleksi: pintasan koleksi/kategori di dasar hero --}}
            @if ($activities->isNotEmpty() && config('shop.storefront.hero_rail', true))
                <div @class(['mt-12 border-t pt-5 sm:mt-16', 'border-white/15' => $light, 'border-ink/10' => ! $light])>
                    <p @class(['eyebrow', 'text-white/50' => $light, 'text-stone-500' => ! $light])>Pilih koleksimu</p>
                    <ul class="no-scrollbar -mx-4 mt-3 flex snap-x scroll-px-4 gap-2 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                        @foreach ($activities as $tile)
                            <li class="shrink-0 snap-start">
                                <a href="{{ $tile['url'] }}"
                                   @class(['group flex items-center gap-2.5 rounded-full py-2 pr-4 pl-2 text-sm font-medium ring-1 transition', 'bg-white/[0.06] ring-white/15 backdrop-blur-md hover:bg-white hover:text-ink' => $light, 'bg-white/70 ring-ink/10 hover:bg-ink hover:text-white' => ! $light])>
                                    <span @class(['grid size-8 place-items-center rounded-full transition', 'bg-white/10 group-hover:bg-sand-100' => $light, 'bg-sand-100 group-hover:bg-white/15' => ! $light])>
                                        <x-category-icon :name="$tile['icon']" :for="$tile['for']" class="size-[18px]" />
                                    </span>
                                    {{ $tile['name'] }}
                                    <span class="text-xs opacity-50">{{ $tile['count'] }}</span>
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
