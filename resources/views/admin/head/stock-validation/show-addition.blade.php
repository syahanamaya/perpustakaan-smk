<x-layouts.app title="Tinjau Penambahan Stok">
    <div x-data="{ rejectOpen: false }" class="space-y-6 text-[11px] max-w-3xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('head.stock-validation.index') }}" class="text-gray-500"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h2 class="text-sm font-medium">Validasi Penambahan Stok</h2>
                <p class="text-[10px] text-gray-400">{{ $stockAddition->book->title ?? '-' }}</p>
            </div>
        </div>
        <div class="bg-white border rounded-xl p-6 space-y-5">
            <div class="flex justify-between border-b pb-4">
                <div>
                    <p class="font-medium text-sm">{{ $stockAddition->book->title ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">{{ $stockAddition->book->book_code ?? '-' }} · {{ $stockAddition->book->category->name ?? '-' }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] bg-amber-100 text-amber-700">Menunggu Validasi</span>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-gray-50 p-3 rounded-lg text-center">
                    <p class="text-[9px] text-gray-400">Stok Saat Ini</p>
                    <p class="text-lg">{{ $stockAddition->book->stock }}</p>
                </div>
                <div class="bg-emerald-50 p-3 rounded-lg text-center">
                    <p class="text-[9px] text-emerald-600">Ditambah</p>
                    <p class="text-lg text-emerald-700">+{{ $stockAddition->jumlah_ditambah }}</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg text-center">
                    <p class="text-[9px] text-blue-600">Stok Sesudah</p>
                    <p class="text-lg text-blue-700">{{ (int) $stockAddition->book->stock + (int) $stockAddition->jumlah_ditambah }}</p>
                </div>
            </div>
            @if($stockAddition->catatan)
                <p class="bg-gray-50 p-3 rounded-lg">{{ $stockAddition->catatan }}</p>
            @endif
            @if($stockAddition->foto_path)
                <img src="{{ asset('storage/'.$stockAddition->foto_path) }}" class="max-h-72 rounded-lg">
            @endif
            <p class="text-[10px] text-gray-500">Petugas: {{ $stockAddition->user->name ?? '-' }} · {{ $stockAddition->submitted_at?->format('d M Y H:i') }}</p>
            <div class="flex gap-3">
                <form action="{{ route('head.stock-validation.approve-addition', $stockAddition) }}" method="POST" onsubmit="return confirm('Setujui penambahan stok ini?')">
                    @csrf @method('PATCH')
                    <button class="bg-green-600 text-white px-5 py-2 rounded-lg">Setujui</button>
                </form>
                <button @click="rejectOpen = true" type="button" class="bg-red-50 text-red-600 px-5 py-2 rounded-lg">Tolak</button>
            </div>
        </div>
        <div x-show="rejectOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div @click.outside="rejectOpen = false" class="bg-white rounded-xl w-full max-w-md p-6">
                <h3 class="text-sm font-medium mb-4">Tolak Penambahan Stok</h3>
                <form action="{{ route('head.stock-validation.reject-addition', $stockAddition) }}" method="POST" class="space-y-4">
                    @csrf @method('PATCH')
                    <textarea name="rejection_reason" rows="4" required class="w-full px-3 py-2 border rounded-lg" placeholder="Alasan penolakan"></textarea>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="rejectOpen = false">Batal</button>
                        <button class="bg-red-600 text-white px-4 py-2 rounded-lg">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
