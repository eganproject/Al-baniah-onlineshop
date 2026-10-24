/**
 * Halaman keranjang tanpa memuat ulang halaman.
 *
 * Berlaku untuk <form data-cart-line> di dalam #cart-content (ubah jumlah & hapus).
 * - Tombol −/+ langsung mengubah angka di layar; ketukan beruntun digabung lalu dikirim
 *   sekali (jeda singkat), jadi menekan + lima kali cepat tetap jadi satu permintaan.
 * - Server membalas JSON berisi isi keranjang yang sudah dirender ulang (subtotal, ringkasan,
 *   pesan stok), lalu bagian itu diganti dan badge keranjang ikut berubah (`cart-updated`).
 * - Tanpa JS, atau bila sesi kedaluwarsa / koneksi bermasalah, form dikirim biasa.
 */
const DEBOUNCE_MS = 400;

const pending = new WeakMap(); // form → { quantity, timer }

// Permintaan dikirim berurutan: sesi Laravel bisa saling timpa bila dua permintaan berjalan bersamaan.
let queue = Promise.resolve();
const enqueue = (task) => (queue = queue.then(task, task));

function root() {
    return document.getElementById('cart-content');
}

function quantityOf(form) {
    return Number(form.querySelector('[data-cart-qty]')?.textContent.trim() || 0);
}

function render(form, quantity) {
    const max = Number(form.dataset.max || Infinity);
    const label = form.querySelector('[data-cart-qty]');
    if (label) label.textContent = quantity;
    const plus = form.querySelector('[data-step="1"]');
    if (plus) plus.disabled = quantity >= max;
}

/** Fokus keyboard kembali ke tombol yang sama setelah isi keranjang diganti. */
function restoreFocus(focusKey) {
    const target = focusKey ? root()?.querySelector(`[data-cart-focus="${CSS.escape(focusKey)}"]`) : null;
    (target && !target.disabled ? target : root()?.querySelector('h1'))?.focus({ preventScroll: true });
}

async function send(form, body, focusKey) {
    const item = form.closest('[data-cart-item]');
    item?.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(form.action, {
            method: 'POST', // _method (PATCH/DELETE) ikut di body, seperti form biasa
            body,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (response.status === 419 || response.redirected || !(response.headers.get('content-type') || '').includes('json')) {
            fallback(form, body);
            return;
        }

        const json = await response.json();

        if (!response.ok) {
            window.toast(json.message || 'Keranjang belum bisa diperbarui. Coba lagi.', 'error');
            item?.removeAttribute('aria-busy');
            return;
        }

        const container = root();
        if (container && typeof json.html === 'string') container.innerHTML = json.html;
        if (json.message) window.toast(json.message, json.type || 'success');
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: json.count } }));
        restoreFocus(focusKey);
    } catch (error) {
        window.toast('Koneksi terputus. Keranjang belum diperbarui, coba lagi.', 'error');
        item?.removeAttribute('aria-busy');
        const original = form.dataset.quantity;
        if (original !== undefined) render(form, Number(original));
    }
}

/** Kirim form biasa (memuat ulang halaman) dengan nilai yang sama. */
function fallback(form, body) {
    const quantity = body.get('quantity');
    if (quantity !== null) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'quantity';
        input.value = quantity;
        form.querySelectorAll('button[name="quantity"]').forEach((button) => button.removeAttribute('name'));
        form.append(input);
    }
    form.submit();
}

function onSubmit(event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('[data-cart-line]') || event.defaultPrevented) return;
    if (!root()?.contains(form)) return;

    event.preventDefault();

    const submitter = event.submitter;
    const step = Number(submitter?.dataset.step || 0);

    // Hapus: kirim langsung.
    if (!step) {
        const body = new FormData(form);
        enqueue(() => send(form, body, null));
        return;
    }

    // Ubah jumlah: tampilkan dulu, kirim setelah ketukan berhenti.
    const max = Number(form.dataset.max || Infinity);
    const state = pending.get(form) || { quantity: quantityOf(form), timer: null };
    state.quantity = Math.min(max, Math.max(0, state.quantity + step));
    render(form, state.quantity);
    clearTimeout(state.timer);

    const focusKey = submitter.dataset.cartFocus;
    const flush = () => {
        pending.delete(form);
        if (state.quantity === Number(form.dataset.quantity)) return; // kembali ke angka semula
        const body = new FormData(form);
        body.set('quantity', String(state.quantity));
        enqueue(() => send(form, body, focusKey));
    };

    // Jumlah 0 = hapus baris: tidak perlu menunggu.
    state.timer = state.quantity === 0 ? null : setTimeout(flush, DEBOUNCE_MS);
    pending.set(form, state);
    if (state.quantity === 0) flush();
}

export default function cartPage() {
    document.addEventListener('submit', onSubmit);
}
