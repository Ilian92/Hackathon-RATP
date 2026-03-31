<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('com.complaints.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900">Plainte #{{ $complaint->id }}</h1>
            @php
                $statusColors = [
                    'EnCours' => 'bg-yellow-100 text-yellow-700',
                    'Clos'    => 'bg-gray-100 text-gray-500',
                    'Abouti'  => 'bg-red-100 text-red-700',
                ];
            @endphp
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusColors[$complaint->status->value] }}">
                {{ $complaint->status->label() }}
            </span>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="rounded-xl bg-[#4bc0ad]/10 border border-[#4bc0ad]/30 p-4 text-sm text-[#38a090] flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Informations de la plainte --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide">Détails de la plainte</h2>

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
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Signalement reçu le</p>
                    <p class="mt-1 font-medium text-gray-800">{{ $complaint->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Description</p>
                <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4">{{ $complaint->description }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm pt-1">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Chauffeur concerné</p>
                    @if ($complaint->driver)
                        <p class="mt-1 font-medium text-gray-800">
                            {{ $complaint->driver->first_name }} {{ $complaint->driver->last_name }}
                        </p>
                        <p class="text-xs text-gray-400 font-mono">{{ $complaint->driver->matricule }}</p>
                    @else
                        <p class="mt-1 text-gray-400">Non identifié</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Usager</p>
                    <p class="mt-1 font-medium text-gray-800">{{ $complaint->client->email }}</p>
                </div>
            </div>
        </div>

        {{-- Changer le statut --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">Statut du dossier</h2>

            <form method="POST" action="{{ route('com.complaints.status', $complaint) }}" class="flex items-center gap-3">
                @csrf
                @method('PATCH')
                <select name="status"
                        class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3]">
                    @foreach (\App\Enums\ComplaintStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected($complaint->status === $case)>
                            {{ $case->label() }}
                        </option>
                    @endforeach
                </select>
                <x-primary-button>Mettre à jour</x-primary-button>
            </form>
        </div>

        {{-- Gravité --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide">Évaluation de la gravité</h2>
                @if ($complaint->severity)
                    <span class="text-xs text-gray-400">
                        Évaluée par {{ $complaint->severity->evaluator->first_name }} {{ $complaint->severity->evaluator->last_name }}
                        le {{ $complaint->severity->updated_at->format('d/m/Y') }}
                    </span>
                @endif
            </div>

            @php
                $severityLabels = [
                    0 => ['label' => 'Négligeable', 'desc' => 'Incident mineur sans impact notable',          'color' => 'border-green-200 bg-green-50 text-green-700'],
                    1 => ['label' => 'Faible',       'desc' => 'Désagrément léger pour l\'usager',             'color' => 'border-lime-200 bg-lime-50 text-lime-700'],
                    2 => ['label' => 'Modérée',      'desc' => 'Impact réel, nécessite un suivi',              'color' => 'border-yellow-200 bg-yellow-50 text-yellow-700'],
                    3 => ['label' => 'Grave',        'desc' => 'Mise en cause sérieuse du comportement',       'color' => 'border-orange-200 bg-orange-50 text-orange-700'],
                    4 => ['label' => 'Critique',     'desc' => 'Danger ou atteinte grave aux usagers',         'color' => 'border-red-200 bg-red-50 text-red-700'],
                ];
                $currentLevel = $complaint->severity?->level;
            @endphp

            <form method="POST" action="{{ route('com.complaints.severity', $complaint) }}" x-data="{ level: {{ $currentLevel ?? 'null' }} }">
                @csrf

                {{-- Sélecteur de niveau --}}
                <div class="grid grid-cols-5 gap-2 mb-5">
                    @foreach ($severityLabels as $level => $info)
                        <label class="cursor-pointer">
                            <input type="radio" name="level" value="{{ $level }}"
                                   x-model.number="level"
                                   class="sr-only">
                            <div class="border-2 rounded-xl p-3 text-center transition"
                                 :class="level === {{ $level }}
                                     ? '{{ $info['color'] }} border-current shadow-sm'
                                     : 'border-gray-200 hover:border-gray-300 text-gray-500'">
                                <p class="text-xl font-bold">{{ $level }}</p>
                                <p class="text-xs font-semibold mt-1">{{ $info['label'] }}</p>
                                <p class="text-xs mt-1 opacity-70 hidden sm:block">{{ $info['desc'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('level')" class="mb-3" />

                {{-- Justification --}}
                <div class="mb-5">
                    <x-input-label for="justification" value="Justification" />
                    <textarea id="justification" name="justification" rows="4" required
                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm"
                              placeholder="Expliquez pourquoi ce niveau de gravité a été attribué…">{{ old('justification', $complaint->severity?->justification) }}</textarea>
                    <x-input-error :messages="$errors->get('justification')" class="mt-2" />
                </div>

                <x-primary-button x-bind:disabled="level === null">
                    {{ $complaint->severity ? 'Modifier l\'évaluation' : 'Enregistrer l\'évaluation' }}
                </x-primary-button>
            </form>
        </div>

    </div>
</x-app-layout>
