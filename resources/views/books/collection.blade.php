<x-layouts.app title="Koleksi Buku">
    <div class="space-y-5 text-[11px] text-gray-600">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-lg">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <p class="text-gray-400 uppercase text-[9px] tracking-wider">Total Buku</p>
                    <p class="text-[16px] text-gray-800">{{ number_format($total_buku) }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex items-center justify-center text-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-gray-400 uppercase text-[9px] tracking-wider">Buku Tersedia</p>
                    <p class="text-[16px] text-gray-800">{{ number_format($buku_tersedia) }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center text-lg">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div>
                    <p class="text-gray-400 uppercase text-[9px] tracking-wider">Buku Dipinjam</p>
                    <p class="text-[16px] text-gray-800">{{ number_format($buku_dipinjam) }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-lg">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <p class="text-gray-400 uppercase text-[9px] tracking-wider">Buku Rusak</p>
                    <p class="text-[16px] text-gray-800">{{ number_format($buku_rusak) }}</p>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <form action="{{ route('head.books.collection') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block mb-1 text-gray-400">Cari Buku</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-300"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul, pengarang, atau ISBN..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg outline-none focus:border-blue-400">
                    </div>
                </div>
                <div>
                    <label class="block mb-1 text-gray-400">Kategori</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-200 rounded-lg outline-none bg-white">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter mr-1"></i> Terapkan
                    </button>
                    <a href="{{ route('head.books.collection') }}" class="px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-sync-alt text-gray-400"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Charts & Top Books --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Pie Chart --}}
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-gray-800 font-semibold mb-4">Jumlah Buku per Kategori</p>
                <div class="h-56"><canvas id="pieCategory"></canvas></div>
            </div>

            {{-- Top Borrowed --}}
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-gray-800 font-semibold mb-4">Buku Paling Sering Dipinjam</p>
                <div class="space-y-4">
                    @foreach($top_books as $index => $top)
                    <div class="flex items-center gap-3">
                        <span class="text-gray-300 font-italic text-[14px]">0{{ $index+1 }}</span>
                        <img src="{{ asset('covers/'.$top->cover_image) }}" class="w-8 h-10 rounded object-cover border border-gray-100" onerror="this.src='{{ asset('images/no-cover.png') }}'">
                        <div class="flex-1">
                            <p class="text-gray-800 leading-tight">{{ $top->title }}</p>
                            <p class="text-[9px] text-gray-400">{{ $top->author }}</p>
                        </div>
                        <span class="text-blue-500 font-semibold text-[14px]">{{ $top->total }}x</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Bar Chart --}}
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-gray-800 font-semibold mb-4">Buku Berdasarkan Rak</p>
                <div class="h-56"><canvas id="barRak"></canvas></div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                <h3 class="text-gray-800 font-bold">Daftar Koleksi Buku 
                    <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-[9px] text-gray-500">{{ $books->total() }} data</span></h3>
                    <div class="flex gap-2">
                        <a href="{{ route('head.reports.export', ['type' => 'koleksi', 'format' => 'excel']) }}" 
                        class="text-[10px] px-3 py-1.5 border border-green-100 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 font-medium">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>

                        <a href="{{ route('head.reports.export', ['type' => 'koleksi', 'format' => 'pdf']) }}" 
                        class="text-[10px] px-3 py-1.5 border border-red-100 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 font-medium">
                            <i class="fas fa-file-pdf mr-1"></i> Export PDF
                        </a>
                    </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-400 uppercase text-[9px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3 font-semibold">ISBN</th>
                            <th class="px-4 py-3 font-semibold">Judul Buku</th>
                            <th class="px-4 py-3 font-semibold">Pengarang</th>
                            <th class="px-4 py-3 font-semibold">Kategori</th>
                            <th class="px-4 py-3 font-semibold">Rak</th>
                            <th class="px-4 py-3 font-semibold">Stok</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($books as $book)
                        <tr class="hover:bg-gray-50/50 transition-all">
                            <td class="px-4 py-3 text-gray-400">{{ $book->isbn ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $book->title }}</td>
                            <td class="px-4 py-3">{{ $book->author }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-500 rounded-md text-[10px]">{{ $book->category->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-400">{{ $book->rak->nama_rak ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $book->stock }}</td>
                            <td class="px-4 py-3">
                                @if($book->stock > 0)
                                    <span class="text-green-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Tersedia</span>
                                @else
                                    <span class="text-red-400 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Kosong</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-50">
                {{ $books->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pie Chart
        new Chart(document.getElementById('pieCategory'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($pie_labels) !!},
                datasets: [{
                    data: {!! json_encode($pie_data) !!},
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#64748b']
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } } }
        });

        // Bar Chart
        new Chart(document.getElementById('barRak'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($bar_labels) !!},
                datasets: [{
                    label: 'Buku',
                    data: {!! json_encode($bar_data) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } } }
        });
    </script>
    @endpush
</x-layouts.app>