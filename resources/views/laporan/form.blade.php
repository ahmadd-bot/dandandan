@extends('layouts.main', ['title' => 'Laporan'])

@section('title-content')
    <i class="fas fa-print mr-2"></i>
    Laporan
@endsection

@section('content')
<div class="row">
    <!-- CARD LAPORAN BIASA -->
    <div class="col-lg-6">
        <div class="card card-orange card-outline">
            <div class="card-header">
                <h3 class="card-title">Laporan Penjualan</h3>
            </div>
            <div class="card-body">
                <form id="formLaporanBiasa" target="_blank" method="GET">
                    <div class="form-group">
                        <label for="kategori-biasa">Jenis Laporan</label>
                        <select id="kategori-biasa" name="kategori" class="form-control">
                            <option value="">-- Pilih Laporan --</option>
                            <option value="harian" data-action="{{ route('laporan.harian') }}">Harian</option>
                            <option value="bulanan" data-action="{{ route('laporan.bulanan') }}">Bulanan</option>
                        </select>
                    </div>

                    {{-- Input tanggal untuk harian --}}
                    <div id="form-tanggal-biasa" class="form-group d-none">
                        <label for="tanggal-biasa">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal-biasa" class="form-control">
                    </div>

                    {{-- Input bulan & tahun untuk bulanan --}}
                    <div id="form-bulan-tahun-biasa" class="row d-none">
                        <div class="col">
                            <div class="form-group">
                                <label for="bulan-biasa">Bulan</label>
                                @php
                                    $pilihan = ['Januari', 'Februari', 'Maret', 'April', 'Mei',
                                                'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                @endphp
                                <select name="bulan" id="bulan-biasa" class="form-control">
                                    <option value="">Pilih Bulan</option>
                                    @foreach ($pilihan as $key => $value)
                                        <option value="{{ $key+1 }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="tahun-biasa">Tahun</label>
                                <select name="tahun" id="tahun-biasa" class="form-control">
                                    <option value="">Pilih Tahun</option>
                                    @php
                                        $tahun = date('Y');
                                        $max = $tahun - 5;
                                    @endphp
                                    @for ($tahun; $tahun > $max; $tahun--)
                                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-print mr-2"></i> Cetak
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- CARD LAPORAN KEUNTUNGAN -->
    <div class="col-lg-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">Laporan Keuntungan</h3>
            </div>
            <div class="card-body">
                <form id="formLaporanKeuntungan" target="_blank" method="GET">
                    <div class="form-group">
                        <label for="kategori-keuntungan">Jenis Laporan</label>
                        <select id="kategori-keuntungan" name="kategori" class="form-control">
                            <option value="">-- Pilih Laporan --</option>
                            <option value="keuntungan_harian" data-action="{{ route('laporan.keuntungan.harian') }}">Harian</option>
                            <option value="keuntungan_bulanan" data-action="{{ route('laporan.keuntungan.bulanan') }}">Bulanan</option>
                        </select>
                    </div>

                    {{-- Input tanggal untuk keuntungan harian --}}
                    <div id="form-tanggal-keuntungan" class="form-group d-none">
                        <label for="tanggal-keuntungan">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal-keuntungan" class="form-control">
                    </div>

                    {{-- Input bulan & tahun untuk keuntungan bulanan --}}
                    <div id="form-bulan-tahun-keuntungan" class="row d-none">
                        <div class="col">
                            <div class="form-group">
                                <label for="bulan-keuntungan">Bulan</label>
                                <select name="bulan" id="bulan-keuntungan" class="form-control">
                                    <option value="">Pilih Bulan</option>
                                    @foreach ($pilihan as $key => $value)
                                        <option value="{{ $key+1 }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="tahun-keuntungan">Tahun</label>
                                <select name="tahun" id="tahun-keuntungan" class="form-control">
                                    <option value="">Pilih Tahun</option>
                                    @php
                                        $tahun = date('Y');
                                        $max = $tahun - 5;
                                    @endphp
                                    @for ($tahun; $tahun > $max; $tahun--)
                                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-chart-line mr-2"></i> Lihat
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    function handleSelect(selectId, formId, tanggalId, bulanTahunId) {
        const select = document.getElementById(selectId);
        const form = document.getElementById(formId);
        const tanggal = document.getElementById(tanggalId);
        const bulanTahun = document.getElementById(bulanTahunId);

        select.addEventListener('change', function() {
            let action = this.options[this.selectedIndex].getAttribute('data-action');
            form.setAttribute('action', action);

            tanggal.classList.add('d-none');
            bulanTahun.classList.add('d-none');

            if (this.value.includes('harian')) {
                tanggal.classList.remove('d-none');
            } else if (this.value.includes('bulanan')) {
                bulanTahun.classList.remove('d-none');
            }
        });
    }

    handleSelect('kategori-biasa', 'formLaporanBiasa', 'form-tanggal-biasa', 'form-bulan-tahun-biasa');
    handleSelect('kategori-keuntungan', 'formLaporanKeuntungan', 'form-tanggal-keuntungan', 'form-bulan-tahun-keuntungan');
});
</script>
@endpush