<x-layouts.app title="Dashboard Siswa">

    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        .bg-navbar { background-color: #0c4aae; }
        .bg-card-blue { background-color: #3b32ce; }
    </style>
    @endsection

    <div class="max-w-7xl mx-auto px-6 pt-8 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            {{-- SEKSI KIRI & TENGAH (Lebar 3 Kolom) --}}
            <div class="lg:col-span-3 space-y-6">
                
                {{-- Welcome Greeting & Search Bar --}}
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div>
                        <h2 class="text-lg font-medium text-gray-800 flex items-center gap-2">
                            Halo, {{ explode(' ', $student->name)[0] }}! <span class="animate-bounce">👋</span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Selamat datang di Perpustakaan Digital SMK Budi Mulia.</p>
                    </div>
                    
                    <form action="{{ route('student.explore') }}" method="GET" class="relative w-full md:w-96">
                        <input type="text" name="search" placeholder="Cari buku yang ingin kamu baca..." 
                               class="w-full py-2.5 px-4 pr-12 rounded-full text-xs text-gray-800 bg-gray-50 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        <button type="submit" class="absolute right-1 top-1 bottom-1 bg-blue-600 text-white px-4 rounded-full hover:bg-blue-700 transition-all flex items-center justify-center">
                            <i class="fas fa-search text-xs"></i>
                        </button>
                    </form>
                </div>

                {{-- 2. RINGKASAN AKTIVITAS (4 COUNTER CARD) --}}
                <div>
                    <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wider mb-3">Ringkasan Aktivitas</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-sm shrink-0">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-medium text-gray-800">{{ $activeBorrowings->count() }}</span>
                                    <span class="text-[10px] text-gray-500 font-medium">Buku</span>
                                </div>
                                <p class="text-[10px] text-gray-400 font-medium leading-none">Buku Dipinjam</p>
                                <a href="#" class="text-[9px] text-blue-500 hover:underline mt-1 block">Lihat Detail →</a>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center text-sm shrink-0">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-medium text-gray-800">{{ $activeBorrowings->where('status', 'pending')->count() }}</span>
                                    <span class="text-[10px] text-gray-500 font-medium">Buku</span>
                                </div>
                                <p class="text-[10px] text-gray-400 font-medium leading-none">Menunggu</p>
                                <a href="#" class="text-[9px] text-blue-500 hover:underline mt-1 block">Lihat Detail →</a>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-50 text-red-600 rounded-full flex items-center justify-center text-sm shrink-0">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-medium text-gray-800">{{ $totalDenda > 0 ? '1' : '0' }}</span>
                                    <span class="text-[10px] text-gray-500 font-medium">Denda</span>
                                </div>
                                <p class="text-[10px] text-gray-400 font-medium leading-none">Denda Aktif</p>
                                <a href="#" class="text-[9px] text-blue-500 hover:underline mt-1 block">Lihat Detail →</a>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center text-sm shrink-0">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-medium text-gray-800">0</span>
                                    <span class="text-[10px] text-gray-500 font-medium">Buku</span>
                                </div>
                                <p class="text-[10px] text-gray-400 font-medium leading-none">Buku Favorit</p>
                                <a href="#" class="text-[9px] text-blue-500 hover:underline mt-1 block">Lihat Detail →</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dua Kolom: Sedang Dipinjam vs Riwayat Terakhir --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    
                    {{-- Buku Yang Sedang Dipinjam (Lebar 3) --}}
                    <div class="md:col-span-3 space-y-3">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wider">Buku yang Sedang Dipinjam</h3>
                        </div>

                        @if($activeBorrowings->count() > 0)
                            <div class="space-y-3">
                                @foreach($activeBorrowings as $borrow)
                                    @php 
                                        $detail = $borrow->details->first(); 
                                        $book = $detail ? $detail->book : null;
                                        $tanggalPinjam = \Carbon\Carbon::parse($borrow->borrow_date)->startOfDay();
                                        $jatuhTempo = \Carbon\Carbon::parse($borrow->due_date)->startOfDay();
                                        $hariIni = \Carbon\Carbon::now()->startOfDay();
                                        $apakahTerlambat = $hariIni->greaterThan($jatuhTempo);
                                        $sisaHari = $hariIni->diffInDays($jatuhTempo, false);

                                        $totalHari = $tanggalPinjam->diffInDays($jatuhTempo) ?: 1;
                                        $hariBerlalu = $tanggalPinjam->diffInDays($hariIni, false);
                                        if ($hariBerlalu < 0) $hariBerlalu = 0;
                                        
                                        $progress = min(100, max(0, ($hariBerlalu / $totalHari) * 100));
                                        
                                        if ($apakahTerlambat) {
                                            $warnaProgress = 'bg-red-500';
                                            $warnaTeks = 'text-red-600';
                                        } else {
                                            $warnaProgress = 'bg-blue-600';
                                            $warnaTeks = 'text-blue-600';
                                        }
                                    @endphp
                                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex gap-4">
                                        <div class="w-20 h-28 bg-gray-100 rounded-xl overflow-hidden shrink-0 shadow-sm border border-gray-200">
                                            @if($book && $book->cover_image)
                                                <img src="{{ asset('covers/' . $book->cover_image) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                    <i class="fas fa-book text-xl"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex flex-col justify-between w-full py-0.5">
                                            <div>
                                                <h4 class="font-medium text-gray-800 text-sm leading-tight line-clamp-1">{{ $book->title ?? 'Judul Tidak Tersedia' }}</h4>
                                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $book->author ?? 'Penulis' }}</p>
                                                
                                                <div class="grid grid-cols-2 gap-2 mt-2 text-[10px] text-gray-500">
                                                    <div>
                                                        <span class="block text-gray-400 text-[9px]">Dipinjam pada</span>
                                                        <span class="font-medium text-gray-700"><i class="far fa-calendar text-gray-400 mr-1"></i> {{ \Carbon\Carbon::parse($borrow->borrow_date)->format('d M Y') }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-gray-400 text-[9px]">Batas Pengembalian</span>
                                                        <span class="font-medium {{ $apakahTerlambat ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                                                            <i class="far fa-calendar-alt text-gray-400 mr-1"></i> {{ $jatuhTempo->format('d M Y') }}
                                                            @if(!$apakahTerlambat && $sisaHari >= 0)
                                                                <span class="text-blue-600 block text-[9px] font-medium">({{ $sisaHari }} hari lagi)</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-2 pt-2 border-t border-gray-50 flex items-center justify-between gap-4">
                                                <div class="w-full">
                                                    <div class="flex justify-between text-[9px] text-gray-400 mb-1">
                                                        <span>Progress Peminjaman</span>
                                                        <span class="font-medium {{ $warnaTeks }}">{{ round($progress) }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                                        <div class="{{ $warnaProgress }} h-1.5 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm">
                                <p class="text-gray-400 text-xs">Kamu tidak memiliki pinjaman aktif.</p>
                            </div>
                        @endif
                    </div>

                    {{-- 3. RIWAYAT PEMINJAMAN TERAKHIR (Lebar 2) --}}
                    <div class="md:col-span-2 space-y-3">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wider">Riwayat Peminjaman Terakhir</h3>
                            <a href="{{ route('student.history') }}" class="text-[10px] text-blue-600 font-medium hover:underline">Lihat Semua →</a>
                        </div>

                        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-100">
                            @forelse($borrowingHistory as $history)
                                @php
                                    $book = $history->details->first()?->book;
                                    $statusClass = '';
                                    $statusText = '';

                                    switch ($history->status) {
                                        case 'returned':
                                            $statusClass = 'bg-green-50 text-green-600 border-green-100';
                                            $statusText = 'Selesai';
                                            break;
                                        case 'late':
                                            $statusClass = 'bg-red-50 text-red-600 border-red-100';
                                            $statusText = 'Terlambat';
                                            break;
                                        case 'borrowed':
                                            $statusClass = 'bg-amber-50 text-amber-600 border-amber-100';
                                            $statusText = 'Dipinjam';
                                            break;
                                        case 'pending':
                                            $statusClass = 'bg-blue-50 text-blue-600 border-blue-100';
                                            $statusText = 'Menunggu';
                                            break;
                                        default:
                                            $statusClass = 'bg-gray-50 text-gray-600 border-gray-100';
                                            $statusText = ucfirst($history->status);
                                    }
                                @endphp
                                <div class="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-8 h-10 bg-gray-100 rounded border overflow-hidden shrink-0">
                                            @if($book && $book->cover_image)
                                                <img src="{{ asset('covers/' . $book->cover_image) }}" class="w-full h-full object-cover" alt="{{ $book->title ?? '' }}">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-gray-50 text-gray-300"><i class="fas fa-book"></i></div>
                                            @endif
                                        </div>
                                        <div class="overflow-hidden">
                                            <h4 class="text-xs font-medium text-gray-800 line-clamp-1" title="{{ $book->title ?? 'Judul Tidak Tersedia' }}">{{ $book->title ?? 'Judul Tidak Tersedia' }}</h4>
                                            <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($history->borrow_date)->translatedFormat('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-medium px-2 py-0.5 rounded border uppercase shrink-0 {{ $statusClass }}">{{ $statusText }}</span>
                                </div>
                            @empty
                                <div class="py-8 text-center">
                                    <p class="text-xs text-gray-400">Belum ada riwayat peminjaman.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- 4. REKOMENDASI BUKU UNTUKMU --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wider">Buku Sering Dipinjam</h3>
                        <a href="{{ route('student.explore') }}" class="text-[10px] text-blue-600 font-medium hover:underline">Lihat Semua →</a>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                        @forelse($recommendedBooks as $book)
                        <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="w-full h-36 bg-gray-100 rounded-lg overflow-hidden border mb-2 shadow-sm relative">
                                    @if($book->cover_image)
                                        <img src="{{ asset('covers/' . $book->cover_image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <i class="fas fa-book text-2xl"></i>
                                        </div>
                                    @endif
                                    <span class="absolute top-1.5 left-1.5 bg-blue-600 text-white text-[8px] font-medium px-1.5 py-0.5 rounded">{{ (int) ($book->borrow_count ?? 0) }}x dipinjam</span>
                                </div>
                                <h4 class="text-xs font-medium text-gray-800 line-clamp-1" title="{{ $book->title }}">{{ $book->title }}</h4>
                                <p class="text-[9px] text-gray-400 mt-0.5 line-clamp-1" title="{{ $book->author }}">{{ $book->author }}</p>
                            </div>
                            @php
                                // Array warna untuk kategori
                                $colors = [
                                    ['text' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                                    ['text' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                                    ['text' => 'text-green-600', 'bg' => 'bg-green-50'],
                                    ['text' => 'text-purple-600', 'bg' => 'bg-purple-50'],
                                    ['text' => 'text-pink-600', 'bg' => 'bg-pink-50'],
                                ];
                                $colorIndex = ($loop->index) % count($colors);
                                $color = $colors[$colorIndex];
                            @endphp
                            <span class="text-[8px] font-medium {{ $color['text'] }} {{ $color['bg'] }} px-1.5 py-0.5 rounded mt-2 self-start line-clamp-1">{{ $book->category->name ?? 'Umum' }}</span>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-4 bg-white rounded-xl border border-gray-100">
                            <p class="text-[10px] text-gray-400">Belum ada rekomendasi buku saat ini.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- SEKSI KANAN (Lebar 1 Kolom) --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- KARTU ANGGOTA DIGITAL (Sesuai Gambar) --}}
                <div class="bg-indigo-700 p-6 rounded-2xl text-white shadow-lg relative overflow-hidden">

                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>

                    <div class="flex justify-between items-start mb-8">

                        <div>

                            <p class="text-[9px] text-indigo-200 uppercase tracking-[0.2em] font-medium">Member Card</p>

                            <p class="text-xl font-mono tracking-widest mt-1 text-white">{{ $student->nis }}</p>

                        </div>

                        <i class="fas fa-shield-alt text-indigo-300/30 text-3xl"></i>

                    </div>

                    <div class="flex justify-between items-end">

                        <div>

                            <p class="text-xs font-medium uppercase text-white">{{ $student->name }}</p>

                            <p class="text-[10px] text-indigo-100">{{ $student->class }} - {{ $student->major }}</p>

                        </div>

                        <div class="px-2 py-1 rounded bg-white/20 text-[9px] font-medium uppercase text-white">

                            {{ $student->status === 'active' ? 'Aktif' : ($student->status === 'graduated' ? 'Alumni' : 'Nonaktif') }}

                        </div>

                    </div>

                </div>

                {{-- RINGKASAN AKUN (Denda & Total Pinjam) --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-100">
                    <h3 class="text-xs font-medium text-gray-700 p-4 pb-2 uppercase tracking-wider">Ringkasan Akun</h3>
                    
                        <div class="flex items-center p-4 hover:bg-gray-50/50 transition-all gap-3">
                            <div class="w-8 h-8 bg-red-50 text-red-500 rounded-lg flex items-center justify-center text-xs"><i class="fas fa-wallet"></i></div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-medium leading-none">Tunggakan Denda</p>
                                <p class="text-xs font-medium text-gray-800 mt-1">Rp{{ number_format($totalDenda, 0, ',', '.') }}</p>
                            </div>
                        </div>                    
                        <div class="flex items-center p-4 hover:bg-gray-50/50 transition-all gap-3">
                            <div class="w-8 h-8 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center text-xs"><i class="fas fa-book"></i></div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-medium leading-none">Total Peminjaman</p>
                                <p class="text-xs font-medium text-gray-800 mt-1">12 kali</p>
                            </div>
                        </div>
                </div>

                {{-- 5. PENGUMUMAN PERPUSTAKAAN --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wider">Pengumuman Perpustakaan</h3>
                        <a href="{{ route('siswa.announcements') }}" class="text-[10px] text-blue-600 font-medium hover:underline">Lihat Semua →</a>
                    </div>

                    <div class="space-y-3">
                        @forelse($latestAnnouncements as $announcement)
                            @php
                                $bgColor = 'bg-blue-50';
                                $textColor = 'text-blue-600';
                                $icon = 'fa-bullhorn';
                                
                                if($announcement->type === 'penting') {
                                    $bgColor = 'bg-red-50';
                                    $textColor = 'text-red-600';
                                    $icon = 'fa-exclamation-triangle';
                                } elseif($announcement->type === 'kegiatan') {
                                    $bgColor = 'bg-purple-50';
                                    $textColor = 'text-purple-600';
                                    $icon = 'fa-calendar-alt';
                                } elseif($announcement->type === 'pengingat') {
                                    $bgColor = 'bg-amber-50';
                                    $textColor = 'text-amber-600';
                                    $icon = 'fa-bell';
                                } elseif($announcement->type === 'info') {
                                    $bgColor = 'bg-green-50';
                                    $textColor = 'text-green-600';
                                    $icon = 'fa-info-circle';
                                }
                            @endphp
                            <a href="{{ route('siswa.announcements.show', $announcement->slug) }}" class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-start gap-3 hover:bg-gray-50 transition-colors">
                                <div class="w-8 h-8 {{ $bgColor }} {{ $textColor }} rounded-lg flex items-center justify-center text-xs shrink-0"><i class="fas {{ $icon }}"></i></div>
                                <div class="overflow-hidden">
                                    <h4 class="text-xs font-medium text-gray-800 leading-tight truncate">{{ $announcement->title }}</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5 truncate">{{ strip_tags($announcement->content) }}</p>
                                    <span class="text-[8px] text-gray-400 mt-1 block">{{ \Carbon\Carbon::parse($announcement->created_at)->translatedFormat('d M Y') }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                                <p class="text-[10px] text-gray-500">Belum ada pengumuman.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FOOTER HAK CIPTA --}}
    <footer class="text-center py-6 text-gray-400 text-[10px] border-t border-gray-100 mt-12">
        &copy; 2026 Perpustakaan Digital SMK Budi Mulia. All rights reserved.
    </footer>
</x-layouts.app>