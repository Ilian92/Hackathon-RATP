@props(['user', 'satisfactionStats'])

@php
    use App\Enums\ComplaintStatus;

    $avgNote = $satisfactionStats?->average ?? 0;
    $avgSur5 = $avgNote / 2;
    $totalAvis = $satisfactionStats?->total ?? 0;

    $aboutiesCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Abouti)->count();
    $totalComplaints = $user->complaints->count();

    // Score interne : 70% satisfaction (sur 5) + 30% absence de plaintes abouties (sur 5)
    $penalite = min($aboutiesCount, 5);
    $scoreInterne = round($avgSur5 * 0.7 + (5 - $penalite) * 0.3, 1);

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

<div class="space-y-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-[#004fa3]/10 flex items-center justify-center shrink-0">
                    <span class="text-2xl font-bold text-[#004fa3]">
                        {{ strtoupper(substr($user->first_name, 0, 1).substr($user->last_name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $user->role->value }}</p>
                    <p class="text-xs text-gray-400 font-mono mt-1">{{ $user->matricule ?? '—' }}</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $statusClass }}">
                {{ $user->status->label() }}
            </span>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Début de contrat</p>
                <p class="mt-1 font-medium text-gray-700">
                    {{ $user->contract_start_date?->format('d/m/Y') ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide">Manager</p>
                @if ($user->managers->isNotEmpty())
                    @foreach ($user->managers as $manager)
                        <p class="mt-1 font-medium text-gray-700">{{ $manager->first_name }} {{ $manager->last_name }}</p>
                        <p class="text-xs text-gray-400">{{ $manager->email }}</p>
                    @endforeach
                @else
                    <p class="mt-1 text-gray-400">—</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Score interne</p>
            <div class="flex items-end gap-2">
                <span class="text-4xl font-bold text-[#004fa3]">{{ number_format($scoreInterne, 1) }}</span>
                <span class="text-gray-400 text-lg mb-1">/ 5</span>
            </div>
            <div class="mt-3 h-2 rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full rounded-full bg-[#004fa3] transition-all"
                     style="width: {{ min($scoreInterne / 5 * 100, 100) }}%"></div>
            </div>
            <p class="mt-2 text-xs text-gray-400">70% satisfaction · 30% plaintes</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-3">Satisfaction usagers</p>
            <div class="flex items-end gap-2">
                <span class="text-4xl font-bold text-[#4bc0ad]">{{ number_format($avgSur5, 1) }}</span>
                <span class="text-gray-400 text-lg mb-1">/ 5</span>
            </div>
            <div class="mt-2 flex gap-0.5">
                @for ($i = 1; $i <= 5; $i++)
                    @php $fill = min(max($avgSur5 - ($i - 1), 0), 1); @endphp
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <defs>
                            <linearGradient id="star-fill-{{ $i }}">
                                <stop offset="{{ $fill * 100 }}%" stop-color="#4bc0ad"/>
                                <stop offset="{{ $fill * 100 }}%" stop-color="#e5e7eb"/>
                            </linearGradient>
                        </defs>
                        <path fill="url(#star-fill-{{ $i }})"
                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                @endfor
            </div>
            <p class="mt-2 text-xs text-gray-400">{{ $totalAvis }} avis</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Signalements</p>
            <div class="flex gap-3 text-xs">
                <span class="text-yellow-600 font-medium">
                    {{ $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::EnCours)->count() }} en cours
                </span>
                <span class="text-red-600 font-medium">
                    {{ $aboutiesCount }} aboutis
                </span>
                <span class="text-gray-400">
                    {{ $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Clos)->count() }} clos
                </span>
            </div>
        </div>

        @if ($user->complaints->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Aucun signalement</p>
        @else
            <div class="divide-y divide-gray-50">
                @foreach ($user->complaints->sortByDesc('incident_time')->take(5) as $complaint)
                    @php $sc = $complaintStatusColors[$complaint->status->value] ?? 'bg-gray-100 text-gray-500'; @endphp
                    <div class="py-3 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $complaint->complaintType->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Bus {{ $complaint->bus->code }} ·
                                {{ $complaint->incident_time->format('d/m/Y') }}
                            </p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $sc }}">
                            {{ $complaint->status->label() }}
                        </span>
                    </div>
                @endforeach
            </div>
            @if ($user->complaints->count() > 5)
                <p class="mt-2 text-xs text-center text-gray-400">
                    + {{ $user->complaints->count() - 5 }} autres signalements
                </p>
            @endif
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-4">Gratifications</p>

        @if ($user->gratifications->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Aucune gratification</p>
        @else
            <div class="divide-y divide-gray-50">
                @foreach ($user->gratifications->sortByDesc('awarded_at') as $gratification)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $gratification->reason }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $gratification->awarded_at->format('d/m/Y') }}</p>
                        </div>
                        <span class="text-sm font-bold text-[#4bc0ad] shrink-0">+{{ number_format($gratification->amount) }} €</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-sm">
                <span class="text-gray-500">Total</span>
                <span class="font-bold text-[#4bc0ad]">{{ number_format($user->gratifications->sum('amount')) }} €</span>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-4">Sanctions</p>

        @if ($user->sanctions->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Aucune sanction</p>
        @else
            <div class="divide-y divide-gray-50">
                @foreach ($user->sanctions->sortByDesc('sanctioned_at') as $sanction)
                    <div class="py-3 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $sanction->description }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $sanction->sanctioned_at->format('d/m/Y') }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 bg-red-50 text-red-600">
                            {{ $sanction->type }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
