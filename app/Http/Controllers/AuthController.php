<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle user login attempt (Admin & Siswa).
     */
    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $login_id = $request->login_id;
        $password = $request->password;

        // 2. Cek Logika Login
        // --- COBA LOGIN SEBAGAI PETUGAS / KEPALA DENGAN NIP (Guard: web) ---
        if (Auth::guard('web')->attempt(['nip' => $login_id, 'password' => $password])) {
            $request->session()->regenerate();
            
            // AMBIL DATA USER UNTUK CEK ROLE
            $user = Auth::guard('web')->user();

            // LOGIKA PENGALIHAN BERDASARKAN ROLE
            if ($user->role === 'head') {
                return redirect()->route('head.dashboard')->with('success', 'Selamat datang, Kepala Perpustakaan!');
            }

            return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat datang, Admin!');
        }

        // --- JIKA GAGAL, COBA LOGIN SEBAGAI SISWA DENGAN NIS (Guard: student) ---
        if (Auth::guard('student')->attempt(['nis' => $login_id, 'password' => $password])) {
            $request->session()->regenerate();
            return redirect()->route('student.dashboard')->with('success', 'Selamat datang, Siswa!');
        }

        // 3. Jika Gagal Keduanya
        return back()->withErrors([
            'login_id' => 'NIP/NIS atau password salah.',
        ])->onlyInput('login_id');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        // Cek guard mana yang sedang aktif, lalu logout
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        } elseif (Auth::guard('student')->check()) {
            Auth::guard('student')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout berhasil!');
    }
}