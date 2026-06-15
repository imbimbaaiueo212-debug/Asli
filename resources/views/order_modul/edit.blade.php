@extends('layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">Edit Order Modul</h3>

    <form action="{{ route('order_modul.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- TANGGAL --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Tanggal</label>
            <input type="date" name="tanggal_order" class="form-control"
                   value="{{ $order->tanggal_order->format('Y-m-d') }}">
        </div>

        {{-- UNIT --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Unit</label>
            <select name="unit_id" class="form-control">
                @foreach($units as $u)
                    <option value="{{ $u->id }}"
                        {{ $order->unit_id == $u->id ? 'selected' : '' }}>
                        {{ $u->no_cabang }} | {{ $u->biMBA_unit }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- STATUS / APPROVAL --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Approval / Status</label>
                <select name="status" class="form-control">
                    <option value="pending"  {{ old('status', $order->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accept"   {{ old('status', $order->status) === 'accept' ? 'selected' : '' }}>Disetujui</option>
                    <option value="reject"   {{ old('status', $order->status) === 'reject' ? 'selected' : '' }}>Ditolak</option>
                    
                    {{-- Tambahan jika ada nilai "Kurang" di database --}}
                    <option value="Kurang"   {{ old('status', $order->status) === 'Kurang' ? 'selected' : '' }}>Kurang</option>
                </select>
            </div>

        <hr>

        {{-- PRODUK --}}
        <div id="produk-wrapper">

            @foreach($order->items as $item)
            <div class="row mb-3 produk-item">

                <div class="col-md-4">
                    <select name="produk[]" class="form-control produk-select">
                        <option value="">-- pilih produk --</option>
                        @foreach($produks as $p)
                            <option value="{{ $p->label }}"
                                data-harga="{{ $p->harga }}"
                                {{ $item['kode'] == $p->label ? 'selected' : '' }}>
                                {{ $p->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="number" name="jumlah[]" class="form-control jumlah"
                           value="{{ $item['jumlah'] }}">
                </div>

                <div class="col-md-2">
                    <input type="text" class="form-control harga" readonly
                           value="{{ number_format($item['harga_satuan'],0,',','.') }}">
                </div>

                <div class="col-md-2">
                    <input type="text" class="form-control total" readonly
                           value="{{ number_format($item['harga_total'],0,',','.') }}">
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove">X</button>
                </div>

            </div>
            @endforeach

        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('order_modul.index') }}" class="btn btn-secondary">Kembali</a>

    </form>

</div>

{{-- JAVASCRIPT (Diperbaiki & Dipercantik) --}}
<script>
function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function updateRow(row){
    let harga = parseInt(row.find('.produk-select option:selected').data('harga')) || 0;
    let jumlah = parseInt(row.find('.jumlah').val()) || 0;
    let total = harga * jumlah;

    row.find('.harga').val(formatRupiah(harga));
    row.find('.total').val(formatRupiah(total));
}

// Event Listeners
$(document).on('change', '.produk-select', function(){
    updateRow($(this).closest('.produk-item'));
});

$(document).on('input', '.jumlah', function(){
    updateRow($(this).closest('.produk-item'));
});

$('#addRow').click(function(){
    let html = `
    <div class="row mb-3 produk-item">
        <div class="col-md-4">
            <select name="produk[]" class="form-control produk-select">
                <option value="">-- pilih produk --</option>
                @foreach($produks as $p)
                    <option value="{{ $p->label }}" data-harga="{{ $p->harga }}">
                        {{ $p->label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <input type="number" name="jumlah[]" class="form-control jumlah">
        </div>

        <div class="col-md-2">
            <input type="text" class="form-control harga" readonly>
        </div>

        <div class="col-md-2">
            <input type="text" class="form-control total" readonly>
        </div>

        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove">X</button>
        </div>
    </div>`;
    
    $('#produk-wrapper').append(html);
});

$(document).on('click', '.remove', function(){
    $(this).closest('.produk-item').remove();
});
</script>

@endsection