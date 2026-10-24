{{--
    Tombol WhatsApp melayang di halaman pelanggan. Nomor dari Admin › Pengaturan Toko
    (config shop.whatsapp, format 62xxx). Tidak tampil bila nomor kosong.
    Di HP/tablet (< lg) diangkat di atas navigasi bawah / bar beli.
--}}
@props(['text' => null])
@php
    $number = preg_replace('/\D+/', '', (string) config('shop.whatsapp'));
    $text ??= 'Halo '.config('shop.name').', saya mau tanya.';
@endphp
@if ($number !== '')
    <a href="https://wa.me/{{ $number }}?text={{ rawurlencode($text) }}" target="_blank" rel="noopener"
       aria-label="Chat admin lewat WhatsApp" data-whatsapp-button
       class="wa-float group fixed right-4 z-30 flex items-center rounded-full bg-[#25D366] text-white shadow-lg shadow-ink/15 ring-1 ring-black/5 transition hover:bg-[#1EBE5A] hover:shadow-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#25D366] active:scale-95 sm:right-6">
        <span class="grid size-13 shrink-0 place-items-center lg:size-14">
            <svg viewBox="0 0 24 24" class="size-7" fill="currentColor" aria-hidden="true">
                <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35Zm-5.42 7.4h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.9-9.88a9.83 9.83 0 0 1 9.88 9.89c0 5.45-4.44 9.88-9.89 9.88Zm8.41-18.3A11.81 11.81 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89 0-3.18-1.24-6.16-3.48-8.41Z" />
            </svg>
        </span>
        {{-- Label hanya di desktop: muncul saat kursor di atas tombol. --}}
        <span class="max-w-0 overflow-hidden text-sm font-semibold whitespace-nowrap opacity-0 transition-all duration-300 max-lg:hidden lg:group-hover:max-w-40 lg:group-hover:pr-5 lg:group-hover:opacity-100 lg:group-focus-visible:max-w-40 lg:group-focus-visible:pr-5 lg:group-focus-visible:opacity-100">
            Chat admin
        </span>
    </a>
@endif
