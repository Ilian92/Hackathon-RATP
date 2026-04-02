<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mouche.dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">←</a>
            <h1 class="text-lg font-semibold text-gray-900">Rapport de mission</h1>
        </div>
    </x-slot>

    @php
        $criteria = [
            'ponctualite'    => ['label' => 'Ponctualité', 'help' => 'Le bus respectait-il les horaires et les arrêts prévus ?'],
            'conduite'       => ['label' => 'Qualité de conduite', 'help' => 'Conduite souple, freinage, accélération, sécurité générale.'],
            'politesse'      => ['label' => 'Politesse & accueil', 'help' => 'Attitude envers les passagers, réponses aux questions.'],
            'tenue'          => ['label' => 'Tenue & présentation', 'help' => 'Uniforme, badge, présentation générale du chauffeur.'],
            'securite'       => ['label' => 'Sécurité', 'help' => 'Fermeture des portes, annonces, respect des règles de sécurité.'],
        ];
        $scaleLabels = [1 => 'Très mauvais', 2 => 'Mauvais', 3 => 'Correct', 4 => 'Bien', 5 => 'Excellent'];
    @endphp

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <form method="POST" action="{{ route('rapport.store', $mission) }}" class="space-y-6">
            @csrf

            {{-- Informations de base --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-800">Informations de l'observation</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="date_observation" class="block text-xs font-medium text-gray-600 mb-1.5">
                            Date d'observation <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date_observation" name="date_observation"
                               value="{{ old('date_observation', now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}"
                               required
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/40">
                        @error('date_observation')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ligne_id" class="block text-xs font-medium text-gray-600 mb-1.5">Ligne observée</label>
                        <select id="ligne_id" name="ligne_id"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/40">
                            <option value="">— Optionnel —</option>
                            @foreach ($lignes as $ligne)
                                <option value="{{ $ligne->id }}" {{ old('ligne_id') == $ligne->id ? 'selected' : '' }}>
                                    {{ $ligne->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Critères d'évaluation --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
                <h2 class="text-sm font-semibold text-gray-800">Évaluation par critère</h2>

                @foreach ($criteria as $key => $meta)
                    <div>
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $meta['label'] }} <span class="text-red-500">*</span></p>
                                <p class="text-xs text-gray-400">{{ $meta['help'] }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            @foreach ($scaleLabels as $value => $scaleLabel)
                                <label class="flex-1 text-center cursor-pointer" title="{{ $scaleLabel }}">
                                    <input type="radio" name="{{ $key }}" value="{{ $value }}"
                                           {{ old($key) == $value ? 'checked' : '' }}
                                           required class="sr-only peer">
                                    <div class="py-2 rounded-lg border-2 text-sm font-bold transition
                                        peer-checked:border-[#004fa3] peer-checked:bg-[#004fa3] peer-checked:text-white
                                        border-gray-200 text-gray-400 hover:border-[#004fa3]/50 hover:text-[#004fa3]">
                                        {{ $value }}
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 hidden sm:block">{{ $scaleLabel }}</p>
                                </label>
                            @endforeach
                        </div>
                        @error($key)
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                {{-- Gestion de conflit — optionnel --}}
                <div>
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-sm font-medium text-gray-800">Gestion de conflit <span class="text-gray-400 font-normal">(si applicable)</span></p>
                            <p class="text-xs text-gray-400">Y a-t-il eu un incident ou un conflit pendant l'observation ?</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @foreach ($scaleLabels as $value => $scaleLabel)
                            <label class="flex-1 text-center cursor-pointer" title="{{ $scaleLabel }}">
                                <input type="radio" name="gestion_conflit" value="{{ $value }}"
                                       {{ old('gestion_conflit') == $value ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="py-2 rounded-lg border-2 text-sm font-bold transition
                                    peer-checked:border-[#004fa3] peer-checked:bg-[#004fa3] peer-checked:text-white
                                    border-gray-200 text-gray-400 hover:border-[#004fa3]/50 hover:text-[#004fa3]">
                                    {{ $value }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Observation libre --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-3">
                <h2 class="text-sm font-semibold text-gray-800">Observation libre</h2>
                <textarea name="observation" rows="5"
                          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/40 resize-none"
                          placeholder="Décrivez ce que vous avez observé, des faits précis, le contexte, etc.">{{ old('observation') }}</textarea>
                @error('observation')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('mouche.dashboard') }}"
                   class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">
                    Annuler
                </a>
                <button type="submit"
                        class="px-5 py-2 bg-[#004fa3] text-white text-sm font-semibold rounded-lg hover:bg-[#003d80] transition">
                    Soumettre le rapport
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
