<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    // 1. Buat Akun Admin
        User::create([
            'name' => 'Admin Perpus',
            'email' => 'admin@gmail.com',
            'nip' => '12345678',         // NIP untuk login
            'password' => Hash::make('password'), // Password: password
            'created_at' => now(),
        ]);

        // Buat Kepala Perpustakaan
        User::create([
            'name' => 'Kepala Perpus',
            'email' => 'kepala@mail.com',
            'nip' => '11223344',
            'password' => bcrypt('password123'),
            'role' => 'head',
        ]);

        // Buat Petugas Biasa
        User::create([
            'name' => 'Petugas Admin',
            'email' => 'admin@mail.com',
            'nip' => '87654321',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
    }
}
