{{--
    Logo resmi Albaniah: bunga rose gold (5 kelopak lengkung + 5 daun + huruf "A")
    dan tulisan "ALBANIAH" serif kapital, tagline "TEMANI IBADAHMU".
    Digambar ulang ke SVG dari file logo brand (public/images/brand).

    variant: full    = bunga + tulisan berdampingan (header, footer)
             stacked = bunga di atas tulisan, persis logo asli (halaman masuk, error)
             mark    = hanya bunga (ikon, ruang sempit, watermark)
             word    = hanya tulisan "ALBANIAH"
    tagline: tampilkan "TEMANI IBADAHMU" (bawaan: ya untuk full & stacked).
    dark:    untuk latar gelap (tulisan putih, bunga tetap rose gold).
    mono:    satu warna mengikuti `color` (currentColor), mis. untuk watermark.
    adaptive: tulisan ikut jadi putih saat header transparan (group/header data-clear).
    Atur ukuran lewat class tinggi, mis. <x-logo class="h-10" />.
--}}
@props(['variant' => 'full', 'dark' => false, 'mono' => false, 'tagline' => null, 'adaptive' => false])
@php
    $id = 'alb'.\Illuminate\Support\Str::random(6);
    $tagline ??= in_array($variant, ['full', 'stacked'], true);
    $hasHeight = preg_match('/(^|\s|:)h-/', (string) $attributes->get('class'));
    $hasWidth = preg_match('/(^|\s|:)w-/', (string) $attributes->get('class'));
    $markFill = $mono ? 'currentColor' : "url(#{$id})";
    $wordClass = $mono ? 'fill-current' : ($dark ? 'fill-white' : 'fill-ink'.($adaptive ? ' transition-[fill] group-data-[clear]/header:fill-white' : ''));
    $tagClass = $mono ? 'fill-current' : ($dark ? 'fill-brand-200' : 'fill-brand-600');

    // Kelopak lengkung (mengarah ke bawah, ujung bulat ke tengah) & daun, diputar 5x72°.
    $petal = 'M-27 118V67a27 27 0 0 1 54 0v51H10V93L0 79l-10 14v25Z';
    $leaf = 'M0 64Q14 95 0 126Q-14 95 0 64Z';
    $letter = 'M-14 20h7l3.2-9.5h7.6L7 20h7L2.4-20h-4.8ZM-4 4 0-8l4 12Z';

    [$viewBox, $markTransform] = match ($variant) {
        'mark' => ['-132 -132 264 264', null],
        'word' => ['0 0 440 64', null],
        'stacked' => [$tagline ? '0 0 440 330' : '0 0 440 296', 'translate(220 110) scale(.8)'],
        default => ['0 0 470 112', 'translate(56 56) scale(.4)'],
    };
@endphp
<svg {{ $attributes->class(['block shrink-0', 'w-auto' => ! $hasWidth, 'h-9' => ! $hasHeight]) }} xmlns="http://www.w3.org/2000/svg"
     viewBox="{{ $viewBox }}" role="img" aria-label="{{ config('shop.full_name') }}">
    <title>{{ config('shop.full_name') }}</title>
    @unless ($mono)
        <defs>
            <linearGradient id="{{ $id }}" x1="-120" y1="-120" x2="120" y2="125" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="#ecd7c2" />
                <stop offset=".55" stop-color="#cda083" />
                <stop offset="1" stop-color="#ad7c5f" />
            </linearGradient>
        </defs>
    @endunless

    @if ($variant !== 'word')
        <g @if ($markTransform) transform="{{ $markTransform }}" @endif fill="{{ $markFill }}">
            @foreach ([0, 72, 144, 216, 288] as $deg)
                <path d="{{ $petal }}" transform="rotate({{ $deg }})" />
                <path d="{{ $leaf }}" transform="rotate({{ $deg + 180 }})" />
            @endforeach
            <path d="{{ $letter }}" fill-rule="evenodd" />
        </g>
    @endif

    @if ($variant === 'word')
        <text x="220" y="50" text-anchor="middle" textLength="436" lengthAdjust="spacingAndGlyphs" class="{{ $wordClass }} font-logo" font-size="58" font-weight="500">ALBANIAH</text>
    @elseif ($variant === 'stacked')
        <text x="220" y="272" text-anchor="middle" textLength="420" lengthAdjust="spacingAndGlyphs" class="{{ $wordClass }} font-logo" font-size="56" font-weight="500">ALBANIAH</text>
        @if ($tagline)
            <text x="220" y="314" text-anchor="middle" textLength="236" lengthAdjust="spacing" class="{{ $tagClass }} font-sans" font-size="17" font-weight="400" letter-spacing="5">TEMANI IBADAHMU</text>
        @endif
    @else
        <text x="128" y="{{ $tagline ? 66 : 76 }}" textLength="338" lengthAdjust="spacingAndGlyphs" class="{{ $wordClass }} font-logo" font-size="56" font-weight="500">ALBANIAH</text>
        @if ($tagline)
            <text x="131" y="102" textLength="332" lengthAdjust="spacing" class="{{ $tagClass }} font-sans" font-size="17" font-weight="500" letter-spacing="7">TEMANI IBADAHMU</text>
        @endif
    @endif
</svg>
