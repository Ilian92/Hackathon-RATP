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
        $totalVisible = ($stepBreakdown->manager_review ?? 0) + ($stepBreakdown->rh_review ?? 0) + ($stepBreakdown->closed ?? 0);
        $totalNature = ($natureBreakdown->negative_count ?? 0) + ($natureBreakdown->positive_count ?? 0) + ($natureBreakdown->unclassified_count ?? 0);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">En attente</p>
                <p class="mt-2 text-3xl font-bold text-[#004fa3]">{{ $pendingCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Dossiers à décision manager</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">En cours au RH</p>
                <p class="mt-2 text-3xl font-bold text-amber-500">{{ $rhCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Transmis au service RH</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Clôturés ce mois</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $closedThisMonth }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Chauffeurs</p>
                <p class="mt-2 text-3xl font-bold text-gray-700">{{ $teamCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Dans mon équipe</p>
            </div>
        </div>

        @if ($pendingMissionDecisionCount > 0)
            <a href="{{ route('missions.index') }}"
               class="flex items-center justify-between gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 hover:bg-amber-100 transition">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🕵️</span>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">
                            {{ $pendingMissionDecisionCount }} mission{{ $pendingMissionDecisionCount > 1 ? 's mouche nécessitent' : ' mouche nécessite' }} une décision
                        </p>
                        <p class="text-xs text-amber-600">Tous les rapports sont reçus — cliquez pour décider</p>
                    </div>
                </div>
                <span class="text-amber-600 text-sm font-semibold shrink-0">Voir →</span>
            </a>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Répartition par étape --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Répartition par étape</h2>
                <p class="text-xs text-gray-400 mb-4">{{ $totalVisible }} dossiers au total dans mon périmètre</p>
                @if ($totalVisible === 0)
                    <p class="text-sm text-gray-400 text-center py-6">Aucune donnée</p>
                @else
                    @php
                        $steps = [
                            ['label' => 'En attente manager', 'count' => $stepBreakdown->manager_review ?? 0, 'bar' => 'bg-[#004fa3]', 'text' => 'text-[#004fa3]'],
                            ['label' => 'Transmis au RH', 'count' => $stepBreakdown->rh_review ?? 0, 'bar' => 'bg-amber-400', 'text' => 'text-amber-600'],
                            ['label' => 'Clôturés', 'count' => $stepBreakdown->closed ?? 0, 'bar' => 'bg-emerald-400', 'text' => 'text-emerald-600'],
                        ];
                    @endphp
                    <div class="space-y-4">
                        @foreach ($steps as $step)
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs text-gray-600">{{ $step['label'] }}</span>
                                    <span class="text-xs font-bold {{ $step['text'] }}">
                                        {{ $step['count'] }}
                                        <span class="font-normal text-gray-400">({{ $totalVisible > 0 ? round($step['count'] / $totalVisible * 100) : 0 }}%)</span>
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full {{ $step['bar'] }}"
                                         style="width: {{ $totalVisible > 0 ? round($step['count'] / $totalVisible * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Nature des signalements --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Nature des signalements</h2>
                <p class="text-xs text-gray-400 mb-4">Tous les dossiers de mon périmètre</p>
                @if ($totalNature === 0)
                    <p class="text-sm text-gray-400 text-center py-6">Aucune donnée</p>
                @else
                    @php
                        $natures = [
                            ['label' => 'Négatifs', 'count' => $natureBreakdown->negative_count ?? 0, 'bar' => 'bg-red-400', 'text' => 'text-red-600'],
                            ['label' => 'Positifs', 'count' => $natureBreakdown->positive_count ?? 0, 'bar' => 'bg-emerald-400', 'text' => 'text-emerald-600'],
                            ['label' => 'Non qualifiés', 'count' => $natureBreakdown->unclassified_count ?? 0, 'bar' => 'bg-gray-300', 'text' => 'text-gray-500'],
                        ];
                    @endphp
                    <div class="space-y-4">
                        @foreach ($natures as $nature)
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs text-gray-600">{{ $nature['label'] }}</span>
                                    <span class="text-xs font-bold {{ $nature['text'] }}">
                                        {{ $nature['count'] }}
                                        <span class="font-normal text-gray-400">({{ $totalNature > 0 ? round($nature['count'] / $totalNature * 100) : 0 }}%)</span>
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full {{ $nature['bar'] }}"
                                         style="width: {{ $totalNature > 0 ? round($nature['count'] / $totalNature * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Distribution de gravité --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Distribution de gravité</h2>
                <p class="text-xs text-gray-400 mb-4">Tous les dossiers avec niveau assigné</p>
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
        </div>

        {{-- Performance metrics --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- Délai moyen de résolution --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Délai moyen de résolution</h2>
                <p class="text-xs text-gray-400 mb-4">Incident → clôture (mes dossiers)</p>
                @if ($avgDaysToClose === null)
                    <p class="text-sm text-gray-400 text-center py-4">Aucune donnée</p>
                @else
                    <p class="text-4xl font-bold text-[#004fa3]">{{ $avgDaysToClose }}<span class="text-base font-normal text-gray-400 ml-1">j</span></p>
                    <p class="mt-2 text-xs text-gray-500">en moyenne sur les dossiers clôturés</p>
                @endif
            </div>

            {{-- Note satisfaction équipe --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Satisfaction équipe</h2>
                <p class="text-xs text-gray-400 mb-4">Note moyenne des chauffeurs de mon équipe</p>
                @if ($teamSatisfaction->total == 0)
                    <p class="text-sm text-gray-400 text-center py-4">Aucune évaluation</p>
                @else
                    <p class="text-4xl font-bold text-emerald-600">{{ round($teamSatisfaction->avg / 2, 1) }}<span class="text-base font-normal text-gray-400 ml-1">/ 5</span></p>
                    <p class="mt-2 text-xs text-gray-500">sur {{ $teamSatisfaction->total }} évaluation{{ $teamSatisfaction->total > 1 ? 's' : '' }}</p>
                @endif
            </div>

            {{-- Ancienneté des dossiers en attente --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 lg:col-span-2">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Ancienneté des dossiers en attente</h2>
                <p class="text-xs text-gray-400 mb-4">Dossiers en attente de décision manager</p>
                @php
                    $agingData = [
                        ['label' => '0 – 3 j', 'count' => $agingPending->age_0_3 ?? 0, 'bar' => 'bg-emerald-400'],
                        ['label' => '4 – 7 j', 'count' => $agingPending->age_4_7 ?? 0, 'bar' => 'bg-yellow-400'],
                        ['label' => '8 – 14 j', 'count' => $agingPending->age_8_14 ?? 0, 'bar' => 'bg-orange-400'],
                        ['label' => '> 14 j', 'count' => $agingPending->age_over_14 ?? 0, 'bar' => 'bg-red-500'],
                    ];
                    $maxAging = max(collect($agingData)->pluck('count')->max(), 1);
                @endphp
                @if (collect($agingData)->sum('count') === 0)
                    <p class="text-sm text-gray-400 text-center py-4">Aucun dossier en attente</p>
                @else
                    <div class="grid grid-cols-4 gap-3">
                        @foreach ($agingData as $band)
                            <div class="text-center">
                                <div class="flex flex-col justify-end h-16 mb-1.5">
                                    <div class="{{ $band['bar'] }} rounded-t-md mx-auto w-full"
                                         style="height: {{ $band['count'] > 0 ? max(8, round($band['count'] / $maxAging * 64)) : 0 }}px"></div>
                                </div>
                                <p class="text-lg font-bold text-gray-700">{{ $band['count'] }}</p>
                                <p class="text-xs text-gray-400">{{ $band['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Volume mensuel --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Volume mensuel de signalements</h2>
            <p class="text-xs text-gray-400 mb-5">Dossiers de mon périmètre sur les 6 derniers mois</p>
            @php $maxMonthly = max($monthlyVolume->max('count'), 1); @endphp
            <div class="flex items-end gap-3 h-24">
                @foreach ($monthlyVolume as $month)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs font-semibold text-gray-600">{{ $month['count'] }}</span>
                        <div class="w-full bg-[#004fa3] rounded-t-md"
                             style="height: {{ $month['count'] > 0 ? max(4, round($month['count'] / $maxMonthly * 80)) : 2 }}px"></div>
                        <span class="text-xs text-gray-400">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Carte interactive des lignes --}}
        <x-complaints-map />

        {{-- Team stats --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Performance de l'équipe</h2>
            </div>
            @if ($teamStats->isEmpty())
                <div class="px-5 py-12 text-center text-gray-400 text-sm">Aucun chauffeur assigné</div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-50 bg-gray-50 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-5 py-3 text-left">Chauffeur</th>
                            <th class="px-5 py-3 text-center">Total signalements</th>
                            <th class="px-5 py-3 text-center">Négatifs</th>
                            <th class="px-5 py-3 text-center">Positifs</th>
                            <th class="px-5 py-3 text-center">Sanctions</th>
                            <th class="px-5 py-3 text-center">Gratifications</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($teamStats as $driver)
                            @php
                                $ratio = $driver->total_complaints > 0
                                    ? round($driver->negative_complaints / $driver->total_complaints * 100)
                                    : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 font-medium text-gray-800">
                                    {{ $driver->first_name }} {{ $driver->last_name }}
                                </td>
                                <td class="px-5 py-3 text-center text-gray-600">{{ $driver->total_complaints }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if ($driver->negative_complaints > 0)
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                                            {{ $driver->negative_complaints }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">0</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($driver->positive_complaints > 0)
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                            {{ $driver->positive_complaints }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">0</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($driver->sanctions_count > 0)
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                            {{ $driver->sanctions_count }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">0</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($driver->gratifications_count > 0)
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#4bc0ad]/20 text-[#38a090]">
                                            {{ $driver->gratifications_count }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">0</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('drivers.show', $driver) }}"
                                       class="text-[#004fa3] hover:text-[#003d80] font-medium text-xs">Profil →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>
</x-app-layout>
