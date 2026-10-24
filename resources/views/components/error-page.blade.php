{{--
    Kerangka halaman error (404, 500, dll.). Tidak memakai sesi/keranjang
    karena bisa dirender tanpa middleware web (mis. URL yang tidak punya route).
--}}
@props(['code', 'title', 'search' => false])
<x-layouts.base :noindex="true" :title="$title" body-class="bg-ink text-white">
    <div class="relative isolate flex min-h-screen flex-col overflow-hidden">
        <span class="pointer-events-none absolute -top-40 -right-40 -z-10 size-[28rem] rounded-full border-[56px] border-brand-400 sm:size-[40rem] sm:border-[80px]" aria-hidden="true"></span>
        <span class="pointer-events-none absolute bottom-16 left-10 -z-10 size-16 rounded-full bg-volt sm:size-24" aria-hidden="true"></span>

        <header class="px-5 py-6 sm:px-10">
            <a href="{{ url('/') }}" class="inline-block" aria-label="{{ config('shop.full_name') }}"><x-logo class="h-8" /></a>
        </header>

        <main class="flex flex-1 items-center px-5 pb-20 sm:px-10">
            <div class="mx-auto w-full max-w-2xl">
                <p class="font-display text-[7rem] leading-none font-black tracking-tighter text-brand-400 italic sm:text-[11rem]">{{ $code }}</p>
                <h1 class="display mt-2 text-3xl sm:text-5xl">{{ $title }}</h1>
                <div class="mt-4 max-w-md text-base leading-relaxed text-white/65">{{ $slot }}</div>

                @if ($search)
                    <form action="{{ url('/produk') }}" method="GET" role="search" class="mt-8 flex max-w-md items-center gap-2 rounded-full bg-white/10 p-1.5 ring-1 ring-white/15 focus-within:ring-brand-400">
                        <label for="error-search" class="sr-only">Cari produk</label>
                        <x-icon name="search" class="ml-3 size-5 shrink-0 text-white/50" />
                        <input id="error-search" type="search" name="q" placeholder="Cari produk..." class="min-w-0 flex-1 border-0 bg-transparent px-1 text-base text-white placeholder:text-white/40 focus:ring-0">
                        <button class="btn btn-brand btn-pill shrink-0 py-2.5">Cari</button>
                    </form>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ url('/') }}" class="btn btn-pill bg-white text-ink hover:bg-brand-50">Kembali ke beranda</a>
                    <a href="{{ url('/produk') }}" class="btn btn-pill text-white ring-1 ring-white/30 ring-inset hover:bg-white/10">Lihat produk <x-icon name="arrow-right" class="size-4" /></a>
                </div>
            </div>
        </main>
    </div>
</x-layouts.base>
