<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Voucher biMBA-AIUEO</title>
    <style>
        @page { margin: 15mm; size: A4 landscape; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        h3 { text-align: center; color: #1976d2; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #fff59d; color: #d32f2f; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .header { text-align: center; margin-bottom: 15px; }
        .logo { font-size: 24px; color: #f57c00; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">biMBA-AIUEO®</div>
    <h3>DAFTAR VOUCHER PEMBAYARAN SPP</h3>
    <p style="font-size: 10px;">Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>No Voucher</th>
            <th>NIM Humas</th>
            <th>Nama Murid Humas</th>
            <th>NIM Murid Baru</th>
            <th>Nama Murid Baru</th>
            <th>Unit</th>
            <th>Status</th>
            <th>Tgl Penyerahan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($vouchers as $voucher)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $voucher->voucher ?? $voucher->no_voucher ?? '-' }}</td>
            <td>{{ $voucher->nim ?? '-' }}</td>
            <td>{{ $voucher->nama_murid ?? '-' }}</td>
            <td>{{ $voucher->nim_murid_baru ?? '-' }}</td>
            <td>{{ $voucher->nama_murid_baru ?? '-' }}</td>
            <td>{{ $voucher->bimba_unit ?? '-' }}</td>
            <td>{{ ucfirst($voucher->status ?? 'belum_diserahkan') }}</td>
            <td>{{ $voucher->tanggal_penyerahan ? \Carbon\Carbon::parse($voucher->tanggal_penyerahan)->format('d-m-Y') : '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center; padding:20px;">Tidak ada data voucher yang sesuai filter.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>