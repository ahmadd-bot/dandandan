<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Produk;
use Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request) {
    $cart = Cart::name($request->user()->id);

    $cart->applyTax([
        'id' => 1,
        'rate' => 10,
        'title' => 'Pajak PPN 10%'
    ]);
    return $cart->getDetails()->toJson();
}

public function store(Request $request) {
    $request->validate([
        'kode_produk' => ['required', 'exists:produks,kode_produk']
    ]);
    $produk = Produk::where('kode_produk', $request->kode_produk)->first();

    $cart = Cart::name($request->user()->id);

    $cart->addItem([
        'id' => $produk->id,
        'title' => $produk->nama_produk,
        'quantity' => 1,
        'price' => $produk->harga,
        'options'=>[
            'diskon'=>$produk->diskon,
            'harga_produk'=>$produk->harga_produk,
        ]

    ]);
    return response()->json(['message' => 'Berhasil ditambahkan.']);
}

public function update(Request $request, $hash) {
    $request->validate([
        'qty' => ['required', 'in:-1,1']
    ]);

    $cart = Cart::name($request->user()->id);
    $item = $cart->getItem($hash);

    if (!$item) {
        return abort(404);
    }

    $newQuantity = $item->getQuantity() + $request->qty;
    if ($newQuantity < 1) {
        $cart->removeItem($hash);
    } else {
        $cart->updateItem($item->getHash(), [
            'quantity' => $newQuantity
        ]);
    }

    return response()->json(['message' => 'Berhasil diupdate.']);
}

public function destroy(Request $request, $hash) {
    $cart = Cart::name($request->user()->id);
    $cart->removeItem($hash);
    return response()->json(['message' => 'Berhasil dihapus.']);
}

public function clear(Request $request) {
    $cart = Cart::name($request->user()->id);
    $cart->destroy();

    return back();
}

}
