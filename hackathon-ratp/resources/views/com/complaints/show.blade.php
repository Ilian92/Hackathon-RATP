<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('complaints.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900">Plainte #{{ $complaint->id }}</h1>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="rounded-xl bg-[#4bc0ad]/10 border border-[#4bc0ad]/30 p-4 text-sm text-[#38a090] flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="rounded-xl bg-orange-50 border border-orange-200 p-4 text-sm text-orange-700 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ session('warning') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <x-complaint-detail :complaint="$complaint" />

        @php $isMyDossier = $complaint->com_user_id === auth()->id(); @endphp

        {{-- Prise en charge --}}
        @if ($complaint->step->value === 'ComReview' && $complaint->com_user_id === null)
            <div class="bg-white rounded-2xl shadow-sm border border-[#004fa3]/20 p-6 flex items-center justify-between gap-4">
                <div>
                    <p class="font-medium text-gray-900">Ce dossier est disponible</p>
                    <p class="text-sm text-gray-500 mt-0.5">Prenez-le en charge pour l'évaluer. Il vous sera exclusivement attribué.</p>
                </div>
                <form method="POST" action="{{ route('complaints.claim', $complaint) }}">
                    @csrf
                    <x-primary-button>Prendre en charge</x-primary-button>
                </form>
            </div>
        @elseif ($complaint->step->value === 'ComReview' && !$isMyDossier)
            <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">
                    Ce dossier est pris en charge par
                    <span class="font-medium text-gray-700">{{ $complaint->comAgent->first_name }} {{ $complaint->comAgent->last_name }}</span>.
                </p>
            </div>
        @endif

        {{-- Formulaire d'évaluation (uniquement si c'est mon dossier et pas encore transmis) --}}
        @if ($isMyDossier && $complaint->step->value === 'ComReview')
            @php
                $severityLabels = [
                    0 => ['label' => 'Négligeable', 'desc' => 'Dossier annulé',              'color' => 'border-green-200 bg-green-50 text-green-700'],
                    1 => ['label' => 'Faible',       'desc' => 'Transmis au Manager',         'color' => 'border-lime-200 bg-lime-50 text-lime-700'],
                    2 => ['label' => 'Modérée',      'desc' => 'Transmis au Manager',         'color' => 'border-yellow-200 bg-yellow-50 text-yellow-700'],
                    3 => ['label' => 'Grave',        'desc' => 'Transmis directement au RH',  'color' => 'border-orange-200 bg-orange-50 text-orange-700'],
                    4 => ['label' => 'Critique',     'desc' => 'Transmis directement au RH',  'color' => 'border-red-200 bg-red-50 text-red-700'],
                ];
                $currentLevel = $complaint->severity?->level;
                $hasSubstituteManagers = $substituteManagers->isNotEmpty();
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-5">Évaluation de la gravité</h2>

                <form method="POST" action="{{ route('complaints.severity', $complaint) }}" x-data="{ level: {{ $currentLevel ?? 'null' }} }">
                    @csrf

                    <div class="grid grid-cols-5 gap-2 mb-5">
                        @foreach ($severityLabels as $level => $info)
                            <label class="cursor-pointer">
                                <input type="radio" name="level" value="{{ $level }}" x-model.number="level" class="sr-only">
                                <div class="border-2 rounded-xl p-3 text-center transition"
                                     :class="level === {{ $level }} ? '{{ $info['color'] }} border-current shadow-sm' : 'border-gray-200 hover:border-gray-300 text-gray-500'">
                                    <p class="text-xl font-bold">{{ $level }}</p>
                                    <p class="text-xs font-semibold mt-1">{{ $info['label'] }}</p>
                                    <p class="text-xs mt-1 opacity-70 hidden sm:block">{{ $info['desc'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('level')" class="mb-3" />

                    <div class="mb-5">
                        <x-input-label for="justification" value="Justification" />
                        <textarea id="justification" name="justification" rows="4" required
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm"
                                  placeholder="Expliquez pourquoi ce niveau de gravité a été attribué…">{{ old('justification', $complaint->severity?->justification) }}</textarea>
                        <x-input-error :messages="$errors->get('justification')" class="mt-2" />
                    </div>

                    @if ($hasSubstituteManagers)
                        <div x-show="level === 1 || level === 2" x-cloak class="mb-5 rounded-xl border border-orange-200 bg-orange-50 p-4">
                            <p class="text-xs font-semibold text-orange-700 uppercase tracking-wide mb-1">Manager habituel indisponible</p>
                            <p class="text-xs text-orange-600 mb-3">Le manager du chauffeur n'est pas actif. Sélectionnez un manager de remplacement pour ce dossier.</p>
                            <label for="manager_id" class="block text-xs font-medium text-gray-600 mb-1">Manager de remplacement</label>
                            <select id="manager_id" name="manager_id"
                                    x-bind:required="level === 1 || level === 2"
                                    class="block w-full rounded-lg border-orange-200 bg-white shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm">
                                <option value="">-- Sélectionner un manager --</option>
                                @foreach ($substituteManagers as $manager)
                                    <option value="{{ $manager->id }}" @selected(old('manager_id') == $manager->id)>
                                        {{ $manager->first_name }} {{ $manager->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('manager_id')" class="mt-2" />
                        </div>
                    @endif

                    <div x-show="level !== null" x-cloak>
                        <p class="text-xs text-gray-500 mb-3">
                            <span x-show="level === 0">Le dossier sera <strong>annulé</strong>.</span>
                            @if ($hasSubstituteManagers)
                                <span x-show="level === 1 || level === 2">Le dossier sera transmis au <strong>manager de remplacement</strong> sélectionné.</span>
                            @else
                                <span x-show="level === 1 || level === 2">Le dossier sera transmis au <strong>Manager</strong> responsable du chauffeur.</span>
                            @endif
                            <span x-show="level === 3 || level === 4">Le dossier sera transmis directement au <strong>service RH</strong>. Le Manager sera notifié.</span>
                        </p>
                    </div>

                    <x-primary-button x-bind:disabled="level === null">
                        Enregistrer et transmettre
                    </x-primary-button>
                </form>
            </div>
        @endif

    </div>
</x-app-layout>
