@extends('layouts.app')
@section('title','Lembar Komitmen Orang Tua')

@section('content')
<div class="container py-4">
  <h3 class="mb-3">LEMBAR KOMITMEN ORANG TUA PRA - biMBA AIUEO</h3>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="border rounded p-3 mb-4" style="white-space: pre-wrap; max-height: 60vh; overflow:auto;">
“Bersama Menumbuh Kembangkan Minat Baca Dan Minat Belajar Anak Sejak Dini”

1. Komitmen terhadap Filosofi Minat Baca dan Minat Belajar

Dengan ini saya menyatakan memahami dan menyetujui bahwa pelaksanaan pendidikan biMBA AIUEO berfokus pada menumbuh kembangkan minat baca dan minat belajar anak sejak dini, bukan mengejar target akademik instan.

Saya akan selalu berupaya:

Memahami bahwa setiap anak unik dan memiliki ritme belajar masing-masing.

Percaya pada proses belajar anak tanpa tekanan.

Mengutamakan dan Mendukung Paradigma serta filosofi biMBA dalam setiap langkah perkembangan anak.

2. Komitmen Peran Orang Tua di Rumah

Mendukung tumbuh kembangnya minat belajar anak dengan selalu mengutamakan paradigma dan filosofi biMBA AIUEO (Menyenangkan, Bertahap, dan Terarah.

Tidak memaksa anak untuk cepat bisa membaca, menulis, atau berhitung

Memberi apresiasi kecil saat anak menunjukkan minat belajarnya.

Tidak membandingkan anak dengan anak lain.

Merawat dan Menjaga suasana rumah yang positif dan suportif dengan penuh cinta

3. Komitmen Orang Tua dengan biMBA AIUEO

Memahami bahwa biMBA AIUEO adalah lembaga penumbuh minat baca dan minat belajar anak yang berorientasi PROSES bukan HASIL, atau bukan tempat les baca (mengejar target akademik instan).

Menjadi mitra aktif, bukan penonton, dalam proses tumbuh kembang belajar anak.

Menjalin komunikasi secara aktif dan terbuka dengan motivator dan kepala unit.

Siap berpartisipasi dalam program pendukung yang diadakan seperti ha;lnya kelas inspirasi, forum sharing, dan festival atau pentas minat baca dan belajar.

Percaya dan mendukung metode biMBA AIUEO sepenuh hati.

4. Komitmen Etika dan Sikap Positif

Menghargai dan mendukung motivator dan kepala unit biMBA AIUEO.

Tidak memberi tekanan/paksaan pada anak jika prosesnya tidak sesuai ekspektasi.

Menjadi teladan positif dalam sikap proses belajar.

5. Pernyataan Tertulis

Dengan menandatangani lembar komitmen ini, saya menyatakan siap menjadi bagian dari perjalanan tumbuh kembangnya minat belajar anak saya di biMBA AIUEO. Saya akan selalu berupaya mengerti, mendukung dengan penuh cinta, dan kepercayaan pada prosesnya.

Nama Orang Tua/Wali: __________________________________________

Nama Anak: __________________________________________

Alamat: __________________________________________

No. Telp/WA: __________________________________________

_____________, ____/____/_________

Yang Membuat,

Nama:_______________

“Bisa Baca itu BIASA, MINAT Baca baru LUAR BIASA”

“Minat lebih penting daripada hasil. Kemampuan akan tumbuh dengan sendirinya saat minatnya hidup.”
  </div>

  <form method="POST" action="{{ route('murid_trials.commitment.store', $murid_trial->id) }}">
    @csrf

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">Nama Orang Tua/Wali</label>
        <input type="text" name="parent_name" class="form-control" required
               value="{{ old('parent_name', $prefill['parent_name'] ?? '') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Nama Anak</label>
        <input type="text" name="child_name" class="form-control" required
               value="{{ old('child_name', $prefill['child_name'] ?? '') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">No. Telp/WA</label>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone', $prefill['phone'] ?? '') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Alamat</label>
        <input type="text" name="address" class="form-control"
               value="{{ old('address', $prefill['address'] ?? '') }}">
      </div>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" value="1" id="agree" name="agree" required>
      <label class="form-check-label" for="agree">
        Saya telah membaca dan <strong>MENYETUJUI</strong> komitmen orang tua.
      </label>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('murid_trials.index') }}" class="btn btn-secondary">Kembali</a>
      <button type="submit" class="btn btn-primary">Setuju & Lanjutkan</button>
    </div>
  </form>
</div>
@endsection
