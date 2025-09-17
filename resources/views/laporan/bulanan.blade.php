@extends('layouts.laporan', ['title' => 'Laporan Bulanan'])

@section('content')
<style>
    .btn {
        margin: 10px 5px;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        color: #fff;
    }
    .btn-print {
        background: #007bff;
    }
    .btn-print:hover {
        background: #0056b3;
    }
    .btn-back {
        background: #28a745;
    }
    .btn-back:hover {
        background: #1e7e34;
    }
    .produk-detail {
        font-size: 0.85em;
        color: #666;
        margin-left: 10px;
    }
    .produk-item {
        margin: 2px 0;
    }

    /* Supaya tombol tidak ikut tercetak */
    @media print {
        .btn {
            display: none;
        }
    }
</style>
<h1 class="text-center">Laporan Bulanan</h1>

<p>Bulan : {{ $bulan }} {{ request()->tahun }}</p>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Jumlah Transaksi</th>
            <th>Produk Terjual</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($penjualan as $key => $row)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $row->tgl }}</td>
            <td>{{ $row->jumlah_transaksi }}</td>
            <td>
                <div class="produk-detail">
                    @if(isset($detailProdukBulanan[$row->tgl]))
                        @foreach($detailProdukBulanan[$row->tgl] as $produk)
                            <div class="produk-item">
                                {{ $produk->nama_produk }} 
                                ({{ $produk->total_jumlah }} pcs @ Rp{{ number_format($produk->harga_rata, 0, ',', '.') }})
                            </div>
                        @endforeach
                    @endif
                </div>
            </td>
            <td>{{ number_format($row->jumlah_total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4">Jumlah Total</th>
            <th>{{ number_format($penjualan->sum('jumlah_total'), 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
<!-- Tombol Print & Kembali -->
<button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
<a href="{{ route('laporan.index') }}" class="btn btn-back">⬅️ Kembali ke Laporan</a>

@endsection