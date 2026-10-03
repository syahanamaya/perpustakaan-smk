@props(['icon', 'label', 'color'])

@php
    $colors = [
        'blue' => 'bg-blue-50 text-blue-600 hover:bg-blue-600',
        'emerald' => 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600',
        'indigo' => 'bg-indigo-50 text-indigo-600 hover:bg-indigo-600',
        'orange' => 'bg-orange-50 text-orange-600 hover:bg-orange-600',
        'teal' => 'bg-teal-50 text-teal-600 hover:bg-teal-600',
        'rose' => 'bg-rose-50 text-rose-600 hover:bg-rose-600',
    ];
    $currentColor = $colors[$color] ?? $colors['blue'];
@endphp

<a href="admin.transaction.index" class="flex flex-col items-center justify-center p-4 rounded-2xl border border-transparent {{ $currentColor }} hover:text-white transition-all group shadow-sm hover:shadow-md active:scale-95">
    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 bg-white/50 group-hover:bg-white/20 transition-colors">
        <i class="fas {{ $icon }} text-sm"></i>
    </div>
    <span class="text-[10px] font-bold uppercase tracking-wide">{{ $label }}</span>
</a>