@props(['column', 'sort', 'direction', 'href'])

<th class="px-5 py-3 text-left">
    <a href="{{ $href }}" class="inline-flex items-center gap-1 group hover:text-gray-600 transition">
        {{ $slot }}
        @if ($sort === $column)
            <svg class="w-3 h-3 text-[#004fa3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if ($direction === 'asc')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                @endif
            </svg>
        @else
            <svg class="w-3 h-3 text-gray-300 group-hover:text-gray-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
        @endif
    </a>
</th>
