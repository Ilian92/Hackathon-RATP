<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900">Missions mouche</h1>
            <a href="{{ route('missions.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#004fa3] text-white text-sm font-semibold rounded-lg hover:bg-[#003d80] transition">
                + Nouvelle mission
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if (session('success'))
            <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @php
            $statusColors = [
                'EnCours'  => 'bg-blue-100 text-blue-700',
                'Completee' => 'bg-amber-100 text-amber-700',
                'Decidee'  => 'bg-gray-100 text-gray-600',
            ];
            $statusLabels = [
                'EnCours'  => 'En cours',
                'Completee' => 'À décider',
                'Decidee'  => 'Décidée',
            ];
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            @if ($missions->isEmpty())
                <div class="px-6 py-16 text-center text-gray-400 text-sm">
                    Aucune mission créée. <a href="{{ route('missions.create') }}" class="text-[#004fa3] hover:underline">Créer la première mission</a>.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-5 py-3 text-left">Chauffeur contrôlé</th>
                            <th class="px-5 py-3 text-center">Mouches assignées</th>
                            <th class="px-5 py-3 text-center">Rapports reçus</th>
                            <th class="px-5 py-3 text-center">Statut</th>
                            <th class="px-5 py-3 text-right">Date</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($missions as $mission)
                            @php
                                $submitted = $mission->mouches->whereNotNull('pivot.submitted_at')->count();
                                $total = $mission->mouches->count();
                                $statusKey = $mission->status->value;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 font-medium text-gray-800">
                                    {{ $mission->driver?->first_name }} {{ $mission->driver?->last_name }}
                                </td>
                                <td class="px-5 py-3 text-center text-gray-600">{{ $total }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="{{ $submitted === $total && $total > 0 ? 'text-emerald-600 font-semibold' : 'text-gray-500' }}">
                                        {{ $submitted }} / {{ $total }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $statusLabels[$statusKey] ?? $statusKey }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-gray-400">
                                    {{ $mission->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('missions.show', $mission) }}"
                                       class="text-[#004fa3] hover:text-[#003d80] font-medium text-xs">
                                        Voir →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($missions->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $missions->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-app-layout>
