{{-- resources/views/wheels/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Roda Voucher biMBA')

@section('content')
  <div class="container py-4">
    <h3 class="text-center mb-3">🎡 Roda Undian Voucher biMBA-AIUEO 🎁</h3>

    <div style="display:flex; gap:24px; align-items:flex-start; justify-content:center; flex-wrap:wrap;">

      <!-- LEFT: roda -->
      <div id="wheel-wrap" style="position: relative; display: inline-block; width: 520px; height: 520px;">
        <!-- Wheel canvas -->
        <canvas id="wheel" width="520" height="520"
          style="display:block; width:100%; height:100%; border-radius:50%; box-shadow: 0 8px 30px rgba(15,23,42,0.08);"></canvas>

        <!-- STOP LINE -->
        <div id="stopLine" aria-hidden="true" style="
              position: absolute;
              left: 50%;
              top: calc(100% - 72px);
              transform: translateX(-50%);
              width: 6px;
              height: 48px;
              background: rgba(0,0,0,0.08);
              border-radius: 4px;
              z-index: 25;
              pointer-events: none;
              box-shadow: 0 2px 6px rgba(0,0,0,0.08);
          "></div>

        <!-- Pointer -->
        <div id="pointer" style="
          position: absolute;
          left: 50%;
          top: calc(100% - 15px);
          transform: translateX(-50%);
          width: 0;
          height: 0;
          border-left: 18px solid transparent;
          border-right: 18px solid transparent;
          border-top: 0;
          border-bottom: 30px solid #e11d48;
          z-index: 30;
          filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
        " aria-hidden="true"></div>
      </div>

      <!-- RIGHT: checklist nama + riwayat -->
      <div style="min-width:320px; max-width:340px;">
        <div class="card p-3 mb-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <h5 class="mb-0">Pilih Nama (untuk undian)</h5>
              <small class="text-muted">Centang 1 nama lalu klik <strong>Putar!</strong></small>
            </div>
            <div class="text-end">
              <button id="refreshNames" class="btn btn-sm btn-outline-secondary">Refresh</button>
            </div>
          </div>

          <div id="nameList" style="max-height: 360px; overflow:auto; padding-right:6px;">
            <div id="loadingNames" class="text-center text-muted py-4">Memuat daftar nama…</div>
          </div>

          <div class="mt-3 d-flex gap-2">
            <button id="clearAllUsed" class="btn btn-sm btn-outline-danger" title="Hapus semua terpakai (UI)">Hapus Semua Terpakai</button>
            <button id="selectRandom" class="btn btn-sm btn-outline-primary">Pilih Random</button>
          </div>

          <small class="text-muted d-block mt-2">Nama yang sudah menang akan otomatis hilang dari daftar.</small>
        </div>

        <!-- Riwayat Terbaru (ringkas) -->
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <h6 class="mb-0">Riwayat Terbaru</h6>
              <small class="text-muted">Pemenang terakhir</small>
            </div>
            <div>
              <button id="refreshHistory" class="btn btn-sm btn-outline-secondary">Refresh</button>
            </div>
          </div>

          <div id="recentHistory" style="max-height: 220px; overflow:auto; padding-right:6px;">
            <div class="text-center text-muted py-3">Memuat riwayat…</div>
          </div>

          <div class="mt-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">Klik "Lihat Semua" untuk riwayat lengkap.</small>
            <button id="openHistoryModal" class="btn btn-sm btn-outline-primary">Lihat Semua</button>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
          <button id="spin" class="btn btn-primary btn-lg me-2" disabled>Putar!</button>
          <button id="reset" class="btn btn-outline-secondary">Reset</button>
      <button id="backBtn" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
      </button>
    </div>

    <div class="mt-4 text-center">
      <h4 id="winner" class="fw-bold text-success"></h4>
    </div>
  </div>

  <!-- Modal: full history (RENAMED to avoid clash with student history modal) -->
  <div class="modal fade" id="winnersHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Riwayat Pemenang</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div id="historyTableContainer">
            <div class="text-center text-muted py-4">Memuat riwayat…</div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <div>
            <small id="historyMeta" class="text-muted"></small>
          </div>
          <div>
            <button id="historyPrev" class="btn btn-sm btn-outline-secondary">Prev</button>
            <button id="historyNext" class="btn btn-sm btn-outline-secondary">Next</button>
            <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    #wheel {
      display: block;
      margin: 0 auto;
      background: radial-gradient(circle at center, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.05));
    }

    #stopLine.flash {
      animation: stopFlash 1.2s ease-in-out;
      background: linear-gradient(180deg, rgba(255, 230, 120, 0.95), rgba(255, 160, 0, 0.95));
      box-shadow: 0 6px 18px rgba(255, 160, 0, 0.28);
    }

    @keyframes stopFlash {
      0% { transform: translateX(-50%) scaleY(0.8); opacity: 0.45; }
      30% { transform: translateX(-50%) scaleY(1.15); opacity: 1; }
      70% { transform: translateX(-50%) scaleY(0.95); opacity: 1; }
      100% { transform: translateX(-50%) scaleY(1); opacity: 1; }
    }

    #nameList .form-check {
      padding: 6px 4px;
      border-bottom: 1px dashed rgba(0, 0, 0, 0.04);
    }

    #nameList .form-check:last-child { border-bottom: none; }

    .badge-new {
      background: linear-gradient(90deg,#10b981,#34d399);
      color: #fff;
      font-weight:600;
      padding: 0.25rem 0.45rem;
      border-radius: 999px;
      font-size: 0.75rem;
      box-shadow: 0 1px 4px rgba(16,185,129,0.12);
    }

    .referrer { font-weight:700; letter-spacing:0.2px; }
    .brought { font-weight:500; color:#6b7280; font-size:0.95rem; }
  </style>
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

  <script>
    (function () {
      /* -------------------------
         Configuration — routes & DOM
         ------------------------- */
      const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const NAMES_URL = '{{ route("wheels.names") }}';
      const SPIN_URL  = '{{ route("wheels.spin") }}';
      const HISTORY_URL = '{{ route("wheels.history") }}';

      const canvas = document.getElementById('wheel');
      const ctx = canvas.getContext && canvas.getContext('2d') ? canvas.getContext('2d') : null;
      const spinBtn = document.getElementById('spin');
      const resetBtn = document.getElementById('reset');
      const backBtn = document.getElementById('backBtn');
      const winnerEl = document.getElementById('winner');
      const stopLine = document.getElementById('stopLine');
      const nameListEl = document.getElementById('nameList');
      const refreshBtn = document.getElementById('refreshNames');
      const selectRandomBtn = document.getElementById('selectRandom');
      const clearAllUsedBtn = document.getElementById('clearAllUsed');
      const refreshHistoryBtn = document.getElementById('refreshHistory');
      const recentEl = document.getElementById('recentHistory');
      const openHistoryModalBtn = document.getElementById('openHistoryModal');
      const historyTableContainer = document.getElementById('historyTableContainer');
      const historyPrevBtn = document.getElementById('historyPrev');
      const historyNextBtn = document.getElementById('historyNext');
      const historyMeta = document.getElementById('historyMeta');
      const winnersHistoryModalEl = document.getElementById('winnersHistoryModal');

      let winnersHistoryModal;
      try { if (winnersHistoryModalEl) winnersHistoryModal = new bootstrap.Modal(winnersHistoryModalEl); } catch (e) { winnersHistoryModal = null; }

      /* -------------------------
         Wheel visuals & data
         ------------------------- */
      let size = Math.min(520, Math.max(360, Math.min(window.innerWidth * 0.6, 520)));
      if (canvas) {
        canvas.width = size; canvas.height = size;
      }
      const wrap = canvas ? canvas.parentElement : null;
      if (wrap) { wrap.style.width = `${size}px`; wrap.style.height = `${size}px`; }
      const r = size / 2;

      const vouchers = [
        'Rp 50.000','Rp 100.000','Rp 150.000','Rp 200.000','Rp 250.000','Rp 300.000','Rp 350.000','Rp 400.000',
        'Rp 450.000','Rp 500.000','Rp 550.000','Rp 600.000','Rp 650.000','Rp 700.000','Rp 750.000','Rp 800.000',
        'Rp 850.000','Rp 900.000','Rp 950.000','Rp 1.000.000','Rp 1.050.000','Rp 1.100.000','Rp 1.150.000','Rp 1.200.000'
      ];

      let rotation = 0, spinning = false;
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      const audioCtx = AudioContext ? new AudioContext() : null;
      function tickSound(vol = 0.06, freq = 1200, len = 0.01) { if (!audioCtx) return; const o = audioCtx.createOscillator(), g = audioCtx.createGain(); o.type = 'square'; o.frequency.value = freq; g.gain.value = vol; o.connect(g); g.connect(audioCtx.destination); o.start(); setTimeout(() => o.stop(), len * 1000); }

      function drawWheel() {
        if (!ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const arc = 2 * Math.PI / vouchers.length;
        for (let i = 0; i < vouchers.length; i++) {
          const angle = i * arc;
          ctx.beginPath(); ctx.moveTo(r, r);
          ctx.fillStyle = `hsl(${i * 360 / vouchers.length} 72% 70%)`;
          ctx.arc(r, r, r - 2, angle, angle + arc);
          ctx.closePath(); ctx.fill();
          ctx.save(); ctx.translate(r, r); ctx.rotate(angle + arc / 2);
          ctx.fillStyle = '#111'; ctx.textAlign = 'right';
          const fontSize = Math.max(12, Math.floor(size / 32));
          ctx.font = `bold ${fontSize}px Inter, Arial`;
          ctx.fillText(vouchers[i], r - 12, 5);
          ctx.restore();
        }
        ctx.beginPath(); ctx.arc(r, r, r * 0.18, 0, 2 * Math.PI); ctx.fillStyle = '#ffffffee'; ctx.fill();
        ctx.lineWidth = 2; ctx.strokeStyle = 'rgba(0,0,0,0.06)'; ctx.stroke();
      }

      function easeOut(t) { return 1 - Math.pow(1 - t, 3); }
      function reset() { rotation = 0; if (canvas) canvas.style.transform = 'rotate(0deg)'; winnerEl.innerHTML = ''; spinning = false; disableSpin(); }
      function norm360(d) { return ((d % 360) + 360) % 360; }
      function burstConfetti() { if (window.confetti) { confetti({ particleCount: 80, startVelocity: 30, spread: 160, origin: { x: 0.5, y: 0.4 } }); } }

      /* -------------------------
         NAMES (Referrer & Brought)
         ------------------------- */
      let availableNames = [];

      async function loadNames() {
        if (nameListEl) nameListEl.innerHTML = '<div id="loadingNames" class="text-center text-muted py-4">Memuat daftar nama…</div>';
        try {
          const res = await fetch(NAMES_URL + '?_=' + Date.now(), { cache: 'no-store', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }});
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const json = await res.json();
          availableNames = json.data || json || [];

          // normalize fields with fallbacks
          availableNames = availableNames.map(it => {
            const ref = it.referrer_name || it.referer || it.ref || it.name || '';
            const brought = it.brought_name || it.student_name || it.brought || it.child_name || '';
            const isNew = !!(it.is_new || it.is_new_student || it.brought_is_new || (it.status && typeof it.status === 'string' && it.status.toLowerCase() === 'baru'));
            return Object.assign({}, it, { referrer_name: (ref||'').toString().trim(), brought_name: (brought||'').toString().trim(), is_new_student: isNew });
          });

          renderNameList();
        } catch (err) {
          console.error('loadNames error', err);
          if (nameListEl) nameListEl.innerHTML = '<div class="text-danger py-3">Gagal memuat nama. Tekan Refresh.</div>';
        }
      }

      function escapeHtml(s) { return (s === null || s === undefined) ? '' : (s + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

      function renderNameList() {
    if (!nameListEl) return;
    if (!availableNames || !availableNames.length) {
        nameListEl.innerHTML = '<div class="text-center text-muted py-4">Tidak ada nama tersedia.</div>';
        disableSpin();
        return;
    }
    nameListEl.innerHTML = '';

    const list = [...availableNames].sort((a,b) => {
        const ar = (a.referrer_name||a.name||'').toString().toUpperCase();
        const br = (b.referrer_name||b.name||'').toString().toUpperCase();
        return ar.localeCompare(br);
    });

    for (const it of list) {
        const rowHash   = it.row_hash || it.rowHash || '';
        const ref       = (it.referrer_name || it.name || '').toString().trim();
        const brought   = (it.brought_name || it.student_name || it.child_name || '').toString().trim();
        const isNew     = !!it.is_new_student;

        const id = 'n_' + (rowHash || Math.random().toString(36).slice(2,9));

        const div = document.createElement('div');
        div.className = 'form-check';

        div.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <input class="form-check-input name-checkbox" type="radio" name="selected_name" 
                           id="${id}" 
                           data-row_hash="${escapeHtml(rowHash)}"
                           data-ref="${escapeHtml(ref)}"
                           data-brought="${escapeHtml(brought)}"
                           ${isNew ? 'data-is_new="1"' : ''}>
                    <label class="form-check-label" for="${id}" style="cursor:pointer;">
                        <div class="referrer fw-bold text-primary">${escapeHtml(ref.toUpperCase())} (Humas)</div>
                        ${brought ? `<div class="brought fw-bold text-success">${escapeHtml(brought)}${isNew ? ' (Murid Baru)' : ''}</div>` : ''}
                    </label>
                </div>

                <!-- Tombol Salin Link untuk Orang Tua -->
                <button onclick="copyParentSpinLink('${escapeHtml(rowHash)}', '${escapeHtml(brought)}', '${escapeHtml(ref)}')" 
                        class="btn btn-sm btn-outline-success" 
                        title="Salin link spin untuk orang tua murid"
                        style="font-size:0.8rem; padding:4px 8px;">
                    📋 Link Orang Tua
                </button>
            </div>
        `;

        nameListEl.appendChild(div);
    }

    nameListEl.addEventListener('change', onNameChange);
    disableSpin();
}

      selectRandomBtn && selectRandomBtn.addEventListener('click', () => {
        const radios = document.querySelectorAll('.name-checkbox'); if (!radios.length) return;
        radios.forEach(r => r.checked = false);
        const idx = Math.floor(Math.random() * radios.length); radios[idx].checked = true; onNameChange();
      });

      clearAllUsedBtn && clearAllUsedBtn.addEventListener('click', () => {
        document.querySelectorAll('.form-check .form-check-input[disabled]').forEach(el => el.closest('.form-check')?.remove());
      });

      refreshBtn && refreshBtn.addEventListener('click', async () => { await loadNames(); });

      function enableSpin() { if (spinBtn) { spinBtn.removeAttribute('disabled'); spinBtn.classList.remove('disabled'); } }
      function disableSpin() { if (spinBtn) { spinBtn.setAttribute('disabled', 'true'); spinBtn.classList.add('disabled'); } }
      function onNameChange() { const any = document.querySelector('input[name="selected_name"]:checked') !== null; if (any) enableSpin(); else disableSpin(); }

      /* -------------------------
         HISTORY (recent + modal)
         ------------------------- */
      async function loadRecentHistory(limit = 6) {
        if (recentEl) recentEl.innerHTML = '<div class="text-center text-muted py-3">Memuat riwayat…</div>';
        try {
          const res = await fetch(HISTORY_URL + '?per_page=' + limit + '&_=' + Date.now(), { cache: 'no-store', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }});
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const json = await res.json();
          const rows = json.data || json || [];
          if (!rows || rows.length === 0) { if (recentEl) recentEl.innerHTML = '<div class="text-center text-muted py-3">Belum ada pemenang.</div>'; return; }
          const list = document.createElement('div'); list.className = 'list-group list-group-flush';
          rows.slice(0, limit).forEach(r => {
            const item = document.createElement('div'); item.className = 'list-group-item d-flex justify-content-between align-items-start';
            const sub = r.new_student ? `<div class="text-muted small">Murid: ${escapeHtml(r.new_student.nama || r.new_student.name || '')}</div>` : '';
            item.innerHTML = `<div><div class="fw-semibold">${escapeHtml(r.name)}</div>${sub}<small class="text-muted">${escapeHtml(r.won_at || r.created_at || '')}</small></div>
                              <div class="text-end"><div class="badge bg-success">${escapeHtml(r.voucher || '')}</div></div>`;
            list.appendChild(item);
          });
          if (recentEl) { recentEl.innerHTML = ''; recentEl.appendChild(list); }
        } catch (err) {
          console.error('loadRecentHistory err', err); if (recentEl) recentEl.innerHTML = '<div class="text-danger py-3">Gagal memuat riwayat.</div>';
        }
      }

      let historyCurrentPage = 1;
      async function loadHistoryPage(page = 1, perPage = 12) {
        if (historyTableContainer) historyTableContainer.innerHTML = '<div class="text-center text-muted py-4">Memuat riwayat…</div>';
        try {
          const res = await fetch(HISTORY_URL + `?page=${page}&per_page=${perPage}&_=` + Date.now(), { cache: 'no-store', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }});
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const json = await res.json(); const rows = json.data || [];
          if (!rows.length) { if (historyTableContainer) historyTableContainer.innerHTML = '<div class="text-center text-muted py-4">Belum ada pemenang.</div>'; historyMeta.textContent = ''; historyPrevBtn.disabled = true; historyNextBtn.disabled = true; return; }
          const table = document.createElement('table'); table.className = 'table table-sm table-hover';
          table.innerHTML = `<thead><tr><th>#</th><th>Nama</th><th>New Murid</th><th>Voucher</th><th>Waktu</th></tr></thead>`;
          const tb = document.createElement('tbody');
          rows.forEach((r, idx) => {
            const newStudentName = r.new_student ? (r.new_student.nama || r.new_student.name || '') : '';
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${((json.current_page - 1) * json.per_page) + idx + 1}</td>
                            <td>${escapeHtml(r.name)}</td>
                            <td>${escapeHtml(newStudentName)}</td>
                            <td>${escapeHtml(r.voucher || '')}</td>
                            <td>${escapeHtml(r.won_at || r.created_at || '')}</td>`;
            tb.appendChild(tr);
          });
          table.appendChild(tb);
          if (historyTableContainer) { historyTableContainer.innerHTML = ''; historyTableContainer.appendChild(table); }
          historyCurrentPage = json.current_page || page;
          const last = json.last_page || 1;
          if (historyMeta) historyMeta.textContent = `Halaman ${historyCurrentPage} dari ${last} — Total ${json.total ?? rows.length}`;
          historyPrevBtn.disabled = historyCurrentPage <= 1;
          historyNextBtn.disabled = historyCurrentPage >= last;
        } catch (err) {
          console.error('loadHistoryPage err', err); if (historyTableContainer) historyTableContainer.innerHTML = '<div class="text-danger py-3">Gagal memuat riwayat.</div>'; if (historyMeta) historyMeta.textContent = '';
        }
      }

      refreshHistoryBtn && refreshHistoryBtn.addEventListener('click', () => loadRecentHistory(6));
      openHistoryModalBtn && openHistoryModalBtn.addEventListener('click', () => { if (winnersHistoryModal) winnersHistoryModal.show(); loadHistoryPage(1, 12); });
      historyPrevBtn && historyPrevBtn.addEventListener('click', () => { if (historyCurrentPage > 1) loadHistoryPage(historyCurrentPage - 1, 12); });
      historyNextBtn && historyNextBtn.addEventListener('click', () => { loadHistoryPage(historyCurrentPage + 1, 12); });

      /* -------------------------
         SPIN logic
         ------------------------- */
      async function spin() {
        if (spinning) return;
        const checked = document.querySelector('input[name="selected_name"]:checked');
        if (!checked) { winnerEl.textContent = 'Pilih satu nama dulu sebelum memutar.'; return; }

        spinning = true; winnerEl.textContent = 'Sedang memutar...';
        if (audioCtx && audioCtx.state === 'suspended') { try { await audioCtx.resume(); } catch (e) { /* ignore */ } }

        const payload = {
          row_hash: checked.dataset.row_hash || null,
          referrer: checked.dataset.ref || null,
          brought: checked.dataset.brought || null,
          name: checked.value || null
        };

        let data;
        try {
          const res = await fetch(SPIN_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': CSRF,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
          });

          const text = await res.text();

          if (!res.ok) {
            if (res.status === 419) {
              winnerEl.textContent = 'Spin gagal: sesi kadaluarsa atau CSRF token mismatch. Silakan muat ulang halaman dan coba lagi.';
              spinning = false;
              return;
            }
            try {
              const errJson = JSON.parse(text);
              winnerEl.textContent = errJson.error || errJson.message || ('Spin gagal (HTTP ' + res.status + ')');
            } catch (e) {
              winnerEl.textContent = 'Spin gagal (HTTP ' + res.status + ')';
            }
            spinning = false;
            return;
          }

          try { data = JSON.parse(text); } catch (e) { data = {}; }

        } catch (err) {
          console.error('spin request err', err); winnerEl.textContent = 'Gagal memanggil backend.'; spinning = false; return;
        }

        const sectorAngle = 360 / vouchers.length;
        let targetSector = -1;
        if (typeof data.voucher_index === 'number') { targetSector = parseInt(data.voucher_index, 10); if (isNaN(targetSector) || targetSector < 0 || targetSector >= vouchers.length) targetSector = -1; }
        if (targetSector === -1 && data.voucher) { const voucherLower = (data.voucher || '').toString().toLowerCase().trim(); targetSector = vouchers.findIndex(v => (v || '').toLowerCase().trim() === voucherLower); }
        if (targetSector === -1) targetSector = Math.floor(Math.random() * vouchers.length);

        const sectorCenterDeg = targetSector * sectorAngle + (sectorAngle / 2);
        const pointerAngle = 90;
        const finalNorm = ((pointerAngle - sectorCenterDeg) % 360 + 360) % 360;
        const targetOffset = ((-finalNorm) % 360 + 360) % 360;
        const minRounds = 4;
        const extraFull = (Math.floor(Math.random() * 3) * 360);
        const spinAngle = (minRounds * 360) + extraFull + targetOffset;

        // animate
        const duration = 4200 + Math.random() * 1000;
        const start = performance.now();
        let lastTickSector = -1;

        function animate(now) {
          const t = Math.min((now - start) / duration, 1);
          const eased = easeOut(t);
          rotation = spinAngle * eased;
          if (canvas) canvas.style.transform = `rotate(${-rotation}deg)`;

          const normalized = norm360(-rotation);
          const relative = norm360(pointerAngle - normalized);
          const currentSector = Math.floor(relative / sectorAngle) % vouchers.length;
          if (currentSector !== lastTickSector) {
            lastTickSector = currentSector;
            tickSound(0.04, 1200 - (currentSector % 6) * 60, 0.01);
          }

          if (t < 1) requestAnimationFrame(animate);
          else {
            spinning = false;
            const selectedVoucher = vouchers[targetSector];

            // display winner — use format requested
            const refName = (data.referrer || data.name || payload.referrer || payload.name || checked.value || '').toString().trim();
            const broughtName = (data.brought || data.new_student?.nama || payload.brought || checked.dataset.brought || '').toString().trim();
            const isNew = !!(data.new_student || checked.dataset.is_new === '1' || checked.dataset.is_new === 'true' || checked.dataset.is_new === true);

            let winnerHtml = `<div style="font-size:1.05rem;"><strong>${escapeHtml((refName || '').toUpperCase())} (Murid Humas)</strong></div>`;
            if (broughtName) {
              winnerHtml += `<div style="font-size:0.95rem; margin-top:4px;">${escapeHtml(broughtName)}${isNew ? '(murid Baru)' : ''} (Murid Baru)</div>`;
            }
            winnerHtml += `<div style="margin-top:8px;">mendapatkan <strong>${escapeHtml(selectedVoucher)}</strong> 🎁</div>`;

            if (winnerEl) winnerEl.innerHTML = `🎉 ${winnerHtml}`;
            burstConfetti(); tickSound(0.14, 880, 0.06);

            if (stopLine) { stopLine.classList.remove('flash'); void stopLine.offsetWidth; stopLine.classList.add('flash'); }

            // Remove winner from UI and local list
            if (payload.row_hash) {
              const el = document.querySelector(`input[name="selected_name"][data-row_hash="${payload.row_hash}"]`);
              if (el) el.closest('.form-check')?.remove();
              availableNames = availableNames.filter(x => (x.row_hash || x.rowHash || '') !== payload.row_hash);
            } else {
              const nameToRemove = (data.name || '').trim();
              const el = [...document.querySelectorAll('.name-checkbox')].find(i => (i.value || '').trim() === nameToRemove);
              if (el) el.closest('.form-check')?.remove();
              availableNames = availableNames.filter(x => ((x.name || x.referrer_name || '') || '').trim() !== nameToRemove);
            }

            disableSpin();
            loadRecentHistory(6);

            // safety-check (visual vs expected)
            const finalNormNow = norm360(-rotation);
            const relaNow = norm360(pointerAngle - finalNormNow);
            const sectorNow = Math.floor(relaNow / sectorAngle) % vouchers.length;
            console.log({ finalNormNow, relaNow, sectorNow, expected: targetSector });
            if (sectorNow !== targetSector) console.error('Mismatch: visual landed on sector', sectorNow, 'but expected', targetSector);
          }
        }

        requestAnimationFrame(animate);
      }

      // init
      drawWheel(); reset(); loadNames(); loadRecentHistory(6);

      // events
      spinBtn && spinBtn.addEventListener('click', spin);
      resetBtn && resetBtn.addEventListener('click', () => { if (stopLine) stopLine.classList.remove('flash'); reset(); });
      window.addEventListener('resize', () => {
        size = Math.min(520, Math.max(360, Math.min(window.innerWidth * 0.6, 520)));
        if (canvas) { canvas.width = size; canvas.height = size; }
        if (wrap) { wrap.style.width = `${size}px`; wrap.style.height = `${size}px`; }
        drawWheel(); if (canvas) canvas.style.transform = `rotate(${-rotation}deg)`;
      });
      if (backBtn) backBtn.addEventListener('click', () => window.history.back());
    })();


    async function copyParentSpinLink(rowHash, childName, referrerName) {
    try {
        const params = new URLSearchParams();
        if (rowHash) {
            params.append('row_hash', rowHash);
        } else if (childName) {
            params.append('child_name', childName);
        }

        const baseUrl = `{{ route("wheels.parent.link") }}`;
        if (!baseUrl || baseUrl.includes('{{')) {
            throw new Error('Route belum ter-render');
        }

        const res = await fetch(`${baseUrl}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        });

        const json = await res.json();

        if (!json.success || !json.url) {
            throw new Error(json.error || 'Gagal membuat link');
        }

        // === Perbaikan Clipboard ===
        const textToCopy = json.url;

        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(textToCopy);
            showSuccessToast(`✅ Link untuk ${referrerName || childName} berhasil disalin!`);
        } else {
            // Fallback untuk HTTP / localhost
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            showSuccessToast(`✅ Link untuk ${referrerName || childName} berhasil disalin! (Fallback)`);
        }

    } catch (err) {
        console.error('copyParentSpinLink failed:', err);
        
        let message = '❌ Gagal membuat link. Coba refresh halaman.';
        if (err.message.includes('Route')) {
            message = '❌ Route copy link belum dikonfigurasi dengan benar.';
        }
        
        showErrorToast(message);
    }
}

// Helper Toast Sederhana (jika showCopiedToast tidak ada)
function showSuccessToast(msg) {
    alert(msg); // sementara, ganti dengan toast library Anda nanti
    console.log(msg);
}

function showErrorToast(msg) {
    alert(msg);
    console.error(msg);
}
  </script>
@endpush
