<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter berdasarkan pencarian nama atau NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan peran (role)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|string|max:50|unique:users,nip', // Validasi NIP
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'name'     => $request->name,
            'nip'      => $request->nip,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'role'     => 'admin',
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan!');
    }

    // PERBAIKAN: Menggunakan $id untuk memastikan pencarian data akurat dari action URL Alpine.js
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|min:6|confirmed', // Tetap nullable agar opsional saat edit
        ]);

        $data = [
            'name'     => $request->name,
            'nip'      => $request->nip,
            'email'    => $request->email,
            'phone'    => $request->phone,
        ];

        // Memeriksa jika field password diisi oleh user
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Lakukan update ke database
        $user->update($data);

        return redirect()->back()->with('success', 'Data user berhasil diperbarui!');
    }

    // PERBAIKAN: Disamakan menggunakan $id agar konsisten dengan penanganan request dari view
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() == $user->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }
}