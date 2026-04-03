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
        $totalClosed = $allClosedPositive + $allClosedNegative;
        $maxSanctionType = $sanctionTypeBreakdown->max() ?: 1;
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">En attente</p>
                <p class="mt-2 text-3xl font-bold text-[#004fa3]">{{ $availableCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Dossiers RH non réclamés</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Mes dossiers</p>
                <p class="mt-2 text-3xl font-bold text-amber-500">{{ $myInProgressCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Pris en charge, à traiter</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Clôturés ce mois</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $closedThisMonth }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ now()->translatedFormat('F Y') }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ce mois</p>
                <p class="mt-2 text-3xl font-bold text-red-600">{{ $sanctionsThisMonth }}</p>
                <p class="mt-1 text-xs text-gray-500">
                    sanction{{ $sanctionsThisMonth > 1 ? 's' : '' }} ·
                    <span class="text-emerald-600 font-semibold">{{ $gratificationsThisMonth }}</span>
                    gratification{{ $gratificationsThisMonth > 1 ? 's' : '' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Positif / Négatif global --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Nature des dossiers clôturés</h2>
                <p class="text-xs text-gray-400 mb-5">Tous les dossiers clôturés (tous agents RH)</p>
                @if ($totalClosed === 0)
                    <p class="text-sm text-gray-400 text-center py-6">Aucune donnée</p>
                @else
                    @php $negPct = round($allClosedNegative / $totalClosed * 100); @endphp
                    <div class="flex rounded-full overflow-hidden h-4 mb-4">
                        <div class="bg-red-400" style="width: {{ $negPct }}%"></div>
                        <div class="bg-emerald-400" style="width: {{ 100 - $negPct }}%"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-red-50 rounded-xl p-3 text-center">
                            <p class="text-2xl font-bold text-red-600">{{ $allClosedNegative }}</p>
                            <p class="text-xs text-red-500 font-medium mt-0.5">Négatifs</p>
                            <p class="text-xs text-gray-400">{{ $negPct }}%</p>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-3 text-center">
                            <p class="text-2xl font-bold text-emerald-600">{{ $allClosedPositive }}</p>
                            <p class="text-xs text-emerald-600 font-medium mt-0.5">Positifs</p>
                            <p class="text-xs text-gray-400">{{ 100 - $negPct }}%</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Severity distribution (mes dossiers clôturés) --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Gravité des dossiers clôturés</h2>
                <p class="text-xs text-gray-400 mb-4">Mes dossiers clôturés</p>
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

            {{-- Sanction type breakdown --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Types de sanctions prononcées</h2>
                <p class="text-xs text-gray-400 mb-4">Toutes sanctions (tous agents RH)</p>
                @if ($sanctionTypeBreakdown->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">Aucune sanction</p>
                @else
                    <div class="space-y-3">
                        @foreach ($sanctionTypeBreakdown as $type => $count)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-600">{{ $type }}</span>
                                    <span class="text-xs font-semibold text-gray-700">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-orange-400"
                                         style="width: {{ round($count / $maxSanctionType * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Performance metrics --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- Délai moyen de résolution --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Délai moyen de résolution</h2>
                <p class="text-xs text-gray-400 mb-4">Incident → clôture (mes dossiers RH)</p>
                @if ($avgDaysToClose === null)
                    <p class="text-sm text-gray-400 text-center py-4">Aucune donnée</p>
                @else
                    <p class="text-4xl font-bold text-[#004fa3]">{{ $avgDaysToClose }}<span class="text-base font-normal text-gray-400 ml-1">j</span></p>
                    <p class="mt-2 text-xs text-gray-500">en moyenne sur les dossiers clôturés</p>
                @endif
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

            {{-- Taux de dossiers aboutis --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Taux d'aboutissement</h2>
                <p class="text-xs text-gray-400 mb-4">Dossiers clôturés avec action (tous agents RH)</p>
                @if ($aboutiRate === null)
                    <p class="text-sm text-gray-400 text-center py-4">Aucune donnée</p>
                @else
                    <p class="text-4xl font-bold text-amber-500">{{ $aboutiRate }}<span class="text-base font-normal text-gray-400 ml-1">%</span></p>
                    <p class="mt-2 text-xs text-gray-500">{{ $aboutiCount }} / {{ $allClosedCount }} dossiers clôturés</p>
                @endif
            </div>

            {{-- Volume mensuel --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Volume mensuel</h2>
                <p class="text-xs text-gray-400 mb-3">Reçus vs clôturés (6 derniers mois)</p>
                @php $maxMonthly = max($monthlyVolume->max('received'), $monthlyVolume->max('closed'), 1); @endphp
                <div class="flex items-end gap-2 h-16">
                    @foreach ($monthlyVolume as $month)
                        <div class="flex-1 flex flex-col items-center gap-0.5">
                            <div class="w-full flex gap-0.5 items-end" style="height: 52px">
                                <div class="flex-1 bg-[#004fa3]/60 rounded-t-sm"
                                     style="height: {{ $month['received'] > 0 ? max(3, round($month['received'] / $maxMonthly * 52)) : 2 }}px"></div>
                                <div class="flex-1 bg-emerald-400 rounded-t-sm"
                                     style="height: {{ $month['closed'] > 0 ? max(3, round($month['closed'] / $maxMonthly * 52)) : 2 }}px"></div>
                            </div>
                            <span class="text-[10px] text-gray-400">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-4 mt-2">
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-[#004fa3]/60 inline-block"></span>Reçus</span>
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-400 inline-block"></span>Clôturés</span>
                </div>
            </div>

        </div>

        {{-- Recent sanctions & gratifications --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-800">Dernières sanctions</h2>
                </div>
                @if ($recentSanctions->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">Aucune sanction</div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-50 bg-gray-50 text-xs uppercase tracking-wide text-gray-400">
                                <th class="px-5 py-3 text-left">Chauffeur</th>
                                <th class="px-5 py-3 text-left">Type</th>
                                <th class="px-5 py-3 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($recentSanctions as $sanction)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-800">
                                        {{ $sanction->user?->first_name }} {{ $sanction->user?->last_name }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                            {{ $sanction->type }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right text-xs text-gray-400">
                                        {{ $sanction->sanctioned_at->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-800">Dernières gratifications</h2>
                </div>
                @if ($recentGratifications->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">Aucune gratification</div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-50 bg-gray-50 text-xs uppercase tracking-wide text-gray-400">
                                <th class="px-5 py-3 text-left">Chauffeur</th>
                                <th class="px-5 py-3 text-center">Montant</th>
                                <th class="px-5 py-3 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($recentGratifications as $gratification)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-800">
                                        {{ $gratification->user?->first_name }} {{ $gratification->user?->last_name }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($gratification->amount)
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                                {{ number_format($gratification->amount) }} pts
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right text-xs text-gray-400">
                                        {{ $gratification->awarded_at->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>

        {{-- Carte interactive des lignes --}}
        <x-complaints-map />

    </div>
</x-app-layout>
