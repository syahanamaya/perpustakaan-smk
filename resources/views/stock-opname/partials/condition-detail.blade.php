            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Stok Baik Sistem</p>
                    <p class="text-lg font-medium">{{ $stockOpname->stok_sistem }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Dipinjam</p>
                    <p class="text-lg font-medium text-blue-600">{{ $stockOpname->stok_dipinjam }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Stok Master</p>
                    <p class="text-lg font-medium">{{ $stockOpname->stok_master }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-[9px] text-gray-400 uppercase">Stok Baik Fisik</p>
                    <p class="text-lg font-medium text-green-600">{{ $stockOpname->stok_fisik }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl">
                    <p class="text-[10px] text-gray-500 uppercase font-medium mb-2">Kondisi Tercatat Sistem</p>
                    <p class="text-[11px] text-gray-700">Hilang: <strong>{{ $stockOpname->jumlah_hilang_sistem ?? 0 }}</strong></p>
                    <p class="text-[11px] text-gray-700">Rusak Ringan: <strong>{{ $stockOpname->jumlah_rusak_ringan_sistem ?? 0 }}</strong></p>
                    <p class="text-[11px] text-gray-700">Rusak Sedang: <strong>{{ $stockOpname->jumlah_rusak_sedang_sistem ?? 0 }}</strong></p>
                    <p class="text-[11px] text-gray-700">Rusak Berat: <strong>{{ $stockOpname->jumlah_rusak_berat_sistem ?? 0 }}</strong></p>
                </div>
                <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl">
                    <p class="text-[10px] text-amber-700 uppercase font-medium mb-2">Temuan Saat Opname</p>
                    <p class="text-[11px] text-gray-700">Hilang: <strong class="text-red-600">{{ $stockOpname->jumlah_hilang_ditemukan ?? 0 }}</strong></p>
                    <p class="text-[11px] text-gray-700">Rusak Ringan: <strong class="text-orange-600">{{ $stockOpname->jumlah_rusak_ringan ?? 0 }}</strong></p>
                    <p class="text-[11px] text-gray-700">Rusak Sedang: <strong class="text-orange-600">{{ $stockOpname->jumlah_rusak_sedang ?? 0 }}</strong></p>
                    <p class="text-[11px] text-gray-700">Rusak Berat: <strong class="text-orange-600">{{ $stockOpname->jumlah_rusak_berat ?? 0 }}</strong></p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl">
                <p class="text-[10px] text-blue-600 uppercase font-medium">Selisih</p>
                <p class="text-lg font-medium {{ $stockOpname->selisih === 0 ? 'text-green-600' : 'text-amber-600' }}">{{ $stockOpname->selisihLabel() }}</p>
                <p class="text-[10px] text-gray-500 mt-1">{{ $stockOpname->kondisiRingkasan() }}</p>
            </div>
