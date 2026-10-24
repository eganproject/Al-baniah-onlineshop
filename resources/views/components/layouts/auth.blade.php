@props(['title' => null, 'heading', 'subheading' => null])
<x-layouts.base :title="$title" body-class="bg-white text-stone-800">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panel brand --}}
        <div class="relative hidden overflow-hidden bg-ink lg:block">
            <img src="{{ asset(\App\Support\Storefront::DEFAULT_IMAGE_MOBILE) }}" alt="" class="pointer-events-none absolute inset-0 size-full object-cover opacity-90" aria-hidden="true">
            <div class="relative flex h-full flex-col justify-between p-12 text-white">
                <a href="{{ route('home') }}" class="self-start"><x-wordmark dark size="lg" /></a>
                <div class="max-w-md">
                    <p class="kicker text-white/60">{{ config('shop.hashtag', '#BEFITWITHNUFIT') }}</p>
                    <h2 class="display mt-5 text-5xl leading-[1.02]">Healthy body, healthy mind, happy life.</h2>
                    <p class="mt-5 text-base text-white/60">Perlengkapan lari, yoga, pilates, renang, padel, dan workout yang menemani setiap latihanmu.</p>
                    <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-white/75">
                        @foreach ([['shield', 'Produk resmi'], ['truck', 'Kirim se-Indonesia'], ['lock', 'Bayar aman']] as [$icon, $label])
                            <li class="flex items-center gap-2"><x-icon :name="$icon" class="size-4 text-brand-400" /> {{ $label }}</li>
                        @endforeach
                    </ul>
                </div>
                <p class="text-xs text-white/40">&copy; {{ date('Y') }} {{ config('shop.full_name') }}</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="flex flex-col px-5 py-4 sm:px-10 sm:py-8">
            <div class="flex items-center justify-between lg:justify-end">
                <a href="{{ route('home') }}" class="py-1 lg:hidden"><x-wordmark /></a>
                <a href="{{ route('home') }}" class="-mr-2 inline-flex min-h-11 items-center gap-1.5 px-2 text-sm font-medium text-stone-500 hover:text-stone-800 lg:mr-0 lg:min-h-0 lg:px-0">
                    <x-icon name="arrow-left" class="size-4" /> Kembali ke toko
                </a>
            </div>
            <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-8 sm:py-10">
                <h1 class="display text-3xl sm:text-4xl">{{ $heading }}</h1>
                @if ($subheading)
                    <p class="mt-2 text-sm text-stone-500">{{ $subheading }}</p>
                @endif
                <div class="mt-8">{{ $slot }}</div>
            </div>
        </div>
    </div>
</x-layouts.base>
