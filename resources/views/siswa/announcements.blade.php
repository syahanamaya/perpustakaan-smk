<x-layouts.app title="Pengumuman Perpustakaan - SMK Budi Mulia">
    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
    </style>
    @endsection

    <div class="max-w-7xl mx-auto px-6 py-6 space-y-6 min-h-screen pb-16">
        
        {{-- JUMBOTRON HEADER PENGUMUMAN --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div>
                <h2 class="text-xl font-medium text-blue-800">Pengumuman Perpustakaan</h2>
                <p class="text-[11px] text-gray-400 mt-0.5">Informasi terbaru dari perpustakaan untuk seluruh siswa SMK Budi Mulia.</p>
            </div>
        </div>

        {{-- LAYOUT UTAMA: UTAMA (KIRI) & PANEL FILTER/TERBARU (KANAN) --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            {{-- KONTEN UTAMA: DAFTAR SEMUA PENGUMUMAN (LEBAR 3 KOLOM) --}}
            <div class="lg:col-span-3 space-y-4">
                <h3 class="text-xs font-medium text-blue-900 uppercase tracking-wide px-1">Semua Pengumuman</h3>

                @forelse($announcements as $announ)
                    <div class="bg-white p-4 rounded-2xl border border-gray-150 shadow-sm flex flex-col sm:flex-row gap-5 items-start hover:shadow-md transition-all">
                        <div class="w-full sm:w-44 h-28 bg-gray-100 rounded-xl overflow-hidden shrink-0 border border-gray-100 shadow-inner relative">
                            @if($announ->image)
                                <img src="{{ asset('storage/announcements/' . $announ->image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 bg-linear-to-br from-blue-500 to-indigo-600">
                                    <i class="fas fa-bullhorn text-2xl text-white/40"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col justify-between h-auto sm:h-28 w-full">
                            <div>
                                {{-- Rendering Badge Warna Dinamis Sesuai Jenis Karakter Tipe --}}
                                @if($announ->type === 'penting')
                                    <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[8px] font-medium rounded border border-red-150 uppercase tracking-wider">Penting</span>
                                @elseif($announ->type === 'kegiatan')
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[8px] font-medium rounded border border-blue-150 uppercase tracking-wider">Kegiatan</span>
                                @elseif($announ->type === 'pengingat')
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[8px] font-medium rounded border border-amber-150 uppercase tracking-wider">Pengingatan</span>
                                @else
                                    <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[8px] font-medium rounded border border-green-150 uppercase tracking-wider">Info</span>
                                @endif

                                <h4 class="font-medium text-gray-800 text-[13px] leading-snug mt-1.5">{{ $announ->title }}</h4>
                                <p class="text-[11px] text-gray-400 mt-1 line-clamp-2 leading-relaxed text-justify">{{ strip_tags($announ->content) }}</p>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mt-2 pt-2 border-t border-gray-50 text-[10px] text-gray-400">
                                <div class="flex items-center gap-4">
                                    <span><i class="far fa-calendar mr-1"></i> {{ \Carbon\Carbon::parse($announ->created_at)->translatedFormat('d M Y') }}</span>
                                    <span><i class="far fa-user mr-1"></i> {{ $announ->creator->name ?? 'Admin' }}</span>
                                </div>
                                
                                <a href="{{ route('siswa.announcements.show', $announ->slug) }}" 
                                   class="inline-flex items-center gap-1 font-medium text-[10px] px-3 py-1 rounded-lg transition-all border
                                   {{ $announ->type === 'penting' ? 'border-red-200 text-red-600 hover:bg-red-50' : 
                                      ($announ->type === 'kegiatan' ? 'border-blue-200 text-blue-600 hover:bg-blue-50' : 
                                      ($announ->type === 'pengingat' ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-green-200 text-green-600 hover:bg-green-50')) }}">
                                    Baca Selengkapnya <i class="fas fa-arrow-right text-[8px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl p-12 text-center border border-gray-150 text-gray-400 text-xs">
                        <i class="fas fa-bullhorn text-3xl text-gray-200 mb-2 block"></i>
                        Belum ada informasi pengumuman yang diterbitkan.
                    </div>
                @endforelse

                {{-- Pagination Kontrol Tengah --}}
                <div class="pt-4 flex justify-center">
                    {{ $announcements->withQueryString()->links() }}
                </div>

                {{-- Catatan Pengingat Footer Kiri --}}
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-2.5 text-blue-700 text-[11px] font-medium shadow-sm">
                    <i class="fas fa-info-circle text-xs shrink-0"></i>
                    <p><span class="font-medium">Catatan:</span> Pastikan Anda selalu membaca informasi terbaru dari perpustakaan agar tidak ketinggalan informasi penting.</p>
                </div>
            </div>

            {{-- PANEL MENU KANAN: BILAH CARI & FILTER SIDEBAR (LEBAR 1 KOLOM) --}}
            <div class="lg:col-span-1 space-y-4">
                
                <form action="{{ route('siswa.announcements') }}" method="GET" class="relative w-full">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="w-full pl-4 pr-12 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-[11px] text-gray-700 placeholder-gray-400 shadow-sm transition-all" 
                           placeholder="Cari pengumuman...">
                    <button type="submit" class="absolute right-1 top-1 bottom-1 bg-blue-600 hover:bg-blue-700 text-white px-3.5 rounded-lg flex items-center justify-center transition-all">
                        <i class="fas fa-search text-xs"></i>
                    </button>
                </form>

                <div class="bg-white p-4 rounded-2xl border border-gray-150 shadow-sm space-y-1">
                    <h4 class="text-[10px] font-medium text-gray-400 uppercase tracking-wider px-1 mb-2">Kategori Pengumuman</h4>
                    
                    <a href="{{ route('siswa.announcements', ['search' => request('search')]) }}" 
                       class="flex justify-between items-center px-2.5 py-2 text-xs rounded-xl transition-all
                       {{ !request('type') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Semua Kategori</span>
                        <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $counts['all'] }}</span>
                    </a>

                    <a href="{{ route('siswa.announcements', ['type' => 'penting', 'search' => request('search')]) }}" 
                       class="flex justify-between items-center px-2.5 py-2 text-xs rounded-xl transition-all
                       {{ request('type') === 'penting' ? 'bg-red-50 text-red-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Penting</span>
                        <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $counts['penting'] }}</span>
                    </a>

                    <a href="{{ route('siswa.announcements', ['type' => 'info', 'search' => request('search')]) }}" 
                       class="flex justify-between items-center px-2.5 py-2 text-xs rounded-xl transition-all
                       {{ request('type') === 'info' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Info</span>
                        <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $counts['info'] }}</span>
                    </a>

                    <a href="{{ route('siswa.announcements', ['type' => 'kegiatan', 'search' => request('search')]) }}" 
                       class="flex justify-between items-center px-2.5 py-2 text-xs rounded-xl transition-all
                       {{ request('type') === 'kegiatan' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Kegiatan</span>
                        <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $counts['kegiatan'] }}</span>
                    </a>

                    <a href="{{ route('siswa.announcements', ['type' => 'pengingat', 'search' => request('search')]) }}" 
                       class="flex justify-between items-center px-2.5 py-2 text-xs rounded-xl transition-all
                       {{ request('type') === 'pengingat' ? 'bg-amber-50 text-amber-700 font-medium' : 'text-gray-600 hover:bg-gray-50 font-medium' }}">
                        <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pengingat</span>
                        <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $counts['pengingat'] }}</span>
                    </a>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-gray-150 shadow-sm space-y-3">
                    <h4 class="text-[10px] font-medium text-gray-400 uppercase tracking-wider px-1">Pengumuman Terbaru</h4>
                    
                    <div class="space-y-3">
                        @foreach($latestAnnouncements as $latest)
                            <a href="{{ route('siswa.announcements.show', $latest->slug) }}" class="flex items-center gap-3 group border-b border-gray-50 pb-2.5 last:border-none last:pb-0">
                                <div class="w-11 h-9 bg-gray-100 rounded-lg overflow-hidden shrink-0 border border-gray-150">
                                    @if($latest->image)
                                        <img src="{{ asset('storage/announcements/' . $latest->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-blue-600 flex items-center justify-center text-white text-[10px] font-medium">NEW</div>
                                    @endif
                                </div>
                                <div class="truncate">
                                    <h5 class="text-[11px] font-medium text-gray-700 group-hover:text-blue-600 truncate transition-colors">{{ $latest->title }}</h5>
                                    <span class="text-[9px] text-gray-400 block mt-0.5">{{ \Carbon\Carbon::parse($latest->created_at)->format('d M Y') }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <a href="{{ route('siswa.announcements') }}" class="w-full py-1.5 mt-2 border border-gray-200 text-gray-600 hover:text-blue-600 text-[10px] font-medium rounded-xl block text-center transition-all shadow-sm">
                        Lihat Semua Pengumuman →
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>