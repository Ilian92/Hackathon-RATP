@props(['complaint'])

@php
    $severityColors = [
        0 => 'bg-green-100 text-green-700',
        1 => 'bg-lime-100 text-lime-700',
        2 => 'bg-yellow-100 text-yellow-700',
        3 => 'bg-orange-100 text-orange-700',
        4 => 'bg-red-100 text-red-700',
    ];
    $severityLabels = [0 => 'Négligeable', 1 => 'Faible', 2 => 'Modérée', 3 => 'Grave', 4 => 'Critique'];
    $stepColors = [
        'ComReview'     => 'bg-blue-100 text-blue-700',
        'ManagerReview' => 'bg-purple-100 text-purple-700',
        'RHReview'      => 'bg-orange-100 text-orange-700',
        'Closed'        => 'bg-gray-100 text-gray-500',
    ];
@endphp

{{-- Fil d'avancement --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <p class="text-xs text-gray-400 uppercase tracking-wide mb-4">Avancement du dossier</p>
    <div class="flex items-center gap-0">
        @foreach (['ComReview' => 'Com', 'ManagerReview' => 'Manager', 'RHReview' => 'RH', 'Closed' => 'Clôturé'] as $stepValue => $stepLabel)
            @php
                $steps = ['ComReview', 'ManagerReview', 'RHReview', 'Closed'];
                $currentIndex = array_search($complaint->step->value, $steps);
                $thisIndex = array_search($stepValue, $steps);
                $isDone = $thisIndex < $currentIndex;
                $isCurrent = $stepValue === $complaint->step->value;
            @endphp
            <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $isCurrent ? 'bg-[#004fa3] text-white ring-4 ring-[#004fa3]/20' : ($isDone ? 'bg-[#4bc0ad] text-white' : 'bg-gray-100 text-gray-400') }}">
                        @if ($isDone)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $loop->index + 1 }}
                        @endif
                    </div>
                    <p class="text-xs mt-1 font-medium {{ $isCurrent ? 'text-[#004fa3]' : ($isDone ? 'text-[#4bc0ad]' : 'text-gray-400') }}">
                        {{ $stepLabel }}
                    </p>
                </div>
                @if (!$loop->last)
                    <div class="flex-1 h-0.5 mx-2 mb-4 {{ $isDone ? 'bg-[#4bc0ad]' : 'bg-gray-200' }}"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Détails --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
    <p class="text-xs text-gray-400 uppercase tracking-wide">Détails de la plainte</p>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Type</p>
            <p class="mt-1 font-medium text-gray-800">{{ $complaint->complaintType->name }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Bus</p>
            <p class="mt-1 font-mono font-medium text-gray-800">{{ $complaint->bus->code }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Date de l'incident</p>
            <p class="mt-1 font-medium text-gray-800">{{ $complaint->incident_time->format('d/m/Y à H:i') }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Reçue le</p>
            <p class="mt-1 font-medium text-gray-800">{{ $complaint->created_at->format('d/m/Y') }}</p>
        </div>
    </div>

    <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Description</p>
        <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4">{{ $complaint->description }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Chauffeur concerné</p>
            @if ($complaint->driver)
                @if (in_array(auth()->user()->role, [\App\Enums\UserRole::Manager, \App\Enums\UserRole::RH]))
                    <p class="mt-1 font-medium text-gray-800">{{ $complaint->driver->first_name }} {{ $complaint->driver->last_name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $complaint->driver->matricule }}</p>
                @else
                    <p class="mt-1 text-sm text-gray-400 italic">Identifié — confidentiel</p>
                @endif
            @else
                <p class="mt-1 text-gray-400">Non identifié</p>
            @endif
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wide">Usager</p>
            @if (auth()->user()->role === \App\Enums\UserRole::RH)
                <p class="mt-1 font-medium text-gray-800">{{ $complaint->client->email }}</p>
            @else
                <p class="mt-1 text-sm text-gray-400 italic">Confidentiel — accès RH uniquement</p>
            @endif
        </div>
    </div>
</div>

{{-- Évaluation Com --}}
@if ($complaint->severity)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Évaluation Com</p>
                @if ($complaint->negative === false)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                        </svg>
                        Positif
                    </span>
                @elseif ($complaint->negative === true)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-red-100 text-red-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v2a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                        </svg>
                        Négatif
                    </span>
                @endif
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $severityColors[$complaint->severity->level] }}">
                Niveau {{ $complaint->severity->level }} — {{ $severityLabels[$complaint->severity->level] }}
            </span>
        </div>
        <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4">{{ $complaint->severity->justification }}</p>
        <p class="mt-2 text-xs text-gray-400">
            @if ($complaint->severity->evaluator)
                Par {{ $complaint->severity->evaluator->first_name }} {{ $complaint->severity->evaluator->last_name }}
                le {{ $complaint->severity->updated_at->format('d/m/Y') }}
            @else
                Évalué par l'IA le {{ $complaint->severity->updated_at->format('d/m/Y') }}
            @endif
        </p>
    </div>
@endif
