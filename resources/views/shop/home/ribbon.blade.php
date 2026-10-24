{{-- Pita slogan: deret nilai Albaniah di pita navy (teks diatur di Admin › Tampilan Beranda, dipisah "•") --}}
@php($words = \App\Support\Storefront::ribbonWords())
@if ($words)
    <section @class(['bg-ink text-white', 'section-gap' => ! $first]) aria-label="{{ implode(', ', $words) }}">
        <ul class="shop-container flex flex-wrap items-center justify-center gap-x-6 gap-y-3 py-5 sm:gap-x-10 sm:py-6">
            @foreach ($words as $word)
                <li class="flex items-center gap-3 font-logo text-[11px] font-medium tracking-[0.26em] whitespace-nowrap uppercase sm:text-[13px]">
                    <span class="size-1.5 shrink-0 rotate-45 bg-brand-400" aria-hidden="true"></span>{{ $word }}
                </li>
            @endforeach
        </ul>
    </section>
@endif
