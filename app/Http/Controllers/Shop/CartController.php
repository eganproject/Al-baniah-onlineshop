<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View
    {
        return view('shop.cart', [
            'lines' => $this->cart->lines(),
            'adjustments' => $this->cart->adjustments(),
            'subtotal' => $this->cart->subtotal(),
            'weight' => $this->cart->totalWeight(),
        ]);
    }

    /**
     * Tambah ke keranjang. Dengan JS (Accept: application/json) dibalas JSON supaya
     * halaman tidak dimuat ulang; tanpa JS kembali ke halaman sebelumnya dengan toast.
     */
    public function store(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'variant_id' => ['nullable', 'integer'],
        ]);

        $variant = null;

        if ($product->has_variants) {
            $variant = $product->activeVariants()->find($data['variant_id'] ?? 0);

            if (! $variant) {
                return $this->failed($request, "Pilih {$this->variantLabel($product)} terlebih dahulu.", withInput: true);
            }
        }

        if (! ($variant ?? $product)->inStock()) {
            return $this->failed($request, 'Maaf, stok produk ini sedang habis.');
        }

        $requested = (int) ($data['quantity'] ?? 1);
        $key = CartService::key($product->id, $variant?->id);
        $before = $this->cart->find($key)?->quantity ?? 0;
        $inCart = $this->cart->add($product, $variant, $requested);
        $added = $inCart - $before;

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        $name = $variant ? "{$product->name} ({$variant->name})" : $product->name;
        $stock = ($variant ?? $product)->stock;

        [$type, $message] = match (true) {
            $added < 1 => ['warning', "Stok {$name} tinggal {$stock} pcs dan semuanya sudah ada di keranjang."],
            $added < $requested => ['warning', "Hanya {$added} pcs {$name} yang bisa ditambahkan (stok tinggal {$stock}). Di keranjang: {$inCart} pcs."],
            $before > 0 => ['success', "{$added} pcs {$name} ditambahkan. Di keranjang sekarang {$inCart} pcs."],
            default => ['success', "{$added} pcs {$name} ditambahkan ke keranjang."],
        };

        if ($request->expectsJson()) {
            return response()->json([
                'type' => $type,
                'message' => $message,
                'added' => $added,
                'line_quantity' => $inCart,
                'count' => $this->cart->count(),
            ]);
        }

        return back()->with($type, $message);
    }

    public function update(Request $request, string $line): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:999']]);

        $cartLine = $this->cart->find($line);
        abort_unless($cartLine, 404);

        $final = $this->cart->update($line, $data['quantity']);

        if ($final < $data['quantity']) {
            return back()->with('error', "Stok {$cartLine->name()} hanya tersisa {$cartLine->availableStock()}.");
        }

        return back();
    }

    public function destroy(string $line): RedirectResponse
    {
        $cartLine = $this->cart->find($line);
        $this->cart->remove($line);

        return back()->with('success', ($cartLine?->name() ?? 'Produk').' dihapus dari keranjang.');
    }

    private function failed(Request $request, string $message, bool $withInput = false): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['type' => 'error', 'message' => $message], 422);
        }

        $back = back()->with('error', $message);

        return $withInput ? $back->withInput() : $back;
    }

    private function variantLabel(Product $product): string
    {
        return mb_strtolower($product->variant_label ?: 'varian');
    }
}
