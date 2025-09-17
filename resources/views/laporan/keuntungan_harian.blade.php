@extends('layouts.laporan', ['title' => 'Laporan Keuntungan Harian'])

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

    /* Supaya tombol tidak ikut tercetak */
    @media print {
        .btn {
            display: none;
        }
    }
</style>
<h1 class="text-center">Laporan Keuntungan Harian</h1>

<p>Tanggal : {{ date('d/m/Y', strtotime($tanggal)) }}</p>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>No</th>
            <th>No. Transaksi</th>
            <th>Pelanggan</th>
            <th>Kasir</th>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga Modal</th>
            <th>Harga Jual</th>
            <th>Keuntungan/Unit</th>
            <th>Total Keuntungan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($keuntungan as $key => $row)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $row->nomor_transaksi }}</td>
            <td>{{ $row->nama_pelanggan }}</td>
            <td>{{ $row->nama_kasir }}</td>
            <td>{{ $row->nama_produk }}</td>
            <td>{{ $row->jumlah }}</td>
            <td>{{ number_format($row->harga_modal, 0, ',', '.') }}</td>
            <td>{{ number_format($row->harga_jual, 0, ',', '.') }}</td>
            <td>{{ number_format($row->keuntungan_per_unit, 0, ',', '.') }}</td>
            <td>{{ number_format($row->total_keuntungan, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="9">Total Keuntungan Hari Ini</th>
            <th>{{ number_format($totalKeuntungan, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
<!-- Tombol Print & Kembali -->
<button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
<a href="{{ route('laporan.index') }}" class="btn btn-back">⬅️ Kembali ke Laporan</a>

@if($keuntungan->count() == 0)
<div class="alert alert-info text-center">
    <strong>Tidak ada transaksi pada tanggal ini</strong>
</div>
@endif

@endsection