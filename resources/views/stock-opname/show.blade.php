<x-layouts.app title="Detail Stok Opname">
    <div class="space-y-6 text-[11px] max-w-3xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.stock-opname.index') }}" class="text-gray-500 hover:text-gray-700"><i class="fas fa-arrow-left"></i></a>
                <div>
                    <h2 class="text-sm font-medium text-gray-800">Detail Pengajuan Stok Opname</h2>
                    <p class="text-[10px] text-gray-400">#{{ $stockOpname->id }}</p>
                </div>
            </div>
            @if($stockOpname->canEdit())
                <a href="{{ route('admin.stock-opname.edit', $stockOpname) }}"
                    class="bg-amber-50 text-amber-700 hover:bg-amber-100 px-4 py-2 rounded-lg font-medium">
                    <i class="fas fa-edit mr-1"></i> Perbaiki
                </a>
            @endif
        </div>

        <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <p class="font-medium text-gray-800 text-sm">{{ $stockOpname->book->title ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">{{ $stockOpname->book->book_code ?? '-' }} · {{ $stockOpname->book->category->name ?? '-' }}</p>
                </div>
                @php
                    $badge = match($stockOpname->status) {
                        'menunggu_validasi' => 'bg-amber-100 text-amber-700',
                        'ditolak' => 'bg-red-100 text-red-700',
                        'disetujui' => 'bg-green-100 text-green-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <span class="px-3 py-1 rounded-full text-[10px] font-medium {{ $badge }}">{{ $stockOpname->statusLabel() }}</span>
            </div>

            @include('stock-opname.partials.condition-detail')

            @if($stockOpname->rejection_reason)
                <div class="bg-red-50 border border-red-100 p-4 rounded-xl">
                    <p class="text-[10px] text-red-600 uppercase font-medium">Alasan Penolakan</p>
                    <p class="text-gray-700 mt-1">{{ $stockOpname->rejection_reason }}</p>
                </div>
            @endif

            @if($stockOpname->catatan)
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-medium mb-1">Catatan</p>
                    <p class="text-gray-700">{{ $stockOpname->catatan }}</p>
                </div>
            @endif

            @if($stockOpname->foto_path)
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-medium mb-2">Foto Bukti</p>
                    <img src="{{ asset('storage/' . $stockOpname->foto_path) }}" alt="Bukti opname" class="max-h-64 rounded-lg border border-gray-200">
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100 text-[10px] text-gray-500">
                <div>
                    <p>Petugas: <span class="text-gray-800 font-medium">{{ $stockOpname->user->name ?? '-' }}</span></p>
                    <p>Diajukan: {{ $stockOpname->submitted_at?->format('d M Y H:i') ?? '-' }}</p>
                    @if($stockOpname->resubmitted_at)
                        <p>Dikirim ulang: {{ $stockOpname->resubmitted_at->format('d M Y H:i') }} ({{ $stockOpname->revision_count }}x)</p>
                    @endif
                </div>
                @if($stockOpname->validated_at)
                    <div>
                        <p>Validator: <span class="text-gray-800 font-medium">{{ $stockOpname->validator->name ?? '-' }}</span></p>
                        <p>Divalidasi: {{ $stockOpname->validated_at->format('d M Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
