<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-900">Gestion des plaintes</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Tabs statut --}}
        <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 w-fit shadow-sm">
            @php
                $tabs = [
                    null        => ['label' => 'Toutes',   'count' => $counts['all']],
                    'EnCours'   => ['label' => 'En cours', 'count' => $counts['EnCours']],
                    'Abouti'    => ['label' => 'Abouties', 'count' => $counts['Abouti']],
                    'Clos'      => ['label' => 'Closes',   'count' => $counts['Clos']],
                ];
            @endphp
            @foreach ($tabs as $value => $tab)
                <a href="{{ route('com.complaints.index', array_filter(['status' => $value, 'type' => $typeId])) }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition
                          {{ $status === $value ? 'bg-[#004fa3] text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                    {{ $tab['label'] }}
                    <span class="text-xs px-1.5 py-0.5 rounded-full font-semibold
                                 {{ $status === $value ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Filtre type --}}
        <form method="GET" action="{{ route('com.complaints.index') }}" class="flex items-center gap-3">
            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <select name="type" onchange="this.form.submit()"
                    class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3]">
                <option value="">Tous les types</option>
                @foreach ($complaintTypes as $type)
                    <option value="{{ $type->id }}" @selected($typeId === $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            @if ($typeId)
                <a href="{{ route('com.complaints.index', array_filter(['status' => $status])) }}"
                   class="text-xs text-gray-400 hover:text-gray-600">Réinitialiser</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-400">
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Chauffeur</th>
                        <th class="px-5 py-3 text-left">Bus</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Gravité</th>
                        <th class="px-5 py-3 text-left">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($complaints as $complaint)
                        @php
                            $statusColors = [
                                'EnCours' => 'bg-yellow-100 text-yellow-700',
                                'Clos'    => 'bg-gray-100 text-gray-500',
                                'Abouti'  => 'bg-red-100 text-red-700',
                            ];
                            $severityColors = ['bg-green-100 text-green-700', 'bg-lime-100 text-lime-700', 'bg-yellow-100 text-yellow-700', 'bg-orange-100 text-orange-700', 'bg-red-100 text-red-700'];
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
                                    <span class="text-xs text-gray-400">Non évaluée</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$complaint->status->value] }}">
                                    {{ $complaint->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('com.complaints.show', $complaint) }}"
                                   class="text-[#004fa3] hover:text-[#003d80] font-medium text-xs">
                                    Voir →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                                Aucune plainte trouvée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($complaints->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $complaints->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
