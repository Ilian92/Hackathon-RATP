<x-app-layout>
    @php
        $statusColors = [
            'Actif'       => 'bg-green-100 text-green-700',
            'EnVacances'  => 'bg-blue-100 text-blue-700',
            'EnFormation' => 'bg-yellow-100 text-yellow-700',
            'Suspendu'    => 'bg-red-100 text-red-700',
            'Retraite'    => 'bg-gray-100 text-gray-600',
        ];
        $statusClass = $statusColors[$user->status->value] ?? 'bg-gray-100 text-gray-600';

        $complaintStatusColors = [
            'EnCours' => 'bg-yellow-100 text-yellow-700',
            'Clos'    => 'bg-gray-100 text-gray-500',
            'Abouti'  => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="flex h-[calc(100vh-4.125rem)] overflow-hidden">

        {{-- Colonne gauche --}}
        <aside class="w-80 shrink-0 border-r border-gray-200 bg-white flex flex-col overflow-y-auto">

            <div class="bg-[#004fa3] px-6 pt-8 pb-6">
                <a href="{{ route('profile') }}" class="inline-flex items-center gap-1.5 text-white/60 hover:text-white text-xs mb-5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Mon profil
                </a>
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mb-4">
                    <span class="text-2xl font-bold text-white">
                        {{ strtoupper(substr($user->first_name, 0, 1).substr($user->last_name, 0, 1)) }}
                    </span>
                </div>
                <h2 class="text-xl font-bold text-white leading-tight">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p class="text-white/70 text-sm mt-0.5">Chauffeur</p>
                <p class="text-white/50 text-xs font-mono mt-1">{{ $user->matricule ?? '—' }}</p>
                <div class="mt-3">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusClass }}">
                        {{ $user->status->label() }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-4 border-b border-gray-100 space-y-3">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Début de contrat</p>
                    <p class="text-sm font-medium text-gray-700 mt-0.5">
                        {{ $user->contract_start_date?->format('d/m/Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Manager</p>
                    @if ($user->managers->isNotEmpty())
                        @foreach ($user->managers as $manager)
                            <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $manager->first_name }} {{ $manager->last_name }}</p>
                            <p class="text-xs text-gray-400">{{ $manager->email }}</p>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-400 mt-0.5">—</p>
                    @endif
                </div>
            </div>

            <div class="px-6 py-4 border-b border-gray-100">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Satisfaction usagers</p>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-bold text-[#4bc0ad]">{{ number_format($avgSur5, 1) }}</span>
                    <span class="text-gray-400 mb-0.5">/ 5</span>
                </div>
                <div class="flex gap-0.5 mb-1">
                    @for ($i = 1; $i <= 5; $i++)
                        @php $fill = min(max($avgSur5 - ($i - 1), 0), 1); @endphp
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <defs>
                                <linearGradient id="star-{{ $i }}">
                                    <stop offset="{{ $fill * 100 }}%" stop-color="#4bc0ad"/>
                                    <stop offset="{{ $fill * 100 }}%" stop-color="#e5e7eb"/>
                                </linearGradient>
                            </defs>
                            <path fill="url(#star-{{ $i }})" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-xs text-gray-400">{{ $totalAvis }} avis</p>
            </div>

            <div class="px-6 py-4">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Score interne</p>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-bold text-[#004fa3]">{{ number_format($scoreInterne, 1) }}</span>
                    <span class="text-gray-400 mb-0.5">/ 5</span>
                </div>
                <div class="h-2 rounded-full bg-gray-100 overflow-hidden mb-1">
                    <div class="h-full rounded-full bg-[#004fa3]" style="width: {{ min($scoreInterne / 5 * 100, 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-400">70% satisfaction · 30% plaintes abouties</p>
            </div>
        </aside>

        {{-- Colonne droite --}}
        <div class="flex-1 min-w-0 overflow-hidden flex flex-col gap-4 p-6 bg-slate-100">

            {{-- Signalements --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <p class="text-sm font-semibold text-gray-700">Signalements</p>
                    <div class="flex gap-3 text-xs">
                        <span class="text-yellow-600 font-medium">{{ $enCoursCount }} en cours</span>
                        <span class="text-red-600 font-medium">{{ $aboutiesCount }} aboutis</span>
                        <span class="text-gray-400">{{ $closCount }} clos</span>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                    @forelse ($user->complaints->sortByDesc('incident_time') as $complaint)
                        @php $sc = $complaintStatusColors[$complaint->status->value] ?? 'bg-gray-100 text-gray-500'; @endphp
                        <div class="px-5 py-3 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">Bus {{ $complaint->bus->code }} · {{ $complaint->incident_time->format('d/m/Y') }}</p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $sc }}">
                                {{ $complaint->status->label() }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun signalement</p>
                    @endforelse
                </div>
            </div>

            {{-- Gratifications + Sanctions --}}
            <div class="flex gap-4 flex-1 min-h-0">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <p class="text-sm font-semibold text-gray-700">Gratifications</p>
                        @if ($user->gratifications->isNotEmpty())
                            <span class="text-sm font-bold text-[#4bc0ad]">{{ number_format($user->gratifications->sum('amount')) }} €</span>
                        @endif
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($user->gratifications->sortByDesc('awarded_at') as $g)
                            <div class="px-5 py-3 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $g->reason }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $g->awarded_at->format('d/m/Y') }}</p>
                                </div>
                                <span class="text-sm font-bold text-[#4bc0ad] shrink-0">+{{ number_format($g->amount) }} €</span>
                            </div>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucune gratification</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 shrink-0">
                        <p class="text-sm font-semibold text-gray-700">Sanctions</p>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($user->sanctions->sortByDesc('sanctioned_at') as $s)
                            <div class="px-5 py-3 flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $s->description }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $s->sanctioned_at->format('d/m/Y') }}</p>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 bg-red-50 text-red-600">
                                    {{ $s->type }}
                                </span>
                            </div>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucune sanction</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
