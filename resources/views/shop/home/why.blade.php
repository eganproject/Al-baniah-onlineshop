{{-- Kenapa Albaniah: pita navy bermotif lengkung, poin dalam kartu berpuncak kubah (diatur di Admin › Tampilan Beranda) --}}
@php
    use App\Support\Storefront;
    $whyTitle = Storefront::text('why_title') ?? (string) config('shop.motto');
    $points = Storefront::whyPoints();
@endphp
<section class="section-gap relative isolate overflow-hidden bg-ink text-white" aria-labelledby="home-why">
    <div class="ornament-bg absolute inset-0 -z-10" aria-hidden="true"></div>
    <div class="shop-container py-16 lg:py-24">
        <div class="section-head">
            <span class="ornament-mark" aria-hidden="true"><span class="size-1.5 rotate-45 bg-current"></span></span>
            <p class="eyebrow mt-3 text-brand-300">{{ Storefront::text('why_eyebrow', 'Kenapa '.config('shop.name')) }}</p>
            <h2 id="home-why" class="display mt-3 text-[2.25rem] leading-[1.05] sm:text-5xl lg:text-6xl">
                @foreach (array_map('trim', explode('•', $whyTitle)) as $line)
                    <span @class(['block', 'text-brand-300 italic' => $loop->iteration === 2])>{{ $line }}</span>
                @endforeach
            </h2>
            <p class="mt-5 max-w-xl leading-relaxed text-white/65">{{ Storefront::text('why_text', config('shop.tagline').'. Kami memilih bahan yang adem, jatuh, dan tidak menerawang supaya kamu bisa fokus beribadah dengan tenang.') }}</p>
        </div>
        @if ($points)
            <ul class="mt-12 grid gap-4 sm:grid-cols-2 lg:mt-16 lg:grid-cols-4 lg:gap-6">
                @foreach ($points as ['icon' => $icon, 'title' => $title, 'text' => $desc])
                    <li class="arch-soft flex flex-col items-center border border-white/10 bg-white/[0.04] px-6 pt-8 pb-7 text-center sm:pt-10 sm:pb-8 lg:pt-12">
                        <span class="grid size-12 place-items-center rounded-full bg-brand-400/15 text-brand-300 ring-1 ring-brand-400/30"><x-icon :name="$icon" class="size-5" /></span>
                        <h3 class="mt-5 text-base font-semibold">{{ $title }}</h3>
                        @if ($desc)<p class="mt-1.5 text-sm leading-relaxed text-white/60">{{ $desc }}</p>@endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
