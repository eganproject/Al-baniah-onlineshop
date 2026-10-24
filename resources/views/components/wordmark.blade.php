{{-- Logo lengkap Albaniah untuk header/halaman publik. Lihat <x-logo>. --}}
@props(['dark' => false, 'size' => 'md'])
<x-logo :dark="$dark" {{ $attributes->class([
    'h-8 lg:h-9' => $size === 'md',
    'h-11 sm:h-12' => $size === 'lg',
]) }} />
