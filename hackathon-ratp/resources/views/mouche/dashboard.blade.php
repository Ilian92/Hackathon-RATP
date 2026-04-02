<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-900">Tableau de bord</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- KPI --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Missions en attente</p>
                <p class="mt-2 text-3xl font-bold text-[#004fa3]">{{ $totalPending }}</p>
                <p class="mt-1 text-xs text-gray-500">Rapport à soumettre</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Rapports soumis</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $totalSubmitted }}</p>
                <p class="mt-1 text-xs text-gray-500">Depuis le début</p>
            </div>
        </div>

        {{-- Missions en attente --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Missions à traiter</h2>
            </div>
            @if ($pendingMissions->isEmpty())
                <div class="px-5 py-10 text-center text-gray-400 text-sm">
                    Aucune mission en attente.
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach ($pendingMissions as $mission)
                        <div class="px-5 py-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Mission de contrôle</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Assignée le {{ $mission->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                            <a href="{{ route('rapport.create', $mission) }}"
                               class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-[#004fa3] text-white text-xs font-semibold rounded-lg hover:bg-[#003d80] transition">
                                Remplir le rapport →
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Rapports soumis --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Mes rapports soumis</h2>
            </div>
            @if ($submittedMissions->isEmpty())
                <div class="px-5 py-10 text-center text-gray-400 text-sm">
                    Aucun rapport soumis.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-50 bg-gray-50 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-5 py-3 text-left">Date mission</th>
                            <th class="px-5 py-3 text-center">Note moyenne</th>
                            <th class="px-5 py-3 text-center">Statut mission</th>
                            <th class="px-5 py-3 text-right">Soumis le</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($submittedMissions as $mission)
                            @php
                                $rapport = $mission->rapports->first();
                                $avg = $rapport?->averageScore();
                                $statusLabels = ['EnCours' => 'En cours', 'Completee' => 'Complétée', 'Decidee' => 'Décidée'];
                                $statusColors = ['EnCours' => 'bg-blue-100 text-blue-700', 'Completee' => 'bg-amber-100 text-amber-700', 'Decidee' => 'bg-gray-100 text-gray-600'];
                                $sk = $mission->status->value;
                            @endphp
                            <tr>
                                <td class="px-5 py-3 text-gray-700">
                                    {{ $mission->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($avg !== null)
                                        <span class="text-sm font-bold {{ $avg >= 4 ? 'text-emerald-600' : ($avg >= 3 ? 'text-amber-500' : 'text-red-600') }}">
                                            {{ $avg }}/5
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusColors[$sk] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $statusLabels[$sk] ?? $sk }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-gray-400">
                                    {{ $mission->pivot->submitted_at ? \Carbon\Carbon::parse($mission->pivot->submitted_at)->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>
</x-app-layout>
