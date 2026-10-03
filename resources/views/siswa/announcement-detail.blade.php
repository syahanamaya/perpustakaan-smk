<x-layouts.app title="{{ $announcement->title }} - Perpustakaan SMK Budi Mulia">
    @section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; }
    </style>
    @endsection

    <div class="max-w-4xl mx-auto px-6 py-6 space-y-6 min-h-screen pb-16">
        
        {{-- TOMBOL KEMBALI --}}
        <div>
            <a href="{{ route('siswa.announcements') }}" class="inline-flex items-center gap-2 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i> Kembali ke Pengumuman
            </a>
        </div>

        {{-- AREA ISI DETAIL PENGUMUMAN --}}
        <div class="bg-white p-6 md:p-8 rounded-2xl border border-gray-150 shadow-sm space-y-6">
            
            <div class="space-y-3">
                @if($announcement->type === 'penting')
                    <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[9px] font-medium rounded border border-red-150 uppercase tracking-wider">Penting</span>
                @elseif($announcement->type === 'kegiatan')
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-medium rounded border border-blue-150 uppercase tracking-wider">Kegiatan</span>
                @elseif($announcement->type === 'pengingat')
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[9px] font-medium rounded border border-amber-150 uppercase tracking-wider">Pengingat</span>
                @else
                    <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[9px] font-medium rounded border border-green-150 uppercase tracking-wider">Info</span>
                @endif

                <h1 class="text-xl md:text-2xl font-medium text-gray-800 leading-snug">{{ $announcement->title }}</h1>
                
                <div class="flex items-center gap-4 text-xs text-gray-400 pt-1 border-b border-gray-50 pb-3">
                    <span><i class="far fa-calendar mr-1.5"></i>{{ \Carbon\Carbon::parse($announcement->created_at)->translatedFormat('d F Y') }}</span>
                    <span><i class="far fa-user mr-1.5"></i>{{ $announcement->creator->name ?? 'Admin' }}</span>
                </div>
            </div>

            @if($announcement->image)
                <div class="w-full max-h-96 rounded-xl overflow-hidden border border-gray-100 shadow-inner">
                    <img src="{{ asset('storage/announcements/' . $announcement->image) }}" class="w-full h-full object-cover" alt="{{ $announcement->title }}">
                </div>
            @endif

            <div class="text-sm text-gray-600 leading-relaxed text-justify space-y-4 whitespace-pre-line">
                {!! nl2br(e($announcement->content)) !!}
            </div>

        </div>

        {{-- FOOTER NOTE --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-2.5 text-blue-700 text-[11px] font-medium shadow-sm">
            <i class="fas fa-info-circle text-xs shrink-0"></i>
            <p>Pengumuman ini diterbitkan secara resmi oleh pihak manajemen Perpustakaan SMK Budi Mulia.</p>
        </div>

    </div>
</x-layouts.app>