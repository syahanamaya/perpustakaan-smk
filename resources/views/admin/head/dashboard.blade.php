<x-layouts.app title="Dashboard Kepala Perpustakaan">
    <div class="space-y-5">

        {{-- ROW 1: 5 SUMMARY CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
            {{-- Card 1: Total Buku --}}
            <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wide">Total Koleksi Buku</p>
                    <h3 class="text-lg font-medium text-gray-800 leading-tight mt-0.5">{{ number_format($total_books, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Total judul buku</p>
                </div>
            </div>

            {{-- Card 2: Buku Dipinjam --}}
            <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wide">Buku Dipinjam</p>
                    <h3 class="text-lg font-normal text-gray-800 leading-tight mt-0.5">{{ number_format($active_borrowings, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Sedang dipinjam saat ini</p>
                </div>
            </div>

            {{-- Card 3: Total Peminjaman --}}
            <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wide">Total Peminjaman</p>
                    <h3 class="text-lg font-normal text-gray-800 leading-tight mt-0.5">{{ number_format($total_borrowings_month, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Bulan ini</p>
                </div>
            </div>

            {{-- Card 4: Total Pengembalian --}}
            <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-exchange-alt flex-row-reverse"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wide">Total Pengembalian</p>
                    <h3 class="text-lg font-normal text-gray-800 leading-tight mt-0.5">{{ number_format($total_returns_month, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Bulan ini</p>
                </div>
            </div>

            {{-- Card 5: Total Denda --}}
            <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex justify-center items-center text-lg shrink-0">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <p class="text-[9px] font-medium text-gray-500 uppercase tracking-wide">Total Denda</p>
                    <h3 class="text-lg font-normal text-gray-800 leading-tight mt-0.5">Rp {{ number_format($total_fine, 0, ',', '.') }}</h3>
                    <p class="text-[9px] text-gray-400">Bulan ini</p>
                </div>
            </div>
        </div>

        {{-- ROW 2: LINE CHART | DONUT CHART | DENDA --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            
            {{-- Grafik Peminjaman Buku --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[12px] font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-chart-line text-blue-500"></i> Grafik Sirkulasi Buku
                    </h3>
                    <span class="text-[10px] text-gray-400 bg-gray-50 px-2 py-1 rounded">6 Bulan Terakhir</span>
                </div>
                <div class="h-56 relative w-full">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            {{-- Buku Terpopuler --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-[12px] font-medium text-gray-700 flex items-center gap-2 mb-4 border-b border-gray-50 pb-3">
                    <i class="fas fa-bookmark text-blue-500"></i> Buku Terpopuler
                </h3>
                <div class="h-48 relative w-full flex justify-center items-center">
                    @if(count($book_labels) > 0)
                        <canvas id="donutBooks"></canvas>
                    @else
                        <p class="text-gray-400 text-xs italic">Belum ada data peminjaman</p>
                    @endif
                </div>
            </div>

            {{-- Ringkasan Denda --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col">
                <h3 class="text-[12px] font-medium text-gray-700 flex items-center gap-2 mb-4 border-b border-gray-50 pb-3">
                    <i class="fas fa-money-bill-wave text-red-400"></i> Ringkasan Denda
                </h3>
                <div class="flex-1 space-y-3.5">
                    <div class="flex justify-between items-center text-[11px] border-b border-gray-50 pb-2">
                        <span class="text-gray-500">Total Denda</span>
                        <span class="font-medium text-gray-700">Rp {{ number_format($total_fine, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] border-b border-gray-50 pb-2">
                        <span class="text-gray-500">Denda Dibayar</span>
                        <span class="font-medium text-green-600">Rp {{ number_format($denda_dibayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] border-b border-gray-50 pb-2">
                        <span class="text-gray-500">Denda Belum Dibayar</span>
                        <span class="font-medium text-red-500">Rp {{ number_format($denda_belum, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="text-gray-500">Total Peminjam Terlambat</span>
                        <span class="font-medium text-gray-700">{{ $late_students_count }} siswa</span>
                    </div>
                </div>
                <a href="{{ route('head.fines.recap') }}" class="mt-4 w-full py-2 border border-blue-50 text-blue-600 rounded-lg text-[10px] font-medium hover:bg-blue-50 transition-colors">
                    <i class="fas fa-external-link-alt mr-1"></i> Lihat Rekap Denda
                </a>
            </div>
        </div>

        {{-- ROW 3: BAR CHART | STATUS | SISWA AKTIF --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            
            {{-- Peminjaman per Bulan --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
                <div class="flex justify-between items-center mb-4 border-b border-gray-50 pb-3">
                    <h3 class="text-[12px] font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-blue-500"></i> Peminjaman per Bulan
                    </h3>
                </div>
                <div class="h-56 relative w-full">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            {{-- Status Peminjaman --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col">
                <h3 class="text-[12px] font-medium text-gray-700 flex items-center gap-2 mb-4 border-b border-gray-50 pb-3">
                    <i class="fas fa-info-circle text-blue-500"></i> Status Peminjaman
                </h3>
                <div class="h-32 relative w-full mb-4">
                    <canvas id="donutStatus"></canvas>
                </div>
                @if($late_count > 0)
                <div class="mt-auto bg-amber-50/50 border border-amber-100/50 p-2.5 rounded-lg flex items-start gap-2.5">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 text-[11px]"></i>
                    <div>
                        <p class="text-[10px] font-medium text-amber-700">{{ $late_count }} transaksi terlambat</p>
                        <p class="text-[9px] text-amber-600/80 mt-0.5">Segera hubungi siswa yang bersangkutan.</p>
                    </div>
                </div>
                @else
                <div class="mt-auto bg-green-50/50 border border-green-100/50 p-2.5 rounded-lg flex items-start gap-2.5">
                    <i class="fas fa-check-circle text-green-500 mt-0.5 text-[11px]"></i>
                    <div>
                        <p class="text-[10px] font-medium text-green-700">Tidak ada keterlambatan</p>
                        <p class="text-[9px] text-green-600/80 mt-0.5">Semua pengembalian buku tepat waktu.</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Siswa Teraktif --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col">
                <h3 class="text-[12px] font-medium text-gray-700 flex items-center gap-2 mb-4 border-b border-gray-50 pb-3">
                    <i class="fas fa-users text-blue-500"></i> Anggota Teraktif
                </h3>
                <div class="flex-1 space-y-3.5">
                    @forelse($top_students as $key => $student)
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] text-gray-400 font-medium w-3">{{ $key + 1 }}.</span>
                        <div class="flex-1">
                            <p class="text-[11px] font-medium text-gray-700 truncate max-w-32.5">{{ $student->name }}</p>
                            <p class="text-[9px] text-gray-400">{{ $student->class ?? 'Siswa' }}</p>
                        </div>
                        <span class="text-[10px] text-gray-500">{{ $student->borrowings_count }} kali</span>
                    </div>
                    @empty
                        <p class="text-gray-400 text-xs italic text-center mt-5">Belum ada data</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- CARD PENGUMUMAN TERBARU DI DASHBOARD --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-xs font-medium text-gray-700 uppercase tracking-wide flex items-center gap-2">
                    <i class="fas fa-bullhorn text-blue-600"></i> Pengumuman Terbaru
                </h3>
            </div>
            
            <div class="space-y-3">
                @forelse($latestAnnouncements as $announ)
                    <div class="flex items-center gap-4 p-2 rounded-xl hover:bg-gray-50/80 transition-all">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl overflow-hidden shrink-0 border border-gray-100 flex items-center justify-center">
                            @if($announ->image)
                                <img src="{{ asset('storage/announcements/' . $announ->image) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-bullhorn text-blue-500 text-sm"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-medium text-gray-800 truncate">{{ $announ->title }}</h4>
                            <span class="text-[10px] text-gray-400 block mt-0.5">{{ \Carbon\Carbon::parse($announ->created_at)->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            @if($announ->type === 'penting')
                                <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[8px] font-medium rounded uppercase">Penting</span>
                            @elseif($announ->type === 'pengingat')
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[8px] font-medium rounded uppercase">Pengingatan</span>
                            @else
                                <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[8px] font-medium rounded uppercase">Info</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-4">Belum ada pengumuman.</p>
                @endforelse
            </div>

            <a href="{{ route('head.announcements.index') }}" class="w-full py-2 border border-gray-200 text-gray-600 hover:text-blue-600 text-[10px] font-medium rounded-xl block text-center transition-all shadow-sm">
                Lihat Semua Pengumuman &rarr;
            </a>
        </div>

        {{-- ROW 4: TABEL AKTIVITAS & AKSI CEPAT --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            {{-- Aktivitas Terbaru --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
                <div class="p-4 border-b border-gray-50">
                    <h3 class="text-[12px] font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-history text-blue-500"></i> Aktivitas Terbaru
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[11px]">
                        <thead class="bg-gray-50/50 text-gray-500 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-2.5 font-medium w-32">Waktu</th>
                                <th class="px-5 py-2.5 font-medium">Aktivitas</th>
                                <th class="px-5 py-2.5 font-medium">Siswa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-gray-600">
                            @forelse($recent_activities as $activity)
                            <tr class="hover:bg-gray-50/30 transition-colors">
                                <td class="px-5 py-3 text-gray-400">{{ $activity->updated_at->diffForHumans() }}</td>
                                <td class="px-5 py-3">
                                    @if($activity->status == 'returned')
                                        <span class="px-2 py-0.5 bg-green-50 text-green-600 rounded border border-green-100 text-[9px]">Pengembalian</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded border border-blue-100 text-[9px]">Peminjaman</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-600 font-medium">{{ $activity->student->name ?? 'Anonim' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-gray-400 italic">Belum ada aktivitas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Aksi Cepat --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-[12px] font-medium text-gray-700 flex items-center gap-2 mb-4 border-b border-gray-50 pb-3">
                    <i class="fas fa-th-large text-blue-500"></i> Aksi Cepat
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('head.transactions.index') }}" class="bg-blue-50/50 hover:bg-blue-50 border border-transparent hover:border-blue-100 transition-colors p-3.5 rounded-xl flex flex-col items-center justify-center gap-2 text-blue-500">
                        <i class="fas fa-file-alt text-lg"></i>
                        <span class="text-[10px] font-medium text-center">Data<br>Peminjaman</span>
                    </a>
                    <a href="{{ route('head.books.collection') }}" class="bg-green-50/50 hover:bg-green-50 border border-transparent hover:border-green-100 transition-colors p-3.5 rounded-xl flex flex-col items-center justify-center gap-2 text-green-500">
                        <i class="fas fa-book text-lg"></i>
                        <span class="text-[10px] font-medium text-center">Data<br>Buku</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
        Chart.defaults.font.size = 10;
        Chart.defaults.color = '#94a3b8';

        // Menerima Data dari Controller via Blade
        const months = {!! json_encode($months) !!};
        const borrowData = {!! json_encode($borrow_data) !!};
        const returnData = {!! json_encode($return_data) !!};
        
        const bookLabels = {!! json_encode($book_labels) !!};
        const bookData = {!! json_encode($book_data) !!};
        
        const statusData = [{{ $active_borrowings }}, {{ $late_count }}, {{ $completed_borrowings }}];

        // 1. Line Chart
        new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    { label: 'Peminjaman', data: borrowData, borderColor: '#3B82F6', backgroundColor: '#3B82F6', tension: 0.4, borderWidth: 2, pointRadius: 2 },
                    { label: 'Pengembalian', data: returnData, borderColor: '#10B981', backgroundColor: '#10B981', tension: 0.4, borderWidth: 2, pointRadius: 2 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { boxWidth: 10, usePointStyle: true } } }, scales: { y: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#f8fafc' } }, x: { grid: { display: false } } } }
        });

        // 2. Bar Chart
        new Chart(document.getElementById('barChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{ label: 'Peminjaman', data: borrowData, backgroundColor: '#3B82F6', borderRadius: 4, barThickness: 20 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#f8fafc' } }, x: { grid: { display: false } } } }
        });

        // 3. Donut Chart (Buku Terpopuler)
        if(bookLabels.length > 0) {
            new Chart(document.getElementById('donutBooks').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: bookLabels,
                    datasets: [{ data: bookData, backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444'], borderWidth: 2, hoverOffset: 4 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right', labels: { boxWidth: 8, usePointStyle: true, padding: 12 } } } }
            });
        }

        // 4. Donut Chart (Status)
        new Chart(document.getElementById('donutStatus').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Sedang Dipinjam', 'Terlambat', 'Sudah Selesai'],
                datasets: [{ data: statusData, backgroundColor: ['#3B82F6', '#EF4444', '#10B981'], borderWidth: 2, hoverOffset: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'right', labels: { boxWidth: 8, usePointStyle: true, padding: 12 } } } }
        });
    </script>
    @endpush
</x-layouts.app>