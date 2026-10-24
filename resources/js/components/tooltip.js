/**
 * Tooltip info kecil untuk form admin (<x-tip>).
 *
 * - Mouse: muncul saat kursor diam sebentar di ikon, hilang saat kursor pergi.
 *   Klik ikon = "sematkan" supaya tetap terbuka sambil membaca.
 * - Sentuh (HP/tablet): ketuk untuk buka/tutup, ketuk di luar untuk menutup.
 * - Keyboard: muncul saat ikon difokus (Tab), Esc menutup.
 *
 * Panel dipindah ke <body> (x-teleport) supaya tidak terpotong kartu/tabel
 * yang overflow-hidden, lalu diposisikan di atas ikon (atau di bawah bila
 * ruang di atas tidak cukup) dan dijaga tetap di dalam layar.
 */
let uid = 0;
const GAP = 10;
const EDGE = 12;

export default () => ({
    open: false,
    pinned: false,
    placement: 'top',
    pos: { top: 0, left: 0, arrow: 0 },
    timer: null,
    uid: 0,

    init() {
        this.uid = ++uid;
        this.onDocPointer = (e) => {
            if (!this.open) return;
            const panel = this.$refs.panel;
            if (this.$root.contains(e.target) || panel?.contains(e.target)) return;
            this.hide();
        };
        this.onReflow = () => this.open && this.place();
        this.onOther = (e) => e.detail !== this.uid && this.open && this.hide();

        document.addEventListener('pointerdown', this.onDocPointer, true);
        window.addEventListener('tip:open', this.onOther);
    },

    destroy() {
        document.removeEventListener('pointerdown', this.onDocPointer, true);
        window.removeEventListener('tip:open', this.onOther);
        this.unwatch();
    },

    enter(e) {
        if (e.pointerType !== 'mouse') return;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.show(), 120);
    },

    leave(e) {
        if (e.pointerType !== 'mouse' || this.pinned) return;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.hide(), 140);
    },

    toggle() {
        clearTimeout(this.timer);
        // Sudah terbuka karena hover: klik pertama menyematkan, bukan menutup.
        if (this.open && !this.pinned) {
            this.pinned = true;
            return;
        }
        this.open ? this.hide() : this.show(true);
    },

    focus(e) {
        if (e.target.matches(':focus-visible')) this.show();
    },

    blur() {
        if (!this.pinned) this.hide();
    },

    show(pin = false) {
        this.pinned = pin;
        if (this.open) return;
        this.open = true;
        window.dispatchEvent(new CustomEvent('tip:open', { detail: this.uid }));
        this.$nextTick(() => {
            this.place();
            requestAnimationFrame(() => this.place());
        });
        window.addEventListener('scroll', this.onReflow, true);
        window.addEventListener('resize', this.onReflow);
    },

    hide() {
        clearTimeout(this.timer);
        this.open = false;
        this.pinned = false;
        this.unwatch();
    },

    unwatch() {
        window.removeEventListener('scroll', this.onReflow, true);
        window.removeEventListener('resize', this.onReflow);
    },

    escape() {
        if (!this.open) return;
        this.hide();
        this.$refs.trigger.focus({ preventScroll: true });
    },

    place() {
        const panel = this.$refs.panel;
        const trigger = this.$refs.trigger;
        if (!panel || !trigger) return;

        const t = trigger.getBoundingClientRect();
        // Ikon sudah keluar layar (mis. di-scroll jauh): tutup saja.
        if (t.bottom < 0 || t.top > window.innerHeight) return this.hide();

        const w = panel.offsetWidth;
        const h = panel.offsetHeight;
        const vw = document.documentElement.clientWidth;
        const center = t.left + t.width / 2;

        this.placement = t.top - h - GAP < EDGE + 56 && t.bottom + h + GAP < window.innerHeight - EDGE ? 'bottom' : 'top';
        const top = this.placement === 'top' ? t.top - h - GAP : t.bottom + GAP;
        const left = Math.min(Math.max(center - w / 2, EDGE), vw - w - EDGE);

        this.pos = { top, left, arrow: Math.min(Math.max(center - left, 14), w - 14) };
    },
});
