<form action="" method="get" id="formCariPelanggan">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="Nama Pelanggan" id="searchPelanggan">
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary">
                Cari
            </button>
        </div>
    </div>
</form>

<!-- Tambah tombol untuk transaksi umum -->
<div class="mt-2">
    <button type="button" class="btn btn-warning btn-sm btn-block" onclick="setPelangganUmum()">
        <i class="fas fa-user mr-2"></i>Transaksi Umum (Tanpa Pelanggan)
    </button>
</div>

<table class="table table-sm mt-3">
    <thead>
        <tr>
            <th colspan="2" class="border-0"> Hasil Pencarian :</th>
        </tr>
    </thead>
    <tbody id="resultPelanggan"></tbody>
</table>

@push('scripts')
<script>
    $(function () {
        $('#formCariPelanggan').submit(function(e) {
            e.preventDefault();
            const search = $('#searchPelanggan').val();
            if (search.length >= 3) {
            fetchCariPelanggan(search)
            }
        })
    })

    function fetchCariPelanggan(search) {
        $.getJSON("/transaksi/pelanggan", {
            search: search
        },
    function(response) {
        $('#resultPelanggan').html('')
        response.forEach(item => {
            addResultPelanggan(item)
        });
    }   );
    }

    function addResultPelanggan(item) {
        const {
            id,
            nama
        } = item

        const btn = `<button type="button" 
        class="btn btn-xs btn-success" onclick="addPelanggan(${id})">
            Pilih
        </button>`;

        const row = `<tr>
            <td>${nama}</td>
            <td class="text-right">${btn}</td>
        </tr>`;
        $('#resultPelanggan').append(row)
    }

    function addPelanggan(id) {
        $.post("/transaksi/pelanggan", {
            id: id
        },
    function(response) {
        fetchCart();
    }, "json");
    }

    // Fungsi untuk set transaksi umum
    function setPelangganUmum() {
        // Hapus pelanggan dari cart
        $.ajax({
            type: "DELETE",
            url: "/transaksi/pelanggan",
            dataType: "json",
            success: function(response) {
                fetchCart();
                $('#searchPelanggan').val('');
                $('#resultPelanggan').html('');
            }
        });
    }
</script>
@endpush