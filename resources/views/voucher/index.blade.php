@extends('layouts.app')

@section('title', 'Voucher')

@section('content')

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary mb-0">Voucher</h2>

    <div>
      <a href="{{ route('wheels.index') }}" class="btn btn-info me-2 shadow-sm">
        <i class="bi bi-plus-circle"></i> Spin Voucher
      </a>
      <a href="{{ route('voucher.create') }}" class="btn btn-primary me-2 shadow-sm">
        <i class="bi bi-plus-circle"></i> Tambah Data
      </a>
      <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="bi bi-cloud-arrow-up"></i> Import Excel
      </button>
    </div>
  </div>

  {{-- Modal import --}}
  <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 shadow-lg border-0 slide-in-right" id="importModalContent">
        <div class="modal-header bg-primary text-white rounded-top-4">
          <h5 class="modal-title fw-bold"><i class="bi bi-upload"></i> Import Data Voucher Lama</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center p-4">
          <p class="text-muted mb-4">Unggah file Excel (<strong>.xlsx / .xls / .csv</strong>).</p>
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          <form action="{{ route('voucher-lama.import') }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf
            <div class="mb-4">
              <label for="file" class="form-label fw-semibold">Pilih File Excel</label>
              <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv"
                class="form-control form-control-lg border-primary rounded-pill shadow-sm file-input" required>
              <small class="text-muted d-block mt-2">Pastikan format kolom sesuai template data voucher lama.</small>
            </div>
            <button type="submit" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm animated-button">
              <i class="bi bi-cloud-arrow-up"></i> Import Sekarang
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <a href="{{ route('voucher.export') . '?' . http_build_query(request()->query()) }}" 
     class="btn btn-info shadow-sm">
    <i class="bi bi-download"></i> Export Excel
  </a>

  {{-- Modal Upload Bukti by NIM --}}
  <div class="modal fade" id="uploadBuktiModal" tabindex="-1"
     aria-labelledby="uploadBuktiModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <form id="uploadBuktiForm"
              class="modal-content"
              enctype="multipart/form-data">

            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="uploadBuktiModalLabel">
                    Unggah Bukti Penyerahan
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div id="uploadBuktiAlert"
                     class="alert d-none"
                     role="alert"></div>

                {{-- NOMOR VOUCHER --}}
                <div class="mb-3">
                    <label for="no_voucher_input"
                           class="form-label">
                        No Voucher
                    </label>

                    <input type="text"
                           name="no_voucher"
                           id="no_voucher_input"
                           class="form-control"
                           placeholder="Isi nomor voucher">
                </div>

                {{-- NIM MURID BARU --}}
                <div class="mb-3">
                    <label for="nim_murid_baru_input"
                           class="form-label">
                        NIM Murid Baru
                    </label>

                    <input type="text"
                           name="nim_murid_baru"
                           id="nim_murid_baru_input"
                           class="form-control"
                           placeholder="Opsional untuk voucher event/lainnya">
                </div>

                {{-- TANGGAL --}}
                <div class="mb-3">
                    <label for="tanggal_penyerahan_input"
                           class="form-label">
                        Tanggal Penyerahan (opsional)
                    </label>

                    <input type="date"
                           name="tanggal_penyerahan"
                           id="tanggal_penyerahan_input"
                           class="form-control">
                </div>

                {{-- FILE --}}
                <div class="mb-3">
                    <label for="bukti_penyerahan_input"
                           class="form-label">
                        File Bukti (jpg/png/pdf, max 5MB)
                    </label>

                    <input type="file"
                           name="bukti_penyerahan"
                           id="bukti_penyerahan_input"
                           class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf"
                           required>
                </div>

                <small class="text-muted">
                    Voucher regular menggunakan NIM.
                    Voucher event/lainnya dapat menggunakan nomor voucher.
                </small>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Batal
                </button>

                <button type="submit"
                        class="btn btn-primary"
                        id="uploadBuktiSubmitBtn">
                    Unggah dan Terapkan
                </button>

            </div>

        </form>

    </div>
</div>

  {{-- Modal Preview Bukti --}}
  <div class="modal fade" id="buktiPreviewModal" tabindex="-1" aria-labelledby="buktiPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="buktiPreviewModalLabel">Preview Bukti Penyerahan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center" style="min-height:320px;">
          <div id="buktiPreviewImageWrap" class="d-none">
            <img id="buktiPreviewImage" src="" alt="Bukti" style="max-width:100%; height:auto; border-radius:6px; box-shadow:0 4px 18px rgba(0,0,0,0.12)">
          </div>
          <div id="buktiPreviewPdfWrap" class="d-none" style="height:70vh;">
            <iframe id="buktiPreviewPdf" src="" width="100%" height="100%" style="border:0;border-radius:6px;"></iframe>
          </div>
          <div id="buktiPreviewOtherWrap" class="d-none">
            <a id="buktiPreviewOtherLink" href="#" target="_blank">Buka file</a>
          </div>
        </div>
        <div class="modal-footer">
          <small class="text-muted me-auto">Tutup untuk kembali ke daftar.</small>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Hasil spin --}}
  @if(!empty($spinResult) && is_array($spinResult) && !empty($spinResult['rows']))
    <div class="card mb-4 border-info shadow-sm">
      <div class="card-header bg-info text-white">
        <strong>Hasil Spin — {{ $spinResult['count'] ?? count($spinResult['rows']) }} Voucher Dibuat</strong>
        <span class="float-end">Total: Rp {{ $spinResult['nominal_formatted'] ?? number_format($spinResult['nominal'] ?? 0, 0, ',', '.') }}</span>
      </div>
      <div class="card-body p-2">
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Voucher</th>
                <th>Jumlah Voucher</th>
                <th>Nominal (Rp)</th>
                <th>Tanggal Spin</th>
                <th>Status</th>
                <th>NIM (Humas)</th>
                <th>Nama Humas</th>
                <th>NIM Murid Baru</th>
                <th>Nama Murid Baru</th>
                <th>Orangtua Murid Baru</th>
                <th>Telp/HP Murid Baru</th>
                <th>Bukti</th>
              </tr>
            </thead>
            <tbody>
              @foreach($spinResult['rows'] as $i => $row)
                <tr class="table-warning">
                  <td>{{ $i + 1 }}</td>
                  <td>{{ $row['voucher'] ?? '-' }}</td>
                  <td>1</td>
                  <td>Rp {{ number_format($row['nominal'] ?? 0, 0, ',', '.') }}</td>
                  <td>
                    @if(!empty($row['tanggal_penyerahan']))
                      {{ \Carbon\Carbon::parse($row['tanggal_penyerahan'])->format('d-m-Y') }}
                    @elseif(!empty($row['tanggal_spin']))
                      {{ \Carbon\Carbon::parse($row['tanggal_spin'])->format('d-m-Y') }}
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @php
                      $s = $row['status'] ?? null;
                      $statusLabel = ($s === 'penyerahan') ? 'Penyerahan' : (($s === 'pemakaian' || $s === 'Digunakan') ? ucfirst($s) : 'Belum diserahkan');
                    @endphp
                    {{ $statusLabel }}
                  </td>
                  <td>{{ $row['nim'] ?? '-' }}</td>
                  <td>{{ $row['nama_murid'] ?? '-' }}</td>
                  <td>{{ $row['nim_murid_baru'] ?? '-' }}</td>
                  <td>{{ $row['nama_murid_baru'] ?? '-' }}</td>
                  <td>{{ $row['orangtua_murid_baru'] ?? '-' }}</td>
                  <td>{{ $row['telp_hp_murid_baru'] ?? '-' }}</td>
                  <td>
                    @if(!empty($row['bukti_penyerahan_path']))
                      @php
                        $cleanPath = preg_replace('#^public/#', '', $row['bukti_penyerahan_path']);
                        $path = asset('storage/' . $cleanPath);
                        $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                      @endphp

                      @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <a href="javascript:void(0)" class="d-inline-block me-2 bukti-preview-trigger" data-type="image"
                          data-src="{{ $path }}" title="Lihat bukti">
                          <img src="{{ $path }}" alt="bukti"
                            style="height:40px; width:auto; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,0.1)">
                        </a>
                      @elseif($ext === 'pdf')
                        <a href="javascript:void(0)" class="badge bg-secondary text-white text-decoration-none me-2 bukti-preview-trigger" data-type="pdf" data-src="{{ $path }}">PDF</a>
                      @else
                        <a href="javascript:void(0)" class="text-decoration-none bukti-preview-trigger" data-type="other" data-src="{{ $path }}">Lihat</a>
                      @endif
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif

             {{-- FILTER & SEARCH --}}
<div class="card mb-4 border-primary shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter & Pencarian Voucher</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('voucher.index') }}" class="row g-3 align-items-end">

            <!-- Search -->
            <div class="col-lg-3 col-md-4">
                <label class="form-label fw-semibold">Cari</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="No Voucher, Nama, NIM..." 
                       value="{{ request('search') }}">
            </div>

            <!-- Nama Murid -->
<div class="col-lg-3 col-md-4">
    <label class="form-label fw-semibold">NIM | Nama Murid</label>
    <select name="nama_murid" id="namaMuridSelect" class="form-select">
        <option value="">-- Semua Murid --</option>
        @foreach($namaMurid as $display)
            <option value="{{ $display }}" {{ request('nama_murid') == $display ? 'selected' : '' }}>
                {{ $display }}
            </option>
        @endforeach
    </select>
</div>
            <!-- Tanggal Dari -->
            <div class="col-lg-2 col-md-3">
                <label class="form-label fw-semibold">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
            </div>

            <!-- Tanggal Sampai -->
            <div class="col-lg-2 col-md-3">
                <label class="form-label fw-semibold">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
            </div>

            <!-- Unit Bimba (Admin) -->
            @if(auth()->user()->isAdminUser())
                <div class="col-lg-2 col-md-3">
                    <label class="form-label fw-semibold">Unit Bimba</label>
                    <select name="bimba_unit" class="form-select">
                        <option value="">-- Semua Unit --</option>
                        @foreach($listBimbaUnit as $unit)
                            <option value="{{ $unit }}" {{ request('bimba_unit') == $unit ? 'selected' : '' }}>
                                {{ $unit }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Jumlah Per Halaman -->
            <div class="col-lg-2 col-md-3">
                <label class="form-label fw-semibold">Tampilkan</label>
                <select name="per_page" class="form-select">
                    <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <!-- Tombol -->
            <div class="col-lg-2 col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Terapkan
                </button>
                <a href="{{ route('voucher.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
          <!-- Tombol Reset Filter (diperbarui agar include bimba_unit juga) -->
          @if(request()->hasAny(['nama_murid', 'tanggal_dari', 'tanggal_sampai', 'bimba_unit']))
              <div class="mt-3 text-end">
                  <a href="{{ route('voucher.index') }}" class="btn btn-outline-secondary btn-sm">
                      <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                  </a>
              </div>
          @endif
      </div>
  </div>

  {{-- Daftar voucher --}}
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Daftar Voucher</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>NO</th>
              <th>NAMA MURID (Humas)</th>
              <th>TELP/HP (humas)</th>
              <th>ORANGTUA (Humas)</th>
              <th>NIM (Humas)</th>
              <th>NAMA MURID BARU</th>
              <th>TELP/HP ORANG TUA MURID BARU</th>
              <th>ORANGTUA MURID BARU</th>
              <th>NIM MURID BARU</th>
              <th>HISTORY SPIN</th>
              <th>NO. VOUCHER</th>
              <th>JUMLAH VOUCHER</th>
              <th>NOMINAL (Rp)</th>
              <th>TANGGAL SPIN</th>
              <th>TANGGAL PENYERAHAN</th>
              <th>STATUS</th>
              <th>TIPE</th>
              <th>Bimba Unit</th>
              <th>No Cabang</th>         
              <th>TANGGAL PEMAKAIAN</th>
              <th>BUKTI</th>
              <th>ACTION</th>
            </tr>
          </thead>
          <tbody>
    @foreach($vouchers as $index => $v)
        @php
            $tipe = $v->tipe_voucher ?? 'regular';

            $rowClass = match($tipe) {
                'event' => 'table-info',
                'lainnya' => 'table-warning',
                default => ''
            };

            $badgeClass = match($tipe) {
                'event' => 'bg-info',
                'lainnya' => 'bg-warning text-dark',
                default => 'bg-primary'
            };

            $badgeText = match($tipe) {
                'event' => 'EVENT',
                'lainnya' => 'LAINNYA',
                default => 'HUMAS'
            };
        @endphp

        <tr class="{{ $rowClass }}">
            <!-- 1. NO -->
            <td>{{ $index + 1 }}</td>

            <!-- 2. NAMA MURID (Humas) -->
            <td>{{ $v->nama_murid ?? '-' }}</td>

            <!-- 3. TELP/HP (Humas) -->
            <td>{{ $v->telp_hp ?? '-' }}</td>

            <!-- 4. ORANGTUA (Humas) -->
            <td>{{ $v->orangtua ?? '-' }}</td>

            <!-- 5. NIM (Humas) -->
            <td>{{ $v->nim ?? '-' }}</td>

            <!-- 6. NAMA MURID BARU -->
            <td>{{ $v->nama_murid_baru ?? '-' }}</td>

            <!-- 7. TELP/HP ORANG TUA MURID BARU -->
            <td>{{ $v->telp_hp_murid_baru ?? '-' }}</td>

            <!-- 8. ORANGTUA MURID BARU -->
            <td>{{ $v->orangtua_murid_baru ?? '-' }}</td>

            <!-- 9. NIM MURID BARU -->
            <td>{{ $v->nim_murid_baru ?? '-' }}</td>

            <!-- 10. HISTORY SPIN -->
            <td>{{ $v->voucher ?? '-' }}</td>

            <!-- 11. NO. VOUCHER -->
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm inline-edit" 
                           data-id="{{ $v->id }}" data-field="no_voucher" 
                           value="{{ old('no_voucher.' . $v->id, $v->no_voucher) }}"
                           placeholder="Belum diisi" style="min-width:140px;">
                    <div class="invalid-feedback small mt-1 d-none inline-error" id="error-no_voucher-{{ $v->id }}"></div>
                </div>
            </td>

            <!-- 12. JUMLAH VOUCHER -->
            <td>{{ $v->jumlah_voucher }}</td>

            <!-- 13. NOMINAL (Rp) -->
            <td>Rp {{ number_format(($v->jumlah_voucher ?? 0) * 50000, 0, ',', '.') }}</td>

            <!-- 14. TANGGAL SPIN -->
            <td>
                @if(!empty($v->tanggal))
                    {{ \Carbon\Carbon::parse($v->tanggal)->format('d-m-Y') }}
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>

            <!-- 15. TANGGAL PENYERAHAN -->
            <td>
                <div class="position-relative">
                    <input type="date" class="form-control form-control-sm inline-edit" 
                           data-id="{{ $v->id }}" data-field="tanggal_penyerahan"
                           value="{{ $v->tanggal_penyerahan ? \Carbon\Carbon::parse($v->tanggal_penyerahan)->format('Y-m-d') : '' }}"
                           style="min-width:150px;">
                    
                    <small class="d-block text-muted mt-1 tanggal-format-{{ $v->id }}">
                        @if($v->tanggal_penyerahan)
                            {{ \Carbon\Carbon::parse($v->tanggal_penyerahan)->format('d-m-Y') }}
                        @else
                            Belum diisi
                        @endif
                    </small>

                    <div class="invalid-feedback small mt-1 d-none inline-error" id="error-tanggal_penyerahan-{{ $v->id }}"></div>
                </div>
            </td>

            <!-- 16. STATUS -->
            <td class="status-cell-{{ $v->id }}">
                @php $st = $v->status ?? null; @endphp
                @if($st === 'Digunakan' || $v->jumlah_voucher <= 0)
                    <span class="badge bg-dark">Digunakan</span>
                @elseif($st === 'pemakaian')
                    <span class="badge bg-warning text-dark">Dalam Pemakaian</span>
                @elseif($st === 'penyerahan' || !empty($v->tanggal_penyerahan))
                    <span class="badge bg-success">Penyerahan</span>
                @else
                    <span class="badge bg-danger">Belum Diserahkan</span>
                @endif
            </td>

            <!-- 17. TIPE -->
            <td>
                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
            </td>

            <!-- 18. Bimba Unit -->
            <td>{{ $v->bimba_unit }}</td>

            <!-- 19. No Cabang -->
            <td>{{ $v->no_cabang }}</td>

            <!-- 20. TANGGAL PEMAKAIAN -->
            <td>
                @php $tp = $v->histori->first()->tanggal_pemakaian ?? null; @endphp
                @if($tp) 
                    {{ \Carbon\Carbon::parse($tp)->format('d M Y') }} 
                @else 
                    <span class="text-muted">—</span> 
                @endif
            </td>

            <!-- 21. BUKTI -->
            <td class="align-middle">
                @if(!empty($v->bukti_penyerahan_path))
                    @php
                        $cleanPath = preg_replace('#^public/#', '', $v->bukti_penyerahan_path);
                        $path = asset('storage/' . $cleanPath);
                        $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                    @endphp

                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <a href="javascript:void(0)" class="d-inline-block me-2 bukti-preview-trigger" 
                           data-type="image" data-src="{{ $path }}" title="Lihat bukti">
                            <img src="{{ $path }}" alt="bukti"
                                 style="height:36px; width:auto; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,0.08)">
                        </a>
                    @elseif($ext === 'pdf')
                        <a href="javascript:void(0)" class="badge bg-secondary text-white text-decoration-none me-2 bukti-preview-trigger" 
                           data-type="pdf" data-src="{{ $path }}">PDF</a>
                    @else
                        <a href="javascript:void(0)" class="text-decoration-none bukti-preview-trigger" 
                           data-type="other" data-src="{{ $path }}">Lihat</a>
                    @endif
                @else
                    <span class="text-muted">—</span>
                @endif

                <div class="mt-1">
                    <button class="btn btn-sm btn-outline-secondary upload-bukti-btn" type="button"
                            data-nim="{{ $v->nim_murid_baru ?? '' }}"
                            data-voucher="{{ $v->no_voucher ?? '' }}">
                        <i class="bi bi-upload"></i> Upload Bukti
                    </button>
                </div>
            </td>

            <!-- 22. ACTION -->
            <td>
                <a href="{{ route('voucher.print', $v->id) }}" target="_blank" class="btn btn-success btn-sm mt-1">
                    Cetak Voucher
                </a>
                <a href="{{ route('voucher.edit', $v->id) }}" class="btn btn-warning btn-sm mt-1">Edit</a>
                <a href="{{ route('voucher.histori', $v->id) }}" class="btn btn-info btn-sm mt-1">Histori</a>

                @if (auth()->user()?->role === 'admin')
                    <form action="{{ route('voucher.destroy', $v->id) }}" method="POST" style="display:inline;" class="mt-1">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                    </form>
                @endif
            </td>
        </tr>
    @endforeach

    @if($vouchers->isEmpty())
        <tr>
            <td colspan="22" class="text-center text-muted py-3">Belum ada data voucher.</td>
        </tr>
    @endif
</tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Pagination --}}
@if($vouchers instanceof \Illuminate\Contracts\Pagination\Paginator ||
    $vouchers instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)

<div class="card-footer bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap">

        <div class="small text-muted">
            Menampilkan
            <strong>{{ $vouchers->firstItem() }}</strong>
            -
            <strong>{{ $vouchers->lastItem() }}</strong>
            dari
            <strong>{{ $vouchers->total() }}</strong>
            data
        </div>

        <div>
            {{ $vouchers->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

    </div>
</div>

@endif

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    .input-spinner {
      position: absolute;
      right: 8px;
      top: 6px;
      width: 18px;
      height: 18px;
      display: none;
    }

    .input-spinner.show {
      display: inline-block;
    }
  </style>
 @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const form = $('#namaMuridSelect').closest('form');

            $('#namaMuridSelect').select2({
                placeholder: "Ketik nama atau NIM murid...",
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                dropdownParent: $('#namaMuridSelect').parent()
            }).on('change', function() {
                form.submit();   // ← Langsung submit saat pilih
            });

            // Search input juga submit saat Enter
            $('input[name="search"]').on('keypress', function(e) {
                if (e.which === 13) {
                    form.submit();
                }
            });
        });
    document.addEventListener('DOMContentLoaded', function () {
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      function showToast(message, type = 'success') {
        const containerId = 'inline-edit-toast';
        let container = document.getElementById(containerId);
        if (!container) {
          container = document.createElement('div');
          container.id = containerId;
          container.style.position = 'fixed';
          container.style.right = '20px';
          container.style.bottom = '20px';
          container.style.zIndex = 1060;
          document.body.appendChild(container);
        }
        const alert = document.createElement('div');
        alert.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' shadow-sm';
        alert.style.minWidth = '200px';
        alert.innerText = message;
        container.appendChild(alert);
        setTimeout(() => { alert.classList.add('fade'); alert.addEventListener('transitionend', () => alert.remove()); }, 1800);
      }

                      async function sendInlineUpdate(id, field, value, inputEl) {
        const url = "{{ url('/voucher') }}/" + id + "/inline";
        const payloadValue = (value === null || value === '' || value === 'null') ? null : value;

        console.log('🟢 DEBUG JS →', { id, field, value: payloadValue });

        inputEl.classList.remove('is-invalid');
        const errorEl = document.getElementById('error-' + field + '-' + id);
        if (errorEl) { 
            errorEl.classList.add('d-none'); 
            errorEl.innerText = ''; 
        }

        inputEl.disabled = true;
        let spinner = inputEl.parentNode.querySelector('.input-spinner');
        if (!spinner) {
          spinner = document.createElement('div');
          spinner.className = 'spinner-border spinner-border-sm input-spinner';
          spinner.setAttribute('role', 'status');
          spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
          inputEl.parentNode.appendChild(spinner);
        }
        spinner.classList.add('show');

        try {
            const formData = new FormData();
            formData.append('field', field);
            formData.append('value', payloadValue !== null ? payloadValue : '');

            const res = await fetch(url, {
                method: 'POST',                    // ← Ubah ke POST
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();
            console.log('🟡 Server Response:', data);

            if (!res.ok) {
                throw new Error(data.message || 'Gagal menyimpan');
            }

            if (field === 'tanggal_penyerahan') {
                const small = document.querySelector('.tanggal-format-' + id);
                if (small) small.innerText = data.data.tanggal_penyerahan_formatted || 'Belum diisi';

                const statusCell = document.querySelector('.status-cell-' + id);
                if (statusCell && data.data.status) {
                    let badgeHtml = '<span class="badge bg-danger">Belum Diserahkan</span>';
                    if (data.data.status === 'Digunakan') badgeHtml = '<span class="badge bg-dark">Digunakan</span>';
                    else if (data.data.status === 'penyerahan') badgeHtml = '<span class="badge bg-success">Penyerahan</span>';
                    statusCell.innerHTML = badgeHtml;
                }
            }

            inputEl.setAttribute('data-original', payloadValue === null ? '' : payloadValue);
            showToast('✅ Tersimpan', 'success');

        } catch (err) {
            console.error('❌ Error:', err);
            showToast(err.message || 'Gagal menyimpan', 'error');

            inputEl.classList.add('is-invalid');
            if (errorEl) { 
                errorEl.classList.remove('d-none'); 
                errorEl.innerText = err.message; 
            }
            inputEl.value = inputEl.getAttribute('data-original') ?? inputEl.value;
        } finally {
            spinner.classList.remove('show');
            inputEl.disabled = false;
        }
      }

            document.querySelectorAll('.inline-edit').forEach(input => {
        input.setAttribute('data-original', input.value);

        const field = input.dataset.field;
        const id = input.dataset.id;

        if (input.type === 'date') {
          // Tanggal: simpan hanya saat keluar field (blur) atau tekan Enter
          input.addEventListener('blur', function () {
            const val = this.value ? this.value : null;
            if (this.getAttribute('data-original') !== (val || '')) {
              sendInlineUpdate(id, field, val, this);
            }
          });

          input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
              e.preventDefault();
              this.blur(); // trigger simpan
            }
          });
        } else {
          // Field teks (No Voucher)
          input.addEventListener('blur', function () {
            const val = this.value.trim() === '' ? null : this.value.trim();
            if (this.getAttribute('data-original') !== (val || '')) {
              sendInlineUpdate(id, field, val, this);
            }
          });

          input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { 
              e.preventDefault(); 
              this.blur(); 
            }
          });
        }
      });

      // --------- Upload Bukti by NIM: modal handling & AJAX submit ----------
      // --------- Upload Bukti by NIM: modal handling & AJAX submit ----------
document.querySelectorAll('.upload-bukti-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const nim = this.dataset.nim || '';
    const voucher = this.dataset.voucher || '';

    // Isi otomatis dengan pengecekan aman
    const nimInput = document.getElementById('nim_murid_baru_input');
    const voucherInput = document.getElementById('no_voucher_input');
    const fileInput = document.getElementById('bukti_penyerahan_input');
    const tanggalInput = document.getElementById('tanggal_penyerahan_input');

    if (nimInput) nimInput.value = nim;
    if (voucherInput) voucherInput.value = voucher;
    if (fileInput) fileInput.value = '';
    if (tanggalInput) tanggalInput.value = '';

    // Reset alert
    const alertEl = document.getElementById('uploadBuktiAlert');
    if (alertEl) {
      alertEl.classList.add('d-none');
    }

    // Tampilkan modal
    const modalEl = document.getElementById('uploadBuktiModal');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    } else {
      console.error('Modal uploadBuktiModal tidak ditemukan!');
    }
  });
});

      const uploadForm = document.getElementById('uploadBuktiForm');
if (uploadForm) {
    uploadForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('uploadBuktiSubmitBtn');
        btn.disabled = true;
        const origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengunggah...';

        const formData = new FormData(uploadForm);
        const url = "{{ route('voucher.uploadBuktiByNim') }}";

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });

            const data = await res.json();
            const alertEl = document.getElementById('uploadBuktiAlert');
            alertEl.classList.remove('d-none');

            if (!res.ok) {
                alertEl.className = 'alert alert-danger';
                alertEl.innerText = data.message || 'Gagal mengunggah bukti.';
            } else {
                alertEl.className = 'alert alert-success';
                alertEl.innerText = data.message || 'Berhasil!';
                
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('uploadBuktiModal')).hide();
                    window.location.reload();
                }, 1200);
            }
        } catch (err) {
            console.error(err);
            const alertEl = document.getElementById('uploadBuktiAlert');
            alertEl.classList.remove('d-none');
            alertEl.className = 'alert alert-danger';
            alertEl.innerText = 'Terjadi kesalahan koneksi.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    });
}

      // Bukti preview modal handler
      document.querySelectorAll('.bukti-preview-trigger').forEach(el => {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          const type = this.dataset.type || 'image';
          const src = this.dataset.src || '';
          const modalEl = document.getElementById('buktiPreviewModal');
          const modal = new bootstrap.Modal(modalEl);

          document.getElementById('buktiPreviewImageWrap').classList.add('d-none');
          document.getElementById('buktiPreviewPdfWrap').classList.add('d-none');
          document.getElementById('buktiPreviewOtherWrap').classList.add('d-none');
          document.getElementById('buktiPreviewImage').src = '';
          document.getElementById('buktiPreviewPdf').src = '';
          document.getElementById('buktiPreviewOtherLink').href = '#';

          if (!src) {
            document.getElementById('buktiPreviewImageWrap').classList.remove('d-none');
            document.getElementById('buktiPreviewImage').alt = 'Tidak ada file';
          } else if (type === 'image') {
            document.getElementById('buktiPreviewImage').src = src;
            document.getElementById('buktiPreviewImageWrap').classList.remove('d-none');
          } else if (type === 'pdf') {
            document.getElementById('buktiPreviewPdf').src = src;
            document.getElementById('buktiPreviewPdfWrap').classList.remove('d-none');
          } else {
            const link = document.getElementById('buktiPreviewOtherLink');
            link.href = src;
            link.innerText = 'Buka file di tab baru';
            document.getElementById('buktiPreviewOtherWrap').classList.remove('d-none');
          }

          modal.show();
        });
      });

      const previewModalEl = document.getElementById('buktiPreviewModal');
      if (previewModalEl) {
        previewModalEl.addEventListener('hidden.bs.modal', function () {
          const pdf = document.getElementById('buktiPreviewPdf');
          if (pdf) pdf.src = '';
        });
      }
    });
  </script>
@endpush
@endsection