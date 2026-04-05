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

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Nature des signalements traités</h2>
                <p class="text-xs text-gray-400 mb-5">Tous les dossiers transmis (hors en cours)</p>
                @if ($totalClassified === 0)
                    <p class="text-sm text-gray-400 text-center py-6">Aucune donnée</p>
                @else

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

        @php $satAvg = $globalSatisfaction->total > 0 ? round($globalSatisfaction->avg / 2, 1) : null; @endphp
        <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border border-amber-100 rounded-2xl p-5">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <h2 class="text-sm font-semibold text-amber-800">Satisfaction client</h2>
                    </div>
                    <p class="text-xs text-amber-600 mb-4">Note moyenne globale — tous chauffeurs confondus</p>
                    @if ($satAvg === null)
                        <p class="text-sm text-amber-700">Aucune évaluation disponible</p>
                    @else
                        <div class="flex items-end gap-4">
                            <p class="text-5xl font-bold text-amber-500 leading-none">{{ $satAvg }}<span class="text-lg font-normal text-amber-300 ml-1">/ 5</span></p>
                            <div>
                                <div class="flex gap-0.5 mb-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $satAvg >= $i ? 'text-amber-400' : ($satAvg >= $i - 0.5 ? 'text-amber-200' : 'text-gray-200') }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <p class="text-xs text-amber-600">{{ $globalSatisfaction->total }} évaluation{{ $globalSatisfaction->total > 1 ? 's' : '' }} collectées</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="sm:w-64 shrink-0">
                    <p class="text-xs text-amber-600 mb-2">Évolution mensuelle (sur 5)</p>
                    <div class="flex items-end gap-1.5 h-14">
                        @foreach ($satisfactionTrend as $month)
                            <div class="flex-1 flex flex-col items-center gap-0.5">
                                @if ($month['avg5'] !== null)
                                    <span class="text-[9px] text-amber-500 font-semibold">{{ $month['avg5'] }}</span>
                                    <div class="w-full bg-amber-300 rounded-t-sm"
                                         style="height: {{ max(3, round($month['avg5'] / 5 * 40)) }}px"></div>
                                @else
                                    <span class="text-[9px] text-amber-200">—</span>
                                    <div class="w-full bg-amber-100 rounded-t-sm" style="height: 3px"></div>
                                @endif
                                <span class="text-[9px] text-amber-500">{{ $month['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Attente moyenne en file</h2>
                <p class="text-xs text-gray-400 mb-4">Dossiers non réclamés (depuis création)</p>
                <p class="text-4xl font-bold {{ $avgWaitInQueue > 3 ? 'text-red-600' : 'text-[#004fa3]' }}">{{ $avgWaitInQueue }}<span class="text-base font-normal text-gray-400 ml-1">j</span></p>
                <p class="mt-2 text-xs text-gray-500">délai moyen actuel</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Dossiers bloqués</h2>
                <p class="text-xs text-gray-400 mb-4">Sans prise en charge depuis plus de 3 jours</p>
                <p class="text-4xl font-bold {{ $stalledCount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $stalledCount }}</p>
                <p class="mt-2 text-xs text-gray-500">{{ $stalledCount === 0 ? 'Aucun dossier en souffrance' : 'dossier' . ($stalledCount > 1 ? 's' : '') . ' à prendre en charge' }}</p>
            </div>

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

        <x-complaints-map />

    </div>
</x-app-layout>
