<?php

use Illuminate\Support\Facades\Route;
use App\Models\BukuInduk;
use Revolution\Google\Sheets\Facades\Sheets;
use Google\Service\Sheets as GoogleSheets;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Models\MuridTrial;
use Carbon\Carbon;
use App\Http\Controllers\{
    UnitController,
    ProfileController,
    AbsensiRelawanController,
    RekapAbsensiController,
    HumaController,
    UserController,
    UserAuthController,
    MuridTrialController,
    BukuIndukController,
    DaftarMuridDepositController,
    KartuSppController,
    MbcMuridController,
    SertifikatBeasiswaController,
    GaransiBCAController,
    DaftarHargaController,
    VocerController,
    PenerimaanController,
    PettyCashController,
    PengajuanController,
    RekapController,
    SppController,
    SkimController,
    PendapatanTunjanganController,
    PotonganTunjanganController,
    PembayaranTunjanganController,
    SlipTunjanganController,
    RekapProgresifController,
    PembayaranProgresifController,
    SlipPembayaranProgresifController,
    LaporanBagiHasilController,
    PerkembanganUnitController,
    ProdukController,
    PenerimaanProdukController,
    PemakaianProdukController,
    DataProdukController,
    OrderModulController,
    PemesananKaosController,
    PemesananSertifikatController,
    PemesananSTPBController,
    PemesananPerlengkapanUnitController,
    PemesananRaportController,
    KtrController,
    DurasiKegiatanController,
    HargaSaptatarunaController,
    JadwalDetailController,
    HomeController,
    VoucherLamaController,
    CaraPerhitunganController,
    PindahGolonganController,
    GoogleFormController,
    StudentController,
    RegistrationController,
    MutationController,
    WheelController,
    ImbalanRekapController,
    KomisiController,
    PembayaranKomisiController,
    SlipKomisiController,
    AdminPerkembanganUnitController,
    RelawanAbsenController,
    FinancialSummaryController,
    AdjustmentController,
    CashAdvanceController,
    CashAdvanceInstallmentController,
    BukuIndukStatistikController,
    AdminRekapPengeluaranController,
    CutiMuridController,
    LemburController,
    




};

// =========================================
// ROOT & AUTHENTICATION
// =========================================

// Root: redirect ke /home jika sudah login
// Root diarahkan ke Home
Route::get('/', function () {
    return redirect()->route('home');
});

// Halaman Home

Route::get('/home', [HomeController::class, 'home'])
    ->name('home')
    ->middleware('auth');

// Register
Route::get('/register', [UserAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [UserAuthController::class, 'register'])->name('register.process');
Route::get('/password/reset', [UserAuthController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/password/reset', [UserAuthController::class, 'resetPassword'])->name('password.reset.manual');
Route::resource('users', UserController::class);

// Login
Route::get('/login', [UserAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [UserAuthController::class, 'login'])->name('login.process');

// Logout
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

// =========================================
// UNITS
// =========================================
Route::prefix('units')->group(function () {
    Route::get('/', [UnitController::class, 'index'])->name('unit.index')->middleware('auth');
    Route::get('/create', [UnitController::class, 'create'])->name('unit.create')->middleware('auth');
    Route::post('/', [UnitController::class, 'store'])->name('unit.store')->middleware('auth');
    Route::get('/export', [UnitController::class, 'exportToSheet'])->name('units.export')->middleware('auth');
    Route::get('/{id}', [UnitController::class, 'show'])->name('unit.show')->middleware('auth');
    Route::get('/{id}/edit', [UnitController::class, 'edit'])->name('unit.edit')->middleware('auth');
    Route::put('/{id}', [UnitController::class, 'update'])->name('unit.update')->middleware('auth');
    Route::delete('/{id}', [UnitController::class, 'destroy'])->name('unit.destroy')->middleware('auth');
});

Route::prefix('profiles')->name('profiles.')->middleware('auth')->group(function () {

    // ✅ Route yang sudah ada
    Route::get('/next-nik-nourut', [ProfileController::class, 'getNextNikNoUrut'])
        ->name('next-nik-nourut');

    Route::post('/import', [ProfileController::class, 'import'])->name('import');
    Route::get('/export', [ProfileController::class, 'export'])->name('export');

    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::get('/create', [ProfileController::class, 'create'])->name('create');
    Route::post('/', [ProfileController::class, 'store'])->name('store');

    Route::get('/{profile}', [ProfileController::class, 'show'])->name('show');
    Route::get('/{profile}/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/{profile}', [ProfileController::class, 'update'])->name('update');
    Route::delete('/{profile}', [ProfileController::class, 'destroy'])->name('destroy');

    Route::post('/{profile}/inline-update', [ProfileController::class, 'inlineUpdate'])->name('inline-update');
    Route::post('/{profile}/inline-update-ktr', [ProfileController::class, 'inlineUpdateKtr'])->name('inline-update-ktr');
    Route::post('/{profile}/inline-update-field', [ProfileController::class, 'inlineUpdateField'])->name('inline-update-field');
    Route::post('/{profile}/seragam-kolom', [ProfileController::class, 'updateSeragamKolom'])->name('seragam-kolom');

    // ✅ ROUTE HISTORI - INI YANG BENAR
    Route::get('{profile}/histori', [ProfileController::class, 'showHistori'])
         ->name('histori');

});

// =========================================
// ABSENSI RELAWAN & REKAP
// =========================================
Route::resource('absensi-relawan', AbsensiRelawanController::class)
    ->except(['show'])
    ->middleware('auth');

Route::get('/rekap/update-kode-jadwal', [RekapAbsensiController::class, 'updateKodeJadwal'])
    ->name('rekap.updateKodeJadwal');
;

Route::resource('rekap', RekapAbsensiController::class)->only([
    'index',
    'create',
    'store',
    'edit',
    'update',
    'destroy',
]);
Route::get('/absensi-relawan/import', [AbsensiRelawanController::class, 'importForm'])
    ->name('absensi-relawan.import.form')->middleware('auth');
Route::post('/absensi-relawan/import', [AbsensiRelawanController::class, 'importStore'])
    ->name('absensi-relawan.import.store')->middleware('auth');
Route::get('/absensi-relawan/export', [AbsensiRelawanController::class, 'export'])
    ->name('absensi-relawan.export')
    ->middleware('auth');

// =========================================
// HUMAS
// =========================================
Route::resource('humas', HumaController::class)->middleware('auth');
// bila belum ada: preview/import massal
Route::get('humas/import/preview', [\App\Http\Controllers\HumaController::class, 'importPreview'])->name('humas.import.preview');
Route::post('humas/import', [\App\Http\Controllers\HumaController::class, 'importFromBukuInduk'])->name('humas.import');

// import single dari row buku_induk (tombol Import per-row di index)
Route::post('humas/import/single', [\App\Http\Controllers\HumaController::class, 'importSingle'])->name('humas.import.single');
Route::get('/export', [HumaController::class, 'export'])->name('huma.export');
Route::post('humas/import', [HumaController::class, 'import'])->name('humas.import');

Route::get('/cuti-murid', [CutiMuridController::class, 'index'])
    ->name('cuti.index');
// =========================================
// USERS
// =========================================
Route::resource('users', UserController::class)->middleware('auth');

// =========================================
// MURID TRIAL & BUKU INDUK
// =========================================
/*
 * 1) Daftarkan route AJAX dulu (sebelum resource) — ini mencegah konflik dengan
 *    Route::resource yang menampung pola /murid_trials/{murid_trial}.
 */
Route::get('/murid_trials/search-ajax', [MuridTrialController::class, 'searchAjax'])
    ->name('murid_trials.searchAjax')
    ->middleware('auth');

/*
 * 2) Lalu daftarkan resource & route lain seperti biasa
 */
Route::resource('murid_trials', MuridTrialController::class)->middleware('auth');

Route::post('/murid-trials/sync', [MuridTrialController::class, 'sync'])
    ->name('murid_trials.sync')
    ->middleware('auth');

Route::get('murid_trials/{murid_trial}/edit', [MuridTrialController::class, 'edit'])
    ->name('murid_trials.edit')->middleware('auth');

Route::put('murid_trials/{murid_trial}', [MuridTrialController::class, 'update'])
    ->name('murid_trials.update')->middleware('auth');

Route::delete('murid_trials/{murid_trial}', [MuridTrialController::class, 'destroy'])
    ->name('murid_trials.destroy');

Route::patch('/murid_trials/{murid_trial}/status', [MuridTrialController::class, 'updateStatus'])
    ->name('murid_trials.updateStatus')->middleware('auth');

Route::get('/murid-trials/{murid_trial}/commitment', [MuridTrialController::class, 'commitment'])
    ->name('murid_trials.commitment')->middleware('auth');

Route::post('/murid-trials/{murid_trial}/commitment', [MuridTrialController::class, 'storeCommitment'])
    ->name('murid_trials.commitment.store')->middleware('auth');

Route::post('/murid-trials/sync-commitment', [MuridTrialController::class, 'syncCommitment'])
    ->name('murid_trials.syncCommitment')->middleware('auth');

Route::get('murid-trials/{murid_trial}/create-registration', [MuridTrialController::class, 'createRegistration'])
    ->name('murid_trials.create_registration')->middleware('auth');

Route::get('/murid-trials/{murid_trial}/mutasi-masuk', [MuridTrialController::class, 'mutationForm'])
    ->name('murid_trials.mutation_form')->middleware('auth');

Route::post('/murid-trials/{murid_trial}/mutasi-masuk', [MuridTrialController::class, 'storeMutationFromTrial'])
    ->name('murid_trials.mutation_store')->middleware('auth');

Route::patch('/murid-trials/{murid_trial}/guru', [MuridTrialController::class, 'updateGuru'])
    ->name('murid_trials.update_guru')->middleware('auth');

Route::resource('students', StudentController::class)->only(['index', 'create', 'store'])->middleware('auth');
Route::post('students/{student}/mutasi-masuk', [StudentController::class, 'mutasiMasuk'])
    ->name('students.mutasi-masuk');
Route::get('/students/{student}/history/json', [\App\Http\Controllers\StudentController::class, 'historyJson'])
    ->name('students.history.json')->middleware('auth');
Route::get('/wheel/humas', [StudentController::class, 'wheelForHumas'])
    ->name('wheel.humas');
Route::post('/students/{student}/reactivate', [StudentController::class, 'reactivate'])
     ->name('students.reactivate');
Route::post('/students/{student}/reactivate', [StudentController::class, 'reactivate'])
     ->name('students.reactivate');
     Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');

//Buku Induk Router
Route::resource('buku_induk', BukuIndukController::class)->middleware('auth');
Route::post('/buku_induk/import', [BukuIndukController::class, 'import'])->name('buku_induk.import')->middleware('auth');

Route::get('/statistik-murid', [BukuIndukStatistikController::class, 'index'])
    ->name('statistik.murid')
    ->middleware('auth'); // sesuaikan dengan middleware kamu
    Route::get('/buku-induk/next-suffix', [BukuIndukController::class, 'nextSuffix'])->name('buku_induk.next_suffix');
Route::get('/buku-induk/export', [BukuIndukController::class, 'export'])->name('buku_induk.export');
Route::get('/buku-induk/export-to-sheet', [BukuIndukController::class, 'exportToSheet'])
     ->name('buku_induk.exportToSheet');
     // === ROUTE HUMAS PREFILL ===
Route::get('/buku-induk/humas-prefill', [App\Http\Controllers\BukuIndukController::class, 'humasPrefill'])
     ->name('buku_induk.humas-prefill');
     


//daftar murid deposit
Route::get('/daftar-murid-deposit', [DaftarMuridDepositController::class, 'index'])->name('daftar_murid_deposit.index')->middleware('auth');
Route::get('/daftar-murid-deposit/create', [DaftarMuridDepositController::class, 'create'])->name('daftar_murid_deposit.create')->middleware('auth');
Route::post('/daftar-murid-deposit', [DaftarMuridDepositController::class, 'store'])->name('daftar_murid_deposit.store')->middleware('auth');
Route::get('/daftar-murid-deposit/{id}/edit', [DaftarMuridDepositController::class, 'edit'])->name('daftar_murid_deposit.edit')->middleware('auth');
Route::put('/daftar-murid-deposit/{id}', [DaftarMuridDepositController::class, 'update'])->name('daftar_murid_deposit.update')->middleware('auth');


Route::get('buku-induk/{id}/history', [\App\Http\Controllers\BukuIndukController::class, 'history'])
    ->name('buku_induk.history')->middleware('auth');
// routes/web.php
Route::get('/buku-induk/histories', [BukuIndukController::class, 'allHistory'])->name('buku_induk.histories')->middleware('auth');
Route::get('buku-induk/history', [BukuIndukController::class, 'allHistory'])->name('buku_induk.all_history')->middleware('auth');

Route::get('/export-google-sheet', [BukuIndukController::class, 'exportToSheet']);
Route::get('/buku-induk/{id}/surat-pindah', [BukuIndukController::class, 'suratPindah'])
     ->name('buku_induk.surat_pindah');
Route::patch('/buku-induk/{id}/status', [BukuIndukController::class, 'updateStatus'])
     ->name('buku_induk.updateStatus');



//kartu spp
Route::middleware('auth')->group(function () {

    // Resource route dengan nama route pakai hyphen (sesuai yang kamu pakai di Blade)
    Route::resource('kartu-spp', KartuSppController::class);

    // Route detail AJAX - pilih SALAH SATU saja!
    // Saya sarankan pakai method 'detail' karena lebih pendek
    Route::get('/kartu-spp/detail/{nim}', [KartuSppController::class, 'detail'])
        ->name('kartu-spp.detail');

    // HAPUS baris ini karena duplikat:
    // Route::get('/kartu-spp/detail/{nim}', [KartuSppController::class, 'getDetailByNim'])->name('kartu-spp.detail')->middleware('auth');
});

//mbc murid
Route::resource('mbc-murid', MbcMuridController::class)->middleware('auth');

//sertifikat beasiswa
Route::resource('sertifikat-beasiswa', SertifikatBeasiswaController::class)->middleware('auth');

//garansi bca
Route::resource('garansi-bca', GaransiBCAController::class)->middleware('auth');
Route::post('/garansi/approve/{id}', [GaransiBCAController::class, 'approve'])
    ->name('garansi.approve');
Route::post('/garansi/reject/{id}', [GaransiBCAController::class, 'reject'])
    ->name('garansi.reject');

//daftar harga
Route::resource('daftar-harga', DaftarHargaController::class)->middleware('auth');

//vocer
Route::resource('vocers', VocerController::class)->middleware('auth');


// Resource Penerimaan (tanpa show)
Route::resource('penerimaan', PenerimaanController::class)
    ->except(['show'])
    ->middleware('auth');

// Submenu Penerimaan
Route::get('penerimaan/spp', [PenerimaanController::class, 'spp'])
    ->name('penerimaan.spp')
    ->middleware('auth');

Route::get('penerimaan/produk', [PenerimaanController::class, 'produk'])
    ->name('penerimaan.produk')
    ->middleware('auth');

// AJAX Data (Bulan SPP yang Sudah Dibayar)
Route::get('/penerimaan/paid-months', [PenerimaanController::class, 'getPaidMonths'])
    ->name('penerimaan.paid_months')
    ->middleware('auth');

// Import Excel
Route::post('penerimaan/import', [PenerimaanController::class, 'import'])
    ->name('penerimaan.import')
    ->middleware('auth');

// Update Bulan & Tahun
Route::put('penerimaan/{id}/update-bulan-tahun', [PenerimaanController::class, 'updateBulanTahun'])
    ->name('penerimaan.updateBulanTahun')
    ->middleware('auth');

Route::put('penerimaan/{id}/update-tahun', [PenerimaanController::class, 'updateTahun'])
    ->name('penerimaan.updateTahun')
    ->middleware('auth');

Route::get('/penerimaan/rbas', [PenerimaanController::class, 'rbas'])
    ->name('penerimaan.rbas')
    ->middleware('auth');

Route::post('/penerimaan/update-tanggal-penyerahan', [PenerimaanController::class, 'updateTanggalPenyerahan'])
    ->name('penerimaan.update-tanggal-penyerahan');

Route::post('/penerimaan/update-ukuran-kaos', [PenerimaanController::class, 'updateUkuranKaos'])
    ->name('penerimaan.update-ukuran-kaos'); // ← PERBAIKAN DI SINI: pakai -

Route::post('/penerimaan/update-kaos-multi', [PenerimaanController::class, 'updateKaosMulti'])
    ->name('penerimaan.update-kaos-multi');

Route::get('/penerimaan/murid-by-unit', [PenerimaanController::class, 'getMuridByUnit'])
    ->name('penerimaan.murid-by-unit');

Route::get('/penerimaan/export', [PenerimaanController::class, 'export'])
    ->name('penerimaan.export');

// Di dalam group route yang ada auth
Route::get('/penerimaan/vouchers-by-nim', [PenerimaanController::class, 'getVouchersByNim'])
     ->name('penerimaan.vouchers.by.nim');




//petty cash
Route::resource('pettycash', PettyCashController::class)->middleware('auth');
Route::resource('pengajuan', PengajuanController::class)->middleware('auth');
Route::get('/rekap.pettycash', [App\Http\Controllers\RekapController::class, 'petty'])->name('rekap.petty.index')->middleware('auth');
Route::post('/pettycash/saldo-awal', [PettyCashController::class, 'updateSaldoAwal'])
    ->name('pettycash.saldo-awal')
    ->middleware('auth');
Route::get('/rekap/petty', [RekapController::class, 'petty'])->name('penerimaan.rekap');
// ──────────────────────────────────────────────
// Route spesifik / custom → harus di atas resource
// ──────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    Route::get('/spp/polling-status', [SppController::class, 'pollingStatus'])
        ->name('spp.pollingStatus');

    Route::get('/spp/sync-form', [SppController::class, 'syncGoogleForm'])
        ->name('spp.sync-form');

    Route::get('/spp/surat-keterlambatan/{nim}', [SppController::class, 'suratKeterlambatan'])
    ->name('spp.surat-keterlambatan');
    Route::post('/spp/upload/{nim}', [SppController::class, 'uploadPernyataan'])
        ->name('spp.upload_pernyataan');

    // Resource terakhir → pola {spp} baru diproses kalau route di atas tidak cocok
    Route::resource('spp', SppController::class);

});

//Skim & Tunjangan
Route::resource('skim', SkimController::class)->middleware('auth');
// Route untuk menampilkan form import (opsional)
Route::get('skim/import', [SkimController::class, 'showImportForm'])->name('skim.import.form')->middleware('auth');

// Route untuk proses import
Route::post('skim/import', [SkimController::class, 'import'])->name('skim.import')->middleware('auth');


//Pendapatan Tunjangan
Route::resource('pendapatan-tunjangan', PendapatanTunjanganController::class)->middleware('auth');
Route::post('/pendapatan-tunjangan/generate', [PendapatanTunjanganController::class, 'forceGenerateMonth'])
    ->name('pendapatan-tunjangan.generate')->middleware('auth');
Route::post('/pendapatan-tunjangan/backfill', [PendapatanTunjanganController::class, 'backfillFromPenerimaan'])
    ->name('pendapatan-tunjangan.backfill')->middleware('auth');
    Route::post('/pendapatan-tunjangan/hapus-sebelum-2024', [PendapatanTunjanganController::class, 'hapusDataSebelum2024'])
    ->name('pendapatan-tunjangan.hapus-sebelum-2024')
    ->middleware('auth');

// routes/web.php
Route::get('/pendapatan-tunjangan/thp/{profile}', [PendapatanTunjanganController::class, 'getTHP'])->name('pendapatan-tunjangan.getTHP')->middleware('auth');

Route::post('/pendapatan-tunjangan/generate', [PendapatanTunjanganController::class, 'generateBulanBaru'])
    ->name('pendapatan-tunjangan.generate')->middleware('auth');

Route::get('/pendapatan-tunjangan/skim-value/{profile}', [App\Http\Controllers\PendapatanTunjanganController::class, 'getSkimValue'])->name('pendapatan-tunjangan.skim-value');
Route::get(
    '/pendapatan-tunjangan/skim-value/{profile}',
    [PendapatanTunjanganController::class, 'ajaxGetSkimFromProfile']
)->name('pendapatan-tunjangan.skim-value');

//potongan tunjangan
Route::resource('potongan', PotonganTunjanganController::class)->middleware('auth');
// Route sync absensi (custom, bukan bagian dari resource)
Route::post('/potongan/sync-from-absensi', [PotonganTunjanganController::class, 'syncFromAbsensi'])
    ->name('potongan.syncFromAbsensi')
    ->middleware('auth');


Route::get('/pembayaran/pdf', [PembayaranTunjanganController::class, 'exportPdf'])
    ->name('pembayaran.pdf')
    ->middleware('auth');
//pembayaran tunjangan
Route::resource('pembayaran', PembayaranTunjanganController::class)->middleware('auth');


// Hanya route yang diperlukan
Route::get('/slip-tunjangan', [SlipTunjanganController::class, 'index'])
    ->name('slip-tunjangan.index')
    ->middleware('auth');

Route::get('/slip-tunjangan/pdf-preview', [SlipTunjanganController::class, 'previewPDF'])
    ->name('slip-tunjangan.pdf-preview')
    ->middleware('auth');


//rekap progresif
Route::resource('rekap-progresif', RekapProgresifController::class);
Route::post('rekap-progresif/calculate', [App\Http\Controllers\RekapProgresifController::class, 'calculate'])
    ->name('rekap-progresif.calculate');
// Di routes/web.php
// Route::get('rekap-progresif', [RekapProgresifController::class, 'index'])->name('rekap-progresif.index');
// Route::get('rekap-progresif/create', [RekapProgresifController::class, 'create'])->name('rekap-progresif.create');
// Route::post('rekap-progresif', [RekapProgresifController::class, 'store'])->name('rekap-progresif.store');
// Route::get('rekap-progresif/{rekap_progresif}/edit', [RekapProgresifController::class, 'edit'])->name('rekap-progresif.edit');
// Route::put('rekap-progresif/{rekap_progresif}', [RekapProgresifController::class, 'update'])->name('rekap-progresif.update');
// Route::delete('rekap-progresif/{rekap_progresif}', [RekapProgresifController::class, 'destroy'])->name('rekap-progresif.destroy');
// web.php
// routes/web.php
Route::post('/rekap-progresif/generate-all', [RekapProgresifController::class, 'generateAllMissing'])
     ->name('rekap-progresif.generate-all');


// Pembayaran Progresif
Route::resource('pembayaran-progresif', PembayaranProgresifController::class)->only(['index', 'create', 'store', 'edit', 'update'])->middleware('auth');

// Slip Pembayaran Progresif
// daftar semua slip (opsional)
Route::get('/slip-progresif', [SlipPembayaranProgresifController::class, 'index'])->name('slip-progresif.index')->middleware('auth');
Route::get('/slip-progresif/pdf-preview', [SlipPembayaranProgresifController::class, 'previewPdf'])
    ->name('slip-progresif.pdf-preview')->middleware('auth');


// Laporan Bagi Hasil
Route::resource('laporan', LaporanBagiHasilController::class)->middleware('auth');

//perkembangan unit
Route::resource('perkembangan_units', PerkembanganUnitController::class)->middleware('auth');
Route::get('/perkembangan-units', [PerkembanganUnitController::class, 'index'])
    ->name('perkembangan-units.index');

//produk
// routes/web.php
Route::resource('produk', ProdukController::class)
    ->middleware('auth')
    ->except(['show']);
Route::get('produk/export', [ProdukController::class, 'export'])->name('produk.export');

//penerimaan produk
Route::resource('penerimaan_produk', PenerimaanProdukController::class)->middleware('auth');
// Route
Route::get('/penerimaan-produk/dari-kaos', [PenerimaanProdukController::class, 'dariKaos'])->name('penerimaan_produk.dari_kaos');
Route::post('/penerimaan-produk/terima-kaos/{id}', [PenerimaanProdukController::class, 'terimaDariKaos'])->name('penerimaan_produk.terima_kaos');
// Tambah ini di grup route yang sudah ada
Route::get('/penerimaan-produk/create-multi', [PenerimaanProdukController::class, 'createMulti'])
    ->name('penerimaan_produk.create_multi');

Route::post('/penerimaan-produk/store-multi', [PenerimaanProdukController::class, 'storeMulti'])
    ->name('penerimaan_produk.store_multi');

//pemakaian produk
Route::middleware('auth')->group(function () {
    Route::resource('pemakaian_produk', PemakaianProdukController::class);

    // AJAX Routes dengan nama
    Route::get('/pemakaian-produk/murid/{unitId}', [PemakaianProdukController::class, 'getMuridByUnit'])
        ->name('pemakaian_produk.murid_by_unit');

    Route::get('/pemakaian-produk/produk/{unitId}', [PemakaianProdukController::class, 'getProdukByUnit'])
        ->name('pemakaian_produk.produk_by_unit');
});
Route::get('/pemakaian-produk/produk-by-unit/{unitId}', [PemakaianProdukController::class, 'getProdukByUnit']);

//data produk
Route::resource('data_produk', DataProdukController::class)->middleware('auth');
Route::post('/data-produk/generate-template', [DataProdukController::class, 'generateTemplate'])
    ->name('data_produk.generate_template');
Route::post('data-produk/refresh-terima', [DataProdukController::class, 'refreshTerima'])
    ->name('data_produk.refresh_terima');
Route::post(
    '/data-produk/{id}/adjustment',
    [DataProdukController::class, 'adjustment']
)->name('data_produk.adjustment');

Route::post('/produk/import', [ProdukController::class, 'import'])->name('produk.import')->middleware('auth');

//order modul
Route::resource('order_modul', OrderModulController::class)->middleware('auth');
Route::get('/order-modul', [OrderModulController::class, 'index'])->name('order_modul.index')->middleware('auth');
Route::post('/order-modul/import', [OrderModulController::class, 'import'])->name('order_modul.import')->middleware('auth');
Route::get('/order-modul/status-stok', [OrderModulController::class, 'getStatusStok'])
    ->name('order_modul.get_status_stok');
Route::get('/order-modul/produks-by-unit', [OrderModulController::class, 'getProduksByUnit'])
    ->name('order_modul.produks_by_unit');
    Route::post('/order-modul/generate-auto', [OrderModulController::class, 'generateAutoOrder'])
     ->name('order_modul.generate-auto');

//pemesanan kaos
Route::resource('pemesanan_kaos', PemesananKaosController::class)->middleware('auth');
Route::get('/pemesanan-kaos/murid/{unit_id}', [PemesananKaosController::class, 'getMuridByUnit'])
    ->name('pemesanan_kaos.murid');

//pemesanan sertifikat
Route::resource('pemesanan_sertifikat', PemesananSertifikatController::class)->middleware('auth');

// Route AJAX untuk load siswa berdasarkan unit (untuk admin)
Route::get('/pemesanan-sertifikat/siswa-by-unit/{unitId}', function ($unitId) {
    try {
        $unit = \App\Models\Unit::findOrFail($unitId);

        $siswas = \App\Models\BukuInduk::where('status', 'Aktif')
            ->where('bimba_unit', $unit->biMBA_unit)
            ->orderBy('nama')
            ->get(['id', 'nim', 'nama', 'tmpt_lahir', 'tgl_lahir', 'tgl_masuk', 'level', 'bimba_unit'])
            ->map(function ($siswa) {
                return [
                    'id' => $siswa->id,
                    'nim' => $siswa->nim,
                    'nama' => $siswa->nama,
                    'tmpt_lahir' => $siswa->tmpt_lahir ?? '',
                    // Format wajib Y-m-d untuk input date
                    'tgl_lahir' => $siswa->tgl_lahir ? $siswa->tgl_lahir->format('Y-m-d') : '',
                    'tgl_masuk' => $siswa->tgl_masuk ? $siswa->tgl_masuk->format('Y-m-d') : '',
                    'level' => $siswa->level ?? '',
                    'bimba_unit' => $siswa->bimba_unit ?? '',
                ];
            });

        return response()->json($siswas);
    } catch (\Exception $e) {
        Log::error('AJAX Siswa Error: ' . $e->getMessage());
        return response()->json(['error' => 'Gagal memuat siswa'], 500);
    }
})->middleware('auth');

Route::resource('pemesanan_stpb', PemesananSTPBController::class)->middleware('auth');

Route::get(
    '/pemesanan-stpb/siswa-by-unit',
    [PemesananSTPBController::class, 'getSiswaByUnit']
)->name('pemesanan_stpb.siswa_by_unit')->middleware('auth');

//pemesanan perlengkapan unit
Route::resource('pemesanan_perlengkapan_unit', PemesananPerlengkapanUnitController::class)->middleware('auth');

//pemesanan raport
Route::resource('pemesanan_raport', PemesananRaportController::class)->middleware('auth');

//KTR
Route::resource('ktr', KtrController::class)->middleware('auth');
Route::post('/ktr/import', [KtrController::class, 'import'])->name('ktr.import')->middleware('auth');

//Durasi Kegiatan
Route::resource('durasi', DurasiKegiatanController::class)->only([
    'index',
    'create',
    'store',
    'edit',
    'update',
    'destroy'
])->middleware('auth');
Route::post('/durasi/import', [DurasiKegiatanController::class, 'import'])->name('durasi.import')->middleware('auth');

//penyesuaian ktr ku
Route::resource('penyesuaian', App\Http\Controllers\PenyesuaianKtrController::class)->middleware('auth');
Route::post('/penyesuaian/import', [App\Http\Controllers\PenyesuaianKtrController::class, 'import'])->name('penyesuaian.import')->middleware('auth');

//penyesuaian rb guru
Route::resource('rb', App\Http\Controllers\PenyesuaianRbGuruController::class)->middleware('auth');
Route::post('/rb/import', [App\Http\Controllers\PenyesuaianRbGuruController::class, 'import'])->name('rb.import')->middleware('auth');

//harga saptataruna
Route::resource('harga', HargaSaptatarunaController::class)->middleware('auth');

//jadwal detail
Route::get('/jadwal', [JadwalDetailController::class, 'index'])->name('jadwal.index')->middleware('auth');
Route::get('/jadwal/generate', [JadwalDetailController::class, 'generate'])->name('jadwal.generate')->middleware('auth');

// === Resource CRUD Voucher ===
Route::middleware('auth')->group(function () {
    Route::get('/voucher', [VoucherLamaController::class, 'index'])->name('voucher.index');
    Route::get('/voucher/create', [VoucherLamaController::class, 'create'])->name('voucher.create');
    Route::post('/voucher', [VoucherLamaController::class, 'store'])->name('voucher.store');
    Route::get('/voucher/{id}/edit', [VoucherLamaController::class, 'edit'])->name('voucher.edit');
    Route::put('/voucher/{id}', [VoucherLamaController::class, 'update'])->name('voucher.update');
    Route::delete('/voucher/{id}', [VoucherLamaController::class, 'destroy'])->name('voucher.destroy');

    // === AJAX / Helper Routes ===
    Route::get('/get-buku-induk/{nim}', [VoucherLamaController::class, 'getBukuInduk'])->name('voucher.getBukuInduk');

    // === Histori Voucher ===
    Route::get('/voucher/{id}/histori', [VoucherLamaController::class, 'histori'])->name('voucher.histori');

    // === Export Excel ===
    Route::get('/voucher/export', [VoucherLamaController::class, 'export'])->name('voucher.export');
    
    Route::get('/get-murid-by-unit', [VoucherLamaController::class, 'getMuridByUnit'])->name('get.murid.by.unit');
});

// === Import Voucher Lama (GET untuk form + POST untuk proses) ===
Route::middleware('auth')->group(function () {
    // GET: tampilkan form import (modal atau halaman terpisah)
    Route::get('/voucher-lama/import', function () {
        return view('voucher-lama-import'); // pastikan view ini ada
    })->name('voucher-lama.import.form');

    // POST: proses import file Excel
    Route::post('/voucher-lama/import', [VoucherLamaController::class, 'import'])->name('voucher-lama.import');
});
Route::get('/get-buku-induk/{nim}', [VoucherLamaController::class, 'getBukuInduk'])->name('voucher.getBukuInduk');

Route::post('/voucher-lama/import', [VoucherLamaController::class, 'import'])->name('voucher-lama.import')->middleware('auth');

Route::post('/voucher/store-from-spin', [\App\Http\Controllers\VoucherLamaController::class, 'storeFromSpin'])
    ->name('voucher.storeFromSpin');
Route::match(['patch', 'post'], '/voucher/{id}/inline', [VoucherLamaController::class, 'updateInline'])
     ->name('voucher.updateInline');
Route::post('/voucher/upload-bukti-by-nim', [App\Http\Controllers\VoucherLamaController::class, 'uploadBuktiByNim'])
    ->name('voucher.uploadBuktiByNim');
Route::get('/voucher/bukti/{id}', [App\Http\Controllers\VoucherLamaController::class, 'serveBukti'])->name('voucher.serveBukti');
Route::post('/voucher/histori/{id}/upload-bukti', [\App\Http\Controllers\VoucherLamaController::class, 'uploadHistoriBukti'])
    ->name('voucher.histori.uploadBukti');
Route::get('/voucher/pdf', [VoucherLamaController::class, 'exportPdf'])
    ->name('voucher.pdf');    
Route::get('/voucher/{id}/print', [VoucherLamaController::class, 'printVoucher'])
    ->name('voucher.print');
    


Route::get('/cara-perhitungan', [App\Http\Controllers\CaraPerhitunganController::class, 'index'])->name('cara-perhitungan.index')->middleware('auth');

//rumus bikin puyeng
Route::get('/rekap-progresif/profile/{nama}', [App\Http\Controllers\RekapProgresifController::class, 'getProfile'])->middleware('auth');
Route::get('/get-total-spp', [App\Http\Controllers\RekapProgresifController::class, 'getTotalSpp'])->name('get.total.spp')->middleware('auth');
Route::get('/rekap-progresif/spp/{profile_id}/{bulan}/{tahun}', [App\Http\Controllers\RekapProgresifController::class, 'getSPP'])->middleware('auth');
Route::get('/rekap-progresif/calculate', [RekapProgresifController::class, 'calculate'])->middleware('auth');

//pindah golongan
Route::resource('pindah-golongan', PindahGolonganController::class);
// 👇 letakkan ini di bagian bawah atau sesuai pengelompokan route kamu
Route::get('/sync-nim-sheet', function () {
    try {
        $sheetId = env('GOOGLE_SHEETS_SPREADSHEET_ID');
        $sheetName = 'Pindah Golongan Responses';
        $col = 'C'; // Kolom daftar NIM - Nama

        // 1) Client
        $client = new GoogleClient();
        $client->setApplicationName(env('GOOGLE_APPLICATION_NAME', 'Laravel Google Sheet'));
        $client->setAuthConfig(env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION'));
        $client->setScopes([GoogleSheets::SPREADSHEETS]);
        $service = new GoogleSheets($client);

        // 2) Pastikan tab ada
        $spreadsheet = $service->spreadsheets->get($sheetId);
        $sheetTitles = collect($spreadsheet->getSheets())
            ->map(fn($s) => $s->getProperties()->getTitle())
            ->toArray();

        if (!in_array($sheetName, $sheetTitles, true)) {
            return redirect()
                ->route('buku-induk.index') // ganti ke nama route index kamu bila berbeda
                ->with('error', "Tab '{$sheetName}' tidak ditemukan.");
        }

        // 3) Ambil NIM dan Nama dari DB
        $rows = BukuInduk::orderBy('nim')->get(['nim', 'nama']);
        if ($rows->isEmpty()) {
            return redirect()
                ->route('buku-induk.index')
                ->with('warning', 'Tidak ada data NIM di database.');
        }

        // Format: "NIM - Nama"
        $values = $rows->map(fn($r) => ["{$r->nim} - {$r->nama}"])->toArray();

        // 4) Range kolom C saja
        $rangeHeader = "'{$sheetName}'!{$col}1";
        $rangeData = "'{$sheetName}'!{$col}2:{$col}" . (count($values) + 1);

        // Bersihkan isi lama kolom C
        $service->spreadsheets_values->clear(
            $sheetId,
            "'{$sheetName}'!{$col}1:{$col}100000",
            new GoogleSheets\ClearValuesRequest()
        );

        // Tulis header "NIM LIST"
        $service->spreadsheets_values->update(
            $sheetId,
            $rangeHeader,
            new GoogleSheets\ValueRange(['values' => [['NIM LIST']]]),
            ['valueInputOption' => 'RAW']
        );

        // Tulis data NIM - Nama
        $service->spreadsheets_values->update(
            $sheetId,
            $rangeData,
            new GoogleSheets\ValueRange([
                'majorDimension' => 'ROWS',
                'values' => $values
            ]),
            ['valueInputOption' => 'RAW']
        );

        return redirect()
            ->route('buku-induk.index')
            ->with('success', 'Sinkronisasi NIM + Nama ke kolom C pada tab Pindah Golongan Responses sukses.');
    } catch (\Throwable $e) {
        Log::error('Sync NIM Sheet error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return redirect()
            ->route('buku_induk.index')
            ->with('error', 'Terjadi kesalahan saat sinkronisasi. Cek log untuk detailnya.');
    }
})->middleware('auth')->name('sheets.sync-nim');


// Route::get('/google-form/responses', [GoogleFormController::class, 'index'])
//     ->name('google.form.responses')->middleware('auth');

Route::resource('registrations', RegistrationController::class)->middleware('auth');

Route::resource('students', StudentController::class)->only([
    'index',
    'create',
    'store',
    'edit',
    'update',
    'destroy'
])->middleware('auth');

Route::post('/students/import/google', [StudentController::class, 'importFromSheet'])
    ->name('students.import')->middleware('auth');
Route::patch('students/{student}/phone', [StudentController::class, 'updatePhone'])
    ->name('students.updatePhone');

Route::resource('mutations', MutationController::class)->middleware('auth');

Route::get('/wheels', [WheelController::class, 'index'])->name('wheels.index');
Route::get('/wheels/names', [WheelController::class, 'names'])->name('wheels.names');
Route::get('/wheels/lookup', [WheelController::class, 'lookup'])->name('wheels.lookup');
Route::post('/wheels/spin', [WheelController::class, 'spin'])->name('wheels.spin');
Route::get('/wheels/history', [WheelController::class, 'history'])->name('wheels.history');
Route::delete('/wheels/delete', [WheelController::class, 'delete'])->name('wheels.delete');

/*
|--------------------------------------------------------------------------
| Public (signed) wheel page for parents
|--------------------------------------------------------------------------
| - URL: /wheels/public?row_hash=...
| - I recommend protecting this GET with signed middleware so the link cannot
|   be guessed/abused. The view should render the public wheel UI.
| - You may also expose a public spin POST endpoint, but beware CSRF:
|   either use the same signed route for POST or protect with a token.
|
*/
// di routes/web.php atau wheels.php
Route::get('/wheel/{row_hash}', [WheelController::class, 'publicIndex'])
    ->name('wheels.public.index');

// endpoint POST untuk spin dari halaman publik
// jangan beri {row_hash} di path; publicSpin akan baca row_hash dari request (body/query)
Route::post('/wheels/public/spin', [WheelController::class, 'publicSpin'])
    ->name('wheels.public.spin');

Route::post('/wheels/history/clear', [WheelController::class, 'clearHistory'])
    ->name('wheels.history.clear');
Route::get('/wheels/parent-link', [WheelController::class, 'getParentSpinLink'])
     ->name('wheels.parent.link');


Route::prefix('imbalan-rekap')->name('imbalan_rekap.')->group(function () {
    Route::get('/', [ImbalanRekapController::class, 'index'])->name('index');
    Route::get('refresh', [ImbalanRekapController::class, 'refresh'])->name('refresh');
    Route::delete('truncate', [ImbalanRekapController::class, 'truncate'])->name('truncate');
    Route::post('update-inline', [ImbalanRekapController::class, 'updateInline'])->name('update-inline');

    Route::get('slip/{id}', [ImbalanRekapController::class, 'slip'])->name('slip');
    Route::get('{id}/pdf', [ImbalanRekapController::class, 'pdf'])->name('pdf');

    Route::get('generate', [ImbalanRekapController::class, 'showGenerateForm'])->name('generate.form');
    Route::post('generate', [ImbalanRekapController::class, 'generateMonth'])->name('generate');

    Route::get('slips', [ImbalanRekapController::class, 'indexSlip'])->name('slip.index');

    // Tambahkan route bayar-single DI SINI (dalam group)
    Route::post('bayar-single', [ImbalanRekapController::class, 'bayarSingle'])->name('bayar-single');

    // Route lain yang sudah ada
    Route::post('bayar-periode', [ImbalanRekapController::class, 'bayarPeriode'])->name('bayar_periode');
});

Route::middleware('auth')->group(function () {
    Route::get('/imbalan-rekap/relawan-filter', [ImbalanRekapController::class, 'getRelawansByFilter'])
        ->name('imbalan_rekap.relawan_filter');
});


Route::resource('komisi', KomisiController::class);
Route::post('/komisi/generate', [KomisiController::class, 'generate'])->name('komisi.generate');
Route::post('/komisi/sync', [KomisiController::class, 'sync'])->name('komisi.sync');
// Menu Pembayaran Komisi (baru & terpisah)
// Ganti semua route pembayaran-komisi jadi ini saja
Route::prefix('pembayaran-komisi')->name('pembayaran-komisi.')->group(function () {
    Route::get('/', [PembayaranKomisiController::class, 'index'])->name('index');

    // PERBAIKAN: JANGAN PAKAI /pembayaran-komisi/save DI DALAM PREFIX LAGI!
    // CUKUP /save SAJA, KARENA SUDAH DI DALAM PREFIX
    Route::post('/save', [PembayaranKomisiController::class, 'savePembayaran'])
        ->name('save'); // nama route jadi: pembayaran-komisi.save

    // GUNAKAN INI SAJA → cukup satu route
    Route::post('/update', [PembayaranKomisiController::class, 'updateAdjustment'])
        ->name('update');

    Route::get('/export/{bulan}/{tahun}', [PembayaranKomisiController::class, 'export'])
        ->name('export');
});

Route::prefix('slip-komisi')->name('slip-komisi.')->group(function () {
    Route::get('/', [SlipKomisiController::class, 'index'])->name('index');

    // Route ini yang dipanggil iframe
    Route::get('/preview-pdf', [SlipKomisiController::class, 'previewPDF'])
        ->name('preview-pdf');
});
Route::middleware(['auth'])->group(function () {

    // Dashboard semua unit
    Route::get('/admin/perkembangan-units', [AdminPerkembanganUnitController::class, 'index'])
        ->name('admin.perkembangan-units.index');

    // DETAIL PER UNIT → INI YANG DIPAKAI DI BLADE
    Route::get('/admin/perkembangan-units/{unit}', [AdminPerkembanganUnitController::class, 'detail'])
        ->name('perkembangan-units.detail');
    // ↑ nama ini HARUS SAMA persis dengan yang dipakai di blade
    
    Route::get('/admin/rekap-pengeluaran', [AdminRekapPengeluaranController::class, 'index'])
         ->name('admin.rekap-pengeluaran.index');
    
});

Route::prefix('relawan')->name('relawan.')->middleware('auth')->group(function () {
    Route::get('/', [RelawanAbsenController::class, 'index'])->name('index');
    Route::post('/store', [RelawanAbsenController::class, 'store'])->name('store');
    Route::patch('/{id}/status', [RelawanAbsenController::class, 'updateStatus'])->name('updateStatus');
    Route::post('/{id}/pulang', [RelawanAbsenController::class, 'absenPulang'])->name('pulang');
    Route::get('/{id}/edit', [RelawanAbsenController::class, 'edit'])->name('edit');
    Route::put('/{id}', [RelawanAbsenController::class, 'update'])->name('update');
    Route::delete('/{id}', [RelawanAbsenController::class, 'destroy'])->name('destroy');
    Route::post('/import', [RelawanAbsenController::class, 'import'])->name('import');
    Route::get('/export', [RelawanAbsenController::class, 'export'])->name('export');
});

Route::get('/summary-keuangan', [FinancialSummaryController::class, 'index'])
    ->name('summary.keuangan');


Route::resource('adjustments', AdjustmentController::class);

Route::middleware('auth')->group(function () {
    Route::resource('cash-advance', CashAdvanceController::class);

    Route::post('cash-advance/{cashAdvance}/approve', [CashAdvanceController::class, 'approve'])
        ->name('cash-advance.approve');

    Route::post('cash-advance/{cashAdvance}/reject', [CashAdvanceController::class, 'reject'])
        ->name('cash-advance.reject');
    // Di dalam group middleware auth
    Route::delete('/cash-advance/{cashAdvance}', [CashAdvanceController::class, 'destroy'])
        ->name('cash-advance.destroy');
});
Route::resource('cash-advance-installments', CashAdvanceInstallmentController::class)
    ->names('cash-advance.installments')
    ->only(['index']); // hanya index yang dipakai

Route::get('/ajax/murid-detail/{nim}', function ($nim) {
    $murid = \App\Models\BukuInduk::where('nim', trim($nim))->firstOrFail();

    return response()->json([
        'tgl_lahir'     => optional($murid->tgl_lahir)->format('Y-m-d'),
        'alamat'        => $murid->alamat_murid,
        'orang_tua'     => $murid->orangtua,
        'golongan'      => $murid->gol,
    ]);
})->middleware('auth');
Route::get(
    '/sertifikat-beasiswa/{id}/pdf',
    [App\Http\Controllers\SertifikatBeasiswaController::class, 'pdf']
)->name('sertifikat-beasiswa.pdf');

Route::get(
    '/garansi-bca/{id}/pdf',
    [GaransiBCAController::class, 'pdf']
)->name('garansi-bca.pdf')->middleware('auth');
Route::get('/kartu-spp/pdf/{nim}', [App\Http\Controllers\KartuSppController::class, 'exportPdf'])
    ->name('kartu-spp.pdf');
    Route::get('/cron/rekap-26/{token}', function ($token) {
    abort_unless($token === config('app.cron_token'), 403);
    Artisan::call('rekap:generate-bulanan');
});
Route::post('/profiles/import-buku-induk', [ProfileController::class, 'importBukuInduk'])->name('profiles.import-buku-induk');


//teknik inject xss di dalam program
Route::post('/system/auto-activate-trial', function () {

    $batas = now()->subDay();

    $trials = MuridTrial::where('status_trial', 'baru')
        ->whereNull('tanggal_aktif')
        ->where('waktu_submit', '<=', $batas)
        ->get();

    foreach ($trials as $trial) {
        $trial->update([
            'status_trial'  => 'aktif',
            'tanggal_aktif' => Carbon::parse($trial->waktu_submit)->addDay(),
        ]);
    }

    return response()->json([
        'updated' => $trials->count()
    ]);
})->middleware('auth'); // penting supaya tidak bisa diakses publik
// Route khusus AJAX - HARUS DITARUH SEBELUM resource
Route::get('/lembur/get-profiles-by-unit', [LemburController::class, 'getProfilesByUnit'])
     ->name('lembur.getProfilesByUnit');

// Resource Route (standar CRUD)
Route::resource('lembur', LemburController::class);
