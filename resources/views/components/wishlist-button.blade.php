@props(['product', 'variant' => 'icon'])
{{--
    Tombol simpan ke wishlist. variant="icon" (bulat, untuk kartu produk),
    "plain" (ikon hati tanpa latar, untuk kartu abu-abu di beranda), "outline"
    (bulat bergaris, di samping tombol beli) atau
    "button" (tombol penuh di halaman produk). Tamu diarahkan ke halaman masuk.
--}}
@php
    $user = auth()->user();
    $on = $user?->isCustomer() && app(\App\Services\Customer\WishlistService::class)->has($product);
    $iconClasses = $variant === 'icon' ? 'size-[18px]' : 'size-5';
    $buttonClasses = match ($variant) {
        'icon' => 'grid size-10 lg:size-9 place-items-center rounded-full bg-white/90 text-ink shadow-sm transition hover:scale-105 hover:bg-white',
        'plain' => 'grid size-10 lg:size-9 place-items-center rounded-full text-stone-500 transition hover:scale-110 hover:text-ink',
        'outline' => 'grid size-14 place-items-center rounded-full border border-stone-300 text-ink transition hover:border-ink',
        default => 'btn btn-outline btn-pill w-full py-4 text-[15px]',
    };
@endphp
@if ($user?->isAdmin())
    {{-- Admin tidak punya wishlist. --}}
@elseif ($user)
    <form method="POST" action="{{ route('wishlist.toggle', $product) }}" x-data="wishlistButton(@js($on))" @submit.prevent="toggle($el)" {{ $attributes }}>
        @csrf
        <button type="submit" class="{{ $buttonClasses }}" :aria-pressed="on.toString()" aria-pressed="{{ $on ? 'true' : 'false' }}"
                :aria-label="on ? 'Hapus dari wishlist' : 'Simpan ke wishlist'" aria-label="{{ $on ? 'Hapus dari wishlist' : 'Simpan ke wishlist' }}">
            <x-icon name="heart" class="{{ $iconClasses }} transition {{ $on ? 'fill-accent-500 text-accent-500' : '' }}" x-bind:class="{ 'fill-accent-500 text-accent-500': on }" />
            @if ($variant === 'button')
                <span x-text="on ? 'Tersimpan di wishlist' : 'Simpan ke wishlist'">{{ $on ? 'Tersimpan di wishlist' : 'Simpan ke wishlist' }}</span>
            @endif
        </button>
    </form>
@else
    <a href="{{ route('login') }}" aria-label="Masuk untuk menyimpan ke wishlist" {{ $attributes->class([$buttonClasses]) }}>
        <x-icon name="heart" class="{{ $iconClasses }}" />
        @if ($variant === 'button')<span>Simpan ke wishlist</span>@endif
    </a>
@endif
