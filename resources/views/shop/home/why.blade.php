{{-- Kenapa NuFit (teks & poin diatur di Admin › Tampilan Beranda) --}}
@php
    use App\Support\Storefront;
    $whyTitle = Storefront::text('why_title') ?? (string) config('shop.motto');
    $points = Storefront::whyPoints();
@endphp
<section class="shop-container mt-14 lg:mt-24" aria-labelledby="home-why">
    <div class="grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-14">
        <div class="lg:col-span-5">
            <p class="eyebrow text-brand-600">{{ Storefront::text('why_eyebrow', 'Kenapa '.config('shop.name')) }}</p>
            <h2 id="home-why" class="display mt-3 text-4xl leading-[0.95] sm:text-6xl">
                @foreach (array_map('trim', explode('•', $whyTitle)) as $line)
                    <span @class(['block', 'text-brand-500' => $loop->iteration === 2])>{{ $line }}</span>
                @endforeach
            </h2>
            <p class="mt-5 max-w-md leading-relaxed text-stone-600">{{ Storefront::text('why_text', config('shop.tagline').'. Kami memilih bahan yang nyaman dan awet supaya kamu fokus ke latihan, bukan ke perlengkapan.') }}</p>
        </div>
        @if ($points)
        <ul class="grid grid-cols-2 gap-3 sm:gap-4 lg:col-span-7">
            @foreach ($points as ['icon' => $icon, 'title' => $title, 'text' => $desc])
                <li @class(['rounded-3xl p-5 sm:p-6', 'bg-ink text-white' => $loop->first, 'bg-sand-100' => ! $loop->first])>
                    <span @class(['grid size-11 place-items-center rounded-2xl', 'bg-brand-400 text-ink' => $loop->first, 'bg-white text-brand-600' => ! $loop->first])><x-icon :name="$icon" class="size-5" /></span>
                    <h3 class="mt-5 text-sm font-bold sm:text-base">{{ $title }}</h3>
                    @if ($desc)<p @class(['mt-1.5 text-xs leading-relaxed sm:text-sm', 'text-white/65' => $loop->first, 'text-stone-600' => ! $loop->first])>{{ $desc }}</p>@endif
                </li>
            @endforeach
        </ul>
        @endif
    </div>
</section>
