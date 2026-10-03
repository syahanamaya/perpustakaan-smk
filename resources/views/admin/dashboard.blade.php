<x-layouts.app title="Dashboard">
    <div class="space-y-5 pb-10">
        
        {{-- Row 1: 5 Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
            {{-- Card 1: Total Buku --}}
            <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider">Koleksi Buku</p>
                    <h3 class="text-lg font-medium text-gray-800 leading-tight mt-0.5">{{ number_format($total_books, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Total judul buku</p>
                </div>
            </div>

            {{-- Card 2: Buku Dipinjam --}}
            <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider">Buku Dipinjam</p>
                    <h3 class="text-lg font-medium text-gray-800 leading-tight mt-0.5">{{ number_format($active_borrowings, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Sedang dipinjam</p>
                </div>
            </div>

            {{-- Card 3: Total Peminjaman --}}
            <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider">Peminjaman</p>
                    <h3 class="text-lg font-medium text-gray-800 leading-tight mt-0.5">{{ number_format($total_borrowings_month, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Bulan ini</p>
                </div>
            </div>

            {{-- Card 4: Total Pengembalian --}}
            <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider">Pengembalian</p>
                    <h3 class="text-lg font-medium text-gray-800 leading-tight mt-0.5">{{ number_format($total_returns_month, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Bulan ini</p>
                </div>
            </div>

            {{-- Card 5: Total Denda --}}
            <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wider">Total Denda</p>
                    <h3 class="text-base font-medium text-gray-800 leading-tight mt-0.5">Rp {{ number_format($total_fine, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Bulan ini</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Grafik Peminjaman (Placeholder) --}}
            <div class="lg:col-span-2 bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xs font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-chart-line text-blue-500"></i> Grafik Peminjaman 6 Bulan Terakhir
                    </h3>
                    <div class="flex gap-3 text-[9px] font-medium uppercase tracking-wider">
                        <span class="flex items-center gap-1 text-blue-600"><i class="fas fa-circle"></i> Peminjaman</span>
                        <span class="flex items-center gap-1 text-emerald-500"><i class="fas fa-circle"></i> Pengembalian</span>
                    </div>
                </div>
                <div class="h-56 relative w-full">
                    <canvas id="borrowingChart"></canvas>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('borrowingChart').getContext('2d');

                    // Mengambil data dari variabel Laravel yang dikirim via compact()
                    const chartLabels = @json($months);
                    const borrowData = @json($borrow_data);
                    const returnData = @json($return_data);

                    new Chart(ctx, {
                        type: 'bar', // Bisa diganti 'line' kalau kamu lebih suka grafik garis
                        data: {
                            labels: chartLabels,
                            datasets: [
                                {
                                    label: 'Peminjaman',
                                    data: borrowData,
                                    backgroundColor: 'rgba(37, 99, 235, 0.8)', // Warna selaras dengan text-blue-600
                                    borderRadius: 4,
                                    barPercentage: 0.6
                                },
                                {
                                    label: 'Pengembalian',
                                    data: returnData,
                                    backgroundColor: 'rgba(16, 185, 129, 0.8)', // Warna selaras dengan text-emerald-500
                                    borderRadius: 4,
                                    barPercentage: 0.6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false // Disembunyikan karena kamu sudah buat legend kustom sendiri di atas
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1 // Agar sumbu Y angkanya bulat (1, 2, 3) bukan desimal
                                    },
                                    grid: {
                                        borderDash: [5, 5] // Membuat garis background putus-putus agar estetik
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false // Menghilangkan garis vertikal
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
            {{-- 5 Buku Sering Dipinjam --}}
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-50">
                    <h3 class="text-xs font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-star text-amber-400"></i> 5 Buku Sering Dipinjam
                    </h3>
                    <a href="{{ route('admin.transactions.index') }}" class="text-blue-600 text-[9px] font-medium hover:underline uppercase">Lihat Semua</a>
                </div>
                <div class="space-y-3 flex-1">
                    @forelse($popular_books as $book)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-10 bg-gray-100 rounded border border-gray-50 shrink-0 overflow-hidden">
                            <img src="{{ $book->cover_image ? asset('covers/'.$book->cover_image) : 'https://via.placeholder.com/150' }}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-medium text-gray-800 truncate">{{ $book->title }}</p>
                            <p class="text-[9px] text-gray-400 truncate">{{ $book->author }}</p>
                        </div>
                        <p class="text-[9px] font-medium text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">{{ $book->details_count }}x</p>
                    </div>
                    @empty
                    <div class="text-center text-gray-400 text-[10px] py-4 italic">Belum ada data buku dipinjam</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Aktivitas Terbaru Table --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-4 py-3 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                    <h3 class="text-xs font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-history text-blue-500"></i> Aktivitas Terbaru
                    </h3>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 text-[9px] text-gray-500 font-medium uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2.5">Waktu</th>
                                <th class="px-4 py-2.5">Aktivitas</th>
                                <th class="px-4 py-2.5">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="text-[10px] divide-y divide-gray-50">
                            @forelse($recent_activities as $activity)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-2.5 text-gray-500">{{ $activity->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[8px] font-medium uppercase tracking-wider {{ $activity->status == 'returned' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-blue-50 text-blue-600 border border-blue-100' }}">
                                        {{ $activity->status == 'returned' ? 'Pengembalian' : 'Peminjaman' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600">
                                    <span class="font-medium text-gray-800">{{ $activity->student->name ?? 'Siswa' }}</span> 
                                    {{ $activity->status == 'borrowed' ? 'meminjam' : 'mengembalikan' }} 
                                    {{ count($activity->details) }} buku
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-400 text-[10px] italic">Belum ada aktivitas terbaru</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Aksi Cepat --}}
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-xs font-medium text-gray-700 mb-4 pb-2 border-b border-gray-50 flex items-center gap-2">
                    <i class="fas fa-bolt text-yellow-500"></i> Aksi Cepat
                </h3>
                <div class="grid grid-cols-2 gap-2">
                <!-- Tombol 1: Peminjaman (Biru) -->
                <a href="{{ route('admin.transactions.index') }}" class="flex flex-col items-center justify-center py-3 px-2 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-50 transition-colors duration-200 group">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center mb-2 shadow-sm text-blue-600 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-plus text-sm"></i>
                    </div>
                    <span class="text-blue-700 font-medium text-[9px] uppercase tracking-wider text-center">Peminjaman</span>
                </a>

                <!-- Tombol 2: Pengembalian (Emerald / Hijau) -->
                <a href="{{ route('admin.returns.index') }}" class="flex flex-col items-center justify-center py-3 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-50 transition-colors duration-200 group">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center mb-2 shadow-sm text-emerald-600 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-undo text-sm"></i>
                    </div>
                    <span class="text-emerald-700 font-medium text-[9px] uppercase tracking-wider text-center">Pengembalian</span>
                </a>

                <!-- Tombol 3: Data Buku (Indigo / Ungu) -->
                <a href="{{ route('admin.books.index') }}" class="flex flex-col items-center justify-center py-3 px-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 border border-indigo-50 transition-colors duration-200 group">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center mb-2 shadow-sm text-indigo-600 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-book text-sm"></i>
                    </div>
                    <span class="text-indigo-700 font-medium text-[9px] uppercase tracking-wider text-center">Data Buku</span>
                </a>

                <!-- Tombol 4: Data Siswa (Orange) -->
                <a href="{{ route('admin.students.index') }}" class="flex flex-col items-center justify-center py-3 px-2 rounded-xl bg-orange-50 hover:bg-orange-100 border border-orange-50 transition-colors duration-200 group">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center mb-2 shadow-sm text-orange-600 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-users text-sm"></i>
                    </div>
                    <span class="text-orange-700 font-medium text-[9px] uppercase tracking-wider text-center">Data Siswa</span>
                </a>                

                <!-- Tombol 5: Riwayat (Teal / Hijau Kebiruan) -->
                <a href="{{ route('admin.transactions.history') }}" class="flex flex-col items-center justify-center py-3 px-2 rounded-xl bg-teal-50 hover:bg-teal-100 border border-teal-50 transition-colors duration-200 group">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center mb-2 shadow-sm text-teal-600 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-history text-sm"></i>
                    </div>
                    <span class="text-teal-700 font-medium text-[9px] uppercase tracking-wider text-center">Riwayat</span>
                </a>

                <!-- Tombol 6: Rekap Denda (Rose / Merah Muda) -->
                <a href="{{ route('admin.fines.index') }}" class="flex flex-col items-center justify-center py-3 px-2 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-50 transition-colors duration-200 group">
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center mb-2 shadow-sm text-rose-600 group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                    </div>
                    <span class="text-rose-700 font-medium text-[9px] uppercase tracking-wider text-center">Rekap Denda</span>
                </a>

            </div>
            </div>
        </div>
    </div>
</x-layouts.app>