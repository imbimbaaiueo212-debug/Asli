@extends('layouts.app')

@section('title', 'Tambah Data Voucher')

@section('content')
<div class="card card-body">
    <h2>Voucher Old Version</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('voucher.store') }}" method="POST">
        @csrf

        {{-- ================= TIPE VOUCHER ================= --}}
        <div class="mb-3">
            <label>Tipe Voucher <span class="text-danger">*</span></label>
            <select name="tipe_voucher" id="tipe_voucher" class="form-control" required>
                <option value="regular">Humas</option>
                <option value="event">Event</option>
                <option value="lainnya">Lainnya</option>
            </select>
        </div>

        {{-- ================= DATA VOUCHER ================= --}}
        <div class="mb-3">
            <label>Jumlah Voucher</label>
            <input type="number" name="jumlah_voucher" class="form-control" value="1">
        </div>

        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control">
        </div>

        <div class="mb-3">
            <label>Nomor Voucher</label>
            <div id="voucher-container">
                <input type="text" name="no_voucher[]" class="form-control mb-2" required>
            </div>
            <button type="button" id="add-voucher" class="btn btn-secondary btn-sm">Tambah</button>
        </div>

        {{-- ================= HUMAS ================= --}}
        <h5>Detail Humas</h5>

        @if(auth()->user()->isAdminUser())
            <div class="mb-3">
                <label>Bimba Unit</label>
                <select name="bimba_unit" id="bimba_unit" class="form-control">
                    <option value="">-- Pilih Unit --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->biMBA_unit }}">
                            {{ $unit->biMBA_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

        <div class="mb-3">
            <label>NIM</label>
            <select name="nim" id="nim" class="form-control">
                <option value="">-- Pilih NIM --</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Nama Murid</label>
            <input type="text" name="nama_murid" id="nama_murid" class="form-control">
        </div>

        <div class="mb-3">
            <label>Orangtua</label>
            <input type="text" name="orangtua" id="orangtua" class="form-control">
        </div>

        <div class="mb-3">
            <label>Telp</label>
            <input type="text" name="telp_hp" id="telp_hp" class="form-control">
        </div>

        {{-- ================= MURID BARU ================= --}}
        <div id="murid-baru-section">
            <h5>Detail Murid Baru</h5>

            <div class="mb-3">
                <label>NIM Murid Baru</label>
                <input type="text" name="nim_murid_baru" class="form-control">
            </div>

            <div class="mb-3">
                <label>Nama Murid Baru</label>
                <input type="text" name="nama_murid_baru" class="form-control">
            </div>

            <div class="mb-3">
                <label>Orangtua Murid Baru</label>
                <input type="text" name="orangtua_murid_baru" class="form-control">
            </div>

            <div class="mb-3">
                <label>Telp/HP Murid Baru</label>
                <input type="text" name="telp_hp_murid_baru" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(function() {

    // ========================
    // TOGGLE SECTION MURID BARU
    // ========================
    function toggleMuridBaru() {
        let tipe = $('#tipe_voucher').val();

        if (tipe === 'regular') {
            // Humas / Regular → tampilkan Detail Murid Baru
            $('#murid-baru-section').slideDown(300);
            // Buat Nama Murid Baru wajib
            $('#nama_murid_baru').prop('required', true);
        } 
        else {
            // Event atau Lainnya → sembunyikan
            $('#murid-baru-section').slideUp(300);
            $('#nama_murid_baru').prop('required', false);
            
            // Clear field murid baru
            $('#nim_murid_baru, #nama_murid_baru, #orangtua_murid_baru, #telp_hp_murid_baru').val('');
        }
    }

    // Inisialisasi awal
    toggleMuridBaru();

    // Event change tipe voucher
    $('#tipe_voucher').on('change', toggleMuridBaru);

    // Tambah input nomor voucher
    $('#add-voucher').click(function() {
        $('#voucher-container').append(
            '<input type="text" name="no_voucher[]" class="form-control mb-2" required>'
        );
    });

});

$(function() {

    // ========================
    // LOAD MURID BERDASARKAN UNIT (Hanya untuk admin)
    // ========================
    function loadMurid(unit) {
        $('#nim').empty().append('<option value="">-- Pilih NIM --</option>');

        if (!unit) return;

        $.ajax({
            url: '{{ route("get.murid.by.unit") }}',
            type: 'GET',
            data: { bimba_unit: unit },
            success: function(data) {
                $.each(data, function(i, murid) {
                    $('#nim').append(
                        `<option value="${murid.id}"
                            data-nama="${murid.nama || ''}"
                            data-orangtua="${murid.orangtua || ''}"
                            data-telp="${murid.telp_hp || ''}">
                            ${murid.text || murid.nama}
                        </option>`
                    );
                });
            }
        });
    }

    // ADMIN PILIH UNIT
    $('#bimba_unit').on('change', function() {
        let unit = $(this).val();
        loadMurid(unit);
    });

    // AUTO FILL saat pilih NIM existing
    $('#nim').on('change', function() {
        let selected = $(this).find(':selected');

        $('#nama_murid').val(selected.data('nama') || '');
        $('#orangtua').val(selected.data('orangtua') || '');
        $('#telp_hp').val(selected.data('telp') || '');
    });

});
</script>