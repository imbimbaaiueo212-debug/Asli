<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|unique:users,email',
            'password'   => 'required|string|min:6|confirmed', // confirmed = password_confirmation
            'photo'      => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:3048',
            'role'       => 'required|string|in:admin,user,cabang,guru,developer,superadmin,owner,direktur',
            'bimba_unit' => 'nullable|string|max:100',
            'no_cabang'  => 'nullable|string|max:20', // sesuaikan tipe & panjang dengan migration
        ]);

        // Hash password sebelum disimpan
        $validated['password'] = Hash::make($validated['password']);

        // Upload foto kalau ada
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
{
    // SELALU kirim $units, jangan dibatasi role di sini
    $units = \App\Models\Unit::orderBy('biMBA_unit')->get();

    return view('users.edit', compact('user', 'units'));
}

   public function update(Request $request, User $user)
{
    $currentUser = auth()->user();

    // Daftar email khusus yang boleh edit unit (tapi TIDAK boleh edit role)
    $emailKhusus = [
        'oktaviandaaria@gmail.com',
        'robiensyah22@gmail.com',
    ];

    $bolehEditRole = $currentUser->isAdminUser(); // HANYA admin yang boleh ubah role

    // Validasi dasar
    $rules = [
        'name'       => 'required|string|max:255',
        'email'      => 'required|string|email|unique:users,email,' . $user->id,
        'password'   => 'nullable|string|min:6|confirmed',
        'photo'      => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:3048',
        'bimba_unit' => 'nullable|string|max:100',
        'no_cabang'  => 'nullable|string|max:20',
    ];

    // Tambahkan validasi role HANYA jika user boleh edit role
    if ($bolehEditRole) {
        $rules['role'] = 'required|string|in:admin,user,cabang,guru,developer,superadmin,owner,direktur';
    }

    $validated = $request->validate($rules);

    // === LOGIKA ROLE: Hanya admin yang boleh ubah ===
    if ($bolehEditRole) {
        $validated['role'] = $request->role;
    } else {
        // Non-admin: role tetap seperti semula (tidak diubah)
        // Tidak perlu unset, karena kita hanya update field yang ada di $validated
    }

    // Password: hanya hash jika diisi
    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    // Upload foto baru
    if ($request->hasFile('photo')) {
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }
        $validated['photo'] = $request->file('photo')->store('photos', 'public');
    }

    // Update data user
    $user->update($validated);

    // === REDIRECT KONDISIONAL BERDASARKAN ROLE USER YANG LOGIN ===
    if ($currentUser->isAdminUser()) {
        // Admin → kembali ke daftar user
        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    // User biasa (mengedit profil sendiri) → kembali ke home/dashboard
    return redirect()
        ->route('home') // ganti 'home' jika route dashboard kamu berbeda (misal 'dashboard')
        ->with('success', 'Profil Anda berhasil diperbarui');
}

    public function destroy(User $user)
    {
        // Hapus foto jika ada
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }
}