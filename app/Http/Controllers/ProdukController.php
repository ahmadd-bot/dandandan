<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $produks = Produk::join('kategoris', 'kategoris.id', 'produks.kategori_id')
            ->orderBy('produks.id')
            ->select('produks.*', 'nama_kategori')
            ->when($search, function ($q, $search) {
                return $q->where('kode_produk', 'like', "%{$search}%")
                    ->orWhere('nama_produk', 'like', "%{$search}%");
            })
            ->paginate();

        if ($search) {
            $produks->appends(['search' => $search]);
        }

        // cari produk dengan stok < 20
        $stokMenipis = Produk::where('stok', '<=', 20)->get();

        return view('produk.index', compact('produks', 'stokMenipis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dataKategori = Kategori::orderBy('nama_kategori')->get();
        $kategoris = [
            ['', 'Pilih Kategori:']
        ];
        foreach ($dataKategori as $kategori) {
            $kategoris[] = [$kategori->id, $kategori->nama_kategori];
        }

        return view('produk.create', [
            'kategoris' => $kategoris
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => ['required', 'max:250', 'unique:produks'],
            'nama_produk' => ['required', 'max:150'],
            'harga_modal' => ['required', 'numeric'],
            'harga' => ['required', 'numeric'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'diskon' => ['required', 'between:0,100'],
        ]);

        // hitung harga setelah diskon
        $hargaSetelahDiskon = $request->harga - ($request->harga * $request->diskon / 100);

        Produk::create([
            'kode_produk' => $request->kode_produk,
            'nama_produk' => $request->nama_produk,
            'harga_modal' => $request->harga_modal,
            'harga_produk' => $request->harga,
            'diskon' => $request->diskon,
            'harga' => $hargaSetelahDiskon,
            'kategori_id' => $request->kategori_id,
            'stok' => 0, // Set stok awal 0, tidak bisa diinput dari form
        ]);

        return redirect()->route('produk.index')->with('store', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produk $produk)
    {
        $dataKategori = Kategori::orderBy('nama_kategori')->get();
        $kategoris = [
            ['', 'Pilih Kategori:']
        ];
        foreach ($dataKategori as $kategori) {
            $kategoris[] = [$kategori->id, $kategori->nama_kategori];
        }

        return view('produk.edit', [
            'produk' => $produk,
            'kategoris' => $kategoris,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'kode_produk' => ['required', 'max:250', 'unique:produks,kode_produk,' . $produk->id],
            'nama_produk' => ['required', 'max:150'],
            'harga_modal' => ['required', 'numeric'],
            'harga' => ['required', 'numeric'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'diskon' => ['required', 'between:0,100'],
        ]);

        // hitung harga setelah diskon
        $hargaSetelahDiskon = $request->harga - ($request->harga * $request->diskon / 100);

        $produk->update([
            'kode_produk' => $request->kode_produk,
            'nama_produk' => $request->nama_produk,
            'harga_modal' => $request->harga_modal,
            'harga_produk' => $request->harga,
            'diskon' => $request->diskon,
            'harga' => $hargaSetelahDiskon,
            'kategori_id' => $request->kategori_id,
            // stok tidak diupdate dari form, tetap menggunakan stok lama
        ]);

        return redirect()->route('produk.index')->with('update', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        $produk->delete();

        return back()->with('destroy', 'success');
    }
}