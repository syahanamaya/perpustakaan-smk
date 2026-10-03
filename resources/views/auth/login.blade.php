<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>Login - Perpustakaan SMK Budi Mulia</title>
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    
    {{-- Menambahkan Alpine.js untuk interaktivitas --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
        }
        
        .bg-wave {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Dekorasi lengkungan sederhana untuk sisi kiri */
        .bg-wave::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .bg-wave::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
    </style>
</head>
<body class="h-screen flex flex-col md:flex-row antialiased md:overflow-hidden">

    {{-- KIRI: Banner Informasi (Disembunyikan di layar HP, tampil di Desktop) --}}
    <div class="bg-wave hidden md:flex md:w-1/2 lg:w-7/12 flex-col justify-between p-12 lg:p-20 text-white relative shadow-2xl z-10">
        
        {{-- Logo & Header Info --}}
        <div>
            <div class="flex items-center gap-4 mb-12">
                <div class="w-14 h-14 rounded-lg flex items-center justify-center shadow-lg">
                    <img src="{{ asset('logo.png') }}" alt="Logo SMK Budi Mulia" class="h-12 object-contain">
                </div>
                <div>
                    <h1 class="text-2xl font-semibold tracking-wide">SMK BUDI MULIA</h1>
                    <p class="text-sm font-light text-blue-100">Beriman • Berilmu • Berkarya</p>
                </div>
            </div>

            <h2 class="text-3xl font-semibold mb-4 leading-tight">Sistem Informasi<br>Perpustakaan</h2>
            <p class="text-base text-blue-100 font-light max-w-md leading-relaxed">
                Akses layanan perpustakaan dengan mudah, cepat, dan terintegrasi untuk siswa, petugas, dan kepala perpustakaan.
            </p>
        </div>

        {{-- 3 Fitur Bawah --}}
        <div class="grid grid-cols-3 gap-6 mt-12 bg-white/10 p-6 rounded-2xl backdrop-blur-sm border border-white/20">
            <div>
                <i class="fas fa-book-open text-xl mb-3"></i>
                <h3 class="font-medium text-sm mb-1">Koleksi Lengkap</h3>
                <p class="text-xs text-blue-100 font-light">Ribuan buku dan sumber belajar tersedia</p>
            </div>
            <div>
                <i class="fas fa-clock text-xl mb-3"></i>
                <h3 class="font-medium text-sm mb-1">Akses Mudah</h3>
                <p class="text-xs text-blue-100 font-light">Pinjam dan kembalikan kapan saja</p>
            </div>
            <div>
                <i class="fas fa-chart-line text-xl mb-3"></i>
                <h3 class="font-medium text-sm mb-1">Laporan Akurat</h3>
                <p class="text-xs text-blue-100 font-light">Data dan laporan terintegrasi</p>
            </div>
        </div>
    </div>

    {{-- KANAN: Form Login --}}
    <div class="w-full md:w-1/2 lg:w-5/12 flex flex-col items-center justify-center p-6 lg:p-12 relative bg-linear-to-br from-white to-[#eef2f6]">
        
        {{-- Card Login --}}
        <div class="w-full max-w-105 bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.06)] p-8 lg:p-10 z-10 border border-gray-100">
            
            {{-- Icon Header --}}
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 shadow-md shadow-blue-200">
                <img src="{{ asset('logo.png') }}" alt="Logo SMK Budi Mulia" class="h-12 object-contain">
            </div>

            {{-- Welcome Text --}}
            <div class="text-center mb-8">
                <h2 class="text-[22px] font-semibold text-gray-800 mb-2">Selamat Datang</h2>
                <p class="text-[13px] font-normal text-gray-500 leading-relaxed">
                    Masuk untuk mengakses Sistem Perpustakaan<br>SMK Budi Mulia Ciledug
                </p>
            </div>

            {{-- Alert Messages --}}
            @if (session('success'))
                <div class="mb-5 p-3 bg-green-50 border border-green-100 rounded-xl text-green-600 text-sm flex items-start gap-2">
                    <i class="fas fa-check-circle mt-0.5"></i> 
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm flex items-start gap-2">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('login.process') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Input Username --}}
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-2">Username / NIS / NIP</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <i class="fas fa-user text-[14px]"></i>
                        </span>
                        <input 
                            type="text" 
                            name="login_id"
                            value="{{ old('login_id') }}"
                            placeholder="Masukkan ID Anda"
                            class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl outline-none text-[14px] text-gray-700 font-normal focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition-all"
                            required
                        >
                    </div>
                </div>

                {{-- Input Password --}}
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-[13px] font-medium text-gray-700">Password</label>
                    </div>
                    {{-- Menambahkan Alpine.js untuk toggle show/hide password --}}
                    <div x-data="{ showPassword: false }" class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <i class="fas fa-lock text-[14px]"></i>
                        </span>
                        <input 
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            placeholder="Masukkan password Anda"
                            class="w-full pl-11 pr-10 py-3 bg-white border border-gray-200 rounded-xl outline-none text-[14px] text-gray-700 font-normal focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition-all"
                            required
                        >
                        {{-- Toggle Show Password --}}
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas text-[14px]" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Checkbox Remember Me --}}
                <div class="flex items-center pt-1 pb-2">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                    <label for="remember" class="ml-2 text-[13px] font-normal text-gray-600 cursor-pointer select-none">Ingat sesi saya</label>
                </div>

                {{-- Submit Button --}}
                <button 
                    type="submit"
                    class="w-full py-3.5 bg-blue-600 text-white text-[14px] font-medium rounded-xl hover:bg-blue-700 active:bg-blue-800 transition-all shadow-lg shadow-blue-600/30 flex justify-center items-center gap-2"
                >
                    Masuk <i class="fas fa-arrow-right text-[12px]"></i>
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="absolute bottom-2 text-center w-full px-6">
            <p class="text-gray-400 text-[12px] font-normal leading-relaxed">
                &copy; {{ date('Y') }} Sistem Informasi Perpustakaan
                Dikembangkan untuk SMK Budi Mulia
            </p>
        </div>
        
    </div>

</body>
</html>