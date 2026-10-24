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
<x-layouts.shop :title="$context?->name ?? 'Semua Produk'" :description="$metaDescription">
    <div x-data="{ filters: false }" x-effect="document.body.classList.toggle('overflow-hidden', filters)" @keydown.escape.window="filters = false">
        {{-- Kepala katalog --}}
        <section class="border-b border-stone-200/70 bg-white">
            <div class="shop-container pt-6 pb-6 lg:pt-10 lg:pb-8">
                <nav class="flex flex-wrap items-center gap-1.5 text-xs text-stone-500" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-ink">Beranda</a>
                    <x-icon name="chevron-right" class="size-3 text-stone-300" />
                    @if ($activeCategory || $activeCollection)
                        <a href="{{ route('products.index') }}" class="hover:text-ink">Produk</a>
                        @if ($activeCollection)
                            <x-icon name="chevron-right" class="size-3 text-stone-300" />
                            @if ($activeCategory)
                                <a href="{{ $collectionUrl($activeCollection->slug) }}" class="hover:text-ink">{{ $activeCollection->name }}</a>
                            @else
                                <span class="text-ink">{{ $activeCollection->name }}</span>
                            @endif
                        @endif
                    @endif
                    @if ($activeCategory)
                        @if ($activeCategory->parent)
                            <x-icon name="chevron-right" class="size-3 text-stone-300" />
                            <a href="{{ $categoryUrl($activeCategory->parent->slug) }}" class="hover:text-ink">{{ $activeCategory->parent->name }}</a>
                        @endif
                        <x-icon name="chevron-right" class="size-3 text-stone-300" />
                        <span class="text-ink">{{ $activeCategory->name }}</span>
                    @elseif (! $activeCollection)
                        <span class="text-ink">Produk</span>
                    @endif
                </nav>

                <div class="mt-6 flex flex-col gap-4 lg:mt-8 lg:flex-row lg:items-end lg:justify-between lg:gap-10">
                    <div class="min-w-0">
                        <p class="kicker">
                            @if ($activeCollection && $activeCategory)
                                {{ $activeCollection->name }}
                            @elseif ($activeCollection)
                                Aktivitas
                            @elseif ($activeCategory?->parent)
                                {{ $activeCategory->parent->name }}
                            @elseif ($activeCategory)
                                Kategori
                            @else
                                Katalog {{ config('shop.name') }}
                            @endif
                        </p>
                        <h1 class="display mt-3 text-[2.1rem] leading-[1.02] break-words sm:text-5xl lg:text-6xl">{{ $heading }}</h1>
                    </div>
                    <p class="max-w-md text-sm leading-relaxed text-stone-500 sm:text-[15px] lg:pb-1 lg:text-right">{{ $intro }}</p>
                </div>

                {{-- Pilihan aktivitas (koleksi) --}}
                @if ($collections->isNotEmpty())
                    <div class="no-scrollbar -mx-4 mt-7 flex items-center gap-2 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:px-0 lg:mt-9" aria-label="Aktivitas">
                        <span class="eyebrow mr-1 shrink-0 text-stone-400">Aktivitas</span>
                        @foreach ($collections as $item)
                            <a href="{{ $activeCollection?->is($item) ? $collectionUrl(null) : $collectionUrl($item->slug) }}"
                               @class(['chip shrink-0 px-3.5 py-1.5 text-[13px] max-lg:min-h-9', 'chip-active' => $activeCollection?->is($item)])
                               @if ($activeCollection?->is($item)) aria-current="page" @endif>
                                <x-sport-icon :name="$item->iconName()" :for="$item->name.' '.$item->slug" class="size-4 opacity-70" /> {{ $item->name }}
                                @if ($activeCollection?->is($item))<x-icon name="x" class="size-3.5 opacity-70" />@endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Kategori: tab bergaris bawah --}}
            @if ($categories->isNotEmpty())
                <div class="shop-container">
                    <nav class="no-scrollbar -mx-4 flex gap-6 overflow-x-auto px-4 sm:mx-0 sm:gap-8 sm:px-0" aria-label="Kategori">
                        @php($tab = 'relative flex min-h-12 shrink-0 items-center gap-1.5 pb-0.5 text-sm whitespace-nowrap transition')
                        <a href="{{ $categoryUrl(null) }}" @class([$tab, 'font-semibold text-ink after:absolute after:inset-x-0 after:bottom-0 after:h-0.5 after:bg-ink' => ! $activeCategory, 'text-stone-500 hover:text-ink' => $activeCategory])>
                            Semua
                        </a>
                        @foreach ($categories as $category)
                            @php($on = $openParent?->is($category))
                            <a href="{{ $categoryUrl($category->slug) }}" @class([$tab, 'font-semibold text-ink after:absolute after:inset-x-0 after:bottom-0 after:h-0.5 after:bg-ink' => $on, 'text-stone-500 hover:text-ink' => ! $on]) @if ($on) aria-current="page" @endif>
                                {{ $category->name }} <span class="text-xs font-normal text-stone-400">{{ $category->products_count }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endif

            {{-- Subkategori --}}
            @if ($openParent?->children->isNotEmpty())
                <div class="border-t border-stone-100 bg-sand-50">
                    <div class="shop-container no-scrollbar flex items-center gap-2 overflow-x-auto py-3">
                        <span class="eyebrow mr-1 shrink-0 text-stone-400">Jenis</span>
                        <a href="{{ $categoryUrl($openParent->slug) }}"
                           @class(['chip px-3.5 py-1.5 text-xs max-lg:min-h-9', 'chip-active' => $activeCategory->is($openParent)])>Semua {{ $openParent->name }}</a>
                        @foreach ($openParent->children as $child)
                            <a href="{{ $categoryUrl($child->slug) }}"
                               @class(['chip px-3.5 py-1.5 text-xs max-lg:min-h-9', 'chip-active' => $activeCategory->is($child)])>{{ $child->name }} <span class="opacity-50">{{ $child->products_count }}</span></a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- Bar filter horizontal (lengket di bawah navbar) --}}
        <div class="sticky top-16 z-30 border-b border-stone-200/70 bg-white/95 backdrop-blur-md lg:top-[72px]">
            <div class="shop-container flex h-[60px] items-center gap-2">
                {{-- Mobile: satu tombol membuka bottom sheet --}}
                <button type="button" @click="filters = true" class="inline-flex min-h-10 items-center gap-2 rounded-full border border-stone-300 px-4 text-sm font-medium text-ink lg:hidden">
                    <x-icon name="filter" class="size-4" /> Filter
                    @if ($filterCount)
                        <span class="grid size-5 place-items-center rounded-full bg-ink text-[10px] text-white">{{ $filterCount }}</span>
                    @endif
                </button>

                {{-- Desktop: pil filter cepat --}}
                <div class="hidden items-center gap-2 lg:flex">
                    <a href="{{ $toggleUrl('tersedia') }}" @class(['chip px-3.5 py-2', 'chip-active' => request()->boolean('tersedia')]) @if (request()->boolean('tersedia')) aria-pressed="true" @endif>
                        <x-icon name="check" class="size-4" /> Tersedia
                    </a>
                    <a href="{{ $toggleUrl('promo') }}" @class(['chip px-3.5 py-2', 'chip-active' => request()->boolean('promo')]) @if (request()->boolean('promo')) aria-pressed="true" @endif>
                        <x-icon name="tag" class="size-4" /> Diskon
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
                              class="absolute top-full left-0 mt-2 w-80 rounded-2xl border border-stone-200 bg-white p-5 shadow-xl shadow-ink/10">
                            @foreach (Arr::except($baseQuery, ['harga_min', 'harga_max']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <p class="eyebrow text-stone-500">Rentang harga</p>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @foreach ($pricePresets as [$label, $from, $to])
                                    <button type="button" @click="min = @js(\App\Support\FormField::thousands($from)); max = @js(\App\Support\FormField::thousands($to))"
                                            :class="min == @js(\App\Support\FormField::thousands($from)) && max == @js(\App\Support\FormField::thousands($to)) ? '!border-ink !bg-ink !text-white' : ''"
                                            class="rounded-xl border border-stone-200 px-3 py-2 text-xs font-medium transition hover:border-stone-400">{{ $label }}</button>
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
                        <select id="sort" name="urut" class="min-h-10 rounded-full border-stone-200 bg-white py-2 pr-9 pl-10 text-base font-medium focus:border-ink focus:ring-ink/10 lg:text-sm">
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
                        <a href="{{ route('products.index', $without) }}" class="inline-flex items-center gap-1.5 rounded-full bg-sand-100 px-3 py-1.5 text-xs font-medium text-ink hover:bg-sand-200">
                            {{ $label }} <x-icon name="x" class="size-3.5" /><span class="sr-only">Hapus filter</span>
                        </a>
                    @endforeach
                    @if (count($activeFilters) > 1)
                        <a href="{{ route('products.index', Arr::only($baseQuery, ['kategori', 'koleksi'])) }}" class="link-underline px-1 text-xs font-semibold">Hapus semua</a>
                    @endif
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="rounded-2xl border border-dashed border-stone-300">
                    <x-empty-state icon="search" title="Produk tidak ditemukan" description="Coba kata kunci lain atau kurangi filter yang dipakai.">
                        <a href="{{ route('products.index') }}" class="btn btn-ink btn-pill">Lihat semua produk</a>
                    </x-empty-state>
                </div>
            @else
                <div class="grid grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-6 sm:gap-y-10 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" :eager="$loop->index < 4" />
                    @endforeach
                </div>
                <div class="mt-14">{{ $products->links() }}</div>
            @endif
        </div>

        {{-- Bottom sheet filter (mobile) --}}
        <div x-show="filters" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-labelledby="sheet-title">
            <div x-show="filters" x-transition.opacity @click="filters = false" class="absolute inset-0 bg-ink/40 backdrop-blur-sm"></div>
            <form x-show="filters"
                  x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                  x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                  method="GET" action="{{ route('products.index') }}"
                  x-data="{ min: @js(\App\Support\FormField::thousands($minPrice)), max: @js(\App\Support\FormField::thousands($maxPrice)) }"
                  class="absolute inset-x-0 bottom-0 flex max-h-[88svh] flex-col rounded-t-3xl bg-white">
                @foreach (Arr::only($baseQuery, ['q', 'kategori', 'koleksi']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <div class="flex items-center justify-between px-5 pt-3 pb-2">
                    <span class="absolute top-2 left-1/2 h-1 w-10 -translate-x-1/2 rounded-full bg-stone-200" aria-hidden="true"></span>
                    <p id="sheet-title" class="display mt-3 text-xl">Filter</p>
                    <button type="button" @click="filters = false" class="-mr-2 mt-3 grid size-10 place-items-center rounded-full hover:bg-sand-100" aria-label="Tutup filter"><x-icon name="x" /></button>
                </div>

                <div class="flex-1 space-y-6 overflow-y-auto px-5 pt-2 pb-6">
                    <div role="group" aria-labelledby="m-sort">
                        <p id="m-sort" class="eyebrow text-stone-500">Urutkan</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach ($sorts as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="urut" value="{{ $key }}" class="peer sr-only" @checked($sort === $key)>
                                    <span class="flex min-h-11 items-center justify-center rounded-xl border border-stone-200 px-3 text-center text-sm font-medium peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div role="group" aria-labelledby="m-show">
                        <p id="m-show" class="eyebrow text-stone-500">Tampilkan</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="checkbox" name="tersedia" value="1" class="peer sr-only" @checked(request()->boolean('tersedia'))>
                                <span class="flex min-h-11 items-center justify-center gap-2 rounded-xl border border-stone-200 text-sm font-medium peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400"><x-icon name="check" class="size-4" /> Tersedia</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="checkbox" name="promo" value="1" class="peer sr-only" @checked(request()->boolean('promo'))>
                                <span class="flex min-h-11 items-center justify-center gap-2 rounded-xl border border-stone-200 text-sm font-medium peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400"><x-icon name="tag" class="size-4" /> Sedang diskon</span>
                            </label>
                        </div>
                    </div>

                    <div role="group" aria-labelledby="m-price">
                        <p id="m-price" class="eyebrow text-stone-500">Harga</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach ($pricePresets as [$label, $from, $to])
                                <button type="button" @click="min = @js(\App\Support\FormField::thousands($from)); max = @js(\App\Support\FormField::thousands($to))"
                                        :class="min == @js(\App\Support\FormField::thousands($from)) && max == @js(\App\Support\FormField::thousands($to)) ? '!border-ink !bg-ink !text-white' : ''"
                                        class="min-h-11 rounded-xl border border-stone-200 px-3 text-sm font-medium">{{ $label }}</button>
                            @endforeach
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <label class="sr-only" for="harga_min">Harga minimum</label>
                            <input id="harga_min" type="text" name="harga_min" x-model="min" x-money placeholder="Min Rp" class="input text-base">
                            <label class="sr-only" for="harga_max">Harga maksimum</label>
                            <input id="harga_max" type="text" name="harga_max" x-model="max" x-money placeholder="Maks Rp" class="input text-base">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-[auto_1fr] gap-2 border-t border-stone-200 px-5 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                    <a href="{{ route('products.index', Arr::only($baseQuery, ['q', 'kategori', 'koleksi'])) }}" class="btn btn-outline rounded-full px-6 py-3">Reset</a>
                    <button class="btn btn-ink rounded-full py-3">Tampilkan produk</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.shop>
