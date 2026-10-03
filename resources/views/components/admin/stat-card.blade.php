@props(['title', 'value', 'sub', 'icon', 'color'])

@php
    $colors = [
        'blue' => 'bg-blue-500 shadow-blue-100 text-blue-500',
        'emerald' => 'bg-emerald-500 shadow-emerald-100 text-emerald-500',
        'indigo' => 'bg-indigo-500 shadow-indigo-100 text-indigo-500',
        'orange' => 'bg-orange-500 shadow-orange-100 text-orange-500',
        'rose' => 'bg-rose-500 shadow-rose-100 text-rose-500',
    ];
    $currentColor = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-all group">
    <div class="w-12 h-12 flex items-center justify-center rounded-xl text-white {{ explode(' ', $currentColor)[0] }} {{ explode(' ', $currentColor)[1] }} shrink-0">
        <i class="fas {{ $icon }} text-lg"></i>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">{{ $title }}</p>
        <h3 class="text-lg font-bold text-gray-800 leading-none mb-1">{{ $value }}</h3>
        <p class="text-[9px] text-gray-400 font-medium italic">{{ $sub }}</p>
    </div>
</div>