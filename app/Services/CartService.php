<?php

namespace App\Services;

use App\Models\Produk;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * Menambahkan produk ke keranjang belanja (session).
     */
    public function add(int $productId, int $qty = 1, ?string $notes = null): void
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $qty;
        } else {
            $cart[$productId] = [
                'quantity' => $qty,
                'notes' => $notes,
            ];
        }

        // Simpan catatan jika diberikan
        if ($notes !== null) {
            $cart[$productId]['notes'] = $notes;
        }

        session()->put('cart', $cart);
    }

    /**
     * Memperbarui kuantitas produk di keranjang.
     */
    public function update(int $productId, int $qty): void
    {
        $cart = session()->get('cart', []);

        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $qty;
            } else {
                $cart[$productId] = [
                    'quantity' => $qty,
                    'notes' => null,
                ];
            }
        }

        session()->put('cart', $cart);
    }

    /**
     * Menghapus produk dari keranjang.
     */
    public function remove(int $productId): void
    {
        $cart = session()->get('cart', []);
        unset($cart[$productId]);
        session()->put('cart', $cart);
    }

    /**
     * Mengosongkan seluruh keranjang.
     */
    public function clear(): void
    {
        session()->forget('cart');
    }

    /**
     * Mendapatkan semua item di keranjang yang valid (is_available = true).
     */
    public function items(): Collection
    {
        $cart = session()->get('cart', []);
        $ids = array_keys($cart);

        if (empty($ids)) {
            return collect();
        }

        return Produk::query()
            ->whereIn('id', $ids)
            ->where('is_available', true)
            ->get()
            ->map(fn ($p) => [
                'product_id' => $p->id,
                'quantity' => $cart[$p->id]['quantity'] ?? 0,
                'unit_price' => $p->harga,
                'subtotal' => bcmul((string) $p->harga, (string) ($cart[$p->id]['quantity'] ?? 0), 2),
                'notes' => $cart[$p->id]['notes'] ?? null,
                'produk' => $p,
            ]);
    }
}
