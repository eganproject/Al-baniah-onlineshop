{{-- Bento "Belanja per aktivitas": koleksi (atau kategori) — satu ubin besar + ubin kecil --}}
@use('App\Support\Storefront')
@php
    // Warna ubin bergantian bila koleksi tidak memilih warna sendiri.
    $tones = array_map(fn ($t) => [$t[0], $t[1]], array_values(\App\Models\ProductCollection::TONES));
@endphp
@if ($activities->isNotEmpty())
    <section class="shop-container mt-14 lg:mt-24" aria-labelledby="home-categories">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="eyebrow text-brand-600">{{ Storefront::text('activities_eyebrow', $activitiesKind === 'collections' ? 'Aktivitas' : 'Kategori') }}</p>
                <h2 id="home-categories" class="display mt-2 text-3xl leading-none sm:text-5xl">{{ Storefront::text('activities_title', 'Belanja per aktivitas') }}</h2>
            </div>
        </div>
        <ul class="mt-6 grid auto-rows-[150px] grid-cols-2 gap-3 sm:mt-8 sm:auto-rows-[190px] sm:gap-4 lg:grid-cols-4 lg:auto-rows-[210px]">
            @foreach ($activities as $tile)
                @php([$tone, $bubble] = $tile['tone'] ?? $tones[$loop->index % count($tones)])
                <li @class(['col-span-2 lg:row-span-2' => $loop->first])>
                    <a href="{{ $tile['url'] }}"
                       class="group relative flex size-full flex-col justify-between overflow-hidden rounded-3xl p-4 transition duration-300 hover:shadow-xl hover:shadow-ink/10 sm:p-6 {{ $tile['image'] ? 'bg-ink text-white' : $tone }}">
                        @if ($tile['image'])
                            <img src="{{ $tile['image'] }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
                            <span class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/15 to-transparent"></span>
                        @else
                            <span @class(['absolute rounded-full transition duration-700 group-hover:scale-110', $bubble, '-right-16 -bottom-16 size-72 lg:size-96' => $loop->first, '-right-10 -bottom-10 size-36' => ! $loop->first]) aria-hidden="true"></span>
                            <x-sport-icon :name="$tile['icon']" :for="$tile['for']" @class(['absolute transition duration-500 group-hover:-rotate-6', 'top-5 right-5 size-20 sm:size-28 lg:top-8 lg:right-8 lg:size-44' => $loop->first, 'top-4 right-4 size-11 sm:size-14' => ! $loop->first]) />
                        @endif
                        <span class="relative grid size-9 place-items-center rounded-full bg-white/90 text-ink transition group-hover:bg-white sm:size-10">
                            <x-icon name="arrow-right" class="size-4 -rotate-45 transition group-hover:rotate-0" />
                        </span>
                        <span class="relative">
                            <span @class(['block leading-[0.95] font-extrabold italic uppercase', 'text-3xl sm:text-4xl lg:text-6xl' => $loop->first, 'text-base sm:text-xl' => ! $loop->first])>{{ $tile['name'] }}</span>
                            @if ($loop->first && $tile['tagline'])
                                <span class="mt-2 block max-w-sm text-sm font-semibold sm:text-base">{{ $tile['tagline'] }}</span>
                            @endif
                            <span class="mt-1 block text-xs font-medium opacity-80 sm:text-sm">{{ $tile['count'] }} produk</span>
                            @if ($tile['chips'])
                                <span class="mt-3 hidden flex-wrap gap-1.5 sm:flex">
                                    @foreach ($tile['chips'] as $chip)
                                        <span class="rounded-full bg-white/25 px-3 py-1 text-xs font-semibold backdrop-blur">{{ $chip['label'] }}</span>
                                    @endforeach
                                </span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
            <li>
                <a href="{{ route('products.index') }}" class="group flex size-full flex-col justify-between rounded-3xl border-2 border-dashed border-stone-300 p-4 transition hover:border-ink sm:p-6">
                    <span class="grid size-9 place-items-center rounded-full bg-ink text-white sm:size-10"><x-icon name="arrow-right" class="size-4" /></span>
                    <span>
                        <span class="block text-base leading-tight font-extrabold uppercase italic sm:text-xl">Semua produk</span>
                        <span class="mt-1 block text-xs text-stone-500 sm:text-sm">Jelajahi katalog lengkap</span>
                    </span>
                </a>
            </li>
        </ul>
    </section>
@endif
