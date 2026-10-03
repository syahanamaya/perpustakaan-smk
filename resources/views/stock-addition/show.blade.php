<x-layouts.app title="Detail Penambahan Stok">
    <div class="space-y-6 text-[11px] max-w-3xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.stock-addition.index') }}" class="text-gray-500"><i class="fas fa-arrow-left"></i></a>
                <h2 class="text-sm font-medium">Detail Penambahan Stok</h2>
            </div>
            @if($stockAddition->canEdit())
                <a href="{{ route('admin.stock-addition.edit', $stockAddition) }}" class="bg-amber-50 text-amber-700 px-4 py-2 rounded-lg">Perbaiki</a>
            @endif
        </div>
        <div class="bg-white border rounded-xl p-6 space-y-4">
            <div class="flex justify-between border-b pb-3">
                <div>
                    <p class="font-medium text-sm">{{ $stockAddition->book->title ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">{{ $stockAddition->book->book_code ?? '-' }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] {{ $stockAddition->status === 'disetujui' ? 'bg-green-100 text-green-700' : ($stockAddition->status === 'ditolak' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $stockAddition->statusLabel() }}</span>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-gray-50 p-3 rounded-lg text-center"><p class="text-[9px] text-gray-400">Stok Sebelum</p><p class="text-lg">{{ $stockAddition->stok_sebelum }}</p></div>
                <div class="bg-emerald-50 p-3 rounded-lg text-center"><p class="text-[9px] text-emerald-600">Ditambah</p><p class="text-lg text-emerald-700">+{{ $stockAddition->jumlah_ditambah }}</p></div>
                <div class="bg-blue-50 p-3 rounded-lg text-center"><p class="text-[9px] text-blue-600">Stok Sesudah</p><p class="text-lg text-blue-700">{{ $stockAddition->stok_sesudah }}</p></div>
            </div>
            @if($stockAddition->rejection_reason)
                <div class="bg-red-50 p-3 rounded-lg">Alasan tolak: {{ $stockAddition->rejection_reason }}</div>
            @endif
            @if($stockAddition->catatan)<p>Catatan: {{ $stockAddition->catatan }}</p>@endif
            @if($stockAddition->foto_path)
                <img src="{{ asset('storage/'.$stockAddition->foto_path) }}" class="max-h-48 rounded-lg">
            @endif
            <p class="text-[10px] text-gray-500">Petugas: {{ $stockAddition->user->name ?? '-' }} · {{ $stockAddition->submitted_at?->format('d M Y H:i') }}</p>
        </div>
    </div>
</x-layouts.app>
