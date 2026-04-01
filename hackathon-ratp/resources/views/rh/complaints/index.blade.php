<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-900">Dossiers RH</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 w-fit shadow-sm">
            @foreach (['available' => 'Disponibles', 'mine' => 'Mes dossiers', 'closed' => 'Traités'] as $value => $label)
                <a href="{{ route('rh.complaints.index', ['tab' => $value]) }}"
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

        @php
            $sortUrl = fn (string $col) => route('rh.complaints.index', [
                'tab'       => $tab,
                'sort'      => $col,
                'direction' => $sort === $col && $direction === 'asc' ? 'desc' : 'asc',
            ]);
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
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-500 text-xs">
                                {{ $complaint->rhAgent ? $complaint->rhAgent->first_name.' '.$complaint->rhAgent->last_name : '—' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('rh.complaints.show', $complaint) }}"
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
