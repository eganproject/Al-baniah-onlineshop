{{--
    Ikon garis kategori/koleksi Albaniah (24x24). Pilih lewat `name`, atau
    berikan `for` (nama/slug kategori) supaya ikon ditebak dari kata kuncinya.
    Contoh: <x-category-icon for="Mukena Travel" class="size-8" />
--}}
@props(['name' => null, 'for' => null, 'class' => 'size-6', 'stroke' => 1.5])
@php
    $paths = [
        'mukena' => 'M12 3a3.25 3.25 0 0 0-3.25 3.25v.75C8.75 9.25 6.5 11 4.9 13.4 3.7 15.2 3 17.6 3 21h18c0-3.4-.7-5.8-1.9-7.6C17.5 11 15.25 9.25 15.25 7v-.75A3.25 3.25 0 0 0 12 3Zm-2.5 7c.75.6 1.6.9 2.5.9s1.75-.3 2.5-.9M12 13.5V21',
        'veil' => 'M12 3a7 7 0 0 0-7 7v4.25c0 2.6 1 4.75 2.5 6.75h9c1.5-2 2.5-4.15 2.5-6.75V10a7 7 0 0 0-7-7Zm0 4a3.5 3.5 0 0 0-3.5 3.5V12a3.5 3.5 0 0 0 7 0v-1.5A3.5 3.5 0 0 0 12 7Z',
        'travel' => 'M9 6.75v-1.5a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5v1.5M4.5 6.75h15a1.5 1.5 0 0 1 1.5 1.5v10.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V8.25a1.5 1.5 0 0 1 1.5-1.5ZM8.25 6.75v13.5M15.75 6.75v13.5',
        'sajadah' => 'M6 3h12v18H6ZM9 18v-6.75a3 3 0 0 1 6 0V18M12 6.75h.01M3.75 4.5v1.5M3.75 9v1.5M3.75 13.5V15M3.75 18v1.5M20.25 4.5v1.5M20.25 9v1.5M20.25 13.5V15M20.25 18v1.5',
        'kids' => 'M12 8.25a2.625 2.625 0 1 0 0-5.25 2.625 2.625 0 0 0 0 5.25ZM9.75 9.75h4.5l3 9H6.75l3-9ZM10.5 18.75V21M13.5 18.75V21',
        'gift' => 'M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21M2.625 11.25h18.75c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H2.625c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
        'moon' => 'M14.25 3.25A8.75 8.75 0 1 0 20.5 17.6 7.25 7.25 0 0 1 14.25 3.25ZM18 5l.55 1.45L20 7l-1.45.55L18 9l-.55-1.45L16 7l1.45-.55L18 5Z',
        'lace' => 'M3 5.25h18M3 5.25v7.5a1.5 1.5 0 0 0 3 0 1.5 1.5 0 0 0 3 0 1.5 1.5 0 0 0 3 0 1.5 1.5 0 0 0 3 0 1.5 1.5 0 0 0 3 0 1.5 1.5 0 0 0 3 0v-7.5M4.5 18h.01M8.25 18h.01M12 18h.01M15.75 18h.01M19.5 18h.01M6.375 20.25h.01M10.125 20.25h.01M13.875 20.25h.01M17.625 20.25h.01',
        'premium' => 'M6.75 3.75h10.5l3.75 5.25L12 20.25 3 9l3.75-5.25ZM3 9h18M9.75 3.75 8.25 9 12 20.25 15.75 9l-1.5-5.25',
        'flower' => 'M12 14.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5ZM12 9.75C10.4 7.6 10.4 4.6 12 3c1.6 1.6 1.6 4.6 0 6.75Zm2.15 1.55c1.55-2.2 4.4-3.15 6.55-2.45-.35 2.2-2.95 3.95-5.6 3.95Zm-.8 2.55c2.6.8 4.45 3.2 4.35 5.45-2.2.35-4.6-1.5-5.2-4.15Zm-2.7 0c-.6 2.65-3 4.5-5.2 4.15-.1-2.25 1.75-4.65 4.35-5.45Zm-.8-2.55c-2.65 0-5.25-1.75-5.6-3.95 2.15-.7 5 .25 6.55 2.45Z',
        'accessories' => 'M12 6.75a1.875 1.875 0 1 1 1.875-1.875c0 .875-.55 1.3-1.1 1.75-.45.375-.775.775-.775 1.375v.75m0 0-8.6 5.6c-1 .65-.55 2.15.65 2.15h15.9c1.2 0 1.65-1.5.65-2.15L12 8.75ZM6.75 19.5h10.5',
        'sparkles' => 'M9.8 15.9 9 18.75l-.8-2.85a4.5 4.5 0 0 0-3.1-3.1L2.25 12l2.85-.8a4.5 4.5 0 0 0 3.1-3.1L9 5.25l.8 2.85a4.5 4.5 0 0 0 3.1 3.1l2.85.8-2.85.8a4.5 4.5 0 0 0-3.1 3.1ZM18.26 8.72 18 9.75l-.26-1.03a3.38 3.38 0 0 0-2.46-2.46L14.25 6l1.03-.26a3.38 3.38 0 0 0 2.46-2.46L18 2.25l.26 1.03a3.38 3.38 0 0 0 2.46 2.46l1.03.26-1.03.26a3.38 3.38 0 0 0-2.46 2.46Z',
    ];

    if (! $name && $for) {
        $text = \Illuminate\Support\Str::lower($for);
        // Urutan penting: kata kunci yang lebih spesifik dicek lebih dulu.
        $keywords = [
            'gift' => ['hampers', 'gift', 'kado', 'hadiah', 'parcel', 'seserahan', 'hantaran', 'box'],
            'sajadah' => ['sajadah', 'sejadah', 'prayer mat', 'karpet'],
            'kids' => ['anak', 'kids', 'remaja', 'balita'],
            'travel' => ['travel', 'umrah', 'umroh', 'haji', 'parasut', 'mini', 'katun mikro'],
            'veil' => ['tutup dagu', 'malaysia', 'jersey', 'hijab', 'bergo', 'khimar', 'jilbab'],
            'moon' => ['ramadan', 'ramadhan', 'lebaran', 'idul', 'fitri', 'eid'],
            'premium' => ['premium', 'velvet', 'sutra', 'silk', 'mewah', 'eksklusif'],
            'lace' => ['renda', 'brokat', 'bordir', 'lace', 'crinkle'],
            'accessories' => ['aksesoris', 'ciput', 'inner', 'tas', 'pouch', 'manset', 'perlengkapan'],
            'mukena' => ['mukena', 'mukenah', 'telekung', 'dewasa', 'polos', 'katun'],
        ];
        $name = collect($keywords)->search(fn ($words) => collect($words)->contains(fn ($w) => str_contains($text, $w))) ?: 'flower';
    }
@endphp
<svg {{ $attributes->class($class) }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="{{ $stroke }}" stroke="currentColor" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $paths[$name] ?? $paths['flower'] }}" />
</svg>
