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

        $severityColors = [
            0 => 'bg-green-100 text-green-700',
            1 => 'bg-lime-100 text-lime-700',
            2 => 'bg-yellow-100 text-yellow-700',
            3 => 'bg-orange-100 text-orange-700',
            4 => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="flex h-[calc(100vh-4.125rem)] overflow-hidden">

        {{-- ===== Colonne gauche (commune à tous) ===== --}}
        <aside class="w-80 shrink-0 border-r border-gray-200 bg-white flex flex-col overflow-y-auto">

            <div class="bg-[#004fa3] px-6 pt-8 pb-6">
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mb-4">
                    <span class="text-2xl font-bold text-white">
                        {{ strtoupper(substr($user->first_name, 0, 1).substr($user->last_name, 0, 1)) }}
                    </span>
                </div>
                <h2 class="text-xl font-bold text-white leading-tight">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p class="text-white/70 text-sm mt-0.5">{{ $user->role->value }}</p>
                <p class="text-white/50 text-xs font-mono mt-1">{{ $user->matricule ?? '—' }}</p>
                <div class="mt-3">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusClass }}">
                        {{ $user->status->label() }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-3 border-b border-gray-100">
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-medium text-[#004fa3] bg-white border border-[#004fa3]/30 rounded-xl hover:bg-[#004fa3]/5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier mon profil
                </a>
            </div>

            <div class="px-6 py-4 border-b border-gray-100 space-y-3">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Début de contrat</p>
                    <p class="text-sm font-medium text-gray-700 mt-0.5">
                        {{ $user->contract_start_date?->format('d/m/Y') ?? '—' }}
                    </p>
                </div>
                @php
                    $centreBuses = $user->role === \App\Enums\UserRole::Chauffeur
                        ? $user->managers->flatMap->centreBuses->unique('id')
                        : $user->centreBuses;
                @endphp
                @if ($centreBuses->isNotEmpty())
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Centre de bus</p>
                    @foreach ($centreBuses as $centreBus)
                        <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $centreBus->name }}</p>
                    @endforeach
                </div>
                @endif
                @if ($user->managers->isNotEmpty())
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Manager</p>
                    @foreach ($user->managers as $manager)
                        <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $manager->first_name }} {{ $manager->last_name }}</p>
                        <p class="text-xs text-gray-400">{{ $manager->email }}</p>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Scores — Chauffeur uniquement --}}
            @if ($user->role === \App\Enums\UserRole::Chauffeur)
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
            @endif
        </aside>

        {{-- ===== Colonne droite (selon le rôle) ===== --}}
        <div class="flex-1 min-w-0 overflow-hidden flex flex-col gap-4 p-6 bg-slate-100">

            @if ($user->role === \App\Enums\UserRole::Chauffeur)
                {{-- Signalements négatifs + positifs côte à côte --}}
                <div class="flex gap-4 flex-1 min-h-0">
                    {{-- Signalements négatifs --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-w-0 min-h-0">
                        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                            <p class="text-base font-semibold text-gray-700">Signalements</p>
                            <div class="flex gap-3 text-xs">
                                <span class="text-yellow-600 font-medium">{{ $enCoursCount }} en cours</span>
                                <span class="text-red-600 font-medium">{{ $aboutiesCount }} aboutis</span>
                                <span class="text-gray-400">{{ $closCount }} clos</span>
                            </div>
                        </div>
                        <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                            @forelse ($negativeComplaints->sortByDesc('incident_time') as $complaint)
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

                    {{-- Signalements positifs --}}
                    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm flex flex-col flex-1 min-w-0 min-h-0">
                        <div class="px-5 py-3.5 border-b border-emerald-100 flex items-center justify-between shrink-0">
                            <p class="text-base font-semibold text-emerald-700">Signalements positifs</p>
                            <span class="text-base font-semibold text-emerald-600">{{ $positiveComplaints->count() }}</span>
                        </div>
                        <div class="overflow-y-auto flex-1 divide-y divide-emerald-50">
                            @forelse ($positiveComplaints->sortByDesc('incident_time') as $complaint)
                                <div class="px-5 py-3 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Bus {{ $complaint->bus->code }} · {{ $complaint->incident_time->format('d/m/Y') }}</p>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 bg-emerald-100 text-emerald-700">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                        </svg>
                                        Positif
                                    </span>
                                </div>
                            @empty
                                <p class="px-5 py-6 text-sm text-emerald-400 text-center">Aucun signalement positif</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Sanctions + Gratifications --}}
                <div class="flex gap-4 flex-1 min-h-0">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-w-0 min-h-0">
                        <div class="px-5 py-3.5 border-b border-gray-100 shrink-0">
                            <p class="text-base font-semibold text-gray-700">Sanctions</p>
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

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-w-0 min-h-0">
                        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                            <p class="text-base font-semibold text-gray-700">Gratifications</p>
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
                </div>

            @elseif ($user->role === \App\Enums\UserRole::Manager)
                {{-- Plaintes en attente de décision --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <p class="text-base font-semibold text-gray-700">Dossiers en attente de décision</p>
                        <a href="{{ route('complaints.index') }}" class="text-xs text-[#004fa3] hover:text-[#003d80] font-medium">
                            Voir tous →
                        </a>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($pendingComplaints as $complaint)
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 transition block">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $complaint->driver?->first_name }} {{ $complaint->driver?->last_name }}
                                        · Bus {{ $complaint->bus->code }}
                                        · {{ $complaint->incident_time->format('d/m/Y') }}
                                    </p>
                                </div>
                                @if ($complaint->severity)
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $severityColors[$complaint->severity->level] }}">
                                        Niveau {{ $complaint->severity->level }}
                                    </span>
                                @endif
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun dossier en attente</p>
                        @endforelse
                    </div>
                </div>

                {{-- Dossiers transmis au RH --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <p class="text-base font-semibold text-gray-700">
                            Dossiers transmis au RH
                            <span class="text-[#004fa3]">{{ $rhComplaints->count() }}</span>
                        </p>
                        <a href="{{ route('complaints.index', ['tab' => 'rh']) }}" class="text-xs text-[#004fa3] hover:text-[#003d80] font-medium">
                            Voir tous →
                        </a>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($rhComplaints as $complaint)
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 transition block">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $complaint->driver?->first_name }} {{ $complaint->driver?->last_name }}
                                        · Bus {{ $complaint->bus->code }}
                                        · {{ $complaint->incident_time->format('d/m/Y') }}
                                    </p>
                                </div>
                                @if ($complaint->severity)
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $severityColors[$complaint->severity->level] }}">
                                        Niveau {{ $complaint->severity->level }}
                                    </span>
                                @endif
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun dossier transmis au RH</p>
                        @endforelse
                    </div>
                </div>

                {{-- Équipe de chauffeurs --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 shrink-0">
                        <p class="text-base font-semibold text-gray-700">Mon équipe — {{ $user->chauffeurs->count() }} chauffeurs</p>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($user->chauffeurs as $chauffeur)
                            <a href="{{ route('drivers.show', $chauffeur) }}"
                               class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 transition block">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#004fa3]/10 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-[#004fa3]">
                                            {{ strtoupper(substr($chauffeur->first_name, 0, 1).substr($chauffeur->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $chauffeur->first_name }} {{ $chauffeur->last_name }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $chauffeur->matricule }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-3 text-xs text-gray-400">
                                    <span>{{ $chauffeur->complaints->count() }} plaintes</span>
                                    <span>{{ $chauffeur->sanctions->count() }} sanctions</span>
                                </div>
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun chauffeur dans l'équipe</p>
                        @endforelse
                    </div>
                </div>

            @elseif ($user->role === \App\Enums\UserRole::Com)
                {{-- Plaintes disponibles + mes dossiers --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <p class="text-base font-semibold text-gray-700">
                            Plaintes disponibles
                            <span class="text-[#004fa3]">{{ $availableComplaints->count() }}</span>
                        </p>
                        <a href="{{ route('complaints.index') }}" class="text-xs text-[#004fa3] hover:text-[#003d80] font-medium">
                            Voir toutes →
                        </a>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($availableComplaints as $complaint)
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 transition block">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Bus {{ $complaint->bus->code }}
                                        · {{ $complaint->incident_time->format('d/m/Y') }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucune plainte disponible</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <p class="text-base font-semibold text-gray-700">Mes dossiers en cours</p>
                        <a href="{{ route('complaints.index', ['tab' => 'mine']) }}" class="text-xs text-[#004fa3] hover:text-[#003d80] font-medium">
                            Voir tous →
                        </a>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($myComplaints as $complaint)
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 transition block">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Bus {{ $complaint->bus->code }} · {{ $complaint->incident_time->format('d/m/Y') }}</p>
                                </div>
                                @if ($complaint->severity)
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $severityColors[$complaint->severity->level] }}">
                                        Niveau {{ $complaint->severity->level }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 shrink-0">À évaluer</span>
                                @endif
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun dossier en cours</p>
                        @endforelse
                    </div>
                </div>

            @elseif ($user->role === \App\Enums\UserRole::RH)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <p class="text-base font-semibold text-gray-700">Dossiers RH disponibles</p>
                        <a href="{{ route('complaints.index') }}" class="text-xs text-[#004fa3] hover:text-[#003d80] font-medium">
                            Voir tous →
                        </a>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($availableComplaints as $complaint)
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 transition block">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $complaint->driver?->first_name }} {{ $complaint->driver?->last_name }}
                                        · Bus {{ $complaint->bus->code }}
                                        · {{ $complaint->incident_time->format('d/m/Y') }}
                                    </p>
                                </div>
                                @if ($complaint->severity)
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $severityColors[$complaint->severity->level] }}">
                                        Niveau {{ $complaint->severity->level }}
                                    </span>
                                @endif
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun dossier disponible</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <p class="text-base font-semibold text-gray-700">Mes dossiers en cours</p>
                        <a href="{{ route('complaints.index', ['tab' => 'mine']) }}" class="text-xs text-[#004fa3] hover:text-[#003d80] font-medium">
                            Voir tous →
                        </a>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($myComplaints as $complaint)
                            <a href="{{ route('complaints.show', $complaint) }}"
                               class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-gray-50 transition block">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $complaint->driver?->first_name }} {{ $complaint->driver?->last_name }}
                                        · {{ $complaint->incident_time->format('d/m/Y') }}
                                    </p>
                                </div>
                                @if ($complaint->severity)
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $severityColors[$complaint->severity->level] }}">
                                        Niveau {{ $complaint->severity->level }}
                                    </span>
                                @endif
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun dossier en cours</p>
                        @endforelse
                    </div>
                </div>

            @elseif ($user->role === \App\Enums\UserRole::Mouche)

                {{-- KPI --}}
                <div class="grid grid-cols-3 gap-4 shrink-0">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-[#004fa3]">{{ $totalMissions }}</p>
                        <p class="text-xs text-gray-400 mt-1">Missions total</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                        <p class="text-2xl font-bold text-amber-500">{{ $pendingMissions->count() }}</p>
                        <p class="text-xs text-gray-400 mt-1">Rapport à soumettre</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                        @if ($avgScore !== null)
                            <p class="text-2xl font-bold text-emerald-600">{{ $avgScore }}<span class="text-sm font-normal text-gray-400">/5</span></p>
                        @else
                            <p class="text-2xl font-bold text-gray-300">—</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">Note moy. attribuée</p>
                    </div>
                </div>

                {{-- Missions en attente --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <p class="text-base font-semibold text-gray-700">Missions à traiter</p>
                        <a href="{{ route('mouche.dashboard') }}" class="text-xs text-[#004fa3] hover:text-[#003d80] font-medium">Voir tableau de bord →</a>
                    </div>
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        @forelse ($pendingMissions as $mission)
                            <div class="px-5 py-3 flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Mission de contrôle</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Assignée le {{ $mission->created_at->format('d/m/Y') }}</p>
                                </div>
                                <a href="{{ route('rapport.create', $mission) }}"
                                   class="shrink-0 text-xs font-semibold px-3 py-1.5 bg-[#004fa3] text-white rounded-lg hover:bg-[#003d80] transition">
                                    Remplir →
                                </a>
                            </div>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucune mission en attente.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Rapports soumis --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 shrink-0">
                        <p class="text-base font-semibold text-gray-700">Rapports soumis <span class="text-[#004fa3]">{{ $submittedMissions->count() }}</span></p>
                    </div>
                    @if ($submittedMissions->isEmpty())
                        <p class="px-5 py-6 text-sm text-gray-400 text-center">Aucun rapport soumis.</p>
                    @else
                        <div class="overflow-y-auto flex-1">
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
                                            <td class="px-5 py-3 text-gray-700">{{ $mission->created_at->format('d/m/Y') }}</td>
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
                        </div>
                    @endif
                </div>

            @else
                {{-- Avocat et autres rôles --}}
                <div class="flex-1 flex items-center justify-center">
                    <p class="text-gray-400 text-sm">Aucun contenu disponible pour ce rôle.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
