<x-layouts.admin title="Pesanan">
    <x-admin.page-header title="Pesanan" description="Pantau dan proses pesanan pelanggan." />

    {{-- Tab status --}}
    <div class="-mx-4 mb-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <div class="inline-flex gap-1 rounded-2xl bg-white p-1 ring-1 ring-stone-200/70">
            <a href="{{ route('admin.orders.index', request()->except('status', 'page')) }}"
               @class(['whitespace-nowrap rounded-xl px-3.5 py-2 text-sm font-semibold transition', 'bg-stone-900 text-white' => ! $status, 'text-stone-600 hover:bg-stone-50' => $status])>
                Semua <span class="ml-1 opacity-60">{{ $counts->sum() }}</span>
            </a>
            @foreach (\App\Enums\OrderStatus::cases() as $case)
                <a href="{{ route('admin.orders.index', ['status' => $case->value] + request()->except('status', 'page')) }}"
                   @class(['whitespace-nowrap rounded-xl px-3.5 py-2 text-sm font-semibold transition', 'bg-stone-900 text-white' => $status === $case, 'text-stone-600 hover:bg-stone-50' => $status !== $case])>
                    {{ $case->label() }} <span class="ml-1 opacity-60">{{ $counts[$case->value] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="card overflow-hidden">
        <form method="GET" class="flex flex-col gap-3 border-b border-stone-100 p-4 lg:flex-row lg:items-end">
            <x-admin.keep-query />
            @if ($status)<input type="hidden" name="status" value="{{ $status->value }}">@endif
            <div class="relative flex-1 lg:max-w-sm">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-stone-400" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Kode, nama, HP, email, atau resi..." class="input pl-10" aria-label="Cari pesanan">
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:flex">
                <x-form.date-range from="from" to="to" :from-value="request('from')" :to-value="request('to')" :max="today()"
                                   placeholder="Semua tanggal" wrapper-class="col-span-2 lg:w-64" aria-label="Rentang tanggal" />
                <select name="courier" class="input lg:w-44" x-data x-searchable aria-label="Kurir">
                    <option value="">Semua kurir</option>
                    @foreach ($couriers as $courier)
                        <option value="{{ $courier }}" @selected(request('courier') === $courier)>{{ strtoupper($courier) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button class="btn btn-dark flex-1"><x-icon name="filter" class="size-4" /> Saring</button>
                    @if (request()->hasAny(['q', 'from', 'to', 'courier']))
                        <a href="{{ route('admin.orders.index', array_filter(['status' => $status?->value])) }}" class="btn btn-ghost" title="Hapus filter"><x-icon name="x" class="size-4" /></a>
                    @endif
                </div>
            </div>
        </form>

        @if ($orders->isEmpty())
            <x-empty-state icon="receipt" title="Tidak ada pesanan" description="Belum ada pesanan yang cocok dengan filter ini." />
        @else
            @php
                // Tombol status massal: muncul bila ada pesanan terpilih dengan status asalnya.
                $bulkActions = [
                    'process' => ['status' => \App\Enums\OrderStatus::Paid, 'icon' => 'cube', 'label' => 'Tandai diproses', 'to' => 'diproses'],
                    'ship' => ['status' => \App\Enums\OrderStatus::Processing, 'icon' => 'truck', 'label' => 'Tandai dikirim', 'to' => 'dikirim'],
                    'deliver' => ['status' => \App\Enums\OrderStatus::Shipped, 'icon' => 'map-pin', 'label' => 'Tandai sampai tujuan', 'to' => 'sampai tujuan'],
                    'complete' => ['status' => \App\Enums\OrderStatus::Delivered, 'icon' => 'check-circle', 'label' => 'Tandai selesai', 'to' => 'selesai'],
                ];
                $toShip = $orders->getCollection()->filter(fn ($o) => $o->status === \App\Enums\OrderStatus::Processing);
            @endphp
            <form id="bulk-orders" method="POST" action="{{ route('admin.orders.bulk') }}"
                  x-data="{
                      selected: @js(array_map('strval', (array) old('orders', []))),
                      statuses: @js($orders->getCollection()->mapWithKeys(fn ($o) => [$o->id => $o->status->value])),
                      shipOpen: @js(old('action') === 'ship'),
                      count(status) { return this.selected.filter(id => this.statuses[id] === status).length },
                      nextField(el) {
                          const fields = [...document.querySelectorAll('[data-ship-resi]')].filter(i => ! i.disabled);
                          const next = fields[fields.indexOf(el) + 1];
                          next ? next.focus() : document.getElementById('bulk-ship-submit')?.focus();
                      },
                  }">
                @csrf
                <div x-show="selected.length" x-cloak class="flex flex-wrap items-center gap-2 border-b border-stone-100 bg-brand-50/60 px-4 py-3 text-sm">
                    <span class="mr-1 font-semibold text-stone-800"><span x-text="selected.length"></span> dipilih</span>
                    @can('orders.manage')
                        @foreach ($bulkActions as $action => $item)
                            @php $from = $item['status']->value; @endphp
                            <button @if ($action === 'ship') type="button" @click="shipOpen = true" @else name="action" value="{{ $action }}"
                                        data-confirm-title="{{ $item['label'] }}?" data-confirm-ok="Ya, {{ mb_strtolower($item['label']) }}"
                                        x-bind:data-confirm="count('{{ $from }}') + ' pesanan berstatus {{ $item['status']->label() }} akan ditandai {{ $item['to'] }}.' + (count('{{ $from }}') < selected.length ? ' Pesanan dengan status lain dilewati.' : '')"
                                    @endif
                                    x-show="count('{{ $from }}')" class="btn btn-primary btn-sm">
                                <x-icon :name="$item['icon']" class="size-4" /> {{ $item['label'] }}
                                <span x-show="count('{{ $from }}') < selected.length" class="rounded-full bg-white/20 px-1.5 text-xs" x-text="count('{{ $from }}')"></span>
                            </button>
                        @endforeach
                    @endcan
                    <button name="action" value="packing-slips" formtarget="_blank" class="btn btn-secondary btn-sm"><x-icon name="printer" class="size-4" /> Cetak packing slip</button>
                </div>

                @can('orders.manage')
                    @if ($toShip->isNotEmpty())
                        {{-- Kirim massal: isi nomor resi per pesanan --}}
                        <template x-teleport="body">
                            <div x-show="shipOpen" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-stone-900/40 backdrop-blur-sm sm:items-center sm:p-4" @keydown.escape.window="shipOpen = false">
                                <div @click.outside="shipOpen = false" x-show="shipOpen" x-transition
                                     class="flex max-h-[90dvh] w-full max-w-lg flex-col rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl"
                                     role="dialog" aria-modal="true" aria-labelledby="bulk-ship-title">
                                    <div class="flex items-start justify-between gap-3 border-b border-stone-100 p-5">
                                        <div>
                                            <h2 id="bulk-ship-title" class="text-lg font-bold text-stone-900">Tandai dikirim</h2>
                                            <p class="text-sm text-stone-500">Isi nomor resi <span x-text="count('processing')"></span> pesanan. Yang dikosongkan tetap berstatus Diproses.</p>
                                        </div>
                                        <button type="button" @click="shipOpen = false" class="btn btn-ghost -mr-2 -mt-1 p-2" aria-label="Tutup"><x-icon name="x" class="size-5" /></button>
                                    </div>
                                    <div class="flex-1 divide-y divide-stone-100 overflow-y-auto px-5">
                                        @foreach ($toShip as $order)
                                            <div x-show="selected.includes('{{ $order->id }}')" class="py-3">
                                                <label for="resi-{{ $order->id }}" class="flex flex-wrap items-baseline justify-between gap-x-3 text-sm">
                                                    <span class="font-semibold text-stone-900">{{ $order->code }}</span>
                                                    <span class="text-xs text-stone-500">{{ $order->recipient_name }} · {{ $order->courierLabel() ?? 'Kurir belum dipilih' }}</span>
                                                </label>
                                                <input id="resi-{{ $order->id }}" form="bulk-orders" name="tracking[{{ $order->id }}]"
                                                       value="{{ old('tracking.'.$order->id, $order->tracking_number) }}" maxlength="64"
                                                       x-bind:disabled="! shipOpen || ! selected.includes('{{ $order->id }}')"
                                                       @keydown.enter.prevent="nextField($el)" data-ship-resi
                                                       placeholder="Nomor resi" autocomplete="off" class="input mt-1.5 font-mono @error('tracking.'.$order->id) input-error @enderror">
                                                <x-input-error :for="'tracking.'.$order->id" />
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="space-y-3 border-t border-stone-100 p-5">
                                        <p class="flex gap-2 text-xs text-stone-500"><x-icon name="info" class="size-4 shrink-0" /> Bisa pakai pemindai barcode: setelah satu resi terbaca, kursor pindah ke pesanan berikutnya.</p>
                                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                            <button type="button" @click="shipOpen = false" class="btn btn-ghost">Batal</button>
                                            <button id="bulk-ship-submit" form="bulk-orders" name="action" value="ship" class="btn btn-primary">
                                                <x-icon name="truck" class="size-4" /> Tandai dikirim
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    @endif
                @endcan
            <div class="relative overflow-x-auto">
                <table class="table-base">
                    <thead class="bg-stone-50/70"><tr>
                        <th class="w-10"><input type="checkbox" class="checkbox" aria-label="Pilih semua"
                            :checked="selected.length && selected.length === $root.querySelectorAll('input[name=\'orders[]\']').length"
                            @change="selected = $event.target.checked ? [...$root.querySelectorAll('input[name=\'orders[]\']')].map(i => i.value) : []"></th>
                        <th><x-admin.sort-link column="tanggal" desc>Pesanan</x-admin.sort-link></th><th class="hidden md:table-cell">Pelanggan</th><th class="hidden lg:table-cell">Item</th><th><x-admin.sort-link column="total" desc>Total</x-admin.sort-link></th><th class="hidden lg:table-cell">Pengiriman</th><th class="hidden sm:table-cell">Status</th><th class="hidden sm:table-cell"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-stone-50/50">
                                <td><input type="checkbox" name="orders[]" value="{{ $order->id }}" x-model="selected" class="checkbox" aria-label="Pilih {{ $order->code }}"></td>
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-stone-900 hover:text-brand-700">{{ $order->code }}</a>
                                    <p class="text-xs text-stone-500">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    <p class="mt-1 text-xs text-stone-600 md:hidden">{{ $order->user->name }}</p>
                                    <div class="mt-1 sm:hidden"><x-order-status :status="$order->status" /></div>
                                </td>
                                <td class="hidden md:table-cell"><p class="font-medium text-stone-800">{{ $order->user->name }}</p><p class="text-xs text-stone-500">{{ $order->user->email }}</p></td>
                                <td class="hidden lg:table-cell">{{ $order->items_count }}</td>
                                <td class="whitespace-nowrap font-semibold">@rupiah($order->total)</td>
                                <td class="hidden whitespace-nowrap text-xs lg:table-cell">{{ $order->courierLabel() ?? '—' }}@if ($order->tracking_number)<p class="font-mono text-stone-500">{{ $order->tracking_number }}</p>@endif</td>
                                <td class="hidden sm:table-cell"><x-order-status :status="$order->status" /></td>
                                <td class="hidden text-right sm:table-cell"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-sm">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </form>
            <x-admin.pagination :paginator="$orders" label="pesanan" />
        @endif
    </div>
</x-layouts.admin>
