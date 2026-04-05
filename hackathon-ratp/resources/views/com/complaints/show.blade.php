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

        @if ($isMyDossier && $complaint->step->value === 'ComReview')
            @php
                $currentLevel = $complaint->severity?->level;
                $currentNegative = $complaint->negative;
                $hasSubstituteManagers = $substituteManagers->isNotEmpty();
                $initialNegative = $currentNegative === null ? 'null' : ($currentNegative ? 'true' : 'false');
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
                 x-data="{ level: {{ $currentLevel ?? 'null' }}, negative: {{ $initialNegative }} }">

                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-5">Qualification du signalement</h2>

                <div class="mb-6">
                    <p class="text-xs font-medium text-gray-600 mb-2">Nature du signalement</p>
                    <div class="flex gap-3">
                        <button type="button" @click="negative = false"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-semibold transition"
                                :class="negative === false ? 'border-emerald-400 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                            </svg>
                            Signalement positif
                        </button>
                        <button type="button" @click="negative = true"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-semibold transition"
                                :class="negative === true ? 'border-red-400 bg-red-50 text-red-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v2a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                            </svg>
                            Signalement négatif
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('complaints.severity', $complaint) }}">
                    @csrf
                    <input type="hidden" name="negative" :value="negative === null ? '' : (negative ? '1' : '0')">

                    <div class="mb-2">
                        <p class="text-xs font-medium text-gray-600">
                            <span x-show="negative === false">Intensité du signalement positif</span>
                            <span x-show="negative === true || negative === null">Niveau de gravité</span>
                        </p>
                    </div>

                    <div class="grid grid-cols-5 gap-2 mb-5">
                        @php
                            $levels = [
                                0 => ['negLabel' => 'Annuler',   'posLabel' => '',         'negColor' => 'border-green-200 bg-green-50 text-green-700', 'posColor' => 'border-green-200 bg-green-50 text-green-700'],
                                1 => ['negLabel' => 'Faible',    'posLabel' => 'Léger',    'negColor' => 'border-lime-200 bg-lime-50 text-lime-700',   'posColor' => 'border-lime-200 bg-lime-50 text-lime-700'],
                                2 => ['negLabel' => 'Modérée',   'posLabel' => 'Bien',     'negColor' => 'border-yellow-200 bg-yellow-50 text-yellow-700', 'posColor' => 'border-yellow-200 bg-yellow-50 text-yellow-700'],
                                3 => ['negLabel' => 'Grave',     'posLabel' => 'Très bien','negColor' => 'border-orange-200 bg-orange-50 text-orange-700', 'posColor' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                                4 => ['negLabel' => 'Critique',  'posLabel' => 'Excellent','negColor' => 'border-red-200 bg-red-50 text-red-700',       'posColor' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                            ];
                        @endphp
                        @foreach ($levels as $lvl => $info)
                            <label class="cursor-pointer">
                                <input type="radio" name="level" value="{{ $lvl }}" x-model.number="level" class="sr-only"
                                       @if ($lvl === 0 && $currentNegative === false) disabled @endif>
                                <div class="border-2 rounded-xl p-3 text-center transition"
                                     :class="[
                                         level === {{ $lvl }} ? (negative === false ? '{{ $info['posColor'] }} border-current shadow-sm' : '{{ $info['negColor'] }} border-current shadow-sm') : 'border-gray-200 hover:border-gray-300 text-gray-500',
                                         {{ $lvl }} === 0 && negative === false ? 'opacity-30 pointer-events-none' : ''
                                     ]">
                                    <p class="text-xl font-bold">{{ $lvl }}</p>
                                    <p class="text-xs font-semibold mt-1">
                                        <span x-show="negative === false">{{ $lvl === 0 ? '–' : $info['posLabel'] }}</span>
                                        <span x-show="negative !== false">{{ $info['negLabel'] }}</span>
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('level')" class="mb-3" />

                    <div class="mb-5">
                        <x-input-label for="justification" value="Justification" />
                        <textarea id="justification" name="justification" rows="4" required
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm"
                                  placeholder="Expliquez votre évaluation…">{{ old('justification', $complaint->severity?->justification) }}</textarea>
                        <x-input-error :messages="$errors->get('justification')" class="mt-2" />
                    </div>

                    @if ($hasSubstituteManagers)
                        <div x-show="negative !== false && (level === 1 || level === 2)" x-cloak class="mb-5 rounded-xl border border-orange-200 bg-orange-50 p-4">
                            <p class="text-xs font-semibold text-orange-700 uppercase tracking-wide mb-1">Manager habituel indisponible</p>
                            <p class="text-xs text-orange-600 mb-3">Le manager du chauffeur n'est pas actif. Sélectionnez un manager de remplacement pour ce dossier.</p>
                            <label for="manager_id" class="block text-xs font-medium text-gray-600 mb-1">Manager de remplacement</label>
                            <select id="manager_id" name="manager_id"
                                    x-bind:required="negative !== false && (level === 1 || level === 2)"
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

                    <div x-show="level !== null && negative !== null" x-cloak>
                        <p class="text-xs text-gray-500 mb-3">
                            <span x-show="level === 0 && negative !== false">Le dossier sera <strong>annulé</strong>.</span>
                            <span x-show="negative === false && level !== null && level > 0">Le dossier sera transmis directement au <strong>service RH</strong> pour récompenser le chauffeur.</span>
                            @if ($hasSubstituteManagers)
                                <span x-show="negative !== false && (level === 1 || level === 2)">Le dossier sera transmis au <strong>manager de remplacement</strong> sélectionné.</span>
                            @else
                                <span x-show="negative !== false && (level === 1 || level === 2)">Le dossier sera transmis au <strong>Manager</strong> responsable du chauffeur.</span>
                            @endif
                            <span x-show="negative !== false && (level === 3 || level === 4)">Le dossier sera transmis directement au <strong>service RH</strong>.</span>
                        </p>
                    </div>

                    <x-primary-button x-bind:disabled="level === null || negative === null">
                        Enregistrer et transmettre
                    </x-primary-button>
                </form>
            </div>
        @endif

    </div>
</x-app-layout>
