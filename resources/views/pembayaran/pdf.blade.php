@foreach($pembayaranTunjangans as $pembayaran)
<div style="
    position: relative;
    page-break-after: always; 
    font-family: Arial, sans-serif; 
    font-size: 8px;
">

    <!-- Watermark -->
    <div style="
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('{{ public_path('template/img/terakhir.png') }}') no-repeat center;
        background-size: cover;
        opacity: 0.1;   /* makin kecil makin pudar */
        z-index: 0;
    "></div>

    <!-- Header -->

<!-- Info Karyawan -->
<table style="width:100%; margin-bottom:10px; border-collapse: collapse;">
    <tr>
        <!-- Logo di kiri -->
        <td style="text-align:left; vertical-align:top; width:15%; padding:0; margin:0;">
            <img src="{{ public_path('template/img/logoslip.png') }}" 
                 alt="Logo" 
                 style="height:60px; margin-right:5px;">
        </td>

        <!-- Data karyawan di kanan -->
        <td style="width:85%; padding:0; margin:0;">
            <table style="width:100%;">
                <tr>
                    <td>Nama Staff</td>
                    <td>: {{ $pembayaran->nama }}</td>
                    <td>Departemen</td>
                    <td>: {{ $pembayaran->departemen }}</td>
                </tr>
                <tr>
                    <td>Masa Kerja</td>
                    <td>: {{ $pembayaran->masa_kerja }}</td>
                    <td>Status</td>
                    <td>: {{ $pembayaran->status }}</td>
                </tr>
                <tr>
                    <td>No Rekening</td>
                    <td>: {{ $pembayaran->no_rekening }}</td>
                    <td>Bank / Atas Nama</td>
                    <td>: {{ $pembayaran->bank }} / {{ $pembayaran->atas_nama }}</td>
                </tr>
                <tr>
                    <td>Bulan</td>
                    <td>: {{ $pembayaran->bulan }}</td>
                    <td>Unit</td>
                    <td>: {{ $pembayaran->unit }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>




    <!-- Tabel Pendapatan & Potongan -->
    <table style="width:100%; border-collapse: collapse;">
        <tr>
            <!-- Pendapatan -->
            <td style="width:50%; vertical-align: top; padding-right:10px;">
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <th colspan="3" style="text-align:left; border-bottom:1px solid #000;">PENDAPATAN</th>
                    </tr>
                    <tr><td>a. Tunjangan Pokok</td><td>:</td><td>Rp {{ number_format($pembayaran->tunjangan_pokok,0,',','.') }}</td></tr>
                    <tr><td>b. Tunjangan Harian</td><td>:</td><td>Rp {{ number_format($pembayaran->harian,0,',','.') }}</td></tr>
                    <tr><td>c. Tunjangan Fungsional</td><td>:</td><td>Rp {{ number_format($pembayaran->fungsional,0,',','.') }}</td></tr>
                    <tr><td>d. Tunjangan Kesehatan</td><td>:</td><td>Rp {{ number_format($pembayaran->kesehatan,0,',','.') }}</td></tr>
                    <tr><td>e. Tunjangan Kerajinan</td><td>:</td><td>Rp {{ number_format($pembayaran->kerajinan,0,',','.') }}</td></tr>
                    <tr><td>f. Komisi English biMBA</td><td>:</td><td>Rp {{ number_format($pembayaran->english,0,',','.') }}</td></tr>
                    <tr><td>g. Komisi Mentor Magang</td><td>:</td><td>Rp {{ number_format($pembayaran->mentor,0,',','.') }}</td></tr>
                    <tr><td>h. Kekurangan Tunjangan</td><td>:</td><td>Rp {{ number_format($pembayaran->kekurangan,0,',','.') }}</td></tr>
                    <tr><td>i. Tunjangan Keluarga</td><td>:</td><td>Rp {{ number_format($pembayaran->tj_keluarga,0,',','.') }}</td></tr>
                    <tr><td>j. Lain-lain</td><td>:</td><td>Rp {{ number_format($pembayaran->lain_lain_pendapatan ?? 0,0,',','.') }}</td></tr>
                    <!-- Jika mau sesuai total dari DB (SUM(total)) -->
<tr style="font-weight:bold;">
    <td>k. Total Pendapatan</td>
    <td>:</td>
    <td>Rp {{ number_format($pembayaran->pendapatan_lain,0,',','.') }}</td>
</tr>
                </table>
            </td>

            <!-- Potongan -->
            <td style="width:50%; vertical-align: top; padding-left:10px;">
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <th colspan="3" style="text-align:left; border-bottom:1px solid #000;">POTONGAN</th>
                    </tr>
                    <tr><td>l. Sakit</td><td>:</td><td>Rp {{ number_format($pembayaran->sakit,0,',','.') }}</td></tr>
                    <tr><td>m. Izin</td><td>:</td><td>Rp {{ number_format($pembayaran->izin,0,',','.') }}</td></tr>
                    <tr><td>n. Alpa</td><td>:</td><td>Rp {{ number_format($pembayaran->alpa,0,',','.') }}</td></tr>
                    <tr><td>o. Tidak Aktif</td><td>:</td><td>Rp {{ number_format($pembayaran->tidak_aktif,0,',','.') }}</td></tr>
                    <tr><td>p. Kelebihan Tunjangan</td><td>:</td><td>Rp {{ number_format($pembayaran->kelebihan,0,',','.') }}</td></tr>
                    <tr><td>q. Lain-lain</td><td>:</td><td>Rp {{ number_format($pembayaran->lain_lain,0,',','.') }}</td></tr>
                    <tr style="font-weight:bold;">
    <td>r. Total Potongan</td>
    <td>:</td>
    <td>Rp {{ number_format($pembayaran->total_potongan,0,',','.') }}</td>
</tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Jumlah Dibayarkan & Rekening -->
    <p style="margin-top:10px;">Jumlah Yang Dibayarkan: <strong>Rp {{ number_format($pembayaran->dibayarkan,0,',','.') }}</strong></p>
    <p>Rekening & Email: {{ $pembayaran->bank ?? '-' }} | {{ $pembayaran->no_rekening ?? '-' }} | {{ $pembayaran->atas_nama ?? '-' }} <br>{{ $profile->email ?? '-' }}</p>

    <!-- Footer -->
    <table style="width:100%; margin-top:20px;">
        <tr>
            <td style="text-align:center;">Yang Menyerahkan<br><br><br>________________<br>Kepala Unit</td>
            <td style="text-align:center;">Penerima<br><br><br>________________<br>Motivator</td>
        </tr>
    </table>

    <div style="font-size:10px; margin-top:10px;">
        <p>Keterangan:</p>
        <ul>
            <li>Potongan Dengan Izin (Tunjangan Harian : 25 Hari Kerja)</li>
            <li>Potongan Tanpa Izin (Take Home Pay : 25 Hari Kerja)</li>
            <li>Periode Absensi: tgl 26 bulan lalu s/d tgl 25 bulan berikutnya</li>
        </ul>
    </div>

</div>
@endforeach
