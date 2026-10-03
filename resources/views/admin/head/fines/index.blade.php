<x-layouts.app title="Pengaturan Denda">
    <div class="space-y-6" 
         x-data="{ 
            showHistoryModal: false,
            // Satuan default dari database, bersihkan karakter titik jika ada
            lateFeePerDay: parseInt('{{ $currentSetting->late_fee_per_day ?? 1000 }}'),
            maxFeePerBook: parseInt('{{ $currentSetting->max_fee_per_book ?? 50000 }}'),
            toleranceDays: parseInt('{{ $currentSetting->tolerance_days ?? 1 }}'),
            damagedBookFee: parseInt('{{ $currentSetting->damaged_book_fee ?? 10000 }}'),
            roundingRule: '{{ $currentSetting->rounding_rule ?? "Dibulatkan ke atas (Ribuan)" }}',
            maxFeePerTransaction: parseInt('{{ $currentSetting->max_fee_per_transaction ?? 200000 }}'),
            
            // Variabel Simulator Kontrol
            previewDays: 5,
            previewBooks: 2,

            // Fungsi Hitung Live Preview Dinamis
            calculatePreview() {
                let effectiveDays = Math.max(0, this.previewDays - this.toleranceDays);
                let rawFinePerBook = effectiveDays * this.lateFeePerDay;
                
                // Batasi denda per buku jika ada aturan maksimal denda per buku
                if (this.maxFeePerBook > 0) {
                    rawFinePerBook = Math.min(rawFinePerBook, this.maxFeePerBook);
                }

                let totalFine = rawFinePerBook * this.previewBooks;

                // Terapkan Aturan Pembulatan Ribuan ke atas jika aktif
                if (this.roundingRule === 'Dibulatkan ke atas (Ribuan)' && totalFine > 0) {
                    totalFine = Math.ceil(totalFine / 1000) * 1000;
                }

                // Batasi total denda berdasarkan Batas Maksimal Denda Per Transaksi
                if (this.maxFeePerTransaction > 0) {
                    totalFine = Math.min(totalFine, this.maxFeePerTransaction);
                }

                return totalFine;
            },

            formatRupiah(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            }
         }">

        {{-- Pesan Sukses --}}
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        {{-- Pesan Error Validasi --}}
        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        {{-- Grid Form & Preview --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- KOLOM KIRI: FORM PENGATURAN --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <form action="{{ route('head.fines.store') }}" method="POST">
                        @csrf
                        
                        {{-- Form Header --}}
                        <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-white">
                            <h3 class="text-[14px] font-medium text-gray-800 flex items-center gap-2">
                                <i class="fas fa-cog text-blue-500"></i> Pengaturan Aturan Denda
                            </h3>
                            <span class="px-3 py-1 bg-green-50 text-green-600 border border-green-100 rounded-full text-[10px] font-medium flex items-center gap-1">
                                <i class="fas fa-check"></i> Aktif
                            </span>
                        </div>

                        {{-- Form Body --}}
                        <div class="p-6 space-y-5">
                            
                            {{-- Input 1: Denda Keterlambatan --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-800">Denda Keterlambatan</label>
                                    <p class="text-[10px] text-gray-400">Denda per hari keterlambatan per buku</p>
                                </div>
                                <div class="flex items-center border border-gray-200 rounded-lg px-3 overflow-hidden focus-within:border-blue-500 transition-colors">
                                    <span class="text-gray-500 text-[12px] font-medium pr-2">Rp</span>
                                    <input type="number" name="late_fee_per_day" x-model.number="lateFeePerDay" class="w-full py-2.5 outline-none text-right text-[12px] text-gray-700 font-medium bg-transparent" required min="0">
                                </div>
                            </div>

                            {{-- Input 2: Maksimal Denda --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-800">Maksimal Denda per Buku</label>
                                    <p class="text-[10px] text-gray-400">Batas maksimal denda yang harus dibayarkan per buku</p>
                                </div>
                                <div class="flex items-center border border-gray-200 rounded-lg px-3 overflow-hidden focus-within:border-blue-500 transition-colors">
                                    <span class="text-gray-500 text-[12px] font-medium pr-2">Rp</span>
                                    <input type="number" name="max_fee_per_book" x-model.number="maxFeePerBook" class="w-full py-2.5 outline-none text-right text-[12px] text-gray-700 font-medium bg-transparent" required min="0">
                                </div>
                            </div>

                            {{-- Input 3: Toleransi --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-800">Toleransi Keterlambatan</label>
                                    <p class="text-[10px] text-gray-400">Jumlah hari toleransi sebelum dikenakan denda</p>
                                </div>
                                <div class="flex items-center border border-gray-200 rounded-lg px-3 overflow-hidden focus-within:border-blue-500 transition-colors">
                                    <input type="number" name="tolerance_days" x-model.number="toleranceDays" class="w-full py-2.5 outline-none text-right text-[12px] text-gray-700 font-medium bg-transparent" required min="0">
                                    <span class="text-gray-500 text-[12px] font-medium pl-3 border-l ml-2 py-1">hari</span>
                                </div>
                            </div>

                            {{-- Input 4: Kriteria Rusak (% harga buku) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start border-t border-gray-50 pt-5 mt-2">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-800">Kriteria Denda Buku Rusak</label>
                                    <p class="text-[10px] text-gray-400">Persentase dari harga buku di Data Buku. Ringan / Sedang / Berat.</p>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <p class="text-[9px] text-gray-400 mb-1">Ringan %</p>
                                        <input type="number" name="damage_light_percent" min="0" max="100" required
                                            value="{{ old('damage_light_percent', $currentSetting->damage_light_percent ?? 25) }}"
                                            class="w-full border border-gray-200 rounded-lg px-2 py-2 text-right text-[12px] outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-gray-400 mb-1">Sedang %</p>
                                        <input type="number" name="damage_medium_percent" min="0" max="100" required
                                            value="{{ old('damage_medium_percent', $currentSetting->damage_medium_percent ?? 50) }}"
                                            class="w-full border border-gray-200 rounded-lg px-2 py-2 text-right text-[12px] outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-gray-400 mb-1">Berat %</p>
                                        <input type="number" name="damage_heavy_percent" min="0" max="100" required
                                            value="{{ old('damage_heavy_percent', $currentSetting->damage_heavy_percent ?? 75) }}"
                                            class="w-full border border-gray-200 rounded-lg px-2 py-2 text-right text-[12px] outline-none focus:border-blue-500">
                                    </div>
                                </div>
                            </div>

                            {{-- Input 5: Buku Hilang --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-800">Denda Buku Hilang</label>
                                    <p class="text-[10px] text-gray-400">Otomatis 100% dari harga buku di Data Buku.</p>
                                </div>
                                <input type="hidden" name="lost_book_fee_type" value="Sesuai Harga Buku">
                                <div class="border border-gray-200 rounded-lg px-3 py-2.5 text-[12px] text-gray-700 bg-gray-50">
                                    Sesuai Harga Buku (100%)
                                </div>
                            </div>

                            {{-- Input 6: Pembulatan --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center border-t border-gray-50 pt-5 mt-2">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-800">Pembulatan Denda</label>
                                    <p class="text-[10px] text-gray-400">Aturan pembulatan nilai denda</p>
                                </div>
                                <select name="rounding_rule" x-model="roundingRule" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-[12px] text-gray-700 font-medium outline-none focus:border-blue-500 bg-white cursor-pointer">
                                    <option value="Dibulatkan ke atas (Ribuan)">Dibulatkan ke atas (Ribuan)</option>
                                    <option value="Tidak dibulatkan">Tidak dibulatkan</option>
                                </select>
                            </div>

                            {{-- Input 7: Maksimal Per Transaksi --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-800">Batas Maksimal Denda per Transaksi</label>
                                    <p class="text-[10px] text-gray-400">Batas maksimal total denda dalam satu transaksi</p>
                                </div>
                                <div class="flex items-center border border-gray-200 rounded-lg px-3 overflow-hidden focus-within:border-blue-500 transition-colors">
                                    <span class="text-gray-500 text-[12px] font-medium pr-2">Rp</span>
                                    <input type="number" name="max_fee_per_transaction" x-model.number="maxFeePerTransaction" class="w-full py-2.5 outline-none text-right text-[12px] text-gray-700 font-medium bg-transparent" required min="0">
                                </div>
                            </div>
                        </div>

                        {{-- Footer / Buttons --}}
                        <div class="bg-gray-50/50 p-5 border-t border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-blue-600 text-[11px] bg-blue-50 px-3 py-1.5 rounded-lg font-medium">
                                <i class="fas fa-info-circle"></i> Perubahan aturan denda akan langsung berlaku untuk transaksi baru.
                            </div>
                            <div class="flex gap-3">
                                <button type="reset" class="px-5 py-2 text-[12px] font-medium border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">Batalkan</button>
                                <button type="submit" class="px-5 py-2 text-[12px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 shadow-sm">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KOLOM KANAN: PREVIEW LIVE & INFO --}}
            <div class="space-y-4">
                
                {{-- Card Preview (Dinamis Berbasis Input) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-[13px] font-medium text-gray-800 flex items-center gap-2 mb-1">
                        <i class="fas fa-calculator text-blue-500"></i> Preview Perhitungan Denda
                    </h3>
                    <p class="text-[10px] text-gray-500 mb-4">Ubah kontrol simulasi di bawah ini untuk melihat contoh kalkulasi.</p>
                    
                    {{-- Kontrol Simulasi --}}
                    <div class="grid grid-cols-2 gap-3 mb-4 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <div class="font-medium">
                            <label class="block text-[10px] text-gray-500 mb-1">Hari Terlambat</label>
                            <input type="number" min="0" x-model.number="previewDays" class="w-full p-1.5 border border-gray-200 rounded-md text-[11px] text-center text-gray-700 outline-none focus:border-blue-500">
                        </div>
                        <div class="font-medium">
                            <label class="block text-[10px] text-gray-500 mb-1">Jumlah Buku</label>
                            <input type="number" min="1" x-model.number="previewBooks" class="w-full p-1.5 border border-gray-200 rounded-md text-[11px] text-center text-gray-700 outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div class="space-y-2 text-[11px]">
                        <div class="flex justify-between items-center text-gray-600">
                            <span>Denda per hari per buku</span>
                            <span class="font-medium text-gray-800" x-text="formatRupiah(lateFeePerDay)"></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600">
                            <span>Toleransi pengurangan hari</span>
                            <span class="font-medium text-emerald-600" x-text="'- ' + toleranceDays + ' hari'"></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600">
                            <span>Hari kena denda efektif</span>
                            <span class="font-medium text-gray-800" x-text="Math.max(0, previewDays - toleranceDays) + ' hari'"></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600 border-b border-gray-100 pb-2">
                            <span>Buku terhitung</span>
                            <span class="font-medium text-gray-800" x-text="previewBooks + ' buku'"></span>
                        </div>
                        
                        <div class="flex justify-between items-center pt-2">
                            <span class="font-medium text-gray-800 text-[12px]">Total Denda Simulasi</span>
                            <span class="font-medium text-red-600 text-[16px]" x-text="formatRupiah(calculatePreview())"></span>
                        </div>
                        <p class="text-right text-[9px] text-gray-400 mt-1" 
                           x-show="maxFeePerBook > 0 && (Math.max(0, previewDays - toleranceDays) * lateFeePerDay) > maxFeePerBook">
                            (Dibatasi aturan denda maksimal per buku)
                        </p>
                        <p class="text-right text-[9px] text-amber-500 mt-1 font-medium" 
                           x-show="maxFeePerTransaction > 0 && calculatePreview() === maxFeePerTransaction">
                            (Mencapai batas maksimal transaksi denda!)
                        </p>
                    </div>
                </div>

                {{-- Card Keterangan --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 font-medium">
                    <h3 class="text-[13px] text-gray-800 flex items-center gap-2 mb-4">
                        <i class="fas fa-info-circle text-blue-500"></i> Keterangan Aturan
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-green-50 text-green-500 flex justify-center items-center shrink-0 mt-0.5"><i class="fas fa-calendar-check text-[10px]"></i></div>
                            <div class="font-medium">
                                <h4 class="text-[11px] text-gray-800">Toleransi keterlambatan</h4>
                                <p class="text-[10px] text-gray-500 mt-0.5">Hari toleransi tidak akan dihitung sebagai keterlambatan.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-orange-50 text-orange-500 flex justify-center items-center shrink-0 mt-0.5"><i class="fas fa-exclamation text-[10px]"></i></div>
                            <div class="font-medium">
                                <h4 class="text-[11px] text-gray-800">Maksimal denda per buku</h4>
                                <p class="text-[10px] text-gray-500 mt-0.5">Jika denda melebihi batas maksimal, maka akan dibatasi.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-red-50 text-red-500 flex justify-center items-center shrink-0 mt-0.5"><i class="fas fa-ban text-[10px]"></i></div>
                            <div class="font-medium">
                                <h4 class="text-[11px] text-gray-800">Denda buku hilang</h4>
                                <p class="text-[10px] text-gray-500 mt-0.5">Jika memilih sesuai harga buku, maka denda akan mengikuti harga buku saat ini.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-500 flex justify-center items-center shrink-0 mt-0.5"><i class="fas fa-coins text-[10px]"></i></div>
                            <div class="font-medium">
                                <h4 class="text-[11px] text-gray-800">Pembulatan denda</h4>
                                <p class="text-[10px] text-gray-500 mt-0.5">Denda akan dibulatkan sesuai aturan yang dipilih.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- TABEL RIWAYAT PERUBAHAN --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
            <div class="p-5 border-b border-gray-50 flex justify-between items-center">
                <div class="font-medium">
                    <h3 class="text-[14px] text-gray-800 flex items-center gap-2">
                        <i class="fas fa-history text-gray-500"></i> Riwayat Perubahan Aturan Denda
                    </h3>
                    <p class="text-[10px] text-gray-500 mt-1">Histori perubahan aturan denda yang pernah dilakukan.</p>
                </div>
                <button @click="showHistoryModal = true" class="text-blue-600 text-[11px] font-medium hover:underline flex items-center gap-1">
                    <i class="fas fa-eye"></i> Lihat Semua Riwayat
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[11px]">
                    <thead class="bg-gray-50/50 text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 font-medium w-12 text-center">No.</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                            <th class="px-5 py-3 font-medium text-right">Denda per Hari</th>
                            <th class="px-5 py-3 font-medium text-right">Maksimal per Buku</th>
                            <th class="px-5 py-3 font-medium text-center">Toleransi</th>
                            <th class="px-5 py-3 font-medium text-right">Denda Rusak</th>
                            <th class="px-5 py-3 font-medium">Denda Hilang</th>
                            <th class="px-5 py-3 font-medium">Dibuat Oleh</th>
                            <th class="px-5 py-3 font-medium text-center">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-600">
                        @forelse(collect($histories)->take(5) as $index => $history)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-5 py-3 text-center text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-5 py-3">{{ $history->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format($history->late_fee_per_day, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format($history->max_fee_per_book, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-center">{{ $history->tolerance_days }} hari</td>
                            <td class="px-5 py-3 text-right">{{ $history->damageCriteriaLabel() }} harga buku</td>
                            <td class="px-5 py-3">Sesuai Harga Buku (100%)</td>
                            <td class="px-5 py-3">{{ $history->creator->name ?? 'Sistem' }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($history->is_active)
                                    <span class="px-2 py-0.5 bg-green-50 text-green-600 rounded border border-green-100 text-[10px] font-medium">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded border border-gray-200 text-[10px] font-medium">Diganti</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-gray-400 italic">Belum ada riwayat perubahan aturan denda.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL LIHAT SEMUA RIWAYAT --}}
        <div x-show="showHistoryModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showHistoryModal = false" x-show="showHistoryModal" x-transition.opacity></div>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full"
                     x-show="showHistoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-white font-medium">
                        <h3 class="text-[15px] text-gray-800 flex items-center gap-2"><i class="fas fa-history text-blue-500"></i> Semua Riwayat Perubahan Denda</h3>
                        <button @click="showHistoryModal = false" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="p-0 max-h-[65vh] overflow-y-auto">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-gray-50/90 text-gray-500 border-b border-gray-100 sticky top-0 backdrop-blur-sm">
                                <tr>
                                    <th class="px-5 py-3 font-medium w-12 text-center">No.</th>
                                    <th class="px-5 py-3 font-medium">Tanggal</th>
                                    <th class="px-5 py-3 font-medium text-right">Denda per Hari</th>
                                    <th class="px-5 py-3 font-medium text-right">Maks per Buku</th>
                                    <th class="px-5 py-3 font-medium text-center">Toleransi</th>
                                    <th class="px-5 py-3 font-medium text-right">Denda Rusak</th>
                                    <th class="px-5 py-3 font-medium">Denda Hilang</th>
                                    <th class="px-5 py-3 font-medium">Dibuat Oleh</th>
                                    <th class="px-5 py-3 font-medium text-center">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-gray-600">
                                @forelse($histories as $index => $history)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-5 py-3 text-center text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-5 py-3">{{ $history->created_at->translatedFormat('d M Y H:i') }}</td>
                                    <td class="px-5 py-3 text-right">Rp {{ number_format($history->late_fee_per_day, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 text-right">Rp {{ number_format($history->max_fee_per_book, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 text-center">{{ $history->tolerance_days }} hari</td>
                                    <td class="px-5 py-3 text-right">{{ $history->damageCriteriaLabel() }} harga buku</td>
                                    <td class="px-5 py-3">Sesuai Harga Buku (100%)</td>
                                    <td class="px-5 py-3">{{ $history->creator->name ?? 'Sistem' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($history->is_active)
                                            <span class="px-2 py-0.5 bg-green-50 text-green-600 rounded border border-green-100 text-[10px] font-medium">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded border border-gray-200 text-[10px] font-medium">Diganti</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-8 text-center text-gray-400 italic">Belum ada riwayat perubahan aturan denda.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-5 py-3 bg-gray-50/50 flex justify-end border-t border-gray-100">
                        <button type="button" @click="showHistoryModal = false" class="px-5 py-2 text-[11px] font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>