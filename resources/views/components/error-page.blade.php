{{--
    Kerangka halaman error (404, 500, dll.). Tidak memakai sesi/keranjang
    karena bisa dirender tanpa middleware web (mis. URL yang tidak punya route).
--}}
@props(['code', 'title', 'search' => false])
<x-layouts.base :noindex="true" :title="$title" body-class="bg-white text-ink">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-stone-200/70">
            <div class="mx-auto flex h-16 w-full max-w-[1360px] items-center justify-between px-5 sm:px-10">
                <a href="{{ url('/') }}" class="inline-block" aria-label="{{ config('shop.full_name') }}"><x-logo class="h-7" /></a>
                <a href="{{ url('/produk') }}" class="text-sm font-medium text-stone-500 transition hover:text-ink">Belanja</a>
            </div>
        </header>

        <main class="flex flex-1 items-center px-5 py-16 sm:px-10">
            <div class="mx-auto grid w-full max-w-5xl items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="relative order-last lg:order-first" aria-hidden="true">
                    <p class="font-display text-[8rem] leading-none font-bold tracking-[-0.06em] text-sand-200 sm:text-[12rem]">{{ $code }}</p>
                    <span class="absolute bottom-4 left-1 h-1.5 w-24 -skew-x-[30deg] rounded-sm bg-brand-400 sm:bottom-7 sm:w-32"></span>
                </div>
                <div>
                    <p class="kicker">Error {{ $code }}</p>
                    <h1 class="display mt-4 text-3xl leading-tight sm:text-5xl">{{ $title }}</h1>
                    <div class="mt-4 max-w-md text-base leading-relaxed text-stone-500">{{ $slot }}</div>

                    @if ($search)
                        <form action="{{ url('/produk') }}" method="GET" role="search" class="mt-8 flex max-w-md items-center gap-2 rounded-full bg-sand-100 p-1.5 ring-1 ring-transparent focus-within:bg-white focus-within:ring-stone-300">
                            <label for="error-search" class="sr-only">Cari produk</label>
                            <x-icon name="search" class="ml-3 size-5 shrink-0 text-stone-400" />
                            <input id="error-search" type="search" name="q" placeholder="Cari produk..." class="min-w-0 flex-1 border-0 bg-transparent px-1 text-base placeholder:text-stone-400 focus:ring-0">
                            <button class="btn btn-ink btn-pill shrink-0 py-2.5">Cari</button>
                        </form>
                    @endif

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ url('/') }}" class="btn btn-brand btn-pill">Kembali ke beranda</a>
                        <a href="{{ url('/produk') }}" class="btn btn-outline btn-pill">Lihat produk <x-icon name="arrow-right" class="size-4" /></a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-stone-200/70 py-5 text-center text-xs text-stone-400">
            &copy; {{ date('Y') }} {{ config('shop.full_name') }} · {{ config('shop.motto') }}
        </footer>
    </div>
</x-layouts.base>
