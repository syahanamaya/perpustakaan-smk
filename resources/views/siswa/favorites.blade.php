<x-layouts.app title="Favorit Saya - Perpustakaan SMK Budi Mulia">
    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        [x-cloak] { display: none !important; }
    </style>
    @endsection

    {{-- Container diubah ke max-w-3xl agar di tengah dan menyerupai mobile app --}}
    <div class="max-w-3xl mx-auto px-4 py-8 space-y-8 min-h-screen pb-16">

        {{-- STATS CARDS --}}
        <div class="grid grid-cols-3 gap-4">
            {{-- Card Total --}}
            <div class="bg-white rounded-2xl py-3 px-2 shadow-sm flex flex-col items-center justify-center">
                <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-400 flex items-center justify-center mb-1.5">
                    <i class="fas fa-star text-xs"></i>
                </div>
                <p class="text-lg font-medium text-gray-800">{{ $stats['total'] ?? 0 }}</p>
                <p class="text-[10px] text-gray-400">Total</p>
            </div>
            
            {{-- Card Tersedia --}}
            <div class="bg-white rounded-2xl py-3 px-2 shadow-sm flex flex-col items-center justify-center">
                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mb-1.5">
                    <i class="fas fa-check text-xs"></i>
                </div>
                <p class="text-lg font-medium text-gray-800">{{ $stats['tersedia'] ?? 0 }}</p>
                <p class="text-[10px] text-gray-400">Tersedia</p>
            </div>
            
            {{-- Card Habis --}}
            <div class="bg-white rounded-2xl py-3 px-2 shadow-sm flex flex-col items-center justify-center">
                <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-1.5">
                    <i class="fas fa-times text-xs"></i>
                </div>
                <p class="text-lg font-medium text-gray-800">{{ $stats['habis'] ?? 0 }}</p>
                <p class="text-[10px] text-gray-400">Habis</p>
            </div>
        </div>

        {{-- DAFTAR BUKU LIST VIEW --}}
        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-4">Daftar Buku</h3>

            @if($favorites->count() > 0)
                <div class="space-y-4">
                    @foreach($favorites as $favorite)
                        <div class="bg-white rounded-2xl p-4 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
                            
                            {{-- Cover Buku --}}
                            <div class="w-16 h-24 rounded-lg overflow-hidden shrink-0 bg-gray-100 shadow-inner">
                                <img src="{{ $favorite->book->cover_image ? asset('covers/' . $favorite->book->cover_image) : asset('default-cover.png') }}" 
                                     class="w-full h-full object-cover" alt="{{ $favorite->book->title }}">
                            </div>

                            {{-- Info Buku --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-800 truncate" title="{{ $favorite->book->title }}">{{ $favorite->book->title }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $favorite->book->author }}</p>
                                
                                {{-- Status Badge (Contoh warna hijau untuk Tersedia) --}}
                                <div class="mt-2.5">
                                    <span class="text-[10px] font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                                        Tersedia
                                    </span>
                                </div>
                            </div>

                            {{-- Aksi (Panah & Hapus) --}}
                            <div class="flex items-center gap-3 shrink-0 ml-2">
                                <i class="fas fa-chevron-right text-gray-300 text-sm"></i>
                                <button onclick="toggleFavorite({{ $favorite->book->id }}, this)" class="w-8 h-8 flex items-center justify-center rounded-full text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8 flex justify-center">
                    {{ $favorites->withQueryString()->links() }}
                </div>

            @else
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl shadow-sm text-center px-4">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-book-open text-2xl text-gray-300"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-800 mb-1">Belum ada buku favorit</h3>
                    <p class="text-xs text-gray-400 max-w-xs mx-auto">Buku yang kamu tandai akan muncul di sini.</p>
                </div>
            @endif
        </div>

        {{-- Alert Info Footer (Sesuai App) --}}
        <div class="bg-[#f0f7ff] border border-blue-100 rounded-xl p-4 flex items-start gap-3 shadow-sm">
            <i class="fas fa-info-circle text-blue-600 text-lg shrink-0 mt-0.5"></i>
            <p class="text-blue-800 text-xs leading-relaxed font-medium">Buku favorit Anda disimpan di sini untuk mempermudah pencarian nanti.</p>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleFavorite(bookId, btnElement) {
            btnElement.disabled = true;
            // ... (Biarkan script fetch API kamu sama seperti sebelumnya) ...
            fetch("{{ route('student.favorite.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ book_id: bookId })
            })
            .then(response => response.json())
            .then(data => { window.location.reload(); })
            .catch(error => { console.error("Error:", error); btnElement.disabled = false; });
        }
    </script>
    @endpush
</x-layouts.app>