<x-layouts.app title="Detail Siswa - {{ $student->name }}">
    <div class="max-w-5xl mx-auto py-4 px-4 space-y-6 text-[11px] text-gray-600">
        
        <!-- Header / Breadcrumb -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-[10px] uppercase tracking-widest text-gray-400 font-bold">
                <a href="{{ route('admin.students.index') }}" class="hover:text-blue-600 transition-colors">Daftar Siswa</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-gray-600">Profil Anggota</span>
            </div>
            <a href="{{ route('admin.students.edit', $student->id) }}" class="bg-amber-50 text-amber-600 border border-amber-100 px-3 py-1.5 rounded-lg font-bold hover:bg-amber-500 hover:text-white transition-all flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Profil
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- SISI KIRI: PROFIL (4 KOLOM) -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="h-20 bg-linier-to-r from-blue-600 to-blue-400"></div>
                    <div class="px-5 pb-6 -mt-10">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-20 h-20 rounded-full border-4 border-white shadow-md bg-gray-100 overflow-hidden mb-3">
                                @if($student->photo)
                                    <img src="{{ asset('storage/' . $student->photo) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl font-bold bg-blue-50 text-blue-200">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h2 class="text-base font-bold text-gray-800">{{ $student->name }}</h2>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $student->class }} • {{ $student->major }}</p>
                            
                            <div class="mt-4 flex gap-2">
                                <span class="px-3 py-1 bg-green-50 text-green-600 border border-green-100 rounded-full font-bold text-[9px] uppercase">
                                    {{ $student->status }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-8 space-y-4 border-t border-gray-50 pt-6">
                            <div class="flex justify-between">
                                <span class="text-gray-400 font-bold uppercase text-[9px]">NIS</span>
                                <span class="text-gray-700 font-medium">{{ $student->nis }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 font-bold uppercase text-[9px]">Telepon</span>
                                <span class="text-gray-700 font-medium">{{ $student->phone ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 font-bold uppercase text-[9px]">Gender</span>
                                <span class="text-gray-700 font-medium">{{ $student->gender }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mini Stats -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white p-3 rounded-xl border border-gray-100 text-center shadow-sm">
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Total</p>
                        <h4 class="text-sm font-bold text-blue-600">{{ $stats['total_pinjam'] }}</h4>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-gray-100 text-center shadow-sm">
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Aktif</p>
                        <h4 class="text-sm font-bold text-green-600">{{ $stats['sedang_pinjam'] }}</h4>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-gray-100 text-center shadow-sm">
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Telat</p>
                        <h4 class="text-sm font-bold text-red-600">{{ $stats['terlambat'] }}</h4>
                    </div>
                </div>
            </div>

            <!-- SISI KANAN: RIWAYAT (8 KOLOM) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-history text-blue-500"></i> Riwayat Peminjaman Terakhir
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-[9px] text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Kode</th>
                                    <th class="px-4 py-3 font-medium">Buku</th>
                                    <th class="px-4 py-3 font-medium">Tgl Pinjam</th>
                                    <th class="px-4 py-3 font-medium text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($borrowings as $trx)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-3 font-bold text-gray-700 uppercase">{{ $trx->transaction_code }}</td>
                                    <td class="px-4 py-3">
                                        @foreach($trx->details as $d)
                                            <div class="font-medium text-gray-800">• {{ $d->book->title }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $trx->borrow_date }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold border {{ $trx->status == 'returned' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-blue-50 text-blue-600 border-blue-100' }}">
                                            {{ $trx->status == 'returned' ? 'Selesai' : 'Diproses' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-10 text-center text-gray-400 italic">Siswa ini belum pernah meminjam buku.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{ $borrowings->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>