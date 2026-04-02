<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-900">Dossiers de mes chauffeurs</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">

        <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 w-fit shadow-sm">
            @foreach (['pending' => 'En attente', 'rh' => 'Transmis au RH', 'closed' => 'Clôturés'] as $value => $label)
                <a href="{{ route('complaints.index', array_filter(['tab' => $value, 'type' => $typeId, 'driver_id' => $driverFilter, 'severity' => $severityFilter], fn ($v) => $v !== null && $v !== '')) }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition
                          {{ $tab === $value ? 'bg-[#004fa3] text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                    {{ $label }}
                    <span class="text-xs px-1.5 py-0.5 rounded-full font-semibold
                                 {{ $tab === $value ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                        {{ $counts[$value] }}
                    </span>
                </a>
            @endforeach
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

        {{-- Filtre nature --}}
        <div class="flex gap-2">
            @foreach ([null => 'Tous', 'positive' => 'Positifs', 'negative' => 'Négatifs'] as $value => $label)
                @php
                    $isActive = ($nature === $value) || ($value === null && $nature === null);
                    $href = route('complaints.index', array_filter([
                        'tab'       => $tab,
                        'type'      => $typeId,
                        'driver_id' => $driverFilter,
                        'severity'  => $severityFilter,
                        'nature'    => $value,
                    ], fn ($v) => $v !== null && $v !== ''));
                @endphp
                <a href="{{ $href }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border transition
                          {{ $isActive
                              ? ($value === 'positive' ? 'bg-emerald-600 border-emerald-600 text-white' : ($value === 'negative' ? 'bg-red-600 border-red-600 text-white' : 'bg-[#004fa3] border-[#004fa3] text-white'))
                              : ($value === 'positive' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' : ($value === 'negative' ? 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50')) }}">
                    @if ($value === 'positive')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                    @elseif ($value === 'negative')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v2a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/></svg>
                    @endif
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @php
            $currentFilters = array_filter([
                'tab'       => $tab,
                'type'      => $typeId,
                'driver_id' => $driverFilter,
                'severity'  => $severityFilter,
                'nature'    => $nature,
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
                        <th class="px-5 py-3 text-left">Nature</th>
                        <th class="px-5 py-3 text-left">Responsable</th>
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
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($complaint->negative === false)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                                        Positif
                                    </span>
                                @elseif ($complaint->negative === true)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-red-100 text-red-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v2a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/></svg>
                                        Négatif
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-500 text-xs">
                                @if ($complaint->managerAgent && $complaint->manager_user_id !== auth()->id())
                                    <span class="text-orange-600 font-medium">
                                        {{ $complaint->managerAgent->first_name }} {{ $complaint->managerAgent->last_name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Vous</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('complaints.show', $complaint) }}"
                                   class="text-[#004fa3] hover:text-[#003d80] font-medium text-xs">Voir →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">Aucun dossier</td>
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
