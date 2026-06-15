<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Voucher biMBA-AIUEO - {{ $voucher->no_voucher ?? $voucher->voucher ?? 'unknown' }}</title>
    <style>
        @page {
            margin: 0;
            size: 310mm 100mm landscape;  /* A5 landscape – jangan ubah dulu */
        }
        body {
            margin: 0;
            padding: 0;
            font-size: 14px;  /* Sedikit lebih besar agar terbaca jelas di cetak */
            color: #04035bd0;
        }
        .tebal{
            font-weight: 800 !important;
        }
        .page {
            width: 310mm;
            height: 100mm;
            position: relative;
            page-break-after: always;
        }
        img.bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 310mm;
            height: 100mm;
        }
        .overlay {
            position: absolute;
            z-index: 1;
            font-weight: bold !important;
            line-height: 1.35;
            font-family: 'Times Ten', 'Comic Sans', 'Times New', Times, serif, bold;
        }
        .field {
            margin: 4px 0;  /* Jarak antar baris lebih rapat agar pas garis */
            font-weight: bold !important;
            font-family: 'Courier New', Courier, monospace;
            z-index: 1;
        }
        .underline {
            border-bottom: 1.5px dotted #000;
            display: inline-block;
            min-width: 240px;  /* Lebar garis lebih panjang agar pas template */
        }
        .rules {
            font-size: 11px;
            line-height: 1.3;
            margin-top: 6px;
            padding-left: 15px;
        }
        .nominal {
            font-size: 32px;
            font-weight: bold;
            color: #d32f2f;
            margin: 8px 0;
        }
        /* Hilangkan margin ekstra di akhir halaman */
        body > *:last-child {
            page-break-after: avoid;
        }
    </style>
</head>
<body>

    <!-- HALAMAN DEPAN -->
    <div class="page">
        <img src="{{ public_path('template/img/Voucher biMBA Depan.jpeg') }}" class="bg" alt="Voucher Depan">

        <!-- KIRI – Info Penerima (sudah disesuaikan agar pas garis) -->
        <div class="overlay" style="top: 205px; left: 178px; width: 48%;">
            
            <div class="field">
      {{ $voucher->nama_murid ?? '..................................................' }}
    </div>
    <div class="field" style="margin-top: 18px;" >
       {{ $voucher->nim ?? '......................' }}
    </div>

    <div class="field" style="margin-top: 19px;">
     {{ $voucher->nama_murid_baru ?? '..................................................' }}
    </div>
    <div class="field" style="margin-top: 18px;">
      {{ $voucher->nim_murid_baru ?? '......................' }}
    </div>
        </div>

        

        <!-- No. Voucher di atas (kiri & kanan) -->
        <div class="overlay" style="top: 5px; left: 238px; font-size: 20px; font-weight: bold;">
           {{ $voucher->no_voucher ?? $voucher->voucher ?? '................' }}
        </div>
        <div class="overlay" style="top: 5px; right: 110px; font-size: 20px; font-weight: bold;">
            {{ $voucher->no_voucher ?? $voucher->voucher ?? '................' }}
        </div>
    </div>

    <!-- HALAMAN BELAKANG -->
    <div class="page">
        <img src="{{ public_path('template/img/Voucher biMBA Belakang.jpeg') }}" class="bg" alt="Voucher Belakang">

        <!-- Bagian utama – disesuaikan agar pas garis -->
        <div class="overlay" style="top: 52px; left: 270px; width: 82%;">
            
            <div class="field" style="font-size: 20px;">
                {{ $voucher->nama_murid ?? '..........................................................' }}</span>
            </div>
            <div class="field" style="margin-top: 21px; font-size: 20px;">
               {{ $voucher->nim ?? '..............................' }}</span>
            </div>

            <div class="field" style="margin-top: 23px; font-size: 20px;">
                {{ $voucher->nama_murid_baru ?? '..........................................................' }}</span>
            </div>
            <div class="field" style="margin-top: 23px; font-size: 20px;">
                {{ $voucher->nim_murid_baru ?? '..............................' }}</span>
            </div>
        </div>

        <!-- Tanda tangan – posisi lebih bawah agar tidak tumpang tindih -->
        <div class="overlay" style="position: absolute; bottom: 18px; left: 238px; width: 40%; text-align: center; font-size: 12px;">
        {{ $voucher->nama_murid ?? '-' }}<br>
    </div>

    <div class="overlay" style="position: absolute; bottom: 18px; right: 755px; width: 40%; text-align: center; font-size: 13px;">
        {{ $voucher->bimba_unit ?? 'Unit Bimba' }}<br>
    </div>

        <!-- Tanggal di kanan bawah -->
        <div class="overlay"
     style="bottom: 338px; right: 190px; font-size: 18px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; color: #3d3d3dff;">

    {{ $voucher->last_tanggal_pemakaian
        ? \Carbon\Carbon::parse($voucher->last_tanggal_pemakaian)
            ->locale('id')
            ->translatedFormat('d M Y')
        : '-' }}

</div>
    </div>

</body>
</html>