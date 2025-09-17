<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\DetilPenjualan;
use DB;
   
class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.form');
    }

    public function harian(Request $request)
    {
        $penjualan = Penjualan::join('users', 'users.id', '=', 'penjualans.user_id')
            ->join('pelanggans', 'pelanggans.id', '=', 'penjualans.pelanggan_id')
            ->whereDate('tanggal', $request->tanggal)
            ->select(
                'penjualans.*',
                'pelanggans.nama as nama_pelanggan',
                'users.nama as nama_kasir'
            )
            ->orderBy('id')
            ->get();

        // Ambil detail produk untuk setiap penjualan
        foreach ($penjualan as $item) {
            $item->detail_produk = DetilPenjualan::join('produks', 'produks.id', '=', 'detil_penjualans.produk_id')
                ->where('detil_penjualans.penjualan_id', $item->id)
                ->select(
                    'produks.nama_produk',
                    'detil_penjualans.jumlah',
                    'detil_penjualans.harga_produk',
                    'detil_penjualans.subtotal'
                )
                ->get();
        }

        return view('laporan.harian', [
            'penjualan' => $penjualan
        ]);
    }

    public function bulanan(Request $request)
    {
        // Ambil data penjualan per hari
        $penjualan = Penjualan::select(
                'penjualans.*',
                DB::raw('COUNT(penjualans.id) as jumlah_transaksi'),
                DB::raw('SUM(penjualans.total) as jumlah_total'),
                DB::raw("DATE_FORMAT(tanggal, '%d/%m/%Y') tgl")
            )
            ->whereMonth('tanggal', $request->bulan)
            ->whereYear('tanggal', $request->tahun)
            ->groupBy(DB::raw("DATE_FORMAT(tanggal, '%d/%m/%Y')"), DB::raw("DATE(tanggal)"))
            ->orderBy('tanggal')
            ->get();

        // Ambil detail produk untuk bulan dan tahun yang dipilih
        $detailProdukBulanan = DetilPenjualan::join('produks', 'produks.id', '=', 'detil_penjualans.produk_id')
            ->join('penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->whereMonth('penjualans.tanggal', $request->bulan)
            ->whereYear('penjualans.tanggal', $request->tahun)
            ->select(
                'produks.nama_produk',
                DB::raw('SUM(detil_penjualans.jumlah) as total_jumlah'),
                DB::raw('AVG(detil_penjualans.harga_produk) as harga_rata'),
                DB::raw('SUM(detil_penjualans.subtotal) as total_subtotal'),
                DB::raw("DATE_FORMAT(penjualans.tanggal, '%d/%m/%Y') as tanggal")
            )
            ->groupBy('produks.id', 'produks.nama_produk', DB::raw("DATE_FORMAT(penjualans.tanggal, '%d/%m/%Y')"))
            ->orderBy('penjualans.tanggal')
            ->get()
            ->groupBy('tanggal');

        $nama_bulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei',
            'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $bulan = isset($nama_bulan[$request->bulan - 1]) ? $nama_bulan[$request->bulan - 1] : null;

        return view('laporan.bulanan', [
            'penjualan' => $penjualan,
            'detailProdukBulanan' => $detailProdukBulanan,
            'bulan' => $bulan
        ]);
    }

    public function keuntunganHarian(Request $request)
    {
        // Query untuk mendapatkan detail keuntungan per transaksi
        $keuntungan = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->join('pelanggans', 'penjualans.pelanggan_id', '=', 'pelanggans.id')
            ->join('users', 'penjualans.user_id', '=', 'users.id')
            ->whereDate('penjualans.tanggal', $request->tanggal)
            ->where('penjualans.status', '!=', 'batal')
            ->select(
                'penjualans.nomor_transaksi',
                'pelanggans.nama as nama_pelanggan',
                'users.nama as nama_kasir',
                'penjualans.tanggal',
                'produks.nama_produk',
                'detil_penjualans.jumlah',
                'produks.harga_modal',
                'detil_penjualans.harga_produk as harga_jual',
                DB::raw('(detil_penjualans.harga_produk - produks.harga_modal) as keuntungan_per_unit'),
                DB::raw('((detil_penjualans.harga_produk - produks.harga_modal) * detil_penjualans.jumlah) as total_keuntungan')
            )
            ->orderBy('penjualans.id')
            ->get();

        // Hitung total keuntungan
        $totalKeuntungan = $keuntungan->sum('total_keuntungan');

        return view('laporan.keuntungan_harian', [
            'keuntungan' => $keuntungan,
            'totalKeuntungan' => $totalKeuntungan,
            'tanggal' => $request->tanggal
        ]);
    }

    public function keuntunganBulanan(Request $request)
    {
        // Query untuk mendapatkan keuntungan per hari dalam satu bulan
        $keuntungan = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->whereMonth('penjualans.tanggal', $request->bulan)
            ->whereYear('penjualans.tanggal', $request->tahun)
            ->where('penjualans.status', '!=', 'batal')
            ->select(
                DB::raw("DATE_FORMAT(penjualans.tanggal, '%d/%m/%Y') as tanggal"),
                DB::raw('COUNT(DISTINCT penjualans.id) as jumlah_transaksi'),
                DB::raw('SUM(detil_penjualans.jumlah * produks.harga_modal) as total_modal'),
                DB::raw('SUM(detil_penjualans.subtotal) as total_penjualan'),
                DB::raw('SUM(detil_penjualans.subtotal - (detil_penjualans.jumlah * produks.harga_modal)) as total_keuntungan')
            )
            ->groupBy(DB::raw("DATE_FORMAT(penjualans.tanggal, '%d/%m/%Y')"))
            ->orderBy('penjualans.tanggal')
            ->get();

        $nama_bulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei',
            'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $bulan = isset($nama_bulan[$request->bulan - 1]) ? $nama_bulan[$request->bulan - 1] : null;

        return view('laporan.keuntungan_bulanan', [
            'keuntungan' => $keuntungan,
            'bulan' => $bulan,
            'tahun' => $request->tahun
        ]);
    }
}