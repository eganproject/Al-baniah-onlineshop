{{-- "Belanja per koleksi": ubin berbingkai lengkung mihrab, digeser ke samping bila tidak muat --}}
@use('App\Support\Storefront')
@if ($activities->isNotEmpty())
    <section class="section-gap" aria-labelledby="home-categories">
        <div class="shop-container">
            <div class="section-head">
                <span class="ornament-mark" aria-hidden="true"><span class="size-1.5 rotate-45 bg-current"></span></span>
                <p class="eyebrow mt-3 text-brand-700">{{ Storefront::text('activities_eyebrow', $activitiesKind === 'collections' ? 'Koleksi' : 'Kategori') }}</p>
                <h2 id="home-categories" class="section-title">{{ Storefront::text('activities_title', 'Belanja per koleksi') }}</h2>
            </div>
            <ul class="no-scrollbar -mx-4 mt-8 grid snap-x auto-cols-[42%] grid-flow-col justify-center-safe gap-3 overflow-x-auto scroll-px-4 px-4 pb-2 sm:mx-0 sm:auto-cols-[28%] sm:gap-5 sm:scroll-px-0 sm:px-0 lg:mt-12 lg:auto-cols-[calc((100%-6*1.5rem)/7)] lg:gap-6">
                @foreach ($activities as $tile)
                    @php($photo = $tile['image'] ?? $tile['cover'] ?? null)
                    <li class="snap-start">
                        <a href="{{ $tile['url'] }}" class="group block text-center">
                            <span class="arch relative grid aspect-[3/4] place-items-center overflow-hidden bg-sand-100 ring-1 ring-sand-200 transition group-hover:ring-brand-300">
                                <x-category-icon :name="$tile['icon']" :for="$tile['for']" :stroke="1" class="size-14 text-brand-400 sm:size-16" />
                                @if ($photo)
                                    <img src="{{ $photo }}" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.remove()"
                                         class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-[1.04]">
                                @endif
                            </span>
                            <span class="mt-3 block font-display text-lg leading-tight font-semibold text-ink transition group-hover:text-brand-700 sm:text-xl">{{ $tile['name'] }}</span>
                            <span class="mt-0.5 block text-xs text-stone-500">{{ $tile['count'] }} produk</span>
                        </a>
                    </li>
                @endforeach
                <li class="snap-start">
                    <a href="{{ route('products.index') }}" class="group block text-center">
                        <span class="arch grid aspect-[3/4] place-items-center border border-dashed border-brand-300 bg-white transition group-hover:border-ink">
                            <span class="grid size-12 place-items-center rounded-full bg-ink text-white transition group-hover:scale-105"><x-icon name="arrow-right" class="size-5" /></span>
                        </span>
                        <span class="mt-3 block font-display text-lg leading-tight font-semibold text-ink sm:text-xl">Semua produk</span>
                        <span class="mt-0.5 block text-xs text-stone-500">Jelajahi katalog</span>
                    </a>
                </li>
            </ul>
        </div>
    </section>
@endif
