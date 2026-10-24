@php
    use App\Enums\VoucherType;
    $editing = $voucher->exists;
    $type = old('type', $voucher->type?->value ?? 'percent');
@endphp
<x-layouts.admin :title="$editing ? 'Edit Voucher' : 'Buat Voucher'">
    <x-admin.page-header :title="$editing ? 'Edit voucher '.$voucher->code : 'Buat voucher'" :back="route('admin.vouchers.index')" />

    <form method="POST" action="{{ $editing ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}"
          class="grid gap-6 lg:grid-cols-3" x-data="{ type: @js($type) }">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-6 lg:col-span-2">
            <div class="card space-y-5 p-6">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <div class="label-row">
                            <label for="code" class="label">Kode voucher</label>
                            <x-tip title="Kode voucher" text="Kode yang diketik pembeli di halaman checkout untuk mendapat potongan. Harus unik."
                                :tips="['Pendek & mudah diingat, 6–10 huruf, mis. BERKAH10 atau RAMADAN25.', 'Masukkan angka diskonnya di kode supaya pembeli langsung paham.', 'Hindari huruf yang mirip angka (O/0, I/1) agar tidak salah ketik.']" />
                        </div>
                        <input id="code" name="code" value="{{ old('code', $voucher->code) }}" required maxlength="32" autocomplete="off"
                               placeholder="BERKAH10" class="input font-mono uppercase placeholder:normal-case @error('code') input-error @enderror">
                        <p class="mt-1.5 text-xs text-stone-400">Huruf, angka, - dan _. Otomatis huruf besar.</p>
                        <x-input-error for="code" />
                    </div>
                    <div>
                        <div class="label-row">
                            <label for="name" class="label">Nama voucher</label>
                            <x-tip title="Nama voucher" text="Nama untuk memudahkan admin mengenali voucher di daftar & laporan, mis. “Diskon pengguna baru”. Pembeli melihat kode dan besar potongannya." />
                        </div>
                        <input id="name" name="name" value="{{ old('name', $voucher->name) }}" required maxlength="100" placeholder="Diskon pengguna baru" class="input @error('name') input-error @enderror">
                        <x-input-error for="name" />
                    </div>
                </div>
                <div>
                    <div class="label-row">
                        <label for="description" class="label">Keterangan <span class="font-normal text-stone-400">(opsional, untuk catatan admin)</span></label>
                        <x-tip title="Keterangan" text="Catatan internal, tidak dilihat pembeli. Berguna untuk mengingat tujuan voucher, mis. “Kolaborasi dengan @selebgram, Mei 2027”." />
                    </div>
                    <input id="description" name="description" value="{{ old('description', $voucher->description) }}" maxlength="255" class="input">
                    <x-input-error for="description" />
                </div>
            </div>

            <div class="card space-y-5 p-6">
                <div>
                    <p class="label">Jenis voucher</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            [VoucherType::Percent, 'Potong sekian % dari subtotal barang'],
                            [VoucherType::Fixed, 'Potong nominal tetap dari subtotal'],
                            [VoucherType::FreeShipping, 'Tanggung ongkir, penuh atau sebagian'],
                        ] as [$case, $hint])
                            <label class="relative block cursor-pointer rounded-xl border-2 p-4 transition"
                                   :class="type === '{{ $case->value }}' ? 'border-brand-500 bg-brand-50/50' : 'border-stone-200 hover:border-stone-300'">
                                <input type="radio" name="type" value="{{ $case->value }}" x-model="type" class="sr-only" @checked($type === $case->value)>
                                <span class="block text-sm font-semibold text-stone-900">{{ $case->label() }}</span>
                                <span class="mt-0.5 block text-xs text-stone-500">{{ $hint }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error for="type" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="value" class="label">
                            <span x-show="type === 'percent'">Persentase diskon (%)</span>
                            <span x-show="type === 'fixed'" x-cloak>Nominal potongan (Rp)</span>
                            <span x-show="type === 'free_shipping'" x-cloak>Maksimal ongkir ditanggung (Rp)</span>
                        </label>
                        <div class="relative" x-data>
                            <span class="input-addon" x-show="type !== 'percent'" x-cloak>Rp</span>
                            <span class="input-addon right-3.5 left-auto" x-show="type === 'percent'">%</span>
                            <input id="value" name="value" x-money value="{{ \App\Support\FormField::thousands(old('value', $voucher->value)) }}"
                                   :class="type === 'percent' ? 'pr-10' : 'pl-10'" class="input tabular-nums @error('value') input-error @enderror">
                        </div>
                        <p x-show="type === 'free_shipping'" x-cloak class="mt-1.5 text-xs text-stone-400">Isi 0 untuk gratis ongkir penuh.</p>
                        <x-input-error for="value" />
                    </div>
                    <div x-show="type === 'percent'">
                        <x-form.money name="max_discount" label="Maksimal diskon" optional :value="$voucher->max_discount" placeholder="Tanpa batas"
                            tip="Batas atas potongan untuk voucher persen. Contoh: diskon 20% maks. Rp50.000 → belanja Rp500.000 tetap hanya dipotong Rp50.000."
                            :tips="['Sangat disarankan diisi agar diskon persen tidak membengkak di pesanan besar.']" />
                    </div>
                    <x-form.money name="min_spend" label="Minimal belanja" :value="$voucher->min_spend" placeholder="0"
                                  hint="Dihitung dari subtotal barang, sebelum ongkir."
                                  tip="Voucher baru bisa dipakai bila total harga barang di keranjang mencapai angka ini. Isi 0 bila tanpa syarat."
                                  :tips="['Pasang sedikit di atas rata-rata belanja agar pembeli terdorong menambah barang, mis. rata-rata Rp250.000 → minimal Rp300.000.']" />
                </div>
            </div>

            <div class="card space-y-5 p-6">
                <h2 class="font-bold text-stone-900">Batas pemakaian & periode</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.number name="quota" label="Kuota total" optional :value="$voucher->quota" min="1" placeholder="Tanpa batas"
                                   :hint="$editing ? 'Sudah dipakai '.$used.' kali. Pesanan batal mengembalikan kuota.' : null"
                                   tip="Jumlah total voucher bisa dipakai oleh semua pembeli. Setelah habis, voucher otomatis tidak berlaku. Kosongkan bila tanpa batas."
                                   :tips="['Kuota kecil (mis. 50) menciptakan kesan terbatas & mendorong pembeli segera checkout.']" />
                    <x-form.number name="per_user_limit" label="Batas per pelanggan" optional :value="$voucher->per_user_limit" min="1" placeholder="Tanpa batas"
                                   tip="Berapa kali satu akun pelanggan boleh memakai voucher ini. Kosongkan bila tanpa batas."
                                   :tips="['Isi 1 untuk voucher pengguna baru atau promo sekali pakai.']" />
                    <x-form.date name="starts_at" label="Mulai berlaku" optional time :value="$voucher->starts_at"
                                 tip="Voucher baru bisa dipakai mulai waktu ini. Kosongkan untuk langsung berlaku." />
                    <x-form.date name="ends_at" label="Berakhir" optional time :value="$voucher->ends_at"
                                 tip="Setelah waktu ini voucher otomatis tidak bisa dipakai. Kosongkan bila tanpa batas waktu."
                                 :tips="['Batas waktu yang jelas (mis. sampai akhir pekan) membuat promo lebih mendesak.']" />
                </div>
                <p class="text-xs text-stone-400">Waktu mengikuti zona {{ config('app.timezone') }}.</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card space-y-5 p-6">
                <label class="flex items-center justify-between gap-3">
                    <span><span class="block text-sm font-semibold text-stone-800">Aktif</span><span class="text-xs text-stone-500">Nonaktifkan untuk menghentikan sementara</span></span>
                    <input type="checkbox" name="is_active" value="1" class="checkbox size-5" @checked(old('is_active', $voucher->is_active))>
                </label>
                <label class="flex items-center justify-between gap-3">
                    <span><span class="flex items-center gap-1.5 text-sm font-semibold text-stone-800">Tampilkan di checkout <x-tip title="Tampilkan di checkout" text="Bila aktif, voucher muncul di daftar pilihan checkout sehingga semua pembeli bisa memakainya tanpa tahu kodenya."
                        :tips="['Matikan untuk voucher khusus (influencer, member, kompensasi) yang kodenya hanya dibagikan ke orang tertentu.']" /></span><span class="text-xs text-stone-500">Pelanggan bisa memilih tanpa mengetik kode</span></span>
                    <input type="checkbox" name="is_public" value="1" class="checkbox size-5" @checked(old('is_public', $voucher->is_public))>
                </label>
            </div>
            <div class="rounded-2xl bg-sand-100/70 p-5 text-xs leading-relaxed text-stone-600">
                <p class="font-semibold text-stone-800">Cara kerja</p>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    <li>Satu pesanan hanya bisa memakai satu voucher.</li>
                    <li>Potongan tidak melebihi subtotal barang (atau ongkir untuk gratis ongkir).</li>
                    <li>Kuota terpakai saat pesanan dibuat dan kembali bila pesanan dibatalkan/kedaluwarsa.</li>
                </ul>
            </div>
            <button class="btn btn-primary w-full py-3">{{ $editing ? 'Simpan perubahan' : 'Simpan voucher' }}</button>
        </div>
    </form>
</x-layouts.admin>
