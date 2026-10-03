<x-layouts.app title="Data Anggota">
    {{-- Inisialisasi Alpine.js state untuk modal --}}
    <div class="space-y-5" x-data="{ showModal: false, activeStudent: null }">
        
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex justify-center items-center text-lg"><i class="fas fa-users"></i></div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-medium">Total Anggota</p>
                    <h3 class="text-[16px] font-bold text-gray-800">{{ number_format($total_siswa) }}</h3>
                    <p class="text-[9px] text-gray-400 italic">Siswa terdaftar</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex justify-center items-center text-lg"><i class="fas fa-user-check"></i></div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-medium">Anggota Aktif</p>
                    <h3 class="text-[16px] font-bold text-gray-800">{{ number_format($siswa_aktif) }}</h3>
                    <p class="text-[9px] text-green-500 font-medium">{{ $total_siswa > 0 ? round(($siswa_aktif/$total_siswa)*100, 2) : 0 }}% dari total</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-500 flex justify-center items-center text-lg"><i class="fas fa-user-slash"></i></div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-medium">Anggota Nonaktif</p>
                    <h3 class="text-[16px] font-bold text-gray-800">{{ number_format($siswa_nonaktif) }}</h3>
                    <p class="text-[9px] text-red-400 font-medium">{{ $total_siswa > 0 ? round(($siswa_nonaktif/$total_siswa)*100, 2) : 0 }}% dari total</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex justify-center items-center text-lg"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-medium">Kelas Terdaftar</p>
                    <h3 class="text-[16px] font-bold text-gray-800">{{ $total_kelas }}</h3>
                    <p class="text-[9px] text-gray-400 italic">Kelas</p>
                </div>
            </div>
        </div>

        {{-- Filter Area --}}
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-50">
                <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Cari Anggota</label>
                <div class="relative">
                    <input type="text" name="search" placeholder="Ketik nama, NIS, atau kelas..." class="w-full border border-gray-200 rounded-lg pl-3 pr-10 py-2 text-[11px] outline-none focus:border-blue-500">
                    <i class="fas fa-search absolute right-3 top-2.5 text-gray-300 text-[11px]"></i>
                </div>
            </div>
            <div class="w-40">
                <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Kelas</label>
                <select class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[11px] outline-none bg-white">
                    <option>Semua Kelas</option>
                    @foreach($list_kelas as $kelas)
                        <option>{{ $kelas }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg text-[11px] font-medium hover:bg-blue-700 transition-colors">Terapkan Filter</button>
            <button class="px-3 py-2 bg-gray-50 text-gray-400 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"><i class="fas fa-sync-alt text-[11px]"></i></button>
        </div>

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Table --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                    <h2 class="text-[12px] font-semibold text-gray-700">Daftar Anggota (Siswa) <span class="ml-2 text-[10px] text-gray-400 font-normal px-2 py-0.5 bg-gray-50 rounded-full">{{ $total_siswa }} data</span></h2>
                    <div class="flex gap-2">
                        <a href="{{ route('head.reports.export', [
                            'type' => 'anggota', 
                            'format' => 'excel', 
                            'start_date' => request('start_date'), 
                            'end_date' => request('end_date')
                        ]) }}" 
                        class="text-[10px] px-3 py-1.5 border border-green-100 text-green-600 bg-green-50 rounded-lg hover:bg-green-100 font-medium flex items-center">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>

                        <a href="{{ route('head.reports.export', [
                            'type' => 'anggota', 
                            'format' => 'pdf', 
                            'start_date' => request('start_date'), 
                            'end_date' => request('end_date')
                        ]) }}" 
                        class="text-[10px] px-3 py-1.5 border border-red-100 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 font-medium flex items-center">
                            <i class="fas fa-file-pdf mr-1"></i> Export PDF
                        </a>
                    </div>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-[10px] text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 font-medium">No.</th>
                            <th class="px-5 py-3 font-medium">NIS</th>
                            <th class="px-5 py-3 font-medium">Nama Siswa</th>
                            <th class="px-5 py-3 font-medium">Kelas</th>
                            <th class="px-5 py-3 font-medium text-center">Status</th>
                            <th class="px-5 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11px] text-gray-600 divide-y divide-gray-50">
                        @foreach($students as $index => $student)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3 text-gray-400">{{ $students->firstItem() + $index }}</td>
                            <td class="px-5 py-3 font-medium">{{ $student->nis }}</td>
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $student->name }}</td>
                            <td class="px-5 py-3">{{ $student->class }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($student->status === 'active')
                                    <span class="px-2 py-0.5 rounded bg-green-50 text-green-600 border border-green-100 text-[9px] font-bold uppercase tracking-wider">Aktif</span>
                                @elseif($student->status === 'graduated')
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-100 text-[9px] font-bold uppercase tracking-wider">Lulus</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-red-50 text-red-500 border border-red-100 text-[9px] font-bold uppercase tracking-wider">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                {{-- Tombol ini sekarang memicu Modal Detail --}}
                                <button type="button" 
                                        @click="activeStudent = {
                                            nis: '{{ $student->nis }}',
                                            name: '{{ addslashes($student->name) }}',
                                            class: '{{ $student->class }}',
                                            status: '{{ $student->status }}'
                                        }; showModal = true"
                                        class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-lg transition-colors"
                                        title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 border-t border-gray-50">
                    {{ $students->links() }}
                </div>
            </div>

            {{-- Sidebar Statistics --}}
            <div class="space-y-5">
                {{-- Chart Kategori --}}
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-[12px] font-semibold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-chart-pie text-blue-500"></i> Anggota per Kelas</h3>
                    <div class="h-40 w-full relative mb-4">
                        <canvas id="classChart"></canvas>
                    </div>
                </div>

                {{-- Gender Info --}}
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-[12px] font-semibold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-venus-mars text-blue-500"></i> Anggota per Jenis Kelamin</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-50 text-center">
                            <i class="fas fa-male text-blue-600 mb-1"></i>
                            <p class="text-[9px] text-gray-400 font-bold uppercase">Laki-laki</p>
                            <h4 class="text-[14px] font-bold text-gray-800">{{ $laki_laki }}</h4>
                        </div>
                        <div class="p-3 bg-red-50/50 rounded-xl border border-red-50 text-center">
                            <i class="fas fa-female text-red-500 mb-1"></i>
                            <p class="text-[9px] text-gray-400 font-bold uppercase">Perempuan</p>
                            <h4 class="text-[14px] font-bold text-gray-800">{{ $perempuan }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Info Box --}}
                <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl flex gap-3">
                    <i class="fas fa-info-circle text-blue-500 text-[14px] mt-0.5"></i>
                    <div>
                        <h4 class="text-[11px] font-bold text-blue-800">Informasi</h4>
                        <p class="text-[10px] text-blue-600 leading-relaxed mt-0.5">Anggota nonaktif adalah siswa yang sudah lulus atau tidak lagi terdaftar di sekolah.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL DETAIL ANGGOTA --}}
        <div x-show="showModal" 
             style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
             
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                {{-- Background Overlay --}}
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" 
                     @click="showModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    {{-- Header Modal --}}
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center relative">
                        <h3 class="text-lg font-bold text-gray-800" id="modal-title">Detail Siswa</h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="px-6 py-6" x-show="activeStudent">
                        <div class="flex items-center gap-5 mb-6">
                            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex justify-center items-center text-3xl border border-blue-100">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-800" x-text="activeStudent.name"></h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md" x-text="'NIS: ' + activeStudent.nis"></span>
                                    
                                    {{-- Dynamic Status Label --}}
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md"
                                          :class="{
                                              'bg-green-50 text-green-600 border border-green-100': activeStudent.status === 'active',
                                              'bg-blue-50 text-blue-600 border border-blue-100': activeStudent.status === 'graduated',
                                              'bg-red-50 text-red-500 border border-red-100': activeStudent.status !== 'active' && activeStudent.status !== 'graduated'
                                          }"
                                          x-text="activeStudent.status === 'active' ? 'Aktif' : (activeStudent.status === 'graduated' ? 'Lulus' : 'Nonaktif')">
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1"><i class="fas fa-chalkboard-teacher mr-1"></i> Kelas</p>
                                <p class="font-semibold text-gray-800 text-sm" x-text="activeStudent.class"></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1"><i class="fas fa-id-card mr-1"></i> Nomor Induk</p>
                                <p class="font-semibold text-gray-800 text-sm" x-text="activeStudent.nis"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="showModal = false" class="px-6 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- END MODAL --}}

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('classChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($students_per_class->pluck('class')) !!},
                datasets: [{
                    data: {!! json_encode($students_per_class->pluck('total')) !!},
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444'],
                    borderWidth: 2
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                cutout: '70%', 
                plugins: { 
                    legend: { position: 'right', labels: { boxWidth: 10, font: { size: 9 } } } 
                } 
            }
        });
    </script>
    @endpush
</x-layouts.app>