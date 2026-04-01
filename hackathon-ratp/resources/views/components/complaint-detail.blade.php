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
                <p class="mt-1 font-medium text-gray-800">{{ $complaint->driver->first_name }} {{ $complaint->driver->last_name }}</p>
                <p class="text-xs text-gray-400 font-mono">{{ $complaint->driver->matricule }}</p>
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
            <p class="text-xs text-gray-400 uppercase tracking-wide">Évaluation Com</p>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $severityColors[$complaint->severity->level] }}">
                Niveau {{ $complaint->severity->level }} — {{ $severityLabels[$complaint->severity->level] }}
            </span>
        </div>
        <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4">{{ $complaint->severity->justification }}</p>
        <p class="mt-2 text-xs text-gray-400">
            Par {{ $complaint->severity->evaluator->first_name }} {{ $complaint->severity->evaluator->last_name }}
            le {{ $complaint->severity->updated_at->format('d/m/Y') }}
        </p>
    </div>
@endif
