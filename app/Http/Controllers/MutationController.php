<?php

namespace App\Http\Controllers;

use App\Models\Mutation;
use App\Models\Student;
use App\Models\BukuInduk;
use App\Models\MuridTrial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MutationController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $tipe = $request->input('tipe'); // masuk|keluar|null

        $mutations = Mutation::with('student')
            ->when($q, fn($qr)=>$qr->where(function($w) use ($q){
                $w->where('nama','like',"%$q%")
                  ->orWhereHas('student', fn($s)=>$s->where('nama','like',"%$q%")->orWhere('nim','like',"%$q%"));
            }))
            ->when($tipe, fn($qr)=>$qr->where('tipe',$tipe))
            ->latest()->paginate(15)->withQueryString();

        return view('mutations.index', compact('mutations','q','tipe'));
    }

    public function create()
    {
        return view('mutations.create'); // form sederhana
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'     => ['nullable','exists:students,id'],
            'nama'           => ['required_without:student_id','string','max:255'],
            'asal_unit'      => ['nullable','string','max:255'],
            'asal_kode'      => ['nullable','string','max:50'],
            'tanggal_mutasi' => ['nullable','date'],
            'alasan'         => ['nullable','string'],
            'keterangan'     => ['nullable','string'],
            'tipe'           => ['required','in:masuk,keluar'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            // Jika belum ada student, buat student baru minimal (source tetap 'direct' sesuai enum kamu)
            if (empty($data['student_id']) && $data['tipe'] === 'masuk') {
                $student = Student::create([
                    'nim'        => $this->generateNim(),
                    'nama'       => $data['nama'],
                    'source'     => 'direct',         // enum kamu: trial|direct
                    'promoted_at'=> now(),
                ]);
                // Tambah ke Buku Induk agar rapi
                BukuInduk::create([
                    'nim'    => $student->nim,
                    'nama'   => $student->nama,
                    'status' => 'Mutasi Masuk',
                ]);
                $data['student_id'] = $student->id;
            }

            $data['created_by'] = Auth::id();
            $mutation = Mutation::create($data);

            return redirect()->route('mutations.index')
                ->with('success', 'Mutasi berhasil dicatat.');
        });
    }

    public function edit(Mutation $mutation)
    {
        return view('mutations.edit', compact('mutation'));
    }

    public function update(Request $request, Mutation $mutation)
    {
        $data = $request->validate([
            'asal_unit'      => ['nullable','string','max:255'],
            'asal_kode'      => ['nullable','string','max:50'],
            'tanggal_mutasi' => ['nullable','date'],
            'alasan'         => ['nullable','string'],
            'keterangan'     => ['nullable','string'],
            'tipe'           => ['required','in:masuk,keluar'],
        ]);
        $data['updated_by'] = Auth::id();
        $mutation->update($data);

        return redirect()->route('mutations.index')->with('success','Mutasi diperbarui.');
    }

    public function destroy(Mutation $mutation)
    {
        $mutation->delete();
        return redirect()->route('mutations.index')->with('success','Mutasi dihapus.');
    }

    // Helper generate NIM (sinkron dengan pola di project kamu)
    protected function generateNim(): string
    {
        $last = BukuInduk::whereRaw('nim REGEXP "^[0-9]+$"')
            ->orderByRaw('CAST(nim AS UNSIGNED) DESC')
            ->value('nim');
        if (!$last) return '010450001';
        return str_pad((string)((int)$last + 1), 9, '0', STR_PAD_LEFT);
    }
}
