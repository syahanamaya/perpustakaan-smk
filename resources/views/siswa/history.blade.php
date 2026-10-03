<x-layouts.app title="Riwayat Peminjaman - Perpustakaan SMK Budi Mulia">
    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
        [x-cloak] { display: none !important; }
    </style>
    @endsection

    {{-- Alpine.js State untuk Modal Detail Riwayat --}}
    <div x-data="{ openModal: false, selectedHistory: {} }" class="max-w-7xl mx-auto px-6 py-6 space-y-6 min-h-screen pb-16">
        
        {{-- HEADER HALAMAN --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-xl font-medium text-blue-800">Riwayat Peminjaman</h2>
                <p class="text-[11px] text-gray-400 mt-0.5">Daftar buku yang pernah Anda pinjam dan telah dikembalikan.</p>
            </div>
            
            {{-- Info Ringkas Total Riwayat --}}
            <div class="mt-3 md:mt-0 bg-gray-100 px-4 py-2 rounded-2xl border border-gray-200/60 flex items-center gap-2">
                <i class="fas fa-history text-gray-500 text-xs"></i>
                <span class="text-[11px] text-gray-600 font-medium">Total Selesai: <span class="font-medium text-gray-950">{{ $historyBorrowings->total() }} Buku</span></span>
            </div>
        </div>

        {{-- LAYOUT UTAMA: SIDEBAR FILTER (KIRI) & TABEL RIWAYAT (KANAN) --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            {{-- SIDEBAR FILTER KIRI --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-150 shadow-sm space-y-1">
                    <h3 class="text-[10px] font-medium text-gray-400 uppercase tracking-wider px-2 mb-3">Filter Waktu & Status</h3>
                    
                    @php 
                        $currentTime = request('time', 'all'); 
                        $currentStatus = request('status', 'all');
                    @endphp

                    <a href="{{ route('student.history', ['time' => 'all', 'status' => $currentStatus]) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentTime == 'all' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-calendar-alt text-[11px] {{ $currentTime == 'all' ? 'text-blue-500' : 'text-gray-500' }}"></i> Semua Waktu</span>
                        <span class="{{ $currentTime == 'all' ? 'bg-blue-200/60 text-blue-800' : 'bg-gray-100 text-gray-600' }} text-[10px] font-medium px-2 py-0.5 rounded-full">{{ $totalAll ?? $historyBorrowings->total() }}</span>
                    </a>

                    <a href="{{ route('student.history', ['time' => 'month', 'status' => $currentStatus]) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentTime == 'month' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-clock text-[11px] {{ $currentTime == 'month' ? 'text-blue-500' : '' }}"></i> Bulan Ini</span>
                    </a>

                    <a href="{{ route('student.history', ['time' => 'year', 'status' => $currentStatus]) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentTime == 'year' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-history text-[11px] {{ $currentTime == 'year' ? 'text-blue-500' : '' }}"></i> Tahun Ini</span>
                    </a>

                    <hr class="border-gray-100 my-2">

                    <a href="{{ route('student.history', ['time' => $currentTime, 'status' => 'all']) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentStatus == 'all' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-list text-[11px] {{ $currentStatus == 'all' ? 'text-blue-500' : 'text-gray-500' }}"></i> Semua Status</span>
                    </a>

                    <a href="{{ route('student.history', ['time' => $currentTime, 'status' => 'returned']) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentStatus == 'returned' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-check-circle text-[11px] {{ $currentStatus == 'returned' ? 'text-blue-500' : 'text-green-500' }}"></i> Tepat Waktu</span>
                    </a>

                    <a href="{{ route('student.history', ['time' => $currentTime, 'status' => 'fine_paid']) }}" class="flex justify-between items-center px-3 py-2 rounded-xl text-xs transition-all {{ $currentStatus == 'fine_paid' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2.5"><i class="fas fa-money-bill-wave text-[11px] {{ $currentStatus == 'fine_paid' ? 'text-blue-500' : 'text-amber-500' }}"></i> Denda Lunas</span>
                    </a>
                </div>

                {{-- Kotak Catatan Persentase Tertib Pemustaka --}}
                <div class="bg-green-50/60 p-4 rounded-2xl border border-green-100 text-center">
                    <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm text-xs">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4 class="text-xs font-medium text-green-900">Pemustaka Tertib</h4>
                    <p class="text-[10px] text-green-700 mt-1 leading-relaxed">Terima kasih telah mengembalikan buku tepat waktu secara konsisten!</p>
                </div>
            </div>

            {{-- TABEL DATA KANAN --}}
            <div class="lg:col-span-3 bg-white p-5 rounded-2xl border border-gray-150 shadow-sm space-y-4">
                <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wide">Daftar Dokumen Selesai</h3>
                
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse whitespace-nowrap text-[11px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-gray-500 uppercase tracking-wider font-medium">
                                <th class="px-4 py-3 w-5/12">Buku</th>
                                <th class="px-4 py-3">Tanggal Pinjam</th>
                                <th class="px-4 py-3">Tanggal Kembali</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Denda</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($historyBorrowings as $history)
                                @php
                                    $bookDetail = $history->borrowingDetails->first()?->book;
                                    // Ambil data denda dari relasi 'fine'
                                    $fine = $history->fine;
                                @endphp
                                <tr class="hover:bg-gray-50/40 transition-colors">
                                    
                                    <td class="px-4 py-3.5">
                                        <div class="flex gap-3 items-center">
                                            <div class="w-10 h-14 bg-gray-100 rounded-lg overflow-hidden shrink-0 shadow-sm border border-gray-200">
                                                @if($bookDetail && $bookDetail->cover_image)
                                                    <img src="{{ asset('covers/' . $bookDetail->cover_image) }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50"><i class="fas fa-book"></i></div>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-800 text-[12px] leading-snug">{{ $bookDetail->title ?? 'Judul Buku' }}</h4>
                                                <p class="text-gray-400 text-[10px] mt-0.5">{{ $bookDetail->author ?? '-' }}</p>
                                                <span class="text-[9px] text-gray-400 font-mono block mt-1">ID: {{ $history->transaction_code }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-4 py-3.5 text-gray-600 font-medium">
                                        {{ \Carbon\Carbon::parse($history->borrow_date)->translatedFormat('d M Y') }}
                                    </td>
                                    
                                    <td class="px-4 py-3.5 text-gray-600 font-medium">
                                        {{ \Carbon\Carbon::parse($history->return_date)->translatedFormat('d M Y') }}
                                    </td>
                                    
                                    <td class="px-4 py-3.5">
                                        <span class="bg-gray-100 text-gray-600 border border-gray-200 text-[10px] font-medium px-2.5 py-0.5 rounded-md flex items-center gap-1.5 w-fit">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Dikembalikan
                                        </span>
                                    </td>
                                    
                                    <td class="px-4 py-3.5">
                                        @if($fine && $fine->total_fine > 0)
                                            <span class="font-medium text-green-600 block">Rp{{ number_format($fine->total_fine, 0, ',', '.') }}</span>
                                            @if($fine->fine_status == 'paid')
                                                <span class="text-[9px] text-green-500 font-medium">Lunas</span>
                                            @else
                                                <span class="text-[9px] text-red-500 font-medium">Belum Lunas</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-4 py-3.5 text-center">
                                        <button @click="selectedHistory = {
                                                    code: '{{ $history->transaction_code }}',
                                                    title: '{{ $bookDetail->title ?? 'Judul Tidak Tersedia' }}',
                                                    author: '{{ $bookDetail->author ?? '-' }}',
                                                    borrow_date: '{{ \Carbon\Carbon::parse($history->borrow_date)->translatedFormat('d F Y') }}',
                                                    return_date: '{{ \Carbon\Carbon::parse($history->return_date)->translatedFormat('d F Y') }}',
                                                    denda: '{{ $fine && $fine->total_fine > 0 ? 'Rp' . number_format($fine->total_fine, 0, ',', '.') . ' (Lunas)' : 'Tidak Ada Denda' }}',
                                                    note: '{{ $history->notes ?? 'Transaksi selesai tanpa kendala.' }}'
                                                }; openModal = true" 
                                                class="px-3 py-1.5 text-center text-gray-700 bg-gray-50 border border-gray-200 hover:bg-gray-100 font-medium rounded-lg text-[10px] inline-flex items-center gap-1 transition-all">
                                            <i class="fas fa-eye text-[9px] text-gray-400"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                        <div class="mb-2"><i class="fas fa-folder-open text-3xl text-gray-200"></i></div>
                                        Belum ada riwayat peminjaman buku.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-4 pt-2 border-t border-gray-50">
                    {{ $historyBorrowings->links() }}
                </div>
            </div>
        </div>

        {{-- POP-UP MODAL DETAIL RIWAYAT --}}
        <div x-show="openModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.outside="openModal = false" class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 border border-gray-100 relative">
                
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                    <div>
                        <span class="text-[9px] uppercase font-medium text-gray-400 tracking-wider block">ID Arsip Transaksi</span>
                        <h3 class="text-sm font-medium text-gray-700 font-mono" x-text="selectedHistory.code"></h3>
                    </div>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-full hover:bg-gray-100 transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-3.5 text-[11px]">
                    <div>
                        <label class="text-gray-400 font-medium block mb-0.5">Judul Buku</label>
                        <p class="text-xs font-medium text-gray-800" x-text="selectedHistory.title"></p>
                    </div>

                    <div>
                        <label class="text-gray-400 font-medium block mb-0.5">Pengarang</label>
                        <p class="font-medium text-gray-700" x-text="selectedHistory.author"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <div>
                            <label class="text-gray-500 font-medium block mb-0.5">Tanggal Pinjam</label>
                            <p class="font-medium text-gray-800" x-text="selectedHistory.borrow_date"></p>
                        </div>
                        <div>
                            <label class="text-gray-500 font-medium block mb-0.5">Tanggal Kembali</label>
                            <p class="font-medium text-green-600" x-text="selectedHistory.return_date"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-400 font-medium block mb-0.5">Status Denda</label>
                            <p class="font-medium text-gray-800" x-text="selectedHistory.denda"></p>
                        </div>
                        <div>
                            <label class="text-gray-400 font-medium block mb-0.5">Status Transaksi</label>
                            <p class="font-medium text-green-600">Selesai / Arsip</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-gray-400 font-medium block mb-0.5">Keterangan Petugas</label>
                        <p class="text-gray-600 italic" x-text="selectedHistory.note"></p>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-gray-100 flex justify-end">
                    <button @click="openModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2 rounded-xl text-xs transition-all">
                        Tutup Riwayat
                    </button>
                </div>
            </div>
        </div>

    </div> 
</x-layouts.app>