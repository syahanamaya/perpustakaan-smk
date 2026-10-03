<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>{{ $title ?? 'Library Management System' }} - SMK Budi Mulia</title>
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }

        /* Sembunyikan scrollbar di SEMUA elemen halaman */
        * {
            -ms-overflow-style: none !important;  /* Untuk IE dan Edge */
            scrollbar-width: none !important;  /* Untuk Firefox */
        }
        
        *::-webkit-scrollbar {
            display: none !important; /* Untuk Chrome, Safari, dan Opera */
        }
        .sidebar-bg { background-color: #2c3e50; }
        .sidebar-item-active { background-color: #34495e; border-left: 4px solid #3b82f6; color: white !important; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased">

    {{-- ============================================================ --}}
    {{-- CEK JIKA YANG LOGIN ADALAH SISWA --}}
    {{-- ============================================================ --}}
    @if(auth()->guard('student')->check())
        <div x-data="{ mobileMenuOpen: false }" class="flex flex-col min-h-screen">
            
            <nav class="bg-[#0c4aae] text-white shadow-md sticky top-0 z-40">
                <div class="max-w-7xl mx-auto flex justify-between items-center px-4 sm:px-6 lg:px-8 h-16">
                    
                    <div class="flex items-center gap-3">
                        <div class="bg-white p-1 rounded-full w-8 h-8 flex items-center justify-center shadow-sm">
                            <img src="{{ asset('logo.png') }}" alt="Logo SMK Budi Mulia" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <h1 class="font-medium tracking-wider uppercase leading-tight text-[11px]">SMK BUDI MULIA</h1>
                            <p class="text-[9px] text-blue-200 font-medium">Library</p>
                        </div>
                    </div>

                    <div class="hidden lg:flex items-center gap-6 text-[11px] font-medium text-blue-100">
                        <a href="{{ route('student.dashboard') }}" 
                        class="flex items-center gap-1.5 transition-colors {{ request()->routeIs('student.dashboard') ? 'text-white border-b-2 border-white pb-0.5 opacity-100' : 'opacity-80 hover:opacity-100 hover:text-white' }}">
                            <i class="fas fa-home"></i> Beranda
                        </a>
                        
                        <a href="{{ route('student.explore') }}" 
                        class="flex items-center gap-1.5 transition-colors {{ request()->routeIs('student.explore') ? 'text-white border-b-2 border-white pb-0.5 opacity-100' : 'opacity-80 hover:opacity-100 hover:text-white' }}">
                            <i class="fas fa-search"></i> Cari Buku
                        </a>
                        
                        <a href="{{ route('student.borrowed-books') }}" 
                        class="flex items-center gap-1.5 transition-colors {{ request()->routeIs('student.borrowed-books') ? 'text-white border-b-2 border-white pb-0.5 opacity-100' : 'opacity-80 hover:opacity-100 hover:text-white' }}">
                            <i class="fas fa-book"></i> Peminjaman Saya
                        </a>
                        
                        {{-- Menu Tambahan Sesuai Gambar --}}
                        <a href="{{ route('student.history') }}" 
                        class="flex items-center gap-1.5 transition-colors {{ request()->routeIs('student.history') ? 'text-white border-b-2 border-white pb-0.5 opacity-100' : 'opacity-80 hover:opacity-100 hover:text-white' }}">
                            <i class="fas fa-history"></i> Riwayat
                        </a>
                        
                        <a href="{{ route('student.info-fine') }}" 
                        class="flex items-center gap-1.5 transition-colors {{ request()->routeIs('student.info-fine') ? 'text-white border-b-2 border-white pb-0.5 opacity-100' : 'opacity-80 hover:opacity-100 hover:text-white' }}">
                            <i class="fas fa-exclamation-circle"></i> Info Denda
                        </a>
                        
                        <a href="{{ route('student.favorites') }}" 
                        class="flex items-center gap-1.5 transition-colors {{ request()->routeIs('student.favorites') ? 'text-white border-b-2 border-white pb-0.5 opacity-100' : 'opacity-80 hover:opacity-100 hover:text-white' }}">
                            <i class="fas fa-heart"></i> Favorit Saya
                        </a>

                        <a href="{{ route('siswa.announcements') }}" 
                        class="flex items-center gap-1.5 transition-colors {{ request()->routeIs('siswa.announcements') ? 'text-white border-b-2 border-white pb-0.5 opacity-100' : 'opacity-80 hover:opacity-100 hover:text-white' }}">
                            <i class="fas fa-bullhorn"></i> Pengumuman
                        </a>
                    </div>

                    <div class="flex items-center gap-3 ml-4">
                        @php
                            $userSiswa = auth()->guard('student')->user();
                        @endphp
                        
                        <div class="text-right hidden sm:block">
                            <p class="text-[9px] text-blue-200 leading-none mb-0.5">Halo,</p>
                            <p class="font-normal text-[11px] text-white">[{{ $userSiswa->name }}]</p>
                        </div>

                        <div x-data="{ profileOpen: false }" class="relative">
                            <button @click="profileOpen = !profileOpen" class="w-8 h-8 rounded-full bg-blue-400/30 flex items-center justify-center overflow-hidden border border-blue-300/30 hover:bg-blue-400/50 transition-colors focus:outline-none">
                                @if($userSiswa->photo)
                                    <img src="{{ asset('storage/' . $userSiswa->photo) }}" 
                                        alt="Foto {{ $userSiswa->name }}" 
                                        class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-user text-white text-xs"></i>
                                @endif
                            </button>

                            {{-- Dropdown Menu --}}
                            <div x-show="profileOpen" @click.outside="profileOpen = false" x-transition 
                                class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl z-50 py-2 border border-gray-100 text-gray-800" x-cloak>
                                
                                <div class="px-4 py-2 mb-1 border-b border-gray-50">
                                    <p class="text-[9px] font-medium text-gray-400 uppercase tracking-widest">Akun Siswa</p>
                                    <p class="text-xs font-normal text-gray-800 truncate">{{ $userSiswa->name }}</p>
                                </div>

                                <a href="{{ route('student.profile.edit') }}" class="mx-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition-all flex items-center gap-2.5 group">
                                    <div class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-user-edit text-blue-600 text-[10px]"></i>
                                    </div>
                                    Edit Profil
                                </a>

                                <hr class="my-1.5 border-gray-50">

                                <form method="POST" action="{{ route('logout') }}" class="px-1.5">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-1.5 text-xs font-medium hover:bg-red-50 text-red-600 rounded-xl transition-all flex items-center gap-2.5 group">
                                        <div class="w-6 h-6 rounded-lg bg-red-100 flex items-center justify-center group-hover:bg-red-200 transition-colors">
                                            <i class="fas fa-sign-out-alt text-[10px]"></i>
                                        </div>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Tombol Hamburger untuk Mobile --}}
                        <div class="lg:hidden ml-2">
                            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-blue-200 hover:text-white hover:bg-blue-900 focus:outline-none">
                                <span class="sr-only">Buka menu utama</span>
                                <i class="fas" :class="{ 'fa-times': mobileMenuOpen, 'fa-bars': !mobileMenuOpen }"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </nav>

            {{-- MENU MOBILE --}}
            <div x-show="mobileMenuOpen" @click.outside="mobileMenuOpen = false" class="lg:hidden bg-blue-800 border-b border-blue-700" x-cloak>
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="{{ route('student.dashboard') }}" class="block px-3 py-2 rounded-md text-[11px] font-medium {{ request()->routeIs('student.dashboard') ? 'bg-blue-900 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' }}">Beranda</a>
                    
                    <a href="{{ route('student.explore') }}" class="block px-3 py-2 rounded-md text-[11px] font-medium {{ request()->routeIs('student.explore') ? 'bg-blue-900 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' }}">Cari Buku</a>
                    
                    <a href="{{ route('student.borrowed-books') }}" class="block px-3 py-2 rounded-md text-[11px] font-medium {{ request()->routeIs('student.borrowed-books') ? 'bg-blue-900 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' }}">Peminjaman Saya</a>
                    
                    <a href="{{ route('student.history') }}" class="block px-3 py-2 rounded-md text-[11px] font-medium {{ request()->routeIs('student.history') ? 'bg-blue-900 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' }}">Riwayat</a>
                    
                    <a href="{{ route('student.info-fine') }}" class="block px-3 py-2 rounded-md text-[11px] font-medium {{ request()->routeIs('student.info-fine') ? 'bg-blue-900 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' }}">Info Denda</a>
                    
                    <a href="{{ route('student.favorites') }}" class="block px-3 py-2 rounded-md text-[11px] font-medium {{ request()->routeIs('student.favorites') ? 'bg-blue-900 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' }}">Favorit Saya</a>

                    <a href="{{ route('siswa.announcements') }}" class="block px-3 py-2 rounded-md text-[11px] font-medium {{ request()->routeIs('siswa.announcements') ? 'bg-blue-900 text-white' : 'text-blue-200 hover:bg-blue-700 hover:text-white' }}">Pengumuman</a>
                </div>
            </div>

            {{-- MAIN SLOTS --}}
            <main class="flex-1 bg-[#f8fafc]">
                {{ $slot }}
            </main>
        </div>

    {{-- ============================================================ --}}
    {{-- TAMPILAN STAFF (ADMIN / KEPALA PERPUSTAKAAN) --}}
    {{-- ============================================================ --}}
    @elseif(Auth::check())
        
        <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden">
            
            {{-- SIDEBAR KEPALA PERPUSTAKAAN --}}
            @if(Auth::user()->role == 'head')
                <aside class="text-white shadow-2xl flex flex-col transition-all duration-300 ease-in-out relative z-50"
                       style="background: linear-gradient(to bottom, #001d3d, #003566);"
                       :class="sidebarOpen ? 'w-56' : 'w-20'">
                
                {{-- Header Logo --}}
                <div class="h-16 flex items-center px-6 border-b border-white/10">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <img src="{{ asset('logo.png') }}" class="h-8 min-w-8 drop-shadow-md">
                        <span class="font-medium text-white text-sm whitespace-nowrap tracking-wider" x-show="sidebarOpen" x-transition>SMK Budi Mulia</span>
                    </div>
                </div>                                      

                <nav class="flex-1 overflow-y-auto py-4 no-scrollbar space-y-1">
                    <div class="px-6 mb-2 text-white/70 text-[10px] font-medium uppercase tracking-widest" x-show="sidebarOpen">Main Menu</div>
                    
                    {{-- Dashboard --}}
                    <a href="{{ route('head.dashboard') }}" 
                    :title="!sidebarOpen ? 'Dashboard' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.dashboard') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-home w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Dashboard</span>
                    </a>

                    <a href="{{ route('head.reports.index') }}" 
                    :title="!sidebarOpen ? 'Laporan & Statistik' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.reports.index') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-chart-bar w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Laporan & Statistik</span>
                    </a>

                    <a href="{{ route('head.transactions.index') }}" 
                    :title="!sidebarOpen ? 'Riwayat Transaksi' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.transactions.index') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-history w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Riwayat Transaksi</span>
                    </a>

                    {{-- Recap Denda --}}
                    <a href="{{ route('head.fines.recap') }}" 
                    :title="!sidebarOpen ? 'Rekap Denda' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.fines.recap') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-file-invoice-dollar w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Rekap Denda</span>
                    </a>
                    
                     <a href="{{ route('head.books.collection') }}" 
                    :title="!sidebarOpen ? 'Koleksi Buku' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 mt-1 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.books.collection') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-book-open w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Koleksi Buku</span>
                    </a>

                    <a href="{{ route('head.students.index') }}" 
                    :title="!sidebarOpen ? 'Anggota (Siswa)' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 mt-1 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.students.index') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-user-friends w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Anggota (Siswa)</span>
                    </a>

                    <a href="{{ route('head.stock-validation.index') }}" 
                    :title="!sidebarOpen ? 'Validasi Stok' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 mt-1 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.stock-validation.*') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-clipboard-check w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Validasi Stok</span>
                    </a> 
                    
                    <div class="mt-6 px-6 mb-2 text-white/70 text-[10px] font-normal uppercase tracking-widest" x-show="sidebarOpen">Pengaturan</div>
                    
                    {{-- Users --}}
                    <a href="{{ route('head.fines.index') }}" 
                    :title="!sidebarOpen ? 'Pengaturan Denda' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.fines.index') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-file-invoice-dollar w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Pengaturan Denda</span>
                    </a>

                    <a href="{{ route('head.loan-rules.index') }}" 
                    :title="!sidebarOpen ? 'Kuota Peminjaman' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 mt-1 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.loan-rules.*') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-book-reader w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Kuota Peminjaman</span>
                    </a>

                    {{-- Manajemen User --}}
                    <a href="{{ route('head.users.index') }}" 
                    :title="!sidebarOpen ? 'Manajemen User' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 mt-1 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.users.*') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-users-cog w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Manajemen User</span>
                    </a>

                    <div class="mt-6 px-6 mb-2 text-white/70 text-[10px] font-normal uppercase tracking-widest" x-show="sidebarOpen">Informasi</div>
                    <a href="{{ route('head.announcements.index') }}" 
                    :title="!sidebarOpen ? 'Pengumuman' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 mt-1 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('head.announcements.index') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-bullhorn w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Pengumuman</span>
                    </a>
                </nav>

                {{-- Footer Sidebar --}}
                <div class="border-t border-white/10 p-4 space-y-2">
                    <button @click="sidebarOpen = !sidebarOpen" class="w-full flex items-center justify-center py-2 rounded bg-white/10 hover:bg-white/20 transition-colors" :title="sidebarOpen ? 'Tutup Sidebar' : 'Buka Sidebar'">
                        <i class="fas" :class="sidebarOpen ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center py-1.5 text-red-300 hover:bg-red-600 hover:text-white rounded transition-all"
                                :class="sidebarOpen ? 'px-4' : 'justify-center'"
                                :title="!sidebarOpen ? 'Keluar' : ''">
                            <i class="fas fa-sign-out-alt w-6 text-center"></i>
                            <span class="ml-3 font-medium text-sm" x-show="sidebarOpen">Keluar</span>
                        </button>
                    </form>
                </div>
            </aside>

            {{-- SIDEBAR ADMIN / PETUGAS --}}
            @else
                <aside class="text-white shadow-2xl flex flex-col transition-all duration-300 ease-in-out relative z-50"
                       style="background: linear-gradient(to bottom, #001d3d, #003566);"
                       :class="sidebarOpen ? 'w-56' : 'w-20'">
                    
                    <div class="h-16 flex items-center px-6 border-b border-white/10">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <img src="{{ asset('logo.png') }}" class="h-8 min-w-8 drop-shadow-md">
                            <span class="font-medium text-white text-sm whitespace-nowrap tracking-wider" x-show="sidebarOpen" x-transition>SMK Budi Mulia</span>
                        </div>
                    </div>                                      

                    <nav class="flex-1 overflow-y-auto py-4 no-scrollbar space-y-1">
                    <div class="px-6 mb-2 text-white/70 text-[10px] font-normal uppercase tracking-widest" x-show="sidebarOpen">Main Menu</div>
                    
                    {{-- Dashboard --}}
                    <a href="{{ route('admin.dashboard') }}" 
                    :title="!sidebarOpen ? 'Dashboard' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-th-large w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Dashboard</span>
                    </a>

                    <div class="mt-6 px-6 mb-2 text-white/70 text-[10px] font-normal uppercase tracking-widest" x-show="sidebarOpen">Manajemen</div>

                    {{-- Data Master --}}
                    <div x-data="{ open: {{ request()->routeIs('admin.books.*', 'admin.students.*', 'admin.categories.*', 'admin.raks.*') ? 'true' : 'false' }} }">
                        <button @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }" 
                                class="w-[calc(100%-1.5rem)] mx-3 flex items-center px-4 py-2.5 text-xs font-medium rounded-lg hover:bg-white/10 transition-colors"
                                :title="!sidebarOpen ? 'Data Master' : ''">
                            <i class="fas fa-database w-6 text-center text-sm"></i>
                            <span class="ml-3 font-medium text-xs flex-1 text-left" x-show="sidebarOpen">Data Master</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''" x-show="sidebarOpen"></i>
                        </button>
                        <div x-show="open && sidebarOpen" x-cloak class="bg-black/10 py-2 mt-1">
                            <a href="{{ route('admin.categories.index') }}" class="block pl-12 py-2 text-xs rounded-md mx-2 hover:bg-white/10 transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-white/20 border-l-4 border-white' : ''}}">Kategori</a>
                            <a href="{{ route('admin.raks.index') }}" class="block pl-12 py-2 text-xs rounded-md mx-2 hover:bg-white/10 transition-all {{ request()->routeIs('admin.raks.*') ? 'bg-white/20 border-l-4 border-white ' : ''}}">Rak Buku</a>
                            <a href="{{ route('admin.books.index') }}" class="block pl-12 py-2 text-xs rounded-md mx-2 hover:bg-white/10 transition-all {{ request()->routeIs('admin.books.*') ? 'bg-white/20 border-l-4 border-white' : '' }}">Data Buku</a>
                            <a href="{{ route('admin.students.index') }}" class="block pl-12 py-2 text-xs rounded-md mx-2 hover:bg-white/10 transition-all {{ request()->routeIs('admin.students.*') ? 'bg-white/20 border-l-4 border-white' : '' }}">Data Siswa</a>
                        </div>
                    </div>

                    {{-- Transaksi --}}
                    <div x-data="{ open: {{ request()->routeIs('admin.transactions.*') || request()->routeIs('admin.returns.*') || request()->routeIs('admin.stock-opname.*') ? 'true' : 'false' }} }" class="mt-1">
                        <button @click="if(!sidebarOpen) { sidebarOpen = true; open = true; } else { open = !open; }" 
                                class="w-[calc(100%-1.5rem)] mx-3 flex items-center px-4 py-2.5 text-xs font-medium rounded-lg hover:bg-white/10 transition-colors"
                                :title="!sidebarOpen ? 'Transaksi' : ''">
                            <i class="fas fa-exchange-alt w-6 text-center text-sm"></i>
                            <span class="ml-3 font-medium text-xs flex-1 text-left" x-show="sidebarOpen">Transaksi</span>
                            <i class="fas fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''" x-show="sidebarOpen"></i>
                        </button>
                        <div x-show="open && sidebarOpen" x-cloak class="bg-black/10 py-2 mt-1">
                            <a href="{{ route('admin.transactions.index') }}" class="block pl-12 py-2 text-xs rounded-md mx-2 hover:bg-white/10 transition-all {{ request()->routeIs('admin.transactions.index') ? 'bg-white/20 border-l-4 border-white' : '' }}">Peminjaman</a>
                            <a href="{{ route('admin.returns.index') }}" class="block pl-12 py-2 text-xs rounded-md mx-2 hover:bg-white/10 transition-all {{ request()->routeIs('admin.returns.index') ? 'bg-white/20 border-l-4 border-white' : '' }}">Pengembalian</a>
                            <a href="{{ route('admin.transactions.history') }}" class="block pl-12 py-2 text-xs rounded-md mx-2 hover:bg-white/10 transition-all {{ request()->routeIs('admin.transactions.history') ? 'bg-white/20 border-l-4 border-white' : '' }}">Riwayat Transaksi</a>
                            <a href="{{ route('admin.stock-opname.index') }}" class="block pl-12 py-2 text-xs rounded-md mx-2 hover:bg-white/10 transition-all {{ request()->routeIs('admin.stock-opname.*') ? 'bg-white/20 border-l-4 border-white' : '' }}">Stok Opname</a>
                        </div>
                    </div>
                    
                    {{-- Denda --}}
                    <a href="{{ route('admin.fines.index') }}" 
                    :title="!sidebarOpen ? 'Denda' : ''"
                    class="flex items-center px-4 py-2.5 text-xs font-medium rounded-lg mx-3 mt-1 transition-all duration-200 hover:bg-white/10 {{ request()->routeIs('admin.fines.index') ? 'bg-white/20 shadow-inner' : '' }}">
                        <i class="fas fa-file-invoice-dollar w-6 text-center text-sm"></i>
                        <span class="ml-3 font-medium text-xs" x-show="sidebarOpen">Denda</span>
                    </a>

                </nav>

                    <div class="border-t border-gray-700 p-4 space-y-2">
                        <button @click="sidebarOpen = !sidebarOpen" class="w-full flex items-center justify-center py-2 rounded bg-white/10 hover:bg-white/20 transition-colors" :title="sidebarOpen ? 'Tutup Sidebar' : 'Buka Sidebar'">
                            <i class="fas" :class="sidebarOpen ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
                        </button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center py-1.5 text-red-400 hover:bg-red-600 hover:text-white rounded transition-all"
                                    :class="sidebarOpen ? 'px-4' : 'justify-center'"
                                    :title="!sidebarOpen ? 'Keluar' : ''">
                                <i class="fas fa-sign-out-alt w-6 text-center"></i>
                                <span class="ml-3 font-medium text-sm" x-show="sidebarOpen">Keluar</span>
                            </button>
                        </form>
                    </div>
                </aside>
            @endif

            {{-- KONTEN UTAMA UNTUK ADMIN DAN KEPALA PERPUSTAKAAN --}}
            <div class="flex-1 flex flex-col min-w-0 bg-gray-100">
                <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-800 truncate">{{ $title ?? 'Dashboard' }}</h2>                    
                    <div class="flex items-center gap-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-medium text-gray-800 leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-blue-600 font-medium uppercase mt-1">
                                {{ Auth::user()->role == 'head' ? 'Kepala Perpustakaan' : 'Petugas Perpustakaan' }}
                            </p>
                        </div>
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center border shadow-inner">
                            <i class="fas fa-user-shield text-gray-500"></i>
                        </div>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto p-8 bg-gray-50">
                    {{ $slot }}
                </main>
            </div>
            
        </div>
    @endif

    @stack('scripts')
    @yield('scripts')
</body>
</html>