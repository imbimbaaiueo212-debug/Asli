@extends('layouts.plain')

@section('title', 'Roda Voucher biMBA — Publik')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container py-4">
    <h3 class="text-center mb-4">🎡 Roda Undian Voucher biMBA-AIUEO</h3>

    <div class="row justify-content-center">
        <!-- Wheel -->
        <div class="col-12 col-lg-6 mb-4 text-center">
            <div id="wheel-wrap" style="position: relative; display: inline-block; width: 100%; max-width: 520px; aspect-ratio: 1/1;">
                <canvas id="wheel" style="display: block; width: 100%; height: 100%; border-radius: 50%; box-shadow: 0 8px 30px rgba(15,23,42,0.08);"></canvas>

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
                "></div>

                <div id="pointer" style="
                    position: absolute;
                    left: 50%;
                    top: calc(100% - 15px);
                    transform: translateX(-50%);
                    width: 0;
                    height: 0;
                    border-left: 18px solid transparent;
                    border-right: 18px solid transparent;
                    border-bottom: 30px solid #e11d48;
                    z-index: 30;
                    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
                " aria-hidden="true"></div>
            </div>
        </div>

        <!-- Side Content -->
        <div class="col-12 col-lg-5">
            <!-- Pilih Nama -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Pilih Nama (untuk undian)</h5>
                            <small class="text-muted">Pilih 1 nama lalu klik <strong>Putar!</strong></small>
                        </div>
                        <button id="refreshNames" class="btn btn-sm btn-outline-secondary">Refresh</button>
                    </div>

                    <div id="nameList" style="max-height: 320px; overflow-y: auto; padding-right: 8px;">
                        <div class="text-center text-muted py-4">
                            <span class="spinner"></span> Memuat daftar nama…
                        </div>
                    </div>

                    <small class="text-muted d-block mt-3">Nama yang sudah menang akan otomatis hilang dari daftar.</small>
                </div>
            </div>

            <!-- Riwayat -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Riwayat Terbaru</h6>
                            <small class="text-muted">Pemenang terakhir</small>
                        </div>
                        <button id="refreshHistory" class="btn btn-sm btn-outline-secondary">Refresh</button>
                    </div>

                    <div id="recentHistory" style="max-height: 220px; overflow-y: auto; padding-right: 8px;">
                        <div class="text-center text-muted py-4">
                            <span class="spinner"></span> Memuat riwayat…
                        </div>
                    </div>

                    <div class="mt-3 text-end">
                        <button id="openHistoryModal" class="btn btn-sm btn-outline-primary">Lihat Semua</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Putar -->
    <div class="text-center mt-4">
        <button id="spin" class="btn btn-primary btn-lg px-5" disabled aria-label="Putar roda undian">
            PUTAR!
        </button>
    </div>

    <!-- Hasil Pemenang -->
    <div class="mt-4 text-center">
        <h4 id="winner" class="fw-bold text-success"></h4>
    </div>
</div>

<!-- Modal Riwayat -->
<div class="modal fade" id="winnersHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Pemenang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="historyTableContainer">
                    <div class="text-center text-muted py-5">
                        <span class="spinner"></span> Memuat riwayat…
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .spinner {
        display: inline-block;
        width: 15px; height: 15px;
        border: 3px solid rgba(0,0,0,0.1);
        border-top-color: #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    html, body {
        height: auto !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    #wheel-wrap {
        touch-action: none;
    }

    #spin {
        min-height: 62px;
        font-size: 1.25rem;
        font-weight: 600;
        padding: 14px 50px;
        border-radius: 50px;
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.3);
    }

    @media (max-width: 576px) {
        .container.py-4 {
            padding-bottom: 100px !important;
        }
        #wheel-wrap {
            max-width: 92vw;
        }
        #nameList, #recentHistory {
            max-height: 260px;
        }
        #spin {
            width: 100%;
            max-width: 320px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script>
(function(){
    // ==================== KONFIGURASI ====================
    const SPIN_URL = {!! json_encode($spin_post_url ?? route('wheels.public.spin')) !!};
    const NAMES_URL = '{{ route("wheels.names") }}?' + new URLSearchParams({ row_hash: '{{ e($row_hash ?? "") }}' }).toString();
    const HISTORY_URL = '{{ route("wheels.history") }}?' + new URLSearchParams({ row_hash: '{{ e($row_hash ?? "") }}' }).toString();

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // DOM Elements
    const canvas = document.getElementById('wheel');
    const ctx = canvas ? canvas.getContext('2d') : null;
    const spinBtn = document.getElementById('spin');
    const resetBtn = document.getElementById('reset');
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
    const historyMeta = document.getElementById('historyMeta');
    const winnersHistoryModalEl = document.getElementById('winnersHistoryModal');

    let winnersHistoryModal;
    try{ 
        if(winnersHistoryModalEl) winnersHistoryModal = new bootstrap.Modal(winnersHistoryModalEl); 
    }catch(e){ 
        winnersHistoryModal = null; 
    }

    let size = 520;
    let rotation = 0;
    let spinning = false;
    let animationFrameId = null;

    const vouchers = [
        'Rp 50.000','Rp 100.000','Rp 150.000','Rp 200.000','Rp 250.000','Rp 300.000','Rp 350.000','Rp 400.000',
        'Rp 450.000','Rp 500.000','Rp 550.000','Rp 600.000','Rp 650.000','Rp 700.000','Rp 750.000','Rp 800.000',
        'Rp 850.000','Rp 900.000','Rp 950.000','Rp 1.000.000','Rp 1.050.000','Rp 1.100.000','Rp 1.150.000','Rp 1.200.000'
    ];

    const wrap = canvas ? canvas.parentElement : null;

    const AudioContext = window.AudioContext || window.webkitAudioContext;
    const audioCtx = AudioContext ? new AudioContext() : null;

    function tickSound(vol = 0.06, freq = 1200, len = 0.01){
        if(!audioCtx) return;
        const o = audioCtx.createOscillator(), g = audioCtx.createGain();
        o.type = 'square'; o.frequency.value = freq; g.gain.value = vol;
        o.connect(g); g.connect(audioCtx.destination);
        o.start(); setTimeout(()=>o.stop(), len*1000);
    }

    function resizeWheel() {
        const maxWidth = Math.min(520, window.innerWidth * 0.94);
        size = Math.max(300, maxWidth);

        if (canvas) { canvas.width = size; canvas.height = size; }
        if (wrap) { wrap.style.width = `${size}px`; wrap.style.height = `${size}px`; }
        drawWheel();
        if (canvas) canvas.style.transform = `rotate(${-rotation}deg)`;
    }

    function drawWheel(){
        if(!ctx) return;
        ctx.clearRect(0,0,canvas.width,canvas.height);
        const r = size / 2;
        const arc = 2*Math.PI / vouchers.length;
        for(let i=0;i<vouchers.length;i++){
            const angle = i*arc;
            ctx.beginPath(); ctx.moveTo(r,r);
            ctx.fillStyle = `hsl(${i*360/vouchers.length} 72% 70%)`;
            ctx.arc(r,r,r-2,angle, angle+arc);
            ctx.closePath(); ctx.fill();
            ctx.save(); ctx.translate(r,r); ctx.rotate(angle + arc/2);
            ctx.fillStyle='#111'; ctx.textAlign='right';
            const fontSize = Math.max(10, Math.floor(size/35));
            ctx.font = `bold ${fontSize}px Arial`;
            ctx.fillText(vouchers[i], r-10, 4);
            ctx.restore();
        }
        ctx.beginPath(); ctx.arc(r,r,r*0.18,0,2*Math.PI);
        ctx.fillStyle='#ffffffee'; ctx.fill();
        ctx.lineWidth=2; ctx.strokeStyle='rgba(0,0,0,0.06)'; ctx.stroke();
    }

    function easeOut(t){ return 1 - Math.pow(1 - t, 3); }
    function norm360(d){ return ((d%360)+360)%360; }
    function burstConfetti(){ 
        if(window.confetti) confetti({ particleCount:80, startVelocity:30, spread:160, origin:{x:0.5,y:0.4} }); 
    }

    let availableNames = [];

    async function loadNames(retryCount=0, maxRetries=3){
        if(retryCount>=maxRetries){ 
            if(nameListEl) nameListEl.innerHTML = '<div class="text-danger py-3">Gagal memuat nama setelah beberapa percobaan.</div>'; 
            return; 
        }
        if(nameListEl) nameListEl.innerHTML = '<div class="text-center text-muted py-4"><span class="spinner"></span> Memuat daftar nama…</div>';
        try{
            const res = await fetch(NAMES_URL + '&_=' + Date.now(), { cache:'no-store', credentials:'same-origin', headers:{ 'X-Requested-With':'XMLHttpRequest' } });
            if(!res.ok) throw new Error('HTTP '+res.status);
            const json = await res.json();
            availableNames = json.data || json || [];
            availableNames = availableNames.map(it=>({
                referrer_name:(it.referrer_name||it.referer||it.ref||it.name||'').toString().trim(), 
                brought_name:(it.brought_name||it.student_name||it.brought||it.child_name||'').toString().trim(), 
                is_new_student:!!(it.is_new||it.is_new_student||it.brought_is_new), 
                row_hash:it.row_hash||it.rowHash||it.row||'', 
                student_id:it.student_id||it.studentId||''
            }));
            renderNameList();
        }catch(err){ 
            console.error('loadNames error', err); 
            setTimeout(()=>loadNames(retryCount+1, maxRetries), 1000); 
        }
    }

    function escapeHtml(s){ 
        return (s===null||s===undefined)?'':(s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); 
    }

    function renderNameList(){
        if(!nameListEl) return;
        if(!availableNames||!availableNames.length){
            nameListEl.innerHTML = '<div class="text-center text-muted py-4">Tidak ada nama tersedia.</div>';
            disableSpin(); return;
        }
        nameListEl.innerHTML = '';
        const list = [...availableNames].sort((a,b)=> (a.referrer_name||'').toString().toUpperCase().localeCompare((b.referrer_name||'').toString().toUpperCase()));

        for(const it of list){
            const rowHash = it.row_hash||'';
            const studentId = it.student_id??'';
            const uniqueId = rowHash || (studentId ? 'stu:'+studentId : Math.random().toString(36).slice(2,9));
            const ref = (it.referrer_name||'').toString().trim();
            const brought = (it.brought_name||'').toString().trim();
            const isNew = !!it.is_new_student;
            const id = 'n_'+uniqueId;

            const div = document.createElement('div');
            div.className='form-check';
            const left = document.createElement('div');
            left.style.display='flex';
            left.style.alignItems='center';
            left.style.gap='10px';
            const radio = document.createElement('input');
            radio.className='form-check-input name-checkbox';
            radio.type='radio';
            radio.name='selected_name';
            radio.id=id;
            if(rowHash) radio.dataset.row_hash = rowHash;
            if(studentId) radio.dataset.student_id = studentId;
            radio.dataset.ref = ref;
            radio.dataset.brought = brought;
            if(it.is_new_student) radio.dataset.is_new='1';
            radio.value = rowHash || (studentId ? 'stu:'+studentId : ref);

            const label = document.createElement('label');
            label.className='form-check-label';
            label.setAttribute('for', id);
            const refLine = `<div class="referrer fw-bold text-primary">${escapeHtml(ref.toUpperCase())} (Murid Humas)</div>`;
            const broughtLine = brought ? `<div class="brought fw-bold text-success">${escapeHtml(brought)}${isNew ? ' (Murid Baru)' : ''}</div>` : '';
            label.innerHTML = refLine + broughtLine;

            left.appendChild(radio);
            left.appendChild(label);

            const rowWrapper = document.createElement('div');
            rowWrapper.style.display='flex';
            rowWrapper.style.justifyContent='space-between';
            rowWrapper.style.alignItems='center';
            rowWrapper.appendChild(left);
            div.appendChild(rowWrapper);
            nameListEl.appendChild(div);
        }

        nameListEl.removeEventListener('change', onNameChange);
        nameListEl.addEventListener('change', onNameChange);
        disableSpin();
    }

    selectRandomBtn && selectRandomBtn.addEventListener('click', ()=>{
        const radios = document.querySelectorAll('.name-checkbox');
        if(!radios.length) return;
        radios.forEach(r=>r.checked=false);
        const idx = Math.floor(Math.random()*radios.length);
        radios[idx].checked = true;
        onNameChange();
    });

    clearAllUsedBtn && clearAllUsedBtn.addEventListener('click', ()=>{
        document.querySelectorAll('.form-check .form-check-input[disabled]').forEach(el=>{
            const rowHash = el.dataset.row_hash;
            const studentId = el.dataset.student_id;
            el.closest('.form-check')?.remove();
            availableNames = availableNames.filter(x=> 
                (rowHash && (x.row_hash||'') !== rowHash) || 
                (studentId && ((x.student_id||'')+'') !== (studentId+'')) || 
                (!rowHash && !studentId)
            );
        });
        disableSpin();
    });

    refreshBtn && refreshBtn.addEventListener('click', async ()=>{ await loadNames(); });

    function enableSpin(){ 
        if(spinBtn){ 
            spinBtn.removeAttribute('disabled'); 
            spinBtn.classList.remove('disabled'); 
        } 
    }
    function disableSpin(){ 
        if(spinBtn){ 
            spinBtn.setAttribute('disabled','true'); 
            spinBtn.classList.add('disabled'); 
        } 
    }

    function onNameChange(){
        const any = document.querySelector('input[name="selected_name"]:checked') !== null;
        if(any) {
            enableSpin();
            setTimeout(() => spinBtn.scrollIntoView({ behavior: "smooth", block: "center" }), 300);
        } else {
            disableSpin();
        }
    }

    async function loadRecentHistory(limit=6){
        if(recentEl) recentEl.innerHTML = '<div class="text-center text-muted py-3"><span class="spinner"></span> Memuat riwayat…</div>';
        try{
            const res = await fetch(HISTORY_URL + '&per_page=' + limit + '&_=' + Date.now(), { cache:'no-store', credentials:'same-origin', headers:{ 'X-Requested-With':'XMLHttpRequest' } });
            if(!res.ok) throw new Error('HTTP '+res.status);
            const json = await res.json();
            const rows = json.data || json || [];
            if(!rows||rows.length===0){
                if(recentEl) recentEl.innerHTML = '<div class="text-center text-muted py-3">Belum ada pemenang.</div>';
                return;
            }
            const list = document.createElement('div'); 
            list.className='list-group list-group-flush';
            rows.slice(0,limit).forEach(r=>{
                const item = document.createElement('div'); 
                item.className='list-group-item d-flex justify-content-between align-items-start';
                const sub = r.new_student ? `<div class="text-muted small">Murid: ${escapeHtml(r.new_student.nama||r.new_student.name||'')}</div>` : '';
                item.innerHTML = `<div><div class="fw-semibold">${escapeHtml(r.name)}</div>${sub}<small class="text-muted">${escapeHtml(r.won_at||r.created_at||'')}</small></div><div class="text-end"><div class="badge bg-success">${escapeHtml(r.voucher||'')}</div></div>`;
                list.appendChild(item);
            });
            if(recentEl){ 
                recentEl.innerHTML=''; 
                recentEl.appendChild(list); 
            }
        }catch(err){
            console.error('loadRecentHistory err', err);
            if(recentEl) recentEl.innerHTML = '<div class="text-danger py-3">Gagal memuat riwayat.</div>';
        }
    }

    // ==================== SPIN FUNCTION ====================
    async function spin(){
        if(spinning || !CSRF){ 
            if(!CSRF && winnerEl) winnerEl.textContent = 'Error: Token keamanan tidak ditemukan.'; 
            return; 
        }
        const checked = document.querySelector('input[name="selected_name"]:checked');
        if(!checked){
            if(winnerEl) winnerEl.textContent = 'Pilih satu nama dulu sebelum memutar.';
            return;
        }

        spinning = true;
        if(winnerEl) winnerEl.innerHTML = '<span class="spinner"></span> Sedang memutar...';
        if(audioCtx && audioCtx.state==='suspended') await audioCtx.resume().catch(()=>{});

        const payload = {};
        if(checked.dataset.row_hash) payload.row_hash = checked.dataset.row_hash;
        else if(checked.dataset.student_id) payload.student_id = checked.dataset.student_id;
        else payload.name = checked.dataset.ref || checked.value;

        let data;
        try{
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

            if(!res.ok){
                if(res.status===419){ 
                    if(winnerEl) winnerEl.textContent='Sesi kadaluarsa. Silakan muat ulang halaman.'; 
                    spinning=false; return; 
                }
                const text = await res.text();
                try{ 
                    const errJson = JSON.parse(text); 
                    if(winnerEl) winnerEl.textContent = errJson.error || errJson.message || ('Spin gagal (HTTP '+res.status+')'); 
                }catch(e){
                    if(winnerEl) winnerEl.textContent = 'Spin gagal (HTTP '+res.status+')';
                }
                spinning=false; return;
            }

            data = await res.json();
        }catch(err){
            console.error('Spin error', err);
            if(winnerEl) winnerEl.textContent = 'Gagal memutar roda. Coba lagi.';
            spinning=false; return;
        }

        const sectorAngle = 360 / vouchers.length;
        let targetSector = data.voucher_index ?? -1;
        if(targetSector === -1 && data.voucher){
            const voucherLower = (data.voucher||'').toLowerCase().trim();
            targetSector = vouchers.findIndex(v=>v.toLowerCase().trim()===voucherLower);
        }
        if(targetSector === -1) targetSector = Math.floor(Math.random()*vouchers.length);

        const sectorCenterDeg = targetSector * sectorAngle + (sectorAngle/2);
        const pointerAngle = 90;
        const finalNorm = ((pointerAngle - sectorCenterDeg) % 360 + 360) % 360;
        const targetOffset = ((-finalNorm) % 360 + 360) % 360;
        const spinAngle = (4*360) + (Math.floor(Math.random()*3)*360) + targetOffset;

        const duration = 4200 + Math.random()*1000;
        const start = performance.now();
        let lastTickSector = -1;

        function animate(now){
            const t = Math.min((now-start)/duration,1);
            const eased = easeOut(t);
            rotation = spinAngle * eased;
            if(canvas) canvas.style.transform = `rotate(${-rotation}deg)`;

            const normalized = norm360(-rotation);
            const relative = norm360(pointerAngle - normalized);
            const currentSector = Math.floor(relative / sectorAngle) % vouchers.length;

            if(currentSector !== lastTickSector){
                lastTickSector = currentSector;
                tickSound(0.04, 1200 - (currentSector % 6)*60, 0.01);
            }

            if(t < 1){
                animationFrameId = requestAnimationFrame(animate);
            } else {
                spinning = false;
                const selectedVoucher = vouchers[targetSector];
                const checkedLabel = document.querySelector(`label[for="${checked.id}"]`);
                const nameDisplayHtml = checkedLabel ? escapeHtml(checkedLabel.textContent) : 'Nama Pemenang';

                if(winnerEl) winnerEl.innerHTML = `🎉 <div style="font-size:1rem; line-height:1.4;">${nameDisplayHtml}</div><div style="margin-top:8px;">mendapatkan <strong>${escapeHtml(selectedVoucher)}</strong> 🎁</div>`;

                burstConfetti();
                tickSound(0.14, 880, 0.06);

                if(stopLine){
                    stopLine.classList.remove('flash');
                    void stopLine.offsetWidth;
                    stopLine.classList.add('flash');
                }

                setTimeout(() => {
                    winnerEl.scrollIntoView({ behavior: "smooth", block: "center" });
                }, 600);

                const rowHash = data.row_hash || payload.row_hash;
                const studentId = data.student_id || payload.student_id;
                if(rowHash){
                    const el = document.querySelector(`input[name="selected_name"][data-row_hash="${rowHash}"]`);
                    if(el) el.closest('.form-check')?.remove();
                    availableNames = availableNames.filter(x => (x.row_hash||'') !== rowHash);
                } else if(studentId){
                    const el = document.querySelector(`input[name="selected_name"][data-student-id="${studentId}"]`);
                    if(el) el.closest('.form-check')?.remove();
                    availableNames = availableNames.filter(x => ((x.student_id||'')+'') !== (studentId+''));
                }

                disableSpin();
                loadRecentHistory(6);
            }
        }

        requestAnimationFrame(animate);
    }

    function init(){
        resizeWheel();
        drawWheel();
        loadNames();
        loadRecentHistory(6);

        if(spinBtn){
            spinBtn.addEventListener('click', spin);
            spinBtn.addEventListener('touchend', () => {
    spin();
}, { passive: true });
        }

        refreshBtn && refreshBtn.addEventListener('click', async ()=>{ await loadNames(); });
        refreshHistoryBtn && refreshHistoryBtn.addEventListener('click', ()=>loadRecentHistory(6));
        openHistoryModalBtn && openHistoryModalBtn.addEventListener('click', ()=>{ 
            if(winnersHistoryModal) winnersHistoryModal.show(); 
        });

        window.addEventListener('resize', () => {
            clearTimeout(window.resizeTimer);
            window.resizeTimer = setTimeout(resizeWheel, 150);
        });
    }

    init();

})();
</script>
@endpush
