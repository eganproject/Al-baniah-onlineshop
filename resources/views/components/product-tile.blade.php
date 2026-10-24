{{-- Kartu produk untuk beranda (Best Seller). Sama dengan <x-product-card>, plus nomor peringkat. --}}
@props(['product', 'eager' => false, 'rank' => null])
<x-product-card :product="$product" :eager="$eager" :rank="$rank" {{ $attributes }} />
