<?php

namespace App\Http\Controllers;

use App\Models\DetilPenjualan;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use Jackiedo\Cart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $penjualans = Penjualan::join('users', 'users.id', 'penjualans.user_id')
            ->leftJoin('pelanggans', 'pelanggans.id', 'penjualans.pelanggan_id') // Changed to leftJoin
            ->select(
                'penjualans.*',
                'users.nama as nama_kasir',
                'users.role as role_kasir',
                'pelanggans.nama as nama_pelanggan'
            )
            ->orderBy('penjualans.id', 'desc')
            ->when($search, function ($q, $search) {
                return $q->where('nomor_transaksi', 'like', "%{$search}%");
            })
            ->paginate();

        if ($search) $penjualans->appends(['search' => $search]);

        return view('transaksi.index', [
            'penjualans' => $penjualans
        ]);
    }

    public function create(Request $request)
    {
        return view('transaksi.create', [
            'nama_kasir' => $request->user()->nama,
            'tanggal' => date('d F Y')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id' => ['nullable', 'exists:pelanggans,id'], // Changed to nullable
            'cash' => ['required', 'numeric', 'gte:total_bayar']
        ], [], [
            'pelanggan_id' => 'pelanggan'
        ]);

        $user = $request->user();
        $cart = Cart::name($user->id);
        $cartDetails = $cart->getDetails();
        $total = $cartDetails->get('total');
        $kembalian = $request->cash - $total;

        DB::beginTransaction();
        try {
            $today = date('Ymd');

            // Ambil transaksi terakhir hari ini dengan lock
            $lastTransaction = Penjualan::whereDate('tanggal', date('Y-m-d'))
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if ($lastTransaction) {
                $lastNo = (int) substr($lastTransaction->nomor_transaksi, -4);
                $newNo = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNo = '0001';
            }

            $nomorTransaksi = $today . $newNo;

            // Ambil pelanggan_id dari cart atau dari request
            $pelangganId = null;
            $extraInfo = $cart->getExtraInfo();
            if (isset($extraInfo['pelanggan']['id'])) {
                $pelangganId = $extraInfo['pelanggan']['id'];
            } elseif ($request->pelanggan_id) {
                $pelangganId = $request->pelanggan_id;
            }

            // Buat transaksi sekali saja
            $penjualan = Penjualan::create([
                'user_id'        => $user->id,
                'pelanggan_id'   => $pelangganId, // Bisa null untuk transaksi umum
                'nomor_transaksi'=> $nomorTransaksi,
                'tanggal'        => now(),
                'total'          => $total,
                'tunai'          => $request->cash,
                'kembalian'      => $kembalian,
                'pajak'          => $cartDetails->get('tax_amount'),
                'subtotal'       => $cartDetails->get('subtotal')
            ]);

            // Loop item hanya untuk detail + update stok
            foreach ($cartDetails->get('items') as $item) {
                $produk = Produk::find($item->id);

                if (!$produk || $produk->stok < $item->quantity) {
                    DB::rollBack();
                    $cart->destroy();
                    return redirect()
                        ->route('transaksi.create')
                        ->with('store', 'gagal');
                }

                DetilPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id'    => $item->id,
                    'harga_produk' => $item->price,
                    'jumlah'       => $item->quantity,
                    'subtotal'     => $item->subtotal
                ]);

                $produk->stok -= $item->quantity;
                $produk->save();
            }

            $cart->destroy();
            DB::commit();

            return redirect()->route('transaksi.show', ['transaksi' => $penjualan->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Request $request, Penjualan $transaksi)
    {
        $pelanggan = $transaksi->pelanggan_id ? Pelanggan::find($transaksi->pelanggan_id) : null;
        $user = User::find($transaksi->user_id);
        $detilPenjualan = DetilPenjualan::join('produks', 'produks.id', 'detil_penjualans.produk_id')
            ->select('detil_penjualans.*', 'nama_produk')
            ->where('penjualan_id', $transaksi->id)->get();

        return view('transaksi.invoice', [
            'penjualan' => $transaksi,
            'pelanggan' => $pelanggan,
            'user' => $user,
            'detilPenjualan' => $detilPenjualan
        ]);
    }

    public function destroy(Request $request, Penjualan $transaksi)
    {
        $transaksi->update([
            'status' => 'batal'
        ]);

        $detail = DetilPenjualan::where('penjualan_id', $transaksi->id)->get();

        foreach ($detail as $item) {
            $produk = Produk::find($item->produk_id);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->save();
            }
        }

        return back()->with('destroy', 'success');
    }

    public function produk(Request $request)
    {
        $search = $request->search;
        $produks = Produk::select('id', 'kode_produk', 'nama_produk')
            ->when($search, function ($q, $search) {
                return $q->where('nama_produk', 'like', "%{$search}%");
            })
            ->orderBy('nama_produk')
            ->take(15)
            ->get();

        return response()->json($produks);
    }

    public function pelanggan(Request $request)
    {
        $search = $request->search;
        $pelanggans = Pelanggan::select('id', 'nama')
            ->when($search, function ($q, $search) {
                return $q->where('nama', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->take(15)
            ->get();

        return response()->json($pelanggans);
    }

    public function addPelanggan(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:pelanggans']
        ]);

        $pelanggan = Pelanggan::find($request->id);
        $cart = Cart::name($request->user()->id);

        $cart->setExtraInfo([
            'pelanggan' => [
                'id' => $pelanggan->id,
                'nama' => $pelanggan->nama
            ]
        ]);

        return response()->json(['message' => 'Berhasil.']);
    }

    // Method untuk menghapus pelanggan dari cart (untuk transaksi umum)
    public function removePelanggan(Request $request)
    {
        $cart = Cart::name($request->user()->id);
        $cart->setExtraInfo([]);

        return response()->json(['message' => 'Pelanggan dihapus dari transaksi.']);
    }

    public function cetak(Penjualan $transaksi)
    {
        $pelanggan = $transaksi->pelanggan_id ? Pelanggan::find($transaksi->pelanggan_id) : null;
        $user = User::find($transaksi->user_id);
        $detilPenjualan = DetilPenjualan::join('produks', 'produks.id', 'detil_penjualans.produk_id')
            ->select('detil_penjualans.*', 'nama_produk')
            ->where('penjualan_id', $transaksi->id)->get();

        return view('transaksi.cetak', [
            'penjualan' => $transaksi,
            'pelanggan' => $pelanggan,
            'user' => $user,
            'detilPenjualan' => $detilPenjualan
        ]);
    }
}