<x-layouts.app title="Laporan & Statistik">
    <div class="flex flex-col lg:flex-row gap-5 items-start font-sans text-gray-700">
        
        {{-- LEFT COLUMN: Controls & Summary (Sticky) --}}
        <div class="w-full lg:w-1/4 space-y-4 lg:sticky lg:top-5">
            
            {{-- Filter Section --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fas fa-filter text-blue-500"></i> Filter Periode
                </h3>
                <form action="{{ route('head.reports.index') }}" method="GET" class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full text-gray-700 border border-gray-200 rounded-lg px-2.5 py-1.5 text-[11px] outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full text-gray-700 border border-gray-200 rounded-lg px-2.5 py-1.5 text-[11px] outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    </div>
                    <div class="pt-1 flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg py-1.5 text-[11px] font-medium hover:bg-blue-700 transition flex items-center justify-center gap-1.5">
                            Terapkan
                        </button>
                        <a href="{{ route('head.reports.index') }}" class="px-2.5 py-1.5 bg-gray-50 text-gray-600 border border-gray-200 rounded-lg text-[11px] font-medium hover:bg-gray-100 transition flex items-center justify-center" title="Reset Filter">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </div>
                </form>
            </div>

            {{-- Ringkasan Teks --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fas fa-clipboard-list text-emerald-500"></i> Ringkasan Angka
                </h3>
                <div class="space-y-2.5 text-[11px]">
                    <div class="flex justify-between items-center text-gray-500"><span>Total Hari</span><span class="font-semibold text-gray-700">{{ $totalDays }} hari</span></div>
                    <div class="flex justify-between items-center text-gray-500"><span>Rata-rata Pinjam</span><span class="font-semibold text-gray-700">{{ $avg_borrows }} buku/hari</span></div>
                    <div class="flex justify-between items-center text-gray-500"><span>Rata-rata Kembali</span><span class="font-semibold text-gray-700">{{ $avg_returns }} buku/hari</span></div>
                    <div class="flex justify-between items-center text-gray-500 border-t border-gray-50 pt-2.5 mt-1"><span>Siswa Baru</span><span class="font-semibold text-gray-700">{{ $new_students }}</span></div>
                    <div class="flex justify-between items-center text-gray-500"><span>Koleksi Baru</span><span class="font-semibold text-gray-700">{{ $new_books }} buku</span></div>
                    
                    <div class="flex justify-between items-center text-gray-500 border-t border-gray-50 pt-2.5 mt-1"><span>Denda Terlambat</span><span class="font-semibold text-red-600">Rp {{ number_format($denda_terlambat, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between items-center text-gray-500"><span>Denda Lainnya</span><span class="font-semibold text-red-600">Rp {{ number_format($denda_lainnya, 0, ',', '.') }}</span></div>
                </div>
            </div>

            {{-- Export Panel --}}
            <div class="rounded-xl shadow-sm p-4 text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #1d4ed8 100%) !important;">
                <h3 class="text-[11px] font-semibold tracking-wider uppercase mb-1 flex items-center gap-1.5">
                    <i class="fas fa-file-export opacity-80"></i> Ekspor Laporan
                </h3>
                <p class="text-[10px] text-blue-100 mb-3 opacity-90">Unduh Excel/PDF untuk rentang tanggal yang dipilih.</p>
                
                <div class="space-y-1.5">
                    @php
                        $exportOptions = [
                            ['type' => 'peminjaman', 'label' => 'Peminjaman'],
                            ['type' => 'pengembalian', 'label' => 'Pengembalian'],
                            ['type' => 'denda', 'label' => 'Denda'],
                            ['type' => 'anggota', 'label' => 'Anggota'],
                            ['type' => 'koleksi', 'label' => 'Koleksi Buku'],
                        ];
                    @endphp

                    @foreach($exportOptions as $option)
                    <div class="flex justify-between items-center bg-white/10 rounded-lg p-1.5 backdrop-blur-sm border border-white/5 hover:bg-white/15 transition">
                        <span class="text-[10px] font-medium pl-1.5">{{ $option['label'] }}</span>
                        <div class="flex gap-1">
                            <a href="{{ route('head.reports.export', ['type' => $option['type'], 'format' => 'excel', 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="px-1.5 py-1 bg-green-500/20 text-green-300 hover:bg-green-500 hover:text-white rounded text-[9px] font-medium transition-colors" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="{{ route('head.reports.export', ['type' => $option['type'], 'format' => 'pdf', 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="px-1.5 py-1 bg-red-500/20 text-red-300 hover:bg-red-500 hover:text-white rounded text-[9px] font-medium transition-colors" title="PDF"><i class="fas fa-file-pdf"></i></a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN: Visual Reports --}}
        <div class="w-full lg:w-3/4 space-y-4">
            
            {{-- Pesan Sukses/Error --}}
            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif
            
            {{-- Headings & Quick Metrics --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4 border-b border-gray-50 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Analisis Kinerja</h2>
                        <p class="text-[10px] text-gray-500 mt-0.5">Periode: {{ $startDate->translatedFormat('d M Y') }} - {{ $endDate->translatedFormat('d M Y') }}</p>
                    </div>
                    <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-full flex justify-center items-center text-sm">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="p-3 rounded-lg bg-gray-50/80 border border-gray-100">
                        <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Peminjaman</p>
                        <h3 class="text-lg font-bold text-gray-700">{{ number_format($total_borrowings) }}</h3>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50/80 border border-gray-100">
                        <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Pengembalian</p>
                        <h3 class="text-lg font-bold text-gray-700">{{ number_format($total_returns) }}</h3>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50/80 border border-gray-100">
                        <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Siswa Aktif</p>
                        <h3 class="text-lg font-bold text-gray-700">{{ number_format($active_students) }}</h3>
                    </div>
                    <div class="p-3 rounded-lg bg-red-50/80 border border-red-50">
                        <p class="text-[9px] font-semibold text-red-400 uppercase tracking-wider mb-0.5">Total Denda</p>
                        <h3 class="text-base font-bold text-red-600 mt-0.5">Rp {{ number_format($total_fine, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>

            {{-- Main Line Chart --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fas fa-chart-area text-indigo-500"></i> Tren Sirkulasi Buku
                </h3>
                <div class="h-56 w-full"><canvas id="lineChart"></canvas></div>
            </div>

            {{-- Secondary Charts Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Category Bar --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fas fa-layer-group text-blue-500"></i> Minat per Kategori
                    </h3>
                    <div class="h-40 w-full"><canvas id="barCategory"></canvas></div>
                </div>

                {{-- Status Doughnut --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col">
                    <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fas fa-info-circle text-amber-500"></i> Proporsi Status
                    </h3>
                    <div class="flex-1 flex justify-center items-center relative h-32">
                        <canvas id="donutStatus"></canvas>
                    </div>
                    @if($status_terlambat > 0)
                    <div class="mt-3 text-center bg-red-50 text-red-600 rounded-md p-1.5 text-[10px] font-medium border border-red-100">
                        <i class="fas fa-exclamation-triangle mr-1"></i> {{ $status_terlambat }} peminjaman terlambat
                    </div>
                    @endif
                </div>
            </div>

            {{-- Lists: Top Books & Favorites --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Top Books --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fas fa-book text-emerald-500"></i> 5 Buku Terlaris
                    </h3>
                    <ul class="space-y-2.5">
                        @forelse($top_books as $index => $book)
                            <li class="flex justify-between items-center text-[11px] border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-[10px]">{{ $index + 1 }}</span>
                                    <span class="text-gray-700">{{ $book->title }}</span>
                                </div>
                                <span class="text-gray-500 font-semibold bg-gray-50 px-1.5 py-0.5 rounded text-[10px]">
                                    {{ $book->total }}x
                                </span>
                            </li>
                        @empty
                            <li class="text-gray-400 text-[10px] py-3 text-center">Belum ada data.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Top Favorites --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i class="fas fa-heart text-pink-500"></i> 5 Buku Disukai
                    </h3>
                    <ul class="space-y-2.5">
                        @forelse($top_favorites as $index => $favorite)
                            <li class="flex justify-between items-center text-[11px] border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded bg-pink-50 text-pink-600 flex items-center justify-center font-bold text-[10px]">{{ $index + 1 }}</span>
                                    <span class="text-gray-700">{{ $favorite->title }}</span>
                                </div>
                                <span class="text-gray-500 font-semibold bg-gray-50 px-1.5 py-0.5 rounded text-[10px]">
                                    {{ $favorite->total }}x
                                </span>
                            </li>
                        @empty
                            <li class="text-gray-400 text-[10px] py-3 text-center">Belum ada data.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Active Students Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50">
                    <h3 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-medal text-amber-400"></i> Peringkat Siswa Aktif
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[11px]">
                        <thead class="bg-gray-50/50 text-gray-500">
                            <tr>
                                <th class="px-4 py-2.5 font-medium text-center w-10">#</th>
                                <th class="px-4 py-2.5 font-medium">Nama Siswa</th>
                                <th class="px-4 py-2.5 font-medium">Kelas</th>
                                <th class="px-4 py-2.5 font-medium text-center">Pinjam</th>
                                <th class="px-4 py-2.5 font-medium text-right">Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-gray-600">
                            @forelse($top_students as $index => $student)
                            <tr class="hover:bg-gray-50/30 transition">
                                <td class="px-4 py-2.5 text-center font-medium text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5 font-medium text-gray-700">{{ $student->name }}</td>
                                <td class="px-4 py-2.5">{{ $student->class ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-block px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-medium">{{ $student->total_peminjaman }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-[10px]">{{ Carbon\Carbon::parse($student->terakhir_meminjam)->translatedFormat('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 text-[11px]">Belum ada data siswa aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Set up Chart.js defaults
        Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
        Chart.defaults.font.size = 10;
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.scale.grid.color = '#f8fafc';
        Chart.defaults.plugins.tooltip.titleFont.size = 11;
        Chart.defaults.plugins.tooltip.bodyFont.size = 10;
        Chart.defaults.plugins.tooltip.padding = 8;
        Chart.defaults.plugins.tooltip.cornerRadius = 6;

        // 1. LINE CHART (Sirkulasi)
        new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chart_labels) !!},
                datasets: [
                    { label: 'Peminjaman', data: {!! json_encode($chart_borrows) !!}, borderColor: '#4f46e5', backgroundColor: 'rgba(79, 70, 229, 0.05)', fill: true, tension: 0.4, borderWidth: 1.5, pointRadius: 2, pointBackgroundColor: '#4f46e5' },
                    { label: 'Pengembalian', data: {!! json_encode($chart_returns) !!}, borderColor: '#10b981', backgroundColor: 'transparent', tension: 0.4, borderWidth: 1.5, pointRadius: 2, pointBackgroundColor: '#10b981' }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 6, padding: 10 } } }, scales: { y: { beginAtZero: true, border: { dash: [4, 4], display: false } }, x: { grid: { display: false }, border: { display: false } } }, interaction: { intersect: false, mode: 'index' } }
        });

        // 2. BAR CHART (Kategori)
        new Chart(document.getElementById('barCategory').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($category_labels) !!},
                datasets: [{ label: 'Total Dipinjam', data: {!! json_encode($category_data) !!}, backgroundColor: '#3b82f6', borderRadius: 3, barThickness: 12 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: { dash: [4, 4], display: false } }, x: { grid: { display: false }, border: { display: false } } } }
        });

        // 3. DONUT (Status)
        new Chart(document.getElementById('donutStatus').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Sedang Dipinjam', 'Terlambat', 'Sudah Kembali'],
                datasets: [{ data: [{{ $status_dipinjam }}, {{ $status_terlambat }}, {{ $status_selesai }}], backgroundColor: ['#3b82f6', '#ef4444', '#10b981'], borderWidth: 0, hoverOffset: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 10, boxWidth: 6 } } } }
        });
    </script>
    @endpush
</x-layouts.app>