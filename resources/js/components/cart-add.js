/**
 * Tambah ke keranjang tanpa memuat ulang halaman.
 *
 * Berlaku untuk setiap <form data-cart-add> (halaman produk & tombol "+" di kartu).
 * Tombol "Beli sekarang" (name="buy_now") tetap dikirim biasa supaya langsung ke checkout.
 * Tanpa JS, atau bila sesi kedaluwarsa, form dikirim biasa seperti sebelumnya.
 * Setelah berhasil: toast dari server, lalu event `cart-updated` { count } untuk badge keranjang.
 */
function buttonsOf(form) {
    const outside = form.id ? [...document.querySelectorAll(`button[form="${CSS.escape(form.id)}"]`)] : [];
    return [...form.querySelectorAll('button:not([type="button"])'), ...outside];
}

async function onSubmit(event) {
    const form = event.target;

    // Validasi Alpine (mis. varian belum dipilih) sudah membatalkan submit.
    if (!(form instanceof HTMLFormElement) || !form.matches('[data-cart-add]') || event.defaultPrevented) return;
    if (event.submitter?.name === 'buy_now') return;

    event.preventDefault();

    // Cegah ketukan ganda menambah jumlah dua kali.
    if (form.dataset.busy) return;
    form.dataset.busy = '1';
    const buttons = buttonsOf(form);
    buttons.forEach((button) => {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    });

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        // Sesi/CSRF kedaluwarsa atau diarahkan ke halaman lain: pakai alur biasa.
        if (response.status === 419 || response.redirected || !(response.headers.get('content-type') || '').includes('json')) {
            form.submit();
            return;
        }

        const json = await response.json();

        if (!response.ok) {
            window.toast(json.message || 'Produk belum bisa ditambahkan. Coba lagi.', 'error');
            return;
        }

        window.toast(json.message, json.type || 'success');
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: json }));
    } catch (error) {
        window.toast('Koneksi terputus. Produk belum masuk keranjang, coba lagi.', 'error');
    } finally {
        delete form.dataset.busy;
        buttons.forEach((button) => {
            button.disabled = false;
            button.removeAttribute('aria-busy');
        });
    }
}

export default function cartAdd() {
    document.addEventListener('submit', onSubmit);
}
