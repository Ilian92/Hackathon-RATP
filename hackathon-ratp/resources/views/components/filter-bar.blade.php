@props([
    'action',
    'tab',
    'sort',
    'direction',
    'complaintTypes',
    'drivers',
    'typeId' => null,
    'severityFilter' => null,
    'driverFilter' => null,
])

@php
    $hasActiveFilter = $typeId || $severityFilter !== null || $driverFilter;
@endphp

<form method="GET" action="{{ $action }}" class="flex flex-wrap items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-sm">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="hidden" name="direction" value="{{ $direction }}">

    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide pr-1">Filtres</span>

    <select name="type" onchange="this.form.submit()"
            class="text-sm rounded-lg border-gray-200 py-1.5 pl-3 pr-8 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3]
                   {{ $typeId ? 'border-[#004fa3] text-[#004fa3] font-medium' : 'text-gray-600' }}">
        <option value="">Tous les types</option>
        @foreach ($complaintTypes as $type)
            <option value="{{ $type->id }}" @selected($typeId === $type->id)>{{ $type->name }}</option>
        @endforeach
    </select>

    @if ($drivers->isNotEmpty())
        <select name="driver_id" onchange="this.form.submit()"
                class="text-sm rounded-lg border-gray-200 py-1.5 pl-3 pr-8 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3]
                       {{ $driverFilter ? 'border-[#004fa3] text-[#004fa3] font-medium' : 'text-gray-600' }}">
            <option value="">Tous les chauffeurs</option>
            @foreach ($drivers as $driver)
                <option value="{{ $driver->id }}" @selected($driverFilter === $driver->id)>
                    {{ $driver->last_name }} {{ $driver->first_name }}
                </option>
            @endforeach
        </select>
    @endif

    <select name="severity" onchange="this.form.submit()"
            class="text-sm rounded-lg border-gray-200 py-1.5 pl-3 pr-8 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3]
                   {{ $severityFilter !== null ? 'border-[#004fa3] text-[#004fa3] font-medium' : 'text-gray-600' }}">
        <option value="">Tous les niveaux</option>
        @foreach ([0 => 'Niveau 0 — Classé sans suite', 1 => 'Niveau 1 — Mineur', 2 => 'Niveau 2 — Modéré', 3 => 'Niveau 3 — Grave', 4 => 'Niveau 4 — Très grave'] as $level => $label)
            <option value="{{ $level }}" @selected($severityFilter === $level)>{{ $label }}</option>
        @endforeach
    </select>

    @if ($hasActiveFilter)
        <a href="{{ $action }}?tab={{ $tab }}"
           class="inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-700 font-medium ml-1 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Réinitialiser
        </a>
    @endif
</form>
