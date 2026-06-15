<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kartu SPP - {{ $data['nim'] ?? 'Unknown' }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            /* dikecilkan sedikit agar muat lebih banyak */
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.3;
        }

        .container {
    width: 190mm;     /* AMAN UNTUK A5 LANDSCAPE */
    margin: 0 auto;   /* TENGAH KERTAS */
    padding: 0;
}

        .header {
            background: #007bff;
            color: white;
            text-align: center;
            padding: 8px;
            margin-bottom: 10px;
        }

        h1 {
            margin: 0;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        td,
        th {
            border: 1px solid #777;
            padding: 4px 6px;
            /* padding lebih kecil */
            vertical-align: middle;
        }

        table.info td:first-child {
            width: 140px;
            background: #f8f9fa;
            font-weight: bold;
        }

        .riwayat {
            page-break-inside: auto;
        }

        .riwayat tr {
            page-break-inside: avoid !important;
            page-break-after: auto;
        }

        .riwayat thead {
            display: table-header-group;
        }

        .riwayat th {
            background: #e6ffe6;
            color: #006600;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-success {
            color: #006600;
            font-weight: bold;
        }

        .text-danger {
            color: #990000;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #555;
            text-align: center;
            page-break-before: avoid;
        }

        @page {
    size: A5 landscape;
    margin: 8mm;
}
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h1>Kartu SPP Murid</h1>
        </div>

        <table class="info">
            <tr>
                <td>NIM</td>
                <td>{{ $data['nim'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama Murid</td>
                <td>{{ $data['nama'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Golongan</td>
                <td>{{ $data['golongan'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Masuk</td>
                <td>{{ $data['tgl_masuk'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pembayaran SPP</td>
                <td>Rp {{ number_format($data['spp'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Status Bulan Ini</td>
                <td class="{{ $statusClass ?? 'text-dark' }}">{{ $data['status_bayar'] ?? '-' }}</td>
            </tr>
            
        </table>

        <table class="riwayat">
            <thead>
                <tr>
                    <th rowspan="2">Bulan</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">Tgl. Transaksi</th>
                    <th colspan="3">Voucher</th>
                    <th rowspan="2">SPP (Rp)</th>
                </tr>
                <tr>
                    <th>Jumlah</th>
                    <th class="text-danger">Dipakai</th>
                    <th class="text-success">Sisa</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['riwayat'] ?? [] as $r)
                    <tr>
                        <td class="text-left">{{ $r['bulan'] ?? '-' }}</td>
                        <td class="{{ $r['status'] === 'Sudah bayar' ? 'text-success' : 'text-danger' }}">
                            {{ $r['status'] ?? '-' }}
                        </td>
                        <td>{{ $r['tanggal_transaksi'] ?? '-' }}</td>
                        <td>{{ $r['voucher_jumlah'] ?? '-' }}</td>
                        <td class="text-danger">{{ $r['voucher_dipakai'] ?? '0' }}</td>
                        <td class="text-success">{{ $r['voucher_sisa'] ?? '0' }}</td>
                        <td class="text-right">Rp {{ number_format($r['jumlah'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-2">Tidak ada riwayat pembayaran</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Dicetak pada: {{ $currentDate ?? now()->translatedFormat('d F Y') }}<br>
            <small>Kartu ini bukan bukti pembayaran resmi. Silakan konfirmasi ke admin biMBA.</small>
        </div>
    </div>

</body>

</html>