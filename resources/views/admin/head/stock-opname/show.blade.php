<x-layouts.app title="Tinjau Pengajuan Opname">
    <div x-data="{ rejectOpen: false }" class="space-y-6 text-[11px] max-w-3xl">

        <div class="flex items-center gap-3">
            <a href="{{ route('head.stock-opname.index') }}" class="text-gray-500 hover:text-gray-700"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h2 class="text-sm font-medium text-gray-800">Validasi Stok Opname</h2>
                <p class="text-[10px] text-gray-400">{{ $stockOpname->book->title ?? '-' }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <p class="font-medium text-gray-800 text-sm">{{ $stockOpname->book->title ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">
                        {{ $stockOpname->book->book_code ?? '-' }} ·
                        {{ $stockOpname->book->category->name ?? '-' }} ·
                        Rak: {{ $stockOpname->book->rak->nama_rak ?? '-' }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-medium bg-amber-100 text-amber-700">Menunggu Validasi</span>
            </div>

            @include('stock-opname.partials.condition-detail')

            @if($stockOpname->catatan)
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-medium mb-1">Catatan Petugas</p>
                    <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $stockOpname->catatan }}</p>
                </div>
            @endif

            @if($stockOpname->foto_path)
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-medium mb-2">Foto Bukti</p>
                    <img src="{{ asset('storage/' . $stockOpname->foto_path) }}" alt="Bukti opname" class="max-h-72 rounded-lg border border-gray-200">
                </div>
            @else
                <p class="text-[10px] text-gray-400 italic">Tidak ada foto bukti dilampirkan.</p>
            @endif

            <div class="text-[10px] text-gray-500 border-t border-gray-100 pt-4">
                <p>Petugas: <span class="text-gray-800 font-medium">{{ $stockOpname->user->name ?? '-' }}</span></p>
                <p>Diajukan: {{ $stockOpname->submitted_at?->format('d M Y H:i') ?? '-' }}</p>
                @if($stockOpname->revision_count > 0)
                    <p>Pengiriman ulang ke-{{ $stockOpname->revision_count }} · {{ $stockOpname->resubmitted_at?->format('d M Y H:i') }}</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <form action="{{ route('head.stock-opname.approve', $stockOpname) }}" method="POST"
                    onsubmit="return confirm('Setujui pengajuan ini? Stok buku akan diperbarui sesuai stok fisik.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium">
                        <i class="fas fa-check mr-1"></i> Setujui
                    </button>
                </form>

                <button @click="rejectOpen = true" type="button"
                    class="bg-red-50 text-red-600 hover:bg-red-100 px-5 py-2 rounded-lg font-medium">
                    <i class="fas fa-times mr-1"></i> Tolak
                </button>
            </div>
        </div>

        <div x-show="rejectOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div @click.outside="rejectOpen = false" class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-sm font-medium text-gray-800 mb-4">Tolak Pengajuan Opname</h3>
                <form action="{{ route('head.stock-opname.reject', $stockOpname) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-gray-500 font-medium mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="rejection_reason" rows="4" required placeholder="Jelaskan alasan penolakan..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[11px]"></textarea>
                    </div>
                    <div class="flex items-center gap-3 justify-end">
                        <button type="button" @click="rejectOpen = false" class="text-gray-500 px-4 py-2">Batal</button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">Tolak Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
