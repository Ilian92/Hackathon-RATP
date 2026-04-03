<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-900">Tableau de bord</h1>
    </x-slot>

    @php
        $severityColors = [
            0 => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'bar' => 'bg-green-400', 'label' => 'Niveau 0 — Classé sans suite'],
            1 => ['bg' => 'bg-lime-100', 'text' => 'text-lime-700', 'bar' => 'bg-lime-400', 'label' => 'Niveau 1 — Mineur'],
            2 => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'bar' => 'bg-yellow-400', 'label' => 'Niveau 2 — Modéré'],
            3 => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'bar' => 'bg-orange-400', 'label' => 'Niveau 3 — Grave'],
            4 => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'bar' => 'bg-red-500', 'label' => 'Niveau 4 — Très grave'],
        ];
        $maxSeverityCount = $severityDistribution->max() ?: 1;
        $totalClassified = $positiveCount + $negativeCount;
        $maxTypeCount = $typeBreakdown->max() ?: 1;
        $monthEvolution = $treatedLastMonth > 0
            ? round(($treatedThisMonth - $treatedLastMonth) / $treatedLastMonth * 100)
            : null;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">En attente</p>
                <p class="mt-2 text-3xl font-bold text-[#004fa3]">{{ $availableCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Dossiers non réclamés</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Mes dossiers</p>
                <p class="mt-2 text-3xl font-bold text-amber-500">{{ $myInProgressCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Pris en charge, à traiter</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Traités ce mois</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $treatedThisMonth }}</p>
                @if ($monthEvolution !== null)
                    <p class="mt-1 text-xs {{ $monthEvolution >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $monthEvolution >= 0 ? '↑' : '↓' }} {{ abs($monthEvolution) }}% vs mois dernier
                    </p>
                @else
                    <p class="mt-1 text-xs text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total classifiés</p>
                <p class="mt-2 text-3xl font-bold text-gray-700">{{ $totalTreated }}</p>
                <p class="mt-1 text-xs text-gray-500">Dossiers transmis depuis le début</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Nature breakdown --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Nature des signalements traités</h2>
                <p class="text-xs text-gray-400 mb-5">Tous les dossiers transmis (hors en cours)</p>
                @if ($totalClassified === 0)
                    <p class="text-sm text-gray-400 text-center py-6">Aucune donnée</p>
                @else
                    {{-- Visual split bar --}}
                    @php $negPct = round($negativeCount / $totalClassified * 100); @endphp
                    <div class="flex rounded-full overflow-hidden h-4 mb-4">
                        <div class="bg-red-400 transition-all" style="width: {{ $negPct }}%"></div>
                        <div class="bg-emerald-400 transition-all" style="width: {{ 100 - $negPct }}%"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-red-50 rounded-xl p-3 text-center">
                            <p class="text-2xl font-bold text-red-600">{{ $negativeCount }}</p>
                            <p class="text-xs text-red-500 font-medium mt-0.5">Négatifs</p>
                            <p class="text-xs text-gray-400">{{ $negPct }}%</p>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-3 text-center">
                            <p class="text-2xl font-bold text-emerald-600">{{ $positiveCount }}</p>
                            <p class="text-xs text-emerald-600 font-medium mt-0.5">Positifs</p>
                            <p class="text-xs text-gray-400">{{ 100 - $negPct }}%</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Severity distribution --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Niveaux de gravité assignés</h2>
                <p class="text-xs text-gray-400 mb-4">Dossiers traités par moi (hors en cours)</p>
                @if ($severityDistribution->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">Aucune donnée</p>
                @else
                    <div class="space-y-3">
                        @foreach ([4, 3, 2, 1, 0] as $level)
                            @php $count = $severityDistribution[$level] ?? 0; @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-600">{{ $severityColors[$level]['label'] }}</span>
                                    <span class="text-xs font-semibold text-gray-700">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $severityColors[$level]['bar'] }}"
                                         style="width: {{ $count > 0 ? round($count / $maxSeverityCount * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Top types --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Types de signalements</h2>
                <p class="text-xs text-gray-400 mb-4">Top 5 des types traités par moi</p>
                @if ($typeBreakdown->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">Aucune donnée</p>
                @else
                    <div class="space-y-3">
                        @foreach ($typeBreakdown as $typeName => $count)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-600 truncate pr-2">{{ $typeName }}</span>
                                    <span class="text-xs font-semibold text-gray-700 shrink-0">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-[#004fa3]/60"
                                         style="width: {{ round($count / $maxTypeCount * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Performance metrics --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- Délai moyen d'attente en file --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Attente moyenne en file</h2>
                <p class="text-xs text-gray-400 mb-4">Dossiers non réclamés (depuis création)</p>
                <p class="text-4xl font-bold {{ $avgWaitInQueue > 3 ? 'text-red-600' : 'text-[#004fa3]' }}">{{ $avgWaitInQueue }}<span class="text-base font-normal text-gray-400 ml-1">j</span></p>
                <p class="mt-2 text-xs text-gray-500">délai moyen actuel</p>
            </div>

            {{-- Dossiers bloqués --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Dossiers bloqués</h2>
                <p class="text-xs text-gray-400 mb-4">Sans prise en charge depuis plus de 3 jours</p>
                <p class="text-4xl font-bold {{ $stalledCount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $stalledCount }}</p>
                <p class="mt-2 text-xs text-gray-500">{{ $stalledCount === 0 ? 'Aucun dossier en souffrance' : 'dossier' . ($stalledCount > 1 ? 's' : '') . ' à prendre en charge' }}</p>
            </div>

            {{-- Satisfaction globale --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Satisfaction chauffeurs</h2>
                <p class="text-xs text-gray-400 mb-4">Note moyenne globale (tous chauffeurs)</p>
                @if ($globalSatisfaction->total == 0)
                    <p class="text-sm text-gray-400 text-center py-4">Aucune évaluation</p>
                @else
                    <p class="text-4xl font-bold text-emerald-600">{{ round($globalSatisfaction->avg / 2, 1) }}<span class="text-base font-normal text-gray-400 ml-1">/ 5</span></p>
                    <p class="mt-2 text-xs text-gray-500">sur {{ $globalSatisfaction->total }} évaluation{{ $globalSatisfaction->total > 1 ? 's' : '' }}</p>
                @endif
            </div>

            {{-- Volume mensuel --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Volume mensuel entrant</h2>
                <p class="text-xs text-gray-400 mb-4">Signalements reçus (6 derniers mois)</p>
                @php $maxMonthly = max($monthlyVolume->max('count'), 1); @endphp
                <div class="flex items-end gap-1.5 h-16">
                    @foreach ($monthlyVolume as $month)
                        <div class="flex-1 flex flex-col items-center gap-0.5">
                            <div class="w-full bg-[#004fa3]/70 rounded-t-sm"
                                 style="height: {{ $month['count'] > 0 ? max(3, round($month['count'] / $maxMonthly * 52)) : 2 }}px"></div>
                            <span class="text-[10px] text-gray-400">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Carte interactive des lignes --}}
        <x-complaints-map />

    </div>
</x-app-layout>
