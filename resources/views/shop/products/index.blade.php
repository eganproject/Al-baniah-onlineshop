@php
    use Illuminate\Support\Arr;

    $heading = request('q') ? 'Hasil untuk "'.request('q').'"' : ($activeCategory?->name ?? $activeCollection?->name ?? (request()->boolean('promo') ? 'Promo' : 'Semua produk'));
    $baseQuery = request()->except('page');
    $pricePresets = [
        ['Di bawah 50rb', null, 50000],
        ['50rb – 100rb', 50000, 100000],
        ['100rb – 250rb', 100000, 250000],
        ['Di atas 250rb', 250000, null],
    ];

    // Filter aktif yang bisa dihapus satu per satu.
    $activeFilters = array_filter([
        'q' => request('q') ? 'Kata kunci: '.request('q') : null,
        'koleksi' => $activeCollection && ($activeCategory || request('q')) ? 'Aktivitas: '.$activeCollection->name : null,
        'tersedia' => request()->boolean('tersedia') ? 'Tersedia' : null,
        'promo' => request()->boolean('promo') ? 'Sedang diskon' : null,
        'harga' => ($minPrice || $maxPrice)
            ? 'Harga: '.($minPrice ? \App\Support\Money::rupiah($minPrice) : 'Rp0').' – '.($maxPrice ? \App\Support\Money::rupiah($maxPrice) : '∞')
            : null,
    ]);
    $filterCount = count(array_diff_key($activeFilters, ['q' => true, 'koleksi' => true]));
    $collectionUrl = fn (?string $slug) => route('products.index', ($slug ? ['koleksi' => $slug] : []) + Arr::except($baseQuery, ['koleksi', 'kategori']));
    $hasPrice = $minPrice || $maxPrice;

    // Kategori induk yang sedang dibuka (untuk subkategori).
    $openParent = $activeCategory ? $categories->first(fn ($c) => $activeCategory->is($c) || (int) $activeCategory->parent_id === $c->id) : null;
    $categoryUrl = fn (?string $slug) => route('products.index', ($slug ? ['kategori' => $slug] : []) + Arr::except($baseQuery, 'kategori'));
    $toggleUrl = fn (string $key) => route('products.index', request()->boolean($key) ? Arr::except($baseQuery, $key) : [$key => 1] + $baseQuery);

    $context = $activeCategory ?? $activeCollection;
    $metaDescription = $context
        ? ($context->description ?: 'Belanja '.$context->name.' original di '.config('shop.full_name').'. Harga terjangkau, kirim ke seluruh Indonesia.')
        : null;
    $intro = $activeCategory?->description
        ?: ($activeCollection && ! $activeCategory ? ($activeCollection->tagline ?: $activeCollection->description) : null)
        ?: (request('q') ? 'Produk yang cocok dengan pencarianmu.' : (request()->boolean('promo') ? 'Harga spesial untuk perlengkapan favorit, selama stok masih ada.' : 'Perlengkapan lari, yoga, workout, padel, renang, dan lainnya untuk menemani latihanmu.'));
@endphp
<x-layouts.shop :title="$context?->name ?? 'Semua Produk'" :description="$metaDescription" transparent-header>
    <div x-data="{ filters: false }" x-effect="document.body.classList.toggle('overflow-hidden', filters)" @keydown.escape.window="filters = false">
        {{-- Kepala katalog: menyatu dengan navbar transparan --}}
        <section class="relative isolate overflow-hidden bg-ink text-white">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <span class="absolute -top-48 -right-40 size-[34rem] rounded-full border-[56px] border-brand-400/20"></span>
                <span class="absolute -bottom-40 -left-24 size-80 rounded-full bg-brand-500/10 blur-3xl"></span>
            </div>

            <div class="shop-container pt-24 pb-8 sm:pt-28 lg:pt-36 lg:pb-10">
                <nav class="flex flex-wrap items-center gap-1.5 text-xs text-white/55" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                    <x-icon name="chevron-right" class="size-3" />
                    @if ($activeCategory || $activeCollection)
                        <a href="{{ route('products.index') }}" class="hover:text-white">Produk</a>
                        @if ($activeCollection)
                            <x-icon name="chevron-right" class="size-3" />
                            @if ($activeCategory)
                                <a href="{{ $collectionUrl($activeCollection->slug) }}" class="hover:text-white">{{ $activeCollection->name }}</a>
                            @else
                                <span class="text-white">{{ $activeCollection->name }}</span>
                            @endif
                        @endif
                    @endif
                    @if ($activeCategory)
                        @if ($activeCategory->parent)
                            <x-icon name="chevron-right" class="size-3" />
                            <a href="{{ $categoryUrl($activeCategory->parent->slug) }}" class="hover:text-white">{{ $activeCategory->parent->name }}</a>
                        @endif
                        <x-icon name="chevron-right" class="size-3" />
                        <span class="text-white">{{ $activeCategory->name }}</span>
                    @elseif (! $activeCollection)
                        <span class="text-white">Produk</span>
                    @endif
                </nav>

                <div class="mt-5 flex items-end justify-between gap-6">
                    <div class="min-w-0">
                        <p class="eyebrow text-brand-300">
                            @if ($activeCollection && $activeCategory)
                                {{ $activeCollection->name }}
                            @elseif ($activeCollection)
                                Aktivitas
                            @elseif ($activeCategory?->parent)
                                {{ $activeCategory->parent->name }}
                            @elseif ($activeCategory)
                                Kategori
                            @else
                                Katalog NuFit
                            @endif
                        </p>
                        <h1 class="display mt-2 text-[2.6rem] leading-[0.9] break-words sm:text-6xl lg:text-7xl">{{ $heading }}</h1>
                        <p class="mt-4 max-w-xl text-sm leading-relaxed text-white/65 sm:text-base">{{ $intro }}</p>
                    </div>
                    @if ($context)
                        <div class="relative hidden shrink-0 md:block" aria-hidden="true">
                            <span class="absolute -inset-4 rounded-full border border-dashed border-white/15"></span>
                            <span class="grid size-32 place-items-center rounded-full bg-brand-400 text-ink shadow-[0_20px_60px_-15px] shadow-brand-400/60 lg:size-40">
                                <x-sport-icon :name="$activeCategory ? null : $activeCollection->iconName()" :for="$context->name.' '.$context->slug" class="size-16 lg:size-20" />
                            </span>
                            <span class="absolute -top-1 -right-1 size-8 rounded-full bg-volt"></span>
                        </div>
                    @endif
                </div>

                {{-- Pilihan aktivitas (koleksi) --}}
                @if ($collections->isNotEmpty())
                    <div class="no-scrollbar -mx-4 mt-8 flex items-center gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0 lg:mt-10" aria-label="Aktivitas">
                        <span class="eyebrow mr-1 shrink-0 text-white/45">Aktivitas</span>
                        @foreach ($collections as $item)
                            <a href="{{ $activeCollection?->is($item) ? $collectionUrl(null) : $collectionUrl($item->slug) }}"
                               @class(['chip chip-dark shrink-0 px-3.5 py-1.5 text-xs max-lg:min-h-9', 'chip-active' => $activeCollection?->is($item)])
                               @if ($activeCollection?->is($item)) aria-current="page" @endif>
                                <x-sport-icon :name="$item->iconName()" :for="$item->name.' '.$item->slug" class="size-4" /> {{ $item->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Rail kategori --}}
                @if ($categories->isNotEmpty())
                    <div @class(['no-scrollbar -mx-4 flex snap-x scroll-px-4 gap-2.5 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0', 'mt-8 lg:mt-10' => $collections->isEmpty(), 'mt-4' => $collections->isNotEmpty()])>
                        <a href="{{ $categoryUrl(null) }}" @class([
                            'group flex shrink-0 snap-start items-center gap-3 rounded-2xl border py-2 pr-4 pl-2 transition',
                            'border-brand-400 bg-brand-400 text-ink' => ! $activeCategory,
                            'border-white/10 bg-white/5 hover:border-white/30 hover:bg-white/10' => $activeCategory,
                        ])>
                            <span @class(['grid size-10 place-items-center rounded-xl', 'bg-ink text-brand-300' => ! $activeCategory, 'bg-white/10 text-white' => $activeCategory])>
                                <x-icon name="storefront" class="size-5" />
                            </span>
                            <span class="text-sm leading-tight font-semibold">Semua<span @class(['block text-xs font-normal', 'text-ink/70' => ! $activeCategory, 'text-white/50' => $activeCategory])>{{ $activeCollection ? $activeCollection->name : 'Katalog lengkap' }}</span></span>
                        </a>
                        @foreach ($categories as $category)
                            @php($on = $openParent?->is($category))
                            <a href="{{ $categoryUrl($category->slug) }}" @class([
                                'group flex shrink-0 snap-start items-center gap-3 rounded-2xl border py-2 pr-4 pl-2 transition',
                                'border-brand-400 bg-brand-400 text-ink' => $on,
                                'border-white/10 bg-white/5 hover:border-white/30 hover:bg-white/10' => ! $on,
                            ]) @if ($on) aria-current="page" @endif>
                                <span @class(['grid size-10 place-items-center rounded-xl', 'bg-ink text-brand-300' => $on, 'bg-brand-400 text-ink' => ! $on])>
                                    <x-sport-icon :for="$category->name.' '.$category->slug" class="size-5" />
                                </span>
                                <span class="text-sm leading-tight font-semibold whitespace-nowrap">{{ $category->name }}<span @class(['block text-xs font-normal', 'text-ink/70' => $on, 'text-white/50' => ! $on])>{{ $category->products_count }} produk</span></span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Subkategori --}}
            @if ($openParent?->children->isNotEmpty())
                <div class="border-t border-white/10 bg-white/[0.03]">
                    <div class="shop-container no-scrollbar flex items-center gap-2 overflow-x-auto py-3">
                        <span class="eyebrow mr-1 shrink-0 text-white/45">Jenis</span>
                        <a href="{{ $categoryUrl($openParent->slug) }}"
                           @class(['chip chip-dark px-3.5 py-1.5 text-xs max-lg:min-h-9', 'chip-active' => $activeCategory->is($openParent)])>Semua {{ $openParent->name }}</a>
                        @foreach ($openParent->children as $child)
                            <a href="{{ $categoryUrl($child->slug) }}"
                               @class(['chip chip-dark px-3.5 py-1.5 text-xs max-lg:min-h-9', 'chip-active' => $activeCategory->is($child)])>{{ $child->name }} <span class="opacity-60">{{ $child->products_count }}</span></a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- Bar filter horizontal (lengket di bawah navbar) --}}
        <div class="sticky top-16 z-30 border-b border-stone-200 bg-white/95 backdrop-blur lg:top-[72px]">
            <div class="shop-container flex h-16 items-center gap-2">
                {{-- Mobile: satu tombol membuka bottom sheet --}}
                <button type="button" @click="filters = true" class="inline-flex min-h-10 items-center gap-2 rounded-full bg-ink px-4 text-sm font-semibold text-white lg:hidden">
                    <x-icon name="filter" class="size-4" /> Filter
                    @if ($filterCount)
                        <span class="grid size-5 place-items-center rounded-full bg-brand-400 text-[10px] text-ink">{{ $filterCount }}</span>
                    @endif
                </button>

                {{-- Desktop: pil filter cepat --}}
                <div class="hidden items-center gap-2 lg:flex">
                    <a href="{{ $toggleUrl('tersedia') }}" @class(['chip px-3.5 py-2', 'chip-active' => request()->boolean('tersedia')]) @if (request()->boolean('tersedia')) aria-pressed="true" @endif>
                        <x-icon name="check" class="size-4" /> Tersedia
                    </a>
                    <a href="{{ $toggleUrl('promo') }}" @class(['chip px-3.5 py-2', 'chip-active' => request()->boolean('promo')]) @if (request()->boolean('promo')) aria-pressed="true" @endif>
                        <span @class(['size-2 rounded-full', 'bg-accent-400' => request()->boolean('promo'), 'bg-accent-500' => ! request()->boolean('promo')])></span> Diskon
                    </a>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                        <button type="button" @click="open = ! open" :aria-expanded="open" @class(['chip px-3.5 py-2', 'chip-active' => $hasPrice])>
                            Harga
                            @if ($hasPrice)
                                <span class="font-normal opacity-75">· {{ $activeFilters['harga'] ? \Illuminate\Support\Str::after($activeFilters['harga'], 'Harga: ') : '' }}</span>
                            @endif
                            <x-icon name="chevron-down" class="size-4 transition" ::class="open && 'rotate-180'" />
                        </button>
                        <form x-show="open" x-cloak x-transition.opacity method="GET" action="{{ route('products.index') }}"
                              x-data="{ min: @js(\App\Support\FormField::thousands($minPrice)), max: @js(\App\Support\FormField::thousands($maxPrice)) }"
                              class="absolute top-full left-0 mt-2 w-80 rounded-3xl border border-stone-200 bg-white p-5 shadow-2xl shadow-ink/10">
                            @foreach (Arr::except($baseQuery, ['harga_min', 'harga_max']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <p class="eyebrow text-stone-500">Rentang harga</p>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @foreach ($pricePresets as [$label, $from, $to])
                                    <button type="button" @click="min = @js(\App\Support\FormField::thousands($from)); max = @js(\App\Support\FormField::thousands($to))"
                                            :class="min == @js(\App\Support\FormField::thousands($from)) && max == @js(\App\Support\FormField::thousands($to)) ? '!border-ink !bg-ink !text-white' : ''"
                                            class="rounded-xl border border-stone-200 px-3 py-2 text-xs font-medium transition hover:border-brand-400">{{ $label }}</button>
                                @endforeach
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <label class="sr-only" for="d_harga_min">Harga minimum</label>
                                <input id="d_harga_min" type="text" name="harga_min" x-model="min" x-money placeholder="Min Rp" class="input rounded-xl py-2 text-sm">
                                <label class="sr-only" for="d_harga_max">Harga maksimum</label>
                                <input id="d_harga_max" type="text" name="harga_max" x-model="max" x-money placeholder="Maks Rp" class="input rounded-xl py-2 text-sm">
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <a href="{{ route('products.index', Arr::except($baseQuery, ['harga_min', 'harga_max'])) }}" class="link-underline text-xs font-semibold">Reset</a>
                                <button class="btn btn-ink rounded-full px-5 py-2 text-sm">Terapkan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="ml-auto hidden text-sm text-stone-500 sm:block">
                    <span class="font-semibold text-ink">{{ $products->total() }}</span> produk
                </p>

                <form method="GET" x-data @change="$el.submit()" class="ml-auto flex items-center sm:ml-3">
                    @foreach (Arr::except($baseQuery, 'urut') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label for="sort" class="sr-only">Urutkan</label>
                    <div class="relative">
                        <x-icon name="chevron-up-down" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-stone-500" />
                        <select id="sort" name="urut" class="min-h-10 rounded-full border-stone-200 bg-sand-50 py-2 pr-9 pl-10 text-base font-semibold focus:border-ink focus:ring-ink/10 lg:text-sm">
                            @foreach ($sorts as $key => $label)
                                <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="shop-container pt-6 pb-4 lg:pt-8">
            @if ($activeFilters)
                <div class="mb-6 flex flex-wrap items-center gap-2">
                    @foreach ($activeFilters as $key => $label)
                        @php($without = $key === 'harga' ? Arr::except($baseQuery, ['harga_min', 'harga_max']) : Arr::except($baseQuery, $key))
                        <a href="{{ route('products.index', $without) }}" class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-800 ring-1 ring-brand-200 hover:bg-brand-100">
                            {{ $label }} <x-icon name="x" class="size-3.5" /><span class="sr-only">Hapus filter</span>
                        </a>
                    @endforeach
                    @if (count($activeFilters) > 1)
                        <a href="{{ route('products.index', Arr::only($baseQuery, ['kategori', 'koleksi'])) }}" class="link-underline px-1 text-xs font-semibold">Hapus semua</a>
                    @endif
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="rounded-3xl border border-dashed border-stone-300">
                    <x-empty-state icon="search" title="Produk tidak ditemukan" description="Coba kata kunci lain atau kurangi filter yang dipakai.">
                        <a href="{{ route('products.index') }}" class="btn btn-ink btn-pill">Lihat semua produk</a>
                    </x-empty-state>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" :eager="$loop->index < 4" />
                    @endforeach
                </div>
                <div class="mt-12">{{ $products->links() }}</div>
            @endif
        </div>

        {{-- Bottom sheet filter (mobile) --}}
        <div x-show="filters" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-labelledby="sheet-title">
            <div x-show="filters" x-transition.opacity @click="filters = false" class="absolute inset-0 bg-ink/50"></div>
            <form x-show="filters"
                  x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                  x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                  method="GET" action="{{ route('products.index') }}"
                  x-data="{ min: @js(\App\Support\FormField::thousands($minPrice)), max: @js(\App\Support\FormField::thousands($maxPrice)) }"
                  class="absolute inset-x-0 bottom-0 flex max-h-[88svh] flex-col rounded-t-[2rem] bg-white">
                @foreach (Arr::only($baseQuery, ['q', 'kategori', 'koleksi']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <div class="flex items-center justify-between px-5 pt-3 pb-2">
                    <span class="absolute top-2 left-1/2 h-1 w-10 -translate-x-1/2 rounded-full bg-stone-300" aria-hidden="true"></span>
                    <p id="sheet-title" class="display mt-3 text-2xl">Filter</p>
                    <button type="button" @click="filters = false" class="-mr-2 mt-3 grid size-10 place-items-center rounded-full hover:bg-sand-100" aria-label="Tutup filter"><x-icon name="x" /></button>
                </div>

                <div class="flex-1 space-y-6 overflow-y-auto px-5 pt-2 pb-6">
                    <div role="group" aria-labelledby="m-sort">
                        <p id="m-sort" class="eyebrow text-stone-500">Urutkan</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach ($sorts as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="urut" value="{{ $key }}" class="peer sr-only" @checked($sort === $key)>
                                    <span class="flex min-h-11 items-center justify-center rounded-2xl border border-stone-200 px-3 text-center text-sm font-medium peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div role="group" aria-labelledby="m-show">
                        <p id="m-show" class="eyebrow text-stone-500">Tampilkan</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="checkbox" name="tersedia" value="1" class="peer sr-only" @checked(request()->boolean('tersedia'))>
                                <span class="flex min-h-11 items-center justify-center gap-2 rounded-2xl border border-stone-200 text-sm font-medium peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400"><x-icon name="check" class="size-4" /> Tersedia</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="checkbox" name="promo" value="1" class="peer sr-only" @checked(request()->boolean('promo'))>
                                <span class="flex min-h-11 items-center justify-center gap-2 rounded-2xl border border-stone-200 text-sm font-medium peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400"><span class="size-2 rounded-full bg-accent-500"></span> Sedang diskon</span>
                            </label>
                        </div>
                    </div>

                    <div role="group" aria-labelledby="m-price">
                        <p id="m-price" class="eyebrow text-stone-500">Harga</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach ($pricePresets as [$label, $from, $to])
                                <button type="button" @click="min = @js(\App\Support\FormField::thousands($from)); max = @js(\App\Support\FormField::thousands($to))"
                                        :class="min == @js(\App\Support\FormField::thousands($from)) && max == @js(\App\Support\FormField::thousands($to)) ? '!border-ink !bg-ink !text-white' : ''"
                                        class="min-h-11 rounded-2xl border border-stone-200 px-3 text-sm font-medium">{{ $label }}</button>
                            @endforeach
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <label class="sr-only" for="harga_min">Harga minimum</label>
                            <input id="harga_min" type="text" name="harga_min" x-model="min" x-money placeholder="Min Rp" class="input rounded-2xl text-base">
                            <label class="sr-only" for="harga_max">Harga maksimum</label>
                            <input id="harga_max" type="text" name="harga_max" x-model="max" x-money placeholder="Maks Rp" class="input rounded-2xl text-base">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-[auto_1fr] gap-2 border-t border-stone-200 px-5 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                    <a href="{{ route('products.index', Arr::only($baseQuery, ['q', 'kategori', 'koleksi'])) }}" class="btn btn-outline rounded-full px-6 py-3">Reset</a>
                    <button class="btn btn-brand rounded-full py-3">Tampilkan produk</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.shop>
