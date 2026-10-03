<x-layouts.app title="Perbaiki Penambahan Stok">
    <div x-data="{
        stokSebelum: {{ $stockAddition->book->stock }},
        jumlahDitambah: {{ old('jumlah_ditambah', $stockAddition->jumlah_ditambah) }},
        get stokSesudah() { return (parseInt(this.stokSebelum) || 0) + (parseInt(this.jumlahDitambah) || 0); }
    }" class="space-y-6 text-[11px] max-w-3xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.stock-addition.index') }}" class="text-gray-500"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-sm font-medium">Perbaiki Pengajuan: {{ $stockAddition->book->title ?? '-' }}</h2>
        </div>
        @if($stockAddition->rejection_reason)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">{{ $stockAddition->rejection_reason }}</div>
        @endif
        <form action="{{ route('admin.stock-addition.update', $stockAddition) }}" method="POST" enctype="multipart/form-data" class="bg-white border rounded-xl p-6 space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-3 gap-3 bg-gray-50 p-4 rounded-xl text-center">
                <div><p class="text-[9px] text-gray-400">Stok Saat Ini</p><p class="text-lg" x-text="stokSebelum"></p></div>
                <div><p class="text-[9px] text-gray-400">Ditambah</p><p class="text-lg text-emerald-600" x-text="'+' + jumlahDitambah"></p></div>
                <div><p class="text-[9px] text-gray-400">Sesudah</p><p class="text-lg text-blue-600" x-text="stokSesudah"></p></div>
            </div>
            <div>
                <label class="block mb-1">Jumlah Ditambah</label>
                <input type="number" name="jumlah_ditambah" x-model="jumlahDitambah" min="1" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block mb-1">Catatan</label>
                <textarea name="catatan" rows="3" class="w-full px-3 py-2 border rounded-lg">{{ old('catatan', $stockAddition->catatan) }}</textarea>
            </div>
            <div>
                <label class="block mb-1">Ganti Foto (opsional)</label>
                <input type="file" name="foto" class="w-full text-[11px]">
            </div>
            <button class="bg-emerald-600 text-white px-5 py-2 rounded-lg">Kirim Ulang</button>
        </form>
    </div>
</x-layouts.app>
