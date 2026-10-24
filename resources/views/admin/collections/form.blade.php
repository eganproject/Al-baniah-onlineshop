@php
    use App\Models\ProductCollection;
    $editing = $collection->exists;
@endphp
<x-layouts.admin :title="$editing ? 'Edit Koleksi' : 'Tambah Koleksi'">
    <x-admin.page-header :title="$editing ? 'Edit koleksi' : 'Tambah koleksi'" :back="route('admin.collections.index')">
        @if ($editing)
            <a href="{{ $collection->url() }}" target="_blank" rel="noopener" class="btn btn-secondary"><x-icon name="external" class="size-4" /> Lihat di toko</a>
        @endif
    </x-admin.page-header>

    <x-form.errors />

    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.collections.update', $collection) : route('admin.collections.store') }}"
          class="grid gap-6 lg:grid-cols-3"
          x-data="bestSellerPicker(@js([
              'mode' => 'manual',
              'items' => $products,
              'max' => \App\Http\Controllers\Admin\CollectionController::MAX_PRODUCTS,
              'shown' => \App\Http\Controllers\Admin\CollectionController::MAX_PRODUCTS,
              'searchUrl' => route('admin.collections.products'),
          ]))">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="min-w-0 space-y-6 lg:col-span-2">
            <section class="card space-y-5 p-6">
                <x-form.input name="name" label="Nama koleksi" :value="$collection->name" required maxlength="100" placeholder="Contoh: Travel & Umrah" />
                <x-form.input name="tagline" label="Teks pendek" optional :value="$collection->tagline" maxlength="120"
                    placeholder="Contoh: Ringkas dibawa, tetap nyaman dipakai" hint="Tampil sebagai pembuka halaman koleksi."
                    tip="Kalimat penjual singkat untuk koleksi ini, tampil sebagai pembuka di halaman katalog koleksi."
                    :tips="['Satu kalimat, maks. ±60 huruf, fokus manfaat. Mis. “Ringkas dibawa, tetap nyaman dipakai”.']" />
                <x-form.textarea name="description" label="Deskripsi" optional :value="$collection->description" rows="3" maxlength="1000"
                    hint="Untuk halaman koleksi & mesin pencari."
                    tip="Dipakai sebagai deskripsi halaman koleksi di Google & saat link dibagikan. Bila Teks pendek kosong, deskripsi ini juga tampil sebagai pembuka halaman koleksi."
                    :tips="['1–2 kalimat (±150 huruf) yang menyebut isi koleksi dan momennya.']" />
                <x-form.input name="slug" label="Slug URL" optional="kosongkan untuk otomatis" :value="$collection->slug" maxlength="120"
                    tip="Nama koleksi di alamat link, mis. /produk?koleksi=travel-umrah. Dibuat otomatis dari nama bila dikosongkan."
                    :tips="['Jangan diubah setelah link koleksi dipakai di banner, hero, atau iklan.']" />
            </section>

            {{-- Isi koleksi --}}
            <section class="card space-y-4 p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="font-bold text-stone-900">Produk dalam koleksi</h2>
                        <p class="text-xs text-stone-500">Bisa juga diatur dari form produk (kolom "Koleksi").</p>
                    </div>
                    <span class="text-xs text-stone-500"><span x-text="items.length"></span> produk</span>
                </div>

                @if (! $manageable)
                    <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800">Koleksi ini berisi lebih dari {{ \App\Http\Controllers\Admin\CollectionController::MAX_PRODUCTS }} produk, jadi isinya diatur dari form masing-masing produk.</p>
                @else
                <input type="hidden" name="products_managed" value="1">
                <div class="relative" @click.outside="open = false" @keydown.escape.window="open = false">
                    <label for="collection-search" class="sr-only">Tambah produk</label>
                    <div class="relative">
                        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-stone-400" />
                        <input id="collection-search" type="search" x-model="query" autocomplete="off" class="input pl-9"
                               placeholder="Cari nama atau SKU produk untuk ditambahkan..." :disabled="full"
                               @focus="search()" @input.debounce.300ms="search()" @keydown.enter.prevent="results[0] && add(results[0])">
                    </div>
                    <div x-show="open && !full" x-cloak x-transition.opacity class="absolute inset-x-0 top-full z-20 mt-1 max-h-80 overflow-y-auto rounded-xl border border-stone-200 bg-white p-1 shadow-lg">
                        <p x-show="loading && results.length === 0" class="px-3 py-2 text-sm text-stone-500">Memuat...</p>
                        <p x-show="!loading && results.length === 0" class="px-3 py-2 text-sm text-stone-500">Produk tidak ditemukan.</p>
                        <template x-for="product in results" :key="product.id">
                            <button type="button" @click="add(product)" :disabled="has(product.id)"
                                    class="flex w-full items-center gap-3 rounded-lg px-2 py-1.5 text-left hover:bg-stone-50 disabled:cursor-default disabled:opacity-50">
                                <span class="size-10 shrink-0 overflow-hidden rounded-lg bg-stone-100">
                                    <img x-show="product.image" :src="product.image" referrerpolicy="no-referrer" alt="" class="size-full object-cover" loading="lazy">
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-stone-900" x-text="product.name"></span>
                                    <span class="block truncate text-xs text-stone-500"><span x-text="product.price"></span><span x-show="product.sku"> · <span x-text="product.sku"></span></span></span>
                                </span>
                                <span x-show="has(product.id)" class="text-brand-600"><x-icon name="check" class="size-4" /></span>
                                <span x-show="!has(product.id)" class="text-stone-400"><x-icon name="plus" class="size-4" /></span>
                            </button>
                        </template>
                    </div>
                </div>

                <p x-show="items.length === 0" class="rounded-xl border border-dashed border-stone-300 p-6 text-center text-sm text-stone-500">Belum ada produk. Cari produk di atas untuk menambahkan.</p>
                <ul x-show="items.length > 0" x-cloak class="max-h-[28rem] divide-y divide-stone-100 overflow-y-auto rounded-xl border border-stone-200">
                    <template x-for="(product, index) in items" :key="product.id">
                        <li class="flex items-center gap-3 bg-white px-3 py-2">
                            <input type="hidden" name="products[]" x-bind:value="product.id">
                            <span class="size-10 shrink-0 overflow-hidden rounded-lg bg-stone-100">
                                <img x-show="product.image" :src="product.image" referrerpolicy="no-referrer" alt="" class="size-full object-cover" loading="lazy">
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-stone-900" x-text="product.name"></span>
                                <span class="flex flex-wrap items-center gap-2 text-xs text-stone-500">
                                    <span x-text="product.price"></span>
                                    <span x-show="!product.active" class="badge bg-stone-100 text-stone-600">Nonaktif</span>
                                </span>
                            </span>
                            <button type="button" class="rounded p-1 text-stone-400 hover:text-rose-600" @click="remove(index)" title="Keluarkan dari koleksi"><x-icon name="x" class="size-4" /><span class="sr-only">Keluarkan</span></button>
                        </li>
                    </template>
                </ul>
                <x-input-error for="products" />
                @endif
            </section>
        </div>

        <div class="space-y-6">
            <section class="card space-y-5 p-6">
                <label class="flex items-center justify-between gap-3">
                    <span><span class="block text-sm font-semibold text-stone-800">Aktif di toko</span><span class="text-xs text-stone-500">Muncul di menu & filter katalog</span></span>
                    <input type="checkbox" name="is_active" value="1" class="checkbox size-5" @checked(old('is_active', $collection->is_active))>
                </label>
                <label class="flex items-center justify-between gap-3">
                    <span><span class="flex items-center gap-1.5 text-sm font-semibold text-stone-800">Tampil di beranda <x-tip title="Tampil di beranda" text="Koleksi jadi ubin di seksi “Belanja per koleksi” dan pintasan “Pilih koleksimu” di hero. Maksimal {{ \App\Services\Storefront\ActivityTiles::LIMIT }} ubin, diurutkan sesuai Urutan tampil."
                        :tips="['Pilih koleksi yang isinya sudah cukup (minimal 4 produk) dan punya foto.']" /></span><span class="text-xs text-stone-500">Sebagai ubin "Belanja per koleksi"</span></span>
                    <input type="checkbox" name="show_on_home" value="1" class="checkbox size-5" @checked(old('show_on_home', $collection->show_on_home))>
                </label>
                <x-form.number name="sort_order" label="Urutan tampil" :value="$collection->sort_order ?? 0" min="0"
                    hint="Kecil tampil dulu di menu, filter, dan ubin beranda."
                    tip="Menentukan urutan koleksi di menu, filter katalog, dan ubin beranda."
                    :tips="['Taruh koleksi paling laris/musiman di urutan awal supaya langsung terlihat tanpa digeser.', 'Pakai kelipatan 10 supaya mudah menyisipkan koleksi baru.']" />
            </section>

            <section class="card space-y-5 p-6">
                <h2 class="font-bold text-stone-900">Tampilan ubin</h2>
                <x-form.select name="tone" label="Warna" :search="false" placeholder="Otomatis (bergantian)" :selected="$collection->tone"
                    :options="collect(ProductCollection::TONES)->map(fn ($t) => $t[2])->all()"
                    tip="Warna aksen koleksi (dipakai di tampilan lama). Pada ubin lengkung beranda saat ini latarnya selalu krem, jadi pilihan ini tidak berpengaruh." />
                <x-form.select name="icon" label="Ikon" :search="false" placeholder="Otomatis (ditebak dari nama)" :selected="$collection->icon"
                    :options="ProductCollection::ICONS"
                    tip="Ikon garis di tengah ubin, hanya tampil bila koleksi tidak punya foto dan belum berisi produk berfoto. Juga dipakai di tombol pintasan “Pilih koleksimu” di hero. Otomatis = ditebak dari nama." />
                <x-form.image-upload name="image" label="Foto" optional :current="$collection->imageUrl()" remove-name="remove_image" :max-kb="3072"
                    aspect="aspect-[3/4]" wrapper-class="max-w-48" hint="Opsional. Foto tegak 3:4 (min. 900×1200 px). Tanpa foto, ubin memakai foto produk pertama di koleksi."
                    tip="Foto ubin koleksi di seksi “Belanja per koleksi” beranda, tampil tegak 3:4 dalam bingkai lengkung dengan nama koleksi di bawahnya."
                    :tips="['Foto tegak 3:4, minimal 900×1200 px. Ubin berbentuk lengkung (puncak setengah lingkaran) dengan nama di bawahnya.', 'Taruh objek di tengah dan sisakan ruang di atas, karena sudut atas terpotong melengkung.', 'Foto bernuansa senada (latar krem/netral) membuat deretan ubin terlihat rapi.', 'Kompres di bawah 400 KB agar beranda cepat.']" />
            </section>

            <button class="btn btn-primary w-full py-3">{{ $editing ? 'Simpan perubahan' : 'Simpan koleksi' }}</button>
        </div>
    </form>
</x-layouts.admin>
