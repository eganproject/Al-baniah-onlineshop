{{-- Kenapa NuFit (teks & poin diatur di Admin › Tampilan Beranda) --}}
@php
    use App\Support\Storefront;
    $whyTitle = Storefront::text('why_title') ?? (string) config('shop.motto');
    $points = Storefront::whyPoints();
@endphp
<section class="section-gap border-y border-stone-200 bg-sand-50" aria-labelledby="home-why">
    <div class="shop-container grid gap-10 py-14 lg:grid-cols-12 lg:gap-16 lg:py-20">
        <div class="lg:col-span-5">
            <p class="kicker">{{ Storefront::text('why_eyebrow', 'Kenapa '.config('shop.name')) }}</p>
            <h2 id="home-why" class="display mt-4 text-[2rem] leading-[1.05] sm:text-5xl">
                @foreach (array_map('trim', explode('•', $whyTitle)) as $line)
                    <span @class(['block', 'text-brand-600' => $loop->iteration === 2])>{{ $line }}</span>
                @endforeach
            </h2>
            <p class="mt-5 max-w-md leading-relaxed text-stone-600">{{ Storefront::text('why_text', config('shop.tagline').'. Kami memilih bahan yang nyaman dan awet supaya kamu fokus ke latihan, bukan ke perlengkapan.') }}</p>
        </div>
        @if ($points)
            <ul class="grid gap-px overflow-hidden rounded-2xl border border-stone-200 bg-stone-200 sm:grid-cols-2 lg:col-span-7">
                @foreach ($points as ['icon' => $icon, 'title' => $title, 'text' => $desc])
                    <li class="flex gap-4 bg-white p-5 sm:flex-col sm:p-7">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-700"><x-icon :name="$icon" class="size-5" /></span>
                        <div>
                            <h3 class="text-[15px] font-semibold text-ink sm:mt-1">{{ $title }}</h3>
                            @if ($desc)<p class="mt-1 text-sm leading-relaxed text-stone-500">{{ $desc }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
