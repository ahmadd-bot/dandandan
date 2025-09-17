<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Pelanggan;
use Jackiedo\Cart\Facades\Cart;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::name($request->user()->id);

        $cart->applyTax([
            'id' => 1,
            'rate' => 10,
            'title' => 'Pajak PPN 10%'
        ]);

        return $cart->getDetails()->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => ['required', 'exists:produks,kode_produk'],
            'quantity' => ['required', 'integer', 'min:1']
        ]);

        $produk = Produk::where('kode_produk', $request->kode_produk)->first();

        // Validasi stok
        if ($produk->stok < $request->quantity) {
            return response()->json([
                'message' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $produk->stok
            ], 400);
        }

        $cart = Cart::name($request->user()->id);

        $cart->addItem([
            'id' => $produk->id,
            'title' => $produk->nama_produk,
            'quantity' => $request->quantity,
            'price' => $produk->harga,
            'options'=>[
                'kategori_id' => $produk->kategori_id,
                'diskon'=>$produk->diskon,
                'harga_produk'=>$produk->harga_produk,
            ]
        ]);

        // Kurangi stok
        $produk->decrement('stok', $request->quantity);

        return response()->json(['message' => 'Berhasil ditambahkan.']);
    }

    public function update(Request $request, $hash)
    {
        $request->validate([
            'qty' => ['required', 'in:-1,1']
        ]);

        $cart = Cart::name($request->user()->id);
        $item = $cart->getItem($hash);

        if (!$item) {
            return abort(404);
        }

        $produk = Produk::find($item->getId());

        if ($request->qty == 1) {
            // tambah qty → stok harus berkurang
            if ($produk->stok < 1) {
                return response()->json(['message' => 'Stok tidak cukup!'], 400);
            }
            $produk->decrement('stok', 1);
        } else {
            // kurangi qty → stok harus dikembalikan
            $produk->increment('stok', 1);
        }

        $cart->updateItem($item->getHash(), [
            'quantity' => $item->getQuantity() + $request->qty
        ]);

        return response()->json(['message' => 'Berhasil diupdate.']);
    }

    public function destroy(Request $request, $hash)
    {
        $cart = Cart::name($request->user()->id);
        $item = $cart->getItem($hash);

        if ($item) {
            // kembalikan stok sesuai jumlah item
            $produk = Produk::find($item->getId());
            $produk->increment('stok', $item->getQuantity());
        }

        $cart->removeItem($hash);

        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function clear(Request $request)
    {
        $cart = Cart::name($request->user()->id);

        // kembalikan semua stok produk
        foreach ($cart->getItems() as $item) {
            $produk = Produk::find($item->getId());
            if ($produk) {
                $produk->increment('stok', $item->getQuantity());
            }
        }

        $cart->destroy();

        return back();
    }
}
