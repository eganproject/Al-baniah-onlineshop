{{--
    Ikon olahraga untuk kategori (garis, 24x24). Pilih lewat `name`, atau
    berikan `for` (nama/slug kategori) supaya ikon ditebak dari kata kuncinya.
    Contoh: <x-sport-icon for="Yoga & Pilates" class="size-8" />
--}}
@props(['name' => null, 'for' => null, 'class' => 'size-6'])
@php
    $paths = [
        'running' => 'M3 16.5h18V18a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18v-1.5Zm0 0V8.25l3.25-1 2 2.25 3-.75 1.25 2.75 5.25 1.5c2.1.6 4.25 1.8 4.25 4.5M9.75 12.25l1.5-1.25M11.75 14l1.5-1.25',
        'yoga' => 'M12 6.75a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5ZM12 9v5.25M12 10.5l-5.25 2.25M12 10.5l5.25 2.25M6.75 19.5c1.5-1.75 3.25-2.75 5.25-2.75s3.75 1 5.25 2.75M3 21.75h18',
        'workout' => 'M6.75 6v12M17.25 6v12M3.75 9v6M20.25 9v6M6.75 12h10.5',
        'padel' => 'M14.25 2.75a6 6 0 0 1 4.24 10.24l-1.41 1.42a6 6 0 0 1-8.49-8.49l1.42-1.41a6 6 0 0 1 4.24-1.76ZM8.6 15.4l-4.85 4.85M5.25 21.75l-3-3M19.5 21a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z',
        'swimming' => 'M2.25 18c1.63 0 1.63-1.5 3.25-1.5s1.63 1.5 3.25 1.5S10.38 16.5 12 16.5s1.63 1.5 3.25 1.5 1.63-1.5 3.25-1.5S20.13 18 21.75 18M4 11.25a2.75 2.75 0 1 0 5.5 0 2.75 2.75 0 0 0-5.5 0Zm10.5 0a2.75 2.75 0 1 0 5.5 0 2.75 2.75 0 0 0-5.5 0Zm-5 0h5M4 11.25H2.25M20 11.25h1.75',
        'support' => 'M4.9 14.6 14.6 4.9a3 3 0 0 1 4.24 0l.26.26a3 3 0 0 1 0 4.24l-9.7 9.7a3 3 0 0 1-4.24 0l-.26-.26a3 3 0 0 1 0-4.24ZM9.5 9.5l5 5M10.75 12.25h.01M12.25 10.75h.01M11.75 13.75h.01M13.25 12.25h.01',
        'essentials' => 'M9.75 2.25h4.5M10.5 2.25v2.5L8.25 7.5v12a2.25 2.25 0 0 0 2.25 2.25h3a2.25 2.25 0 0 0 2.25-2.25v-12L13.5 4.75v-2.5M8.25 11.25h7.5M8.25 16.5h7.5',
        'apparel' => 'M9 3 4.5 5.25 2.25 10.5l3.75 1.5v8.25h12V12l3.75-1.5-2.25-5.25L15 3a3 3 0 0 1-6 0Z',
        'hiking' => 'M2.25 20.25 9 9l4 6.25 2.75-3.75 6 8.75H2.25ZM17.25 7.5a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z',
        'earphones' => 'M3.75 15v-3a8.25 8.25 0 0 1 16.5 0v3M3.75 15a1.5 1.5 0 0 1 1.5-1.5h.75a1.5 1.5 0 0 1 1.5 1.5v4.5a1.5 1.5 0 0 1-1.5 1.5h-.75a1.5 1.5 0 0 1-1.5-1.5V15Zm16.5 0a1.5 1.5 0 0 0-1.5-1.5H18a1.5 1.5 0 0 0-1.5 1.5v4.5A1.5 1.5 0 0 0 18 21h.75a1.5 1.5 0 0 0 1.5-1.5V15Z',
        'sparkles' => 'M9.8 15.9 9 18.75l-.8-2.85a4.5 4.5 0 0 0-3.1-3.1L2.25 12l2.85-.8a4.5 4.5 0 0 0 3.1-3.1L9 5.25l.8 2.85a4.5 4.5 0 0 0 3.1 3.1l2.85.8-2.85.8a4.5 4.5 0 0 0-3.1 3.1ZM18.26 8.72 18 9.75l-.26-1.03a3.38 3.38 0 0 0-2.46-2.46L14.25 6l1.03-.26a3.38 3.38 0 0 0 2.46-2.46L18 2.25l.26 1.03a3.38 3.38 0 0 0 2.46 2.46l1.03.26-1.03.26a3.38 3.38 0 0 0-2.46 2.46Z',
    ];

    if (! $name && $for) {
        $text = \Illuminate\Support\Str::lower($for);
        $keywords = [
            'running' => ['run', 'lari', 'jogging', 'marathon', 'sepatu', 'kaos kaki', 'socks'],
            'yoga' => ['yoga', 'pilates', 'matras', 'mat'],
            'workout' => ['workout', 'gym', 'fitness', 'dumbbell', 'kettlebell', 'band', 'shape'],
            'padel' => ['padel', 'tenis', 'tennis', 'badminton', 'raket'],
            'swimming' => ['swim', 'renang', 'kacamata'],
            'support' => ['support', 'recovery', 'tape', 'bandage', 'cedera', 'deker'],
            'essentials' => ['essential', 'botol', 'bottle', 'pouch', 'tas', 'handuk', 'belt'],
            'apparel' => ['apparel', 'pakaian', 'baju', 'top', 'pants', 'celana', 'skirt', 'rok', 'jacket', 'hijab', 'hat', 'topi', 'fashion'],
            'hiking' => ['hiking', 'outdoor', 'gunung', 'trail'],
            'earphones' => ['earphone', 'headset', 'audio'],
        ];
        $name = collect($keywords)->search(fn ($words) => collect($words)->contains(fn ($w) => str_contains($text, $w))) ?: 'sparkles';
    }
@endphp
<svg {{ $attributes->class($class) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $paths[$name] ?? $paths['sparkles'] }}" />
</svg>
