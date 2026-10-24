{{-- "Belanja per aktivitas": koleksi (atau kategori) — satu ubin besar + ubin kecil. Warna ubin kosong = palet kalem bawaan. --}}
@use('App\Support\Storefront')
@if ($activities->isNotEmpty())
    <section class="shop-container section-gap" aria-labelledby="home-categories">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="kicker">{{ Storefront::text('activities_eyebrow', $activitiesKind === 'collections' ? 'Aktivitas' : 'Kategori') }}</p>
                <h2 id="home-categories" class="section-title">{{ Storefront::text('activities_title', 'Belanja per aktivitas') }}</h2>
            </div>
            <a href="{{ route('products.index') }}" class="link-arrow hidden sm:inline-flex">Semua produk <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <ul class="mt-7 grid auto-rows-[148px] grid-cols-2 gap-3 sm:mt-9 sm:auto-rows-[180px] sm:gap-4 lg:auto-rows-[200px] lg:grid-cols-4">
            @foreach ($activities as $tile)
                @php
                    [$tone, $iconTone] = $tile['tone'] ?? ($loop->first ? ['bg-ink text-white', 'text-brand-400/50'] : ['bg-sand-100 text-ink', 'text-ink/10']);
                    $big = $loop->first;
                @endphp
                <li @class(['col-span-2 row-span-2' => $big])>
                    <a href="{{ $tile['url'] }}"
                       class="group relative flex size-full flex-col justify-between overflow-hidden rounded-2xl p-4 transition duration-300 sm:p-6 {{ $tile['image'] ? 'bg-ink text-white' : $tone }}">
                        @if ($tile['image'])
                            <img src="{{ $tile['image'] }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-[1.03]">
                            <span class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/10 to-transparent"></span>
                        @else
                            <x-sport-icon :name="$tile['icon']" :for="$tile['for']" @class(['absolute transition duration-500 group-hover:-translate-y-1', $iconTone, 'top-14 -right-4 size-36 sm:top-16 sm:right-6 sm:size-52 lg:size-60' => $big, '-right-2 -bottom-2 size-20 sm:-right-3 sm:-bottom-3 sm:size-28' => ! $big]) />
                        @endif
                        <span class="relative flex items-start justify-between gap-3">
                            @if ($big)
                                <span class="kicker text-current opacity-70">{{ $tile['count'] }} produk</span>
                            @else
                                <span></span>
                            @endif
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-ink opacity-90 transition group-hover:opacity-100 sm:size-10">
                                <x-icon name="arrow-right" class="size-4 -rotate-45 transition group-hover:rotate-0" />
                            </span>
                        </span>
                        <span class="relative">
                            <span @class(['block font-display font-bold tracking-tight', 'text-3xl leading-none sm:text-5xl' => $big, 'text-base leading-tight sm:text-lg' => ! $big])>{{ $tile['name'] }}</span>
                            @if ($big && $tile['tagline'])
                                <span class="mt-3 block max-w-sm text-sm opacity-75 sm:text-base">{{ $tile['tagline'] }}</span>
                            @endif
                            @unless ($big)
                                <span class="mt-0.5 block text-xs opacity-60">{{ $tile['count'] }} produk</span>
                            @endunless
                            @if ($tile['chips'])
                                <span class="mt-5 hidden flex-wrap gap-1.5 sm:flex">
                                    @foreach ($tile['chips'] as $chip)
                                        <span class="rounded-full px-3 py-1 text-xs font-medium ring-1 ring-current/25">{{ $chip['label'] }}</span>
                                    @endforeach
                                </span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
            <li>
                <a href="{{ route('products.index') }}" class="group flex size-full flex-col justify-between rounded-2xl border border-dashed border-stone-300 p-4 transition hover:border-ink sm:p-6">
                    <span class="grid size-9 place-items-center rounded-full bg-ink text-white sm:size-10"><x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" /></span>
                    <span>
                        <span class="block font-display text-base leading-tight font-bold tracking-tight sm:text-lg">Semua produk</span>
                        <span class="mt-0.5 block text-xs text-stone-500">Jelajahi katalog lengkap</span>
                    </span>
                </a>
            </li>
        </ul>
    </section>
@endif
