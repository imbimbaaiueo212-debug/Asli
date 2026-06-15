@extends('layouts.app')

@section('content')
<h2>Histori Voucher: {{ $voucher->voucher }}</h2>
<a href="{{ route('voucher.index') }}" class="btn btn-secondary mb-3">Kembali</a>

<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Histori Penggunaan</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>NO</th>
                        <th>NO VOUCHER</th>
                        <th>TANGGAL PENYERAHAN</th>
                        <th>TANGGAL PEMAKAIAN</th>
                        <th>NIM</th>
                        <th>NAMA MURID</th>
                        <th>NOMINAL</th>
                        <th>STATUS</th>
                        <th>BUKTI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histori as $index => $h)
                    @php
                        // ambil path bukti dari histori (sesuaikan nama kolom jika beda)
                        $rawPath = $h->bukti_penggunaan_path ?? $h->bukti_penyerahan_path ?? null;

                        // jika path tersimpan dengan prefix 'public/...' -> bersihkan
                        $cleanPath = null;
                        if ($rawPath) {
                            $cleanPath = \Illuminate\Support\Str::startsWith($rawPath, 'public/') ? preg_replace('#^public/#', '', $rawPath) : $rawPath;
                        }

                        $buktiUrl = $cleanPath ? asset('storage/' . ltrim($cleanPath, '/')) : null;
                        $ext = $cleanPath ? strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION)) : null;

                        // nominal: gunakan field nominal jika ada, kalau tidak fallback per jumlah_voucher
                        $nominalVal = $h->nominal ?? ($h->jumlah_voucher ? ($h->jumlah_voucher * 50000) : 0);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $voucher->voucher }}</td>
                        <td>
                            @if($h->tanggal)
                                {{ \Carbon\Carbon::parse($h->tanggal)->format('d-m-Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($h->tanggal_pemakaian)
                                {{ \Carbon\Carbon::parse($h->tanggal_pemakaian)->format('d-m-Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $h->nim }}</td>
                        <td>{{ $h->nama_murid }}</td>
                        <td>Rp {{ number_format($nominalVal, 0, ',', '.') }}</td>
                        <td>
                            @if($h->tanggal_pemakaian)
                                <span class="badge bg-success">Sudah Dipakai</span>
                            @else
                                <span class="badge bg-warning text-dark">Belum Dipakai</span>
                            @endif
                        </td>

                        {{-- BUKTI: thumbnail + upload per baris --}}
                        <td class="align-middle text-center" style="min-width:170px;">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <div class="preview-wrapper" id="preview-{{ $h->id }}" style="min-height:36px">
                                    @if($buktiUrl)
                                        @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                                            <a href="javascript:void(0)"
                                               class="bukti-preview-trigger"
                                               data-type="image"
                                               data-src="{{ $buktiUrl }}"
                                               title="Lihat bukti">
                                                <img src="{{ $buktiUrl }}" alt="bukti" id="thumb-{{ $h->id }}" style="height:36px; width:auto; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,0.08)">
                                            </a>
                                        @elseif($ext === 'pdf')
                                            <span class="badge bg-secondary">PDF</span>
                                            <a href="{{ $buktiUrl }}" class="ms-1 small" target="_blank" rel="noopener">Buka</a>
                                        @else
                                            <a href="{{ $buktiUrl }}" target="_blank" rel="noopener">Buka</a>
                                        @endif
                                    @else
                                        <span class="text-muted small">Belum ada bukti</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <label class="btn btn-sm btn-outline-primary mb-0" for="file-{{ $h->id }}">Upload</label>
                                    <input type="file"
                                           id="file-{{ $h->id }}"
                                           name="bukti_penyerahan"
                                           accept=".jpg,.jpeg,.png,.pdf"
                                           class="d-none histori-file-input"
                                           data-hist-id="{{ $h->id }}">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary ms-1 view-bukti-btn"
                                            data-src="{{ $buktiUrl }}"
                                            data-ext="{{ $ext }}"
                                            {{ $buktiUrl ? '' : 'disabled' }}>
                                        Preview
                                    </button>
                                </div>

                                <div class="mt-1 w-100">
                                    <div class="upload-status small text-muted text-center" id="status-{{ $h->id }}"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Belum ada histori penggunaan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal preview sederhana --}}
<div class="modal fade" id="buktiPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Preview Bukti</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div id="buktiPreviewContent">
          {{-- content akan di-inject oleh JS --}}
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('buktiPreviewModal');
    const previewContent = document.getElementById('buktiPreviewContent');

    // helper create/show modal
    function showPreview(type, src) {
        previewContent.innerHTML = '';
        if (!src) return alert('Belum ada bukti.');
        if (type === 'image') {
            const img = document.createElement('img');
            img.src = src;
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.className = 'img-fluid';
            previewContent.appendChild(img);
        } else if (type === 'pdf') {
            const iframe = document.createElement('iframe');
            iframe.src = src;
            iframe.style.width = '100%';
            iframe.style.height = '70vh';
            iframe.setAttribute('frameborder', '0');
            previewContent.appendChild(iframe);
        } else {
            const a = document.createElement('a');
            a.href = src;
            a.target = '_blank';
            a.rel = 'noopener';
            a.textContent = 'Buka file di tab baru';
            previewContent.appendChild(a);
        }
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }

    // attach preview trigger (existing thumbnails)
    document.querySelectorAll('.bukti-preview-trigger').forEach(function(el) {
        el.addEventListener('click', function(e) {
            const type = el.getAttribute('data-type');
            const src = el.getAttribute('data-src');
            showPreview(type, src);
        });
    });

    // attach preview buttons (per-row)
    document.querySelectorAll('.view-bukti-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const src = btn.getAttribute('data-src');
            const ext = (btn.getAttribute('data-ext') || '').toLowerCase();
            if (!src) return alert('Belum ada bukti.');
            showPreview(['jpg','jpeg','png','gif','webp'].includes(ext) ? 'image' : (ext === 'pdf' ? 'pdf' : 'other'), src);
        });
    });

    // CSRF token
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // handle file input change (per-row) -> AJAX upload
    document.querySelectorAll('.histori-file-input').forEach(input => {
        input.addEventListener('change', async function(e) {
            const file = input.files[0];
            if (!file) return;
            const histId = input.getAttribute('data-hist-id');
            const statusEl = document.getElementById('status-' + histId);
            const previewWrapper = document.getElementById('preview-' + histId);

            // basic client-side validation
            const allowed = ['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
            if (!allowed.includes(file.type)) {
                statusEl.innerText = 'Tipe file tidak diperbolehkan.';
                input.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                statusEl.innerText = 'Ukuran file maksimal 5MB.';
                input.value = '';
                return;
            }

            statusEl.innerText = 'Mengunggah...';

            const formData = new FormData();
            formData.append('bukti_penyerahan', file);

            try {
                const resp = await fetch("{{ url('') }}/voucher/histori/" + histId + "/upload-bukti", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    body: formData,
                });

                const data = await resp.json();

                if (!resp.ok || data.status !== 'ok') {
                    statusEl.innerText = data?.message || 'Upload gagal.';
                    console.error('upload error', data);
                    input.value = '';
                    return;
                }

                // sukses: update preview area
                const newUrl = data.url;
                const newExt = (data.ext || '').toLowerCase();

                if (newExt && ['jpg','jpeg','png','gif','webp'].includes(newExt)) {
                    // set or replace img
                    let img = document.getElementById('thumb-' + histId);
                    if (!img) {
                        previewWrapper.innerHTML = '';
                        img = document.createElement('img');
                        img.id = 'thumb-' + histId;
                        img.style.height = '36px';
                        img.style.borderRadius = '4px';
                        img.style.boxShadow = '0 1px 3px rgba(0,0,0,0.08)';
                        previewWrapper.appendChild(img);
                    }
                    img.src = newUrl;

                    // attach click to open modal
                    img.closest('a')?.remove();
                    const a = document.createElement('a');
                    a.href = 'javascript:void(0)';
                    a.className = 'bukti-preview-trigger';
                    a.setAttribute('data-type','image');
                    a.setAttribute('data-src', newUrl);
                    a.title = 'Lihat bukti';
                    a.appendChild(img.cloneNode(true));
                    previewWrapper.innerHTML = '';
                    previewWrapper.appendChild(a);
                    a.addEventListener('click', function(){ showPreview('image', newUrl); });
                } else {
                    previewWrapper.innerHTML = '<span class="badge bg-secondary">FILE</span> <a href="'+newUrl+'" class="ms-1 small" target="_blank">Buka</a>';
                }

                // enable preview button and set data
                const btn = input.closest('td').querySelector('.view-bukti-btn');
                if (btn) {
                    btn.removeAttribute('disabled');
                    btn.setAttribute('data-src', newUrl);
                    btn.setAttribute('data-ext', newExt);
                }

                statusEl.innerText = 'Sukses diunggah';
                input.value = '';
            } catch (err) {
                console.error(err);
                statusEl.innerText = 'Terjadi kesalahan saat upload.';
                input.value = '';
            }

            setTimeout(() => { if (statusEl) statusEl.innerText = ''; }, 3000);
        });
    });

    // bersihkan ketika modal ditutup
    modalEl.addEventListener('hidden.bs.modal', function () {
        previewContent.innerHTML = '';
    });
});
</script>
@endpush
    