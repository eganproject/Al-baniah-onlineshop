{{-- Pita slogan (teks diatur di Admin › Tampilan Beranda, dipisah "•") --}}
@php($words = \App\Support\Storefront::ribbonWords())
@if ($words)
    <section @class(['overflow-hidden border-b border-stone-200 py-4 text-ink sm:py-5', 'mt-16 border-t lg:mt-28' => ! $first]) aria-label="{{ implode(' ', $words) }}">
        <div class="flex w-max animate-marquee motion-reduce:animate-none" aria-hidden="true">
            @foreach (range(1, 2) as $half)
                <div class="flex shrink-0 items-center">
                    @foreach (range(1, max(1, (int) ceil(6 / count($words)))) as $i)
                        @foreach ($words as $word)
                            <span class="px-6 font-logo text-sm font-medium tracking-[0.22em] whitespace-nowrap uppercase sm:px-8 sm:text-base">{{ $word }}</span>
                            <span class="size-1.5 shrink-0 rotate-45 bg-brand-400"></span>
                        @endforeach
                    @endforeach
                </div>
            @endforeach
        </div>
        <p class="sr-only">{{ implode(' ', $words) }}</p>
    </section>
@endif
