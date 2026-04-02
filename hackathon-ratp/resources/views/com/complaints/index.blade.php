<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-900">Gestion des plaintes — Service Com</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">

        <div class="flex flex-wrap gap-3 items-center justify-between">
            <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 shadow-sm">
                @foreach (['available' => 'Disponibles', 'mine' => 'Mes dossiers', 'done' => 'Traités'] as $value => $label)
                    <a href="{{ route('complaints.index', array_filter(['tab' => $value, 'type' => $typeId, 'driver_id' => $driverFilter, 'severity' => $severityFilter], fn ($v) => $v !== null && $v !== '')) }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition
                              {{ $tab === $value && !$importantFilter ? 'bg-[#004fa3] text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                        {{ $label }}
                        <span class="text-xs px-1.5 py-0.5 rounded-full font-semibold
                                     {{ $tab === $value && !$importantFilter ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                            {{ $counts[$value] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Bouton plaintes importantes --}}
            <a href="{{ $importantFilter ? route('complaints.index', array_filter(['tab' => $tab], fn ($v) => $v !== null)) : route('complaints.index', ['important' => 1]) }}"
               class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl transition shadow-sm border
                      {{ $importantFilter
                          ? 'bg-red-600 border-red-600 text-white'
                          : 'bg-red-50 border-red-300 text-red-700 hover:bg-red-100' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                Plaintes importantes
                <span class="text-xs px-1.5 py-0.5 rounded-full font-bold
                             {{ $importantFilter ? 'bg-white/25 text-white' : 'bg-red-200 text-red-800' }}">
                    {{ $importantCount }}
                </span>
            </a>
        </div>

        <x-filter-bar
            :action="route('complaints.index')"
            :tab="$tab"
            :sort="$sort"
            :direction="$direction"
            :complaint-types="$complaintTypes"
            :drivers="$drivers"
            :type-id="$typeId"
            :severity-filter="$severityFilter"
            :driver-filter="$driverFilter"
        />

        @php
            $currentFilters = array_filter([
                'tab'       => $tab,
                'type'      => $typeId,
                'driver_id' => $driverFilter,
                'severity'  => $severityFilter,
                'important' => $importantFilter ?: null,
            ], fn ($v) => $v !== null && $v !== '');

            $sortUrl = fn (string $col) => route('complaints.index', array_merge($currentFilters, [
                'sort'      => $col,
                'direction' => $sort === $col && $direction === 'asc' ? 'desc' : 'asc',
            ]));
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-400">
                        <x-sort-th column="type" :sort="$sort" :direction="$direction" :href="$sortUrl('type')">Type</x-sort-th>
                        <x-sort-th column="driver" :sort="$sort" :direction="$direction" :href="$sortUrl('driver')">Chauffeur</x-sort-th>
                        <x-sort-th column="bus" :sort="$sort" :direction="$direction" :href="$sortUrl('bus')">Bus</x-sort-th>
                        <x-sort-th column="incident_time" :sort="$sort" :direction="$direction" :href="$sortUrl('incident_time')">Date</x-sort-th>
                        <x-sort-th column="severity" :sort="$sort" :direction="$direction" :href="$sortUrl('severity')">Gravité</x-sort-th>
                        <th class="px-5 py-3 text-left">Pris en charge par</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($complaints as $complaint)
                        @php
                            $severityColors = [0 => 'bg-green-100 text-green-700', 1 => 'bg-lime-100 text-lime-700', 2 => 'bg-yellow-100 text-yellow-700', 3 => 'bg-orange-100 text-orange-700', 4 => 'bg-red-100 text-red-700'];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4 font-medium text-gray-800">{{ $complaint->complaintType->name }}</td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $complaint->driver ? $complaint->driver->first_name.' '.$complaint->driver->last_name : '—' }}
                            </td>
                            <td class="px-5 py-4 text-gray-500 font-mono text-xs">{{ $complaint->bus->code }}</td>
                            <td class="px-5 py-4 text-gray-500">{{ $complaint->incident_time->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">
                                @if ($complaint->severity)
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $severityColors[$complaint->severity->level] }}">
                                        Niveau {{ $complaint->severity->level }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-500 text-xs">
                                {{ $complaint->comAgent ? $complaint->comAgent->first_name.' '.$complaint->comAgent->last_name : '—' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('complaints.show', $complaint) }}"
                                   class="text-[#004fa3] hover:text-[#003d80] font-medium text-xs">Voir →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">Aucune plainte trouvée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($complaints->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $complaints->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
