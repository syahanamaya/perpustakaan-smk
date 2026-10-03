<x-layouts.app title="Data Siswa">
    <div class="space-y-6 text-[11px] text-gray-600">

        <!-- 1. STATISTIC CARDS (BAGIAN ATAS) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Total Siswa -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-lg">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Total Siswa</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($total_siswa ?? 1250) }}</h3>
                    <p class="text-[9px] text-gray-400 font-medium">Semua siswa</p>
                </div>
            </div>

            <!-- Siswa Aktif -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-500 flex items-center justify-center text-lg">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Siswa Aktif</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($siswa_aktif ?? 1180) }}</h3>
                    <p class="text-[9px] font-medium text-green-600">{{ $persen_aktif ?? '94.4' }}% dari total siswa</p>
                </div>
            </div>

            <!-- Siswa Nonaktif -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center text-lg">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Siswa Nonaktif</p>
                    <h3 class="text-base font-medium text-gray-800">{{ number_format($siswa_nonaktif ?? 70) }}</h3>
                    <p class="text-[9px] font-medium text-orange-600">{{ $persen_nonaktif ?? '5.6' }}% dari total siswa</p>
                </div>
            </div>

            <!-- Siswa Terdaftar Bulan Ini -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-500 flex items-center justify-center text-lg">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] uppercase font-medium tracking-tight">Siswa Terdaftar</p>
                    <h3 class="text-base font-medium text-gray-800">{{ $siswa_bulan_ini ?? 35 }}</h3>
                    <p class="text-[9px] text-gray-400 font-medium">{{ now()->translatedFormat('F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- 2. FILTER BAR (BAGIAN TENGAH) -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
            <form action="{{ route('admin.students.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <!-- Cari -->
                <div class="flex-1 min-w-50">
                    <label class="block text-gray-400 font-medium mb-1">Cari Siswa</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama siswa atau NIS..." 
                            class="w-full pl-3 pr-10 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 text-[11px]">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-300"></i>
                    </div>
                </div>

                <!-- Kelas (Dinamis) -->
                <div class="w-40">
                    <label class="block text-gray-400 font-medium mb-1">Kelas</label>
                    <select name="class" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class }}" {{ request('class') == $class ? 'selected' : '' }}>
                                {{ $class }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jurusan (Dinamis) -->
                <div class="w-44">
                    <label class="block text-gray-400 font-medium mb-1">Jurusan</label>
                    <select name="major" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="">Semua Jurusan</option>
                        @foreach($majors as $major)
                            <option value="{{ $major }}" {{ request('major') == $major ? 'selected' : '' }}>
                                {{ $major }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="w-36">
                    <label class="block text-gray-400 font-medium mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none bg-white text-[11px]">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Lulus</option>
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2">
                        <i class="fas fa-filter text-[10px]"></i> Terapkan Filter
                    </button>
                    
                    <a href="{{ route('admin.students.index') }}" class="border border-gray-200 text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-100 transition flex items-center justify-center">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            <div class="lg:col-span-4 bg-white p-4 rounded-xl border border-dashed border-green-400 shadow-sm flex flex-col justify-center">
                <h3 class="text-[12px] font-medium text-gray-800">Import Data Siswa</h3>
                <p class="text-[10px] text-gray-500 mt-1">Import banyak data siswa sekaligus dari file Excel</p>
                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="m-0 flex">
                        @csrf
                        <input type="file" name="file" id="import_student_file" class="hidden" onchange="this.form.submit()" accept=".xlsx,.xls">
                        <label for="import_student_file" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg font-medium flex items-center gap-1.5 transition text-[10px] cursor-pointer mb-0">
                            <i class="fas fa-upload"></i> Import Excel
                        </label>
                    </form>
                    <a href="{{ route('admin.students.template') }}" class="bg-white border border-blue-500 text-blue-500 hover:bg-blue-50 px-3 py-1.5 rounded-lg font-medium flex items-center gap-1.5 transition text-[10px]">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
            </div>

            <div class="lg:col-span-8 bg-[#f8fbff] p-4 rounded-xl border border-blue-100 shadow-sm relative overflow-hidden">
                <div class="flex items-center gap-2 text-blue-600 font-medium mb-3 text-[11px]">
                    <i class="fas fa-info-circle"></i> Panduan Import
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative z-10">
                    <div>
                        <ol class="list-decimal list-inside text-[10px] text-gray-600 space-y-1">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format yang tersedia</li>
                            <li>Upload file Excel (.xlsx, .xls)</li>
                            <li>Pastikan data tidak ada yang kosong pada kolom wajib</li>
                        </ol>
                    </div>
                    <div>
                        <ul class="text-[10px] text-gray-600 space-y-1">
                            <li class="flex items-start gap-1.5">
                                <i class="fas fa-check text-gray-500 mt-0.5"></i> 
                                <span>Kolom Wajib: Nama, NIS, Kelas, Jurusan, No. Telepon</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <i class="fas fa-check text-gray-500 mt-0.5"></i> 
                                <span>Maksimal ukuran file: 5MB</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <i class="fas fa-file-excel text-green-500 opacity-60 text-3xl absolute bottom-3 right-4"></i>
            </div>

            {{-- <div class="lg:col-span-3 bg-white p-4 rounded-xl border border-dashed border-gray-300 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-600 mb-3">Riwayat Import Terakhir</h3>
                    
                    @if($lastImport)
                        <div class="flex items-center gap-1.5 text-[11px] {{ $lastImport->status == 'Berhasil' ? 'text-green-600' : 'text-red-600' }} mb-1">
                            <i class="far fa-file-alt text-gray-400"></i>
                            <span class="text-gray-800">{{ $lastImport->file_name }}</span>
                        </div>
                        <p class="text-[9px] text-gray-500">
                            {{ \Carbon\Carbon::parse($lastImport->created_at)->translatedFormat('d F Y, H:i') }} 
                            - {{ $lastImport->status }} 
                            @if($lastImport->total_rows > 0)
                                ({{ $lastImport->total_rows }} data)
                            @endif
                        </p>
                    @else
                        <div class="flex items-center gap-1.5 text-[11px] text-gray-500 mb-1">
                            <i class="far fa-folder-open"></i>
                            <span>Belum ada riwayat import</span>
                        </div>
                        <p class="text-[9px] text-gray-400">Silakan lakukan import data pertama Anda.</p>
                    @endif

                </div>
                
                <a href="#" class="text-[10px] text-blue-500 font-medium hover:text-blue-700 hover:underline transition flex items-center gap-1 mt-2">
                    Lihat Semua Riwayat <i class="fas fa-arrow-right text-[9px]"></i>
                </a>
            </div> --}}
        </div>

        <!-- 3. TABLE DATA (BAGIAN UTAMA) -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-50 flex justify-between items-center text-gray-800 font-medium">
                <h2>Daftar Siswa <span class="ml-2 px-2 py-0.5 bg-gray-100 rounded-full text-[10px] text-gray-400 font-normal">{{ $students->total() }} data</span></h2>
                <div class="flex gap-2">
                    <a href="{{ route('admin.students.create') }}" class="bg-blue-600 text-white px-4 py-1.5 rounded-lg font-medium hover:bg-blue-700 flex items-center gap-2 shadow-sm transition">
                        <i class="fas fa-plus"></i> Tambah Siswa
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-[9px] text-gray-400 uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 font-medium text-center w-10">No.</th>
                            <th class="px-4 py-3 font-medium">NIS</th>
                            <th class="px-4 py-3 font-medium">Nama Siswa</th>
                            <th class="px-4 py-3 font-medium">Kelas</th>
                            <th class="px-4 py-3 font-medium">Jurusan</th>
                            <th class="px-4 py-3 font-medium">No. Telepon</th>
                            <th class="px-4 py-3 font-medium text-center">Status</th>
                            <th class="px-4 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($students as $index => $student)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-gray-400 text-center">
                                {{ ($students->currentPage() - 1) * $students->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-medium">{{ $student->nis }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $student->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $student->class }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $student->major ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $student->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($student->status === 'active')
                                    <span class="px-2 py-0.5 rounded bg-green-50 text-green-600 border border-green-100 text-[9px] font-medium uppercase tracking-wider">Aktif</span>
                                @elseif($student->status === 'graduated')
                                    {{-- Status Lulus dengan warna biru --}}
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-100 text-[9px] font-medium uppercase tracking-wider">Lulus</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-red-50 text-red-500 border border-red-100 text-[9px] font-medium uppercase tracking-wider">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="w-6 h-6 rounded bg-blue-50 text-blue-500 border border-blue-100 hover:bg-blue-500 hover:text-white transition flex items-center justify-center">
                                        <i class="fas fa-eye text-[9px]"></i>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student->id) }}" class="w-6 h-6 rounded bg-amber-50 text-amber-500 border border-amber-100 hover:bg-amber-500 hover:text-white transition flex items-center justify-center">
                                        <i class="fas fa-edit text-[9px]"></i>
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Hapus data siswa?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-6 h-6 rounded bg-red-50 text-red-500 border border-red-100 hover:bg-red-500 hover:text-white transition flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-[9px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-gray-400 italic">Data siswa tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination (Bottom) -->
            <div class="p-4 border-t border-gray-50 bg-gray-50/30">
                {{ $students->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</x-layouts.app>