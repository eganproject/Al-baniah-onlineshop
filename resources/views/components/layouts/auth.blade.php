@props(['title' => null, 'heading', 'subheading' => null])
<x-layouts.base :title="$title" body-class="bg-white text-stone-800">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panel brand --}}
        <div class="relative hidden overflow-hidden bg-ink lg:block">
            <span class="pointer-events-none absolute -top-32 -right-32 size-[30rem] rounded-full border-[56px] border-brand-400" aria-hidden="true"></span>
            <span class="pointer-events-none absolute top-72 right-24 size-20 rounded-full bg-volt" aria-hidden="true"></span>
            <x-logo variant="mark" mono class="pointer-events-none absolute -bottom-10 -left-10 h-72 text-white/[0.05]" aria-hidden="true" />
            <div class="relative flex h-full flex-col justify-between p-12 text-white">
                <a href="{{ route('home') }}" class="self-start"><x-wordmark dark size="lg" /></a>
                <div class="max-w-md">
                    <p class="eyebrow text-brand-300">{{ config('shop.hashtag', '#BEFITWITHNUFIT') }}</p>
                    <h2 class="display mt-4 text-5xl leading-[0.92]">Healthy body, healthy mind, happy life.</h2>
                    <p class="mt-5 text-base text-white/60">Perlengkapan lari, yoga, pilates, renang, padel, dan workout yang menemani setiap latihanmu.</p>
                    <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-white/80">
                        @foreach ([['shield', 'Produk resmi'], ['truck', 'Kirim se-Indonesia'], ['lock', 'Bayar aman']] as [$icon, $label])
                            <li class="flex items-center gap-2"><x-icon :name="$icon" class="size-4 text-brand-300" /> {{ $label }}</li>
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
                <h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $heading }}</h1>
                @if ($subheading)
                    <p class="mt-2 text-sm text-stone-500">{{ $subheading }}</p>
                @endif
                <div class="mt-8">{{ $slot }}</div>
            </div>
        </div>
    </div>
</x-layouts.base>
