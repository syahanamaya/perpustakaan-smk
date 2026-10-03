<x-layouts.app title="Pengaturan Kuota Peminjaman">
    <div class="space-y-5 max-w-5xl">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50">
                <h3 class="text-[14px] font-medium text-gray-800 flex items-center gap-2">
                    <i class="fas fa-users text-blue-500"></i> Tabel 1 — Batas Kuota per Kelas
                </h3>
                <p class="text-[11px] text-gray-400 mt-1">Kuota dipisah: buku paket/pelajaran (wajib) dan buku bebas (novel, komik, umum).</p>
            </div>

            <form action="{{ route('head.loan-rules.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[12px]">
                        <thead class="bg-gray-50 text-[10px] text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 font-medium">Tingkat Kelas</th>
                                <th class="px-5 py-3 font-medium">Maks. Buku Paket</th>
                                <th class="px-5 py-3 font-medium">Maks. Buku Bebas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($rules as $index => $rule)
                            <tr class="hover:bg-gray-50/40">
                                <td class="px-5 py-4">
                                    <input type="hidden" name="rules[{{ $index }}][class_level]" value="{{ $rule->class_level }}">
                                    <p class="font-medium text-gray-800">Kelas {{ $rule->class_level }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2 max-w-48">
                                        <input type="number" name="rules[{{ $index }}][max_paket]" min="0" max="20" required
                                            value="{{ old('rules.'.$index.'.max_paket', $rule->max_paket ?? 4) }}"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px] text-right outline-none focus:border-blue-500">
                                        <span class="text-[10px] text-gray-400 shrink-0">buku</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2 max-w-48">
                                        <input type="number" name="rules[{{ $index }}][max_bebas]" min="0" max="20" required
                                            value="{{ old('rules.'.$index.'.max_bebas', $rule->max_bebas ?? 2) }}"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px] text-right outline-none focus:border-blue-500">
                                        <span class="text-[10px] text-gray-400 shrink-0">buku</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-5 border-t border-gray-100 flex justify-end bg-gray-50/50">
                    <button type="submit" class="px-5 py-2 text-[12px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Kuota Kelas
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50">
                <h3 class="text-[14px] font-medium text-gray-800 flex items-center gap-2">
                    <i class="fas fa-clock text-teal-500"></i> Tabel 2 — Durasi per Kategori Buku
                </h3>
                <p class="text-[11px] text-gray-400 mt-1">Jatuh tempo dihitung otomatis dari kategori buku. Isi <span class="font-medium text-gray-600">0</span> hari untuk buku yang hanya boleh dibaca di tempat.</p>
            </div>

            <form action="{{ route('head.loan-rules.durations') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[12px]">
                        <thead class="bg-gray-50 text-[10px] text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 font-medium">Kategori Buku</th>
                                <th class="px-5 py-3 font-medium">Jenis Kuota</th>
                                <th class="px-5 py-3 font-medium">Durasi Pinjam (Hari)</th>
                                <th class="px-5 py-3 font-medium">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($categories as $index => $category)
                            @php
                                $days = (int) old('categories.'.$index.'.loan_days', $category->loan_days ?? 7);
                                $group = old('categories.'.$index.'.quota_group', $category->quota_group ?? 'bebas');
                            @endphp
                            <tr class="hover:bg-gray-50/40">
                                <td class="px-5 py-4">
                                    <input type="hidden" name="categories[{{ $index }}][id]" value="{{ $category->id }}">
                                    <p class="font-medium text-gray-800">{{ $category->name }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <select name="categories[{{ $index }}][quota_group]" class="w-full max-w-48 border border-gray-200 rounded-lg px-3 py-2 text-[12px] outline-none focus:border-blue-500 bg-white">
                                        <option value="paket" @selected($group === 'paket')>Buku Paket / Pelajaran</option>
                                        <option value="bebas" @selected($group === 'bebas')>Buku Bebas (novel, komik, umum)</option>
                                    </select>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2 max-w-48">
                                        <input type="number" name="categories[{{ $index }}][loan_days]" min="0" max="400" required
                                            value="{{ $days }}"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-[12px] text-right outline-none focus:border-blue-500">
                                        <span class="text-[10px] text-gray-400 shrink-0">hari</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-[11px] text-gray-500">
                                    @if($days === 0)
                                        Baca di tempat (tidak boleh dipinjam)
                                    @elseif($days >= 180)
                                        Setara 1–2 semester
                                    @else
                                        Pinjaman jangka pendek
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada kategori buku. Tambahkan kategori di Data Master.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-5 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <p class="text-[11px] text-teal-700 bg-teal-50 px-3 py-1.5 rounded-lg">
                        <i class="fas fa-info-circle mr-1"></i> Contoh: Buku Paket 360 hari, Novel 7 hari, Referensi 0 hari.
                    </p>
                    <button type="submit" class="px-5 py-2 text-[12px] font-medium bg-teal-600 text-white rounded-lg hover:bg-teal-700 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Durasi Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
