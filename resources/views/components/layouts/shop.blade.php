@props(['title' => null, 'description' => null, 'image' => null, 'canonical' => null, 'type' => 'website', 'noindex' => false, 'bottomNav' => true])
@php
    $user = auth()->user();
    $announcements = \App\Support\Storefront::announcements();
    $nav = [
        ['label' => 'Semua Produk', 'url' => route('products.index'), 'active' => request()->routeIs('products.index') && ! request()->hasAny(['kategori', 'promo', 'urut'])],
        ['label' => 'Terbaru', 'url' => route('products.index', ['urut' => 'terbaru']), 'active' => request('urut') === 'terbaru'],
    ];
    $bestNav = ['label' => 'Best Seller', 'url' => route('products.index', ['urut' => 'terlaris']), 'active' => request('urut') === 'terlaris'];
    $promoNav = ['label' => 'Promo', 'url' => route('products.index', ['promo' => 1]), 'active' => request()->boolean('promo')];
    $articleNav = ['label' => 'Artikel', 'url' => route('articles.index'), 'active' => request()->routeIs('articles.*')];
    $accountUrl = $user ? $user->homeRoute() : route('login');
    $instagram = config('shop.instagram') ? ltrim(config('shop.instagram'), '@') : null;
    $navLink = 'relative flex h-full items-center text-sm font-semibold transition hover:text-brand-600';
    $navActive = 'text-ink after:absolute after:inset-x-0 after:bottom-0 after:h-[3px] after:rounded-t-full after:bg-brand-400';
@endphp
<x-layouts.base :title="$title" :description="$description" :image="$image" :canonical="$canonical" :type="$type" :noindex="$noindex" :viewport-fit="true" body-class="bg-white text-ink">
    @isset($head)
        <x-slot:head>{{ $head }}</x-slot:head>
    @endisset
    @isset($scripts)
        <x-slot:scripts>{{ $scripts }}</x-slot:scripts>
    @endisset

    <div x-data="{ menu: false, search: false, cats: false }"
         x-effect="document.documentElement.classList.toggle('overflow-hidden', menu || search)"
         @keydown.escape.window="menu = false; search = false; cats = false"
         class="flex min-h-screen flex-col">

        {{-- Topbar: pengumuman bergantian (diatur di Admin › Tampilan Beranda) --}}
        <div class="bg-ink text-white">
            <div class="shop-container grid h-9 grid-cols-[auto_1fr_auto] items-center gap-3 text-[11px] sm:text-xs">
                <div class="hidden items-center gap-5 text-white/70 lg:flex">
                    <a href="{{ auth()->check() && ! $user->isAdmin() ? route('account.orders.index') : route('login') }}" class="flex items-center gap-1.5 transition hover:text-white">
                        <x-icon name="truck" class="size-3.5" /> Lacak pesanan
                    </a>
                    @if (config('shop.whatsapp'))
                        <a href="https://wa.me/{{ config('shop.whatsapp') }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 transition hover:text-white">
                            <x-icon name="chat" class="size-3.5" /> Bantuan
                        </a>
                    @endif
                </div>

                @if (count($announcements))
                    <div class="relative col-start-2 flex h-full min-w-0 items-center justify-center"
                         x-data="{ i: 0, n: {{ count($announcements) }}, t: null,
                                   go(d) { this.i = (this.i + d + this.n) % this.n },
                                   play() { if (this.n > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) this.t = setInterval(() => this.go(1), 4500) },
                                   stop() { clearInterval(this.t) } }"
                         x-init="play()" @mouseenter="stop()" @mouseleave="play()" aria-live="polite">
                        @if (count($announcements) > 1)
                            <button type="button" @click="go(-1)" class="absolute left-0 grid size-9 place-items-center text-white/60 hover:text-white lg:static lg:mr-3 lg:size-7" aria-label="Pengumuman sebelumnya">
                                <x-icon name="chevron-left" class="size-3.5" />
                            </button>
                        @endif
                        <div class="relative h-full min-w-0 flex-1 overflow-hidden lg:max-w-xl lg:flex-none lg:w-[36rem]">
                            @foreach ($announcements as $text)
                                <p x-show="i === {{ $loop->index }}" @if (! $loop->first) x-cloak @endif
                                   x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                                   x-transition:leave="transition duration-300 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="-translate-y-full opacity-0"
                                   class="absolute inset-0 flex items-center justify-center gap-2 truncate px-9 text-center font-medium tracking-wide lg:px-0">
                                    <span class="hidden size-1.5 shrink-0 rounded-full bg-volt sm:inline-block" aria-hidden="true"></span>
                                    <span class="truncate">{{ $text }}</span>
                                </p>
                            @endforeach
                        </div>
                        @if (count($announcements) > 1)
                            <button type="button" @click="go(1)" class="absolute right-0 grid size-9 place-items-center text-white/60 hover:text-white lg:static lg:ml-3 lg:size-7" aria-label="Pengumuman berikutnya">
                                <x-icon name="chevron-right" class="size-3.5" />
                            </button>
                        @endif
                    </div>
                @endif

                <div class="col-start-3 hidden items-center gap-5 text-white/70 lg:flex">
                    @if ($instagram)
                        <a href="https://instagram.com/{{ $instagram }}" target="_blank" rel="noopener" class="transition hover:text-white">Instagram</a>
                    @endif
                    @guest
                        <a href="{{ route('login') }}" class="transition hover:text-white">Masuk</a>
                        <a href="{{ route('register') }}" class="font-semibold text-volt transition hover:text-white">Daftar &amp; dapat promo</a>
                    @else
                        <a href="{{ $accountUrl }}" class="transition hover:text-white">Hai, {{ \Illuminate\Support\Str::words($user->name, 1, '') }}</a>
                    @endguest
                </div>
            </div>
        </div>

        {{-- Header --}}
        <header class="sticky top-0 z-40 border-b border-stone-200/80 bg-white/95 backdrop-blur" @mouseleave="cats = false">
            <div class="shop-container flex h-16 items-center gap-2 lg:h-[72px] lg:gap-8">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="shrink-0 py-1 lg:py-0" aria-label="{{ config('shop.full_name') }}">
                    <x-logo class="h-7 lg:h-8" />
                </a>

                {{-- Mobile: kolom cari (menu utama ada di navigasi bawah) --}}
                <button type="button" @click="search = true"
                        class="ml-2 flex h-11 min-w-0 flex-1 items-center gap-2.5 rounded-full bg-stone-100 px-4 text-left text-sm text-stone-400 transition active:bg-stone-200 lg:hidden">
                    <x-icon name="search" class="size-[18px] shrink-0 text-stone-500" />
                    <span class="truncate">{{ request('q') ?: 'Cari matras, botol, running belt...' }}</span>
                </button>

                {{-- Navigasi utama (desktop) --}}
                <nav class="hidden h-full flex-1 items-center justify-center gap-7 lg:flex xl:gap-9" aria-label="Navigasi utama">
                    @if ($navCategories->isNotEmpty())
                        <button type="button" @click="cats = !cats" @mouseenter="cats = true" :aria-expanded="cats" aria-controls="mega-menu"
                                @class([$navLink, 'gap-1', $navActive => request()->filled('kategori')])>
                            Belanja <x-icon name="chevron-down" class="size-3.5 transition" x-bind:class="cats && 'rotate-180'" />
                        </button>
                    @endif
                    @foreach ([$nav[1], $bestNav] as $item)
                        <a href="{{ $item['url'] }}" @mouseenter="cats = false" @class([$navLink, $navActive => $item['active']])>{{ $item['label'] }}</a>
                    @endforeach
                    <a href="{{ $promoNav['url'] }}" @mouseenter="cats = false" @class([$navLink, 'gap-1.5 !text-accent-600 hover:!text-accent-700', 'after:absolute after:inset-x-0 after:bottom-0 after:h-[3px] after:rounded-t-full after:bg-accent-500' => $promoNav['active']])>
                        <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-accent-400 opacity-60 motion-reduce:hidden"></span><span class="relative inline-flex size-2 rounded-full bg-accent-500"></span></span>
                        {{ $promoNav['label'] }}
                    </a>
                    <a href="{{ $articleNav['url'] }}" @mouseenter="cats = false" @class([$navLink, $navActive => $articleNav['active']])>{{ $articleNav['label'] }}</a>
                </nav>

                {{-- Kanan --}}
                <div @class(['flex items-center gap-0.5 lg:ml-0 lg:gap-1', 'ml-auto' => ! $bottomNav, 'max-lg:hidden' => $bottomNav])>
                    <form action="{{ route('products.index') }}" method="GET" role="search" class="mr-2 hidden xl:block">
                        <label class="relative block">
                            <span class="sr-only">Cari produk</span>
                            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-stone-400" />
                            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk..."
                                   class="h-10 w-60 rounded-full border-0 bg-stone-100 pl-10 pr-4 text-sm placeholder:text-stone-400 focus:bg-white focus:ring-2 focus:ring-brand-400">
                        </label>
                    </form>
                    <button type="button" @click="search = true" class="hidden size-10 place-items-center rounded-full hover:bg-sand-100 lg:grid xl:hidden" aria-label="Cari">
                        <x-icon name="search" />
                    </button>

                    @auth
                        <div x-data="{ open: false }" class="relative hidden lg:block">
                            <button type="button" @click="open = !open" @click.outside="open = false" :aria-expanded="open" aria-label="Akun"
                                    class="grid size-10 place-items-center rounded-full hover:bg-sand-100">
                                <x-icon name="user" />
                            </button>
                            <div x-show="open" x-cloak x-transition.origin.top.right
                                 class="absolute right-0 mt-2 w-60 rounded-2xl border border-stone-200 bg-white p-2 shadow-2xl shadow-ink/10">
                                <div class="px-3 py-2">
                                    <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                                    <p class="truncate text-xs text-stone-500">{{ $user->email }}</p>
                                </div>
                                <div class="my-1 h-px bg-stone-100"></div>
                                @if ($user->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm hover:bg-sand-100"><x-icon name="dashboard" class="size-4" /> Admin Panel</a>
                                @else
                                    <a href="{{ route('account.dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm hover:bg-sand-100"><x-icon name="user" class="size-4" /> Akun Saya</a>
                                    <a href="{{ route('account.orders.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm hover:bg-sand-100"><x-icon name="receipt" class="size-4" /> Pesanan Saya</a>
                                    <a href="{{ route('account.wishlist') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm hover:bg-sand-100"><x-icon name="heart" class="size-4" /> Wishlist</a>
                                    <a href="{{ route('account.reviews.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm hover:bg-sand-100"><x-icon name="star" class="size-4" /> Ulasan Saya</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-rose-600 hover:bg-rose-50"><x-icon name="logout" class="size-4" /> Keluar</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hidden size-10 place-items-center rounded-full hover:bg-sand-100 lg:grid" aria-label="Masuk">
                            <x-icon name="user" />
                        </a>
                    @endauth

                    @unless ($user?->isAdmin())
                        <a href="{{ $user ? route('account.wishlist') : route('login') }}" class="relative grid size-11 lg:size-10 place-items-center rounded-full hover:bg-sand-100"
                           x-data="{ count: {{ $wishlistCount }} }" @wishlist-updated.window="count = $event.detail.count" aria-label="Wishlist">
                            <x-icon name="heart" />
                            <span x-show="count > 0" x-text="count > 99 ? '99+' : count" @if (! $wishlistCount) x-cloak @endif
                                  class="absolute right-0.5 top-0.5 grid min-w-[18px] place-items-center rounded-full bg-ink px-1 text-[10px] leading-[18px] font-bold text-white">{{ $wishlistCount }}</span>
                        </a>
                    @endunless

                    <a href="{{ route('cart.index') }}" class="relative -mr-1 grid size-11 lg:size-10 place-items-center rounded-full hover:bg-sand-100 lg:mr-0" aria-label="Keranjang ({{ $cartCount }})">
                        <x-icon name="bag" />
                        @if ($cartCount > 0)
                            <span class="absolute right-0.5 top-0.5 grid min-w-[18px] place-items-center rounded-full bg-brand-400 px-1 text-[10px] leading-[18px] font-bold text-ink">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- Mega menu kategori (desktop) --}}
            @if ($navCategories->isNotEmpty())
                <div id="mega-menu" x-show="cats" x-cloak @click.outside="cats = false"
                     x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="absolute inset-x-0 top-full hidden border-b border-stone-200 bg-white shadow-2xl shadow-ink/10 lg:block">
                    <div class="shop-container grid grid-cols-[1fr_300px] gap-10 py-8">
                        <div>
                            <div class="flex items-center justify-between">
                                <p class="eyebrow text-stone-400">Belanja sesuai aktivitas</p>
                                <a href="{{ route('products.index') }}" class="link-underline text-sm font-semibold">Lihat semua produk</a>
                            </div>
                            <ul class="mt-5 grid grid-cols-3 gap-x-8 gap-y-6 xl:grid-cols-4">
                                @foreach ($navCategories as $category)
                                    <li>
                                        <a href="{{ route('products.index', ['kategori' => $category->slug]) }}" class="group flex items-center gap-3">
                                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-400 group-hover:text-ink">
                                                <x-sport-icon :for="$category->name.' '.$category->slug" class="size-5" />
                                            </span>
                                            <span class="text-sm font-bold group-hover:text-brand-700">{{ $category->name }}</span>
                                        </a>
                                        @if ($category->children->isNotEmpty())
                                            <ul class="mt-2 space-y-1.5 pl-[3.25rem]">
                                                @foreach ($category->children as $child)
                                                    <li><a href="{{ route('products.index', ['kategori' => $child->slug]) }}" class="text-sm text-stone-500 transition hover:text-ink">{{ $child->name }}</a></li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <a href="{{ $promoNav['url'] }}" class="group relative flex min-h-56 flex-col justify-end overflow-hidden rounded-2xl bg-ink p-6 text-white">
                            <span class="absolute -right-12 -top-12 size-48 rounded-full border-[18px] border-brand-400"></span>
                            <span class="absolute right-6 top-16 size-14 rounded-full bg-volt"></span>
                            <span class="relative eyebrow text-brand-300">Promo</span>
                            <span class="relative display mt-2 text-3xl leading-none">Lagi diskon, buruan!</span>
                            <span class="relative mt-4 inline-flex items-center gap-1.5 text-sm font-semibold">Belanja promo <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span>
                        </a>
                    </div>
                </div>
            @endif
        </header>

        {{-- Panel pencarian --}}
        <div x-show="search" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Cari produk">
            <div x-show="search" x-transition.opacity @click="search = false" class="absolute inset-0 bg-ink/50"></div>
            <div x-show="search"
                 x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-full"
                 class="relative rounded-b-3xl bg-white">
                <div class="shop-container py-6 sm:py-10">
                    <div class="flex items-center justify-between">
                        <p class="eyebrow text-stone-500">Cari produk</p>
                        <button type="button" @click="search = false" class="-mr-2 grid size-10 place-items-center rounded-full hover:bg-sand-100" aria-label="Tutup pencarian">
                            <x-icon name="x" />
                        </button>
                    </div>
                    <form action="{{ route('products.index') }}" method="GET" class="mt-3 flex items-center gap-3 border-b-2 border-ink pb-3">
                        <x-icon name="search" class="size-6 shrink-0 text-stone-400" />
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Matras yoga, botol, running belt..."
                               x-effect="if (search) $nextTick(() => $el.focus())"
                               class="w-full border-0 bg-transparent p-0 font-display text-2xl font-bold tracking-tight placeholder:text-stone-300 focus:ring-0 sm:text-4xl">
                        <button class="btn btn-ink btn-pill hidden shrink-0 sm:inline-flex">Cari</button>
                    </form>
                    @if ($navCategories->isNotEmpty())
                        <p class="eyebrow mt-6 text-stone-400">Aktivitas populer</p>
                        <div class="no-scrollbar -mx-4 mt-3 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0">
                            @foreach ($navCategories->take(8) as $category)
                                <a href="{{ route('products.index', ['kategori' => $category->slug]) }}" class="chip"><x-sport-icon :for="$category->name.' '.$category->slug" class="size-4 text-brand-600" /> {{ $category->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Panel "Jelajahi" (tab Kategori di navigasi bawah, mobile) --}}
        <div x-show="menu" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-labelledby="explore-title" id="explore-sheet">
            <div x-show="menu" x-transition.opacity @click="menu = false" class="absolute inset-0 bg-ink/50"></div>
            <div x-show="menu"
                 x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                 class="absolute inset-x-0 bottom-0 flex max-h-[88dvh] flex-col rounded-t-3xl bg-white shadow-2xl">
                <div class="flex shrink-0 items-center justify-between px-5 pt-3">
                    <span class="absolute left-1/2 top-2 h-1 w-10 -translate-x-1/2 rounded-full bg-stone-300" aria-hidden="true"></span>
                    <h2 id="explore-title" class="display mt-3 text-xl">Jelajahi</h2>
                    <button type="button" @click="menu = false" class="-mr-2 mt-3 grid size-11 place-items-center rounded-full hover:bg-sand-100" aria-label="Tutup">
                        <x-icon name="x" />
                    </button>
                </div>
                <div class="overflow-y-auto overscroll-contain px-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))]">
                    <ul class="mt-3 grid grid-cols-4 gap-2">
                        @foreach ([
                            ['Semua', 'cube', $nav[0], 'bg-brand-50 text-brand-700'],
                            ['Terbaru', 'sparkles', $nav[1], 'bg-volt/40 text-ink'],
                            ['Promo', 'tag', $promoNav, 'bg-accent-50 text-accent-600'],
                            ['Artikel', 'newspaper', $articleNav, 'bg-stone-100 text-stone-700'],
                        ] as [$label, $icon, $item, $tone])
                            <li>
                                <a href="{{ $item['url'] }}" class="flex flex-col items-center gap-2 rounded-2xl py-2 text-center text-xs font-semibold active:bg-sand-50">
                                    <span class="grid size-12 place-items-center rounded-2xl {{ $tone }} {{ $item['active'] ? 'ring-2 ring-current' : '' }}"><x-icon :name="$icon" class="size-6" /></span>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @if ($navCategories->isNotEmpty())
                        <p class="eyebrow mt-6 text-stone-400">Kategori</p>
                        <ul class="mt-1 divide-y divide-stone-100">
                            @foreach ($navCategories as $category)
                                @php($current = request('kategori') === $category->slug)
                                <li x-data="{ open: {{ $category->children->contains('slug', request('kategori')) ? 'true' : 'false' }} }">
                                    <div class="flex items-center">
                                        <a href="{{ route('products.index', ['kategori' => $category->slug]) }}" @if ($current) aria-current="page" @endif
                                           @class(['flex min-h-14 flex-1 items-center gap-3 py-2 text-[15px] font-semibold', 'text-brand-700' => $current])>
                                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-sport-icon :for="$category->name.' '.$category->slug" class="size-5" /></span>
                                            {{ $category->name }}
                                        </a>
                                        @if ($category->children->isNotEmpty())
                                            <button type="button" @click="open = !open" :aria-expanded="open" class="-mr-2 grid size-11 place-items-center text-stone-400" aria-label="Subkategori {{ $category->name }}">
                                                <x-icon name="chevron-down" class="size-4 transition" x-bind:class="open && 'rotate-180'" />
                                            </button>
                                        @else
                                            <x-icon name="chevron-right" class="size-4 shrink-0 text-stone-400" />
                                        @endif
                                    </div>
                                    @if ($category->children->isNotEmpty())
                                        <ul x-show="open" x-cloak x-transition.opacity class="pb-3 pl-12">
                                            @foreach ($category->children as $child)
                                                <li><a href="{{ route('products.index', ['kategori' => $child->slug]) }}" @class(['flex min-h-10 items-center text-sm text-stone-600', 'font-semibold text-brand-700' => request('kategori') === $child->slug])>{{ $child->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-5 rounded-2xl bg-sand-50 p-4">
                        @auth
                            <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                            <p class="truncate text-xs text-stone-500">{{ $user->email }}</p>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @if ($user->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-ink btn-pill col-span-2 py-3">Admin Panel</a>
                                @else
                                    <a href="{{ route('account.orders.index') }}" class="btn btn-ink btn-pill py-3"><x-icon name="receipt" class="size-4" /> Pesanan</a>
                                    <a href="{{ route('account.addresses.index') }}" class="btn btn-outline btn-pill py-3"><x-icon name="map-pin" class="size-4" /> Alamat</a>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-stone-600">Masuk untuk checkout, simpan wishlist, dan melacak pesananmu.</p>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <a href="{{ route('login') }}" class="btn btn-ink btn-pill py-3">Masuk</a>
                                <a href="{{ route('register') }}" class="btn btn-outline btn-pill py-3">Daftar</a>
                            </div>
                        @endauth
                        @if (config('shop.whatsapp'))
                            <a href="https://wa.me/{{ config('shop.whatsapp') }}" target="_blank" rel="noopener" class="mt-3 flex min-h-11 items-center justify-center gap-2 text-sm font-semibold text-brand-700">
                                <x-icon name="chat" class="size-4" /> Butuh bantuan? Chat WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="bottom-nav-spacer mt-20 bg-ink text-white lg:mt-28">
            {{-- Ajakan komunitas --}}
            <div class="relative overflow-hidden border-b border-white/10">
                <span class="pointer-events-none absolute -right-24 -top-24 size-72 rounded-full border-[28px] border-brand-400/90 sm:size-96" aria-hidden="true"></span>
                <span class="pointer-events-none absolute bottom-8 right-40 hidden size-16 rounded-full bg-volt lg:block" aria-hidden="true"></span>
                <div class="shop-container relative py-14 lg:py-20">
                    <p class="eyebrow text-brand-300">{{ config('shop.hashtag', '#BEFITWITHNUFIT') }}</p>
                    <h2 class="display mt-3 max-w-2xl text-4xl leading-[0.95] sm:text-6xl">Latihan lebih seru bareng komunitas.</h2>
                    <p class="mt-4 max-w-lg text-sm leading-relaxed text-white/65 sm:text-base">Bagikan momen latihanmu dengan tagar {{ config('shop.hashtag', '#BEFITWITHNUFIT') }}. Tim kami juga siap membantu memilih perlengkapan yang pas, dari pemula sampai atlet.</p>
                    <div class="mt-7 flex flex-wrap gap-3">
                        @if ($instagram)
                            <a href="https://instagram.com/{{ $instagram }}" target="_blank" rel="noopener" class="btn btn-pill bg-brand-400 text-ink hover:bg-brand-300">Follow &#64;{{ $instagram }}</a>
                        @endif
                        @if (config('shop.whatsapp'))
                            <a href="https://wa.me/{{ config('shop.whatsapp') }}" target="_blank" rel="noopener" class="btn btn-pill bg-white/10 text-white ring-1 ring-white/20 ring-inset hover:bg-white/15">
                                <x-icon name="chat" class="size-4" /> Chat WhatsApp
                            </a>
                        @endif
                        @if (! $instagram && ! config('shop.whatsapp'))
                            <a href="{{ route('products.index') }}" class="btn btn-pill bg-brand-400 text-ink hover:bg-brand-300">Mulai belanja <x-icon name="arrow-right" class="size-4" /></a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="shop-container py-14 lg:py-16">
                <div class="grid gap-12 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <a href="{{ route('home') }}" class="inline-block" aria-label="{{ config('shop.full_name') }}"><x-logo dark class="h-10" /></a>
                        <p class="mt-5 max-w-xs text-sm leading-relaxed text-white/60">{{ config('shop.tagline') }}.</p>
                        <p class="mt-4 text-xs font-semibold tracking-[0.18em] text-brand-300 uppercase">{{ config('shop.motto') }}</p>
                        @if (config('shop.email') || config('shop.whatsapp'))
                            <ul class="mt-6 space-y-2 text-sm text-white/70">
                                @if (config('shop.whatsapp'))
                                    <li class="flex items-center gap-2"><x-icon name="phone" class="size-4 text-white/40" /> +{{ config('shop.whatsapp') }}</li>
                                @endif
                                @if (config('shop.email'))
                                    <li class="flex items-center gap-2"><x-icon name="envelope" class="size-4 text-white/40" /> <a href="mailto:{{ config('shop.email') }}" class="hover:text-white">{{ config('shop.email') }}</a></li>
                                @endif
                            </ul>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-8">
                        <div>
                            <h3 class="eyebrow text-white/40">Belanja</h3>
                            <ul class="mt-3 text-sm text-white/80 lg:mt-4 lg:space-y-2.5">
                                <li><a href="{{ route('products.index') }}" class="inline-flex min-h-11 items-center hover:text-brand-300 lg:min-h-0">Semua produk</a></li>
                                @foreach ($navCategories->take(6) as $category)
                                    <li><a href="{{ route('products.index', ['kategori' => $category->slug]) }}" class="inline-flex min-h-11 items-center hover:text-brand-300 lg:min-h-0">{{ $category->name }}</a></li>
                                @endforeach
                                <li><a href="{{ $promoNav['url'] }}" class="inline-flex min-h-11 items-center font-semibold text-accent-300 hover:text-accent-200 lg:min-h-0">Promo</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="eyebrow text-white/40">Bantuan</h3>
                            <ul class="mt-3 text-sm text-white/80 lg:mt-4 lg:space-y-2.5">
                                <li><a href="{{ auth()->check() ? route('account.orders.index') : route('login') }}" class="inline-flex min-h-11 items-center hover:text-brand-300 lg:min-h-0">Lacak pesanan</a></li>
                                <li><a href="{{ $accountUrl }}" class="inline-flex min-h-11 items-center hover:text-brand-300 lg:min-h-0">Akun saya</a></li>
                                <li><a href="{{ route('cart.index') }}" class="inline-flex min-h-11 items-center hover:text-brand-300 lg:min-h-0">Keranjang</a></li>
                                <li><a href="{{ route('articles.index') }}" class="inline-flex min-h-11 items-center hover:text-brand-300 lg:min-h-0">Artikel &amp; tips latihan</a></li>
                                @if (config('shop.whatsapp'))
                                    <li><a href="https://wa.me/{{ config('shop.whatsapp') }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center hover:text-brand-300 lg:min-h-0">WhatsApp</a></li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <h3 class="eyebrow text-white/40">Kenapa {{ config('shop.name') }}</h3>
                            <ul class="mt-3 text-sm text-white/80 lg:mt-4 lg:space-y-2.5">
                                <li class="flex min-h-9 items-center gap-2 lg:min-h-0"><x-icon name="shield" class="size-4 text-brand-300" /> Produk resmi NuFit</li>
                                <li class="flex min-h-9 items-center gap-2 lg:min-h-0"><x-icon name="truck" class="size-4 text-brand-300" /> Kirim se-Indonesia</li>
                                <li class="flex min-h-9 items-center gap-2 lg:min-h-0"><x-icon name="refresh" class="size-4 text-brand-300" /> Garansi tukar 7 hari</li>
                                <li class="flex min-h-9 items-center gap-2 lg:min-h-0"><x-icon name="lock" class="size-4 text-brand-300" /> Pembayaran aman</li>
                            </ul>
                            <div class="mt-5 flex gap-2">
                                @if ($instagram)
                                    <a href="https://instagram.com/{{ $instagram }}" target="_blank" rel="noopener" class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20">Instagram</a>
                                @endif
                                @if (config('shop.tiktok'))
                                    <a href="https://tiktok.com/@{{ ltrim(config('shop.tiktok'), '@') }}" target="_blank" rel="noopener" class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20">TikTok</a>
                                @endif
                                @if (config('shop.facebook'))
                                    <a href="https://facebook.com/{{ ltrim(config('shop.facebook'), '@') }}" target="_blank" rel="noopener" class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20">Facebook</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden border-t border-white/10" aria-hidden="true">
                <div class="shop-container pt-10">
                    <x-logo mono class="h-auto w-full text-white/[0.05]" />
                </div>
            </div>
            <div class="border-t border-white/10">
                <div class="shop-container flex flex-col gap-3 py-5 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} {{ config('shop.full_name') }}. Semua hak dilindungi.</p>
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach (['Transfer bank', 'E-wallet', 'QRIS', 'Kartu kredit'] as $method)
                            <li class="rounded-md border border-white/15 px-2 py-1 font-medium text-white/60">{{ $method }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </footer>

        @if ($bottomNav)
            <x-bottom-nav :cart-count="$cartCount" :wishlist-count="$wishlistCount" />
        @endif
    </div>
</x-layouts.base>
