{{-- Pita slogan (teks diatur di Admin › Tampilan Beranda, dipisah "•") --}}
@php($words = \App\Support\Storefront::ribbonWords())
@if ($words)
    <section @class(['overflow-hidden bg-volt py-3 text-ink sm:py-4', 'mt-14 lg:mt-24' => ! $first]) aria-label="{{ implode(' ', $words) }}">
        <div class="flex w-max animate-marquee motion-reduce:animate-none" aria-hidden="true">
            @foreach (range(1, 2) as $half)
                <div class="flex shrink-0 items-center">
                    @foreach (range(1, max(1, (int) ceil(6 / count($words)))) as $i)
                        @foreach ($words as $word)
                            <span class="px-5 text-lg font-extrabold tracking-tight whitespace-nowrap uppercase italic sm:px-6 sm:text-2xl">{{ $word }}</span>
                            <x-logo variant="mark" mono class="h-4 text-ink sm:h-5" />
                        @endforeach
                    @endforeach
                </div>
            @endforeach
        </div>
        <p class="sr-only">{{ implode(' ', $words) }}</p>
    </section>
@endif
