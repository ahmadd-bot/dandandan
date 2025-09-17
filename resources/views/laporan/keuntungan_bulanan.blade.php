@extends('layouts.laporan', ['title' => 'Laporan Keuntungan Bulanan'])

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
<h1 class="text-center">Laporan Keuntungan Bulanan</h1>

<p>Bulan : {{ $bulan }} {{ $tahun }}</p>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Jumlah Transaksi</th>
            <th>Total Modal</th>
            <th>Total Penjualan</th>
            <th>Total Keuntungan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($keuntungan as $key => $row)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $row->tanggal }}</td>
            <td>{{ $row->jumlah_transaksi }}</td>
            <td>{{ number_format($row->total_modal, 0, ',', '.') }}</td>
            <td>{{ number_format($row->total_penjualan, 0, ',', '.') }}</td>
            <td>{{ number_format($row->total_keuntungan, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3">Total Bulan {{ $bulan }}</th>
            <th>{{ number_format($keuntungan->sum('total_modal'), 0, ',', '.') }}</th>
            <th>{{ number_format($keuntungan->sum('total_penjualan'), 0, ',', '.') }}</th>
            <th>{{ number_format($keuntungan->sum('total_keuntungan'), 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
<!-- Tombol Print & Kembali -->
<button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
<a href="{{ route('laporan.index') }}" class="btn btn-back">⬅️ Kembali ke Laporan</a>

@if($keuntungan->count() == 0)
<div class="alert alert-info text-center">
    <strong>Tidak ada transaksi pada bulan ini</strong>
</div>
@endif

@endsection