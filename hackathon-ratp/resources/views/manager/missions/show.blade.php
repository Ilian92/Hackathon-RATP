<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('missions.index') }}" class="text-gray-400 hover:text-gray-600 transition">←</a>
            <h1 class="text-lg font-semibold text-gray-900">
                Mission mouche — {{ $mission->driver?->first_name }} {{ $mission->driver?->last_name }}
            </h1>
        </div>
    </x-slot>

    @php
        $statusColors = [
            'EnCours'   => 'bg-blue-100 text-blue-700',
            'Completee' => 'bg-amber-100 text-amber-700',
            'Decidee'   => 'bg-gray-100 text-gray-600',
        ];
        $statusLabels = [
            'EnCours'   => 'En cours',
            'Completee' => 'À décider',
            'Decidee'   => 'Décidée',
        ];
        $criteriaLabels = [
            'ponctualite'    => 'Ponctualité',
            'conduite'       => 'Qualité de conduite',
            'politesse'      => 'Politesse',
            'tenue'          => 'Tenue & présentation',
            'securite'       => 'Sécurité',
            'gestion_conflit' => 'Gestion de conflit',
        ];
        $statusKey = $mission->status->value;
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- En-tête mission --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Chauffeur contrôlé</p>
                    <p class="text-xl font-bold text-gray-900">
                        {{ $mission->driver?->first_name }} {{ $mission->driver?->last_name }}
                    </p>
                    <p class="text-xs text-gray-500">Mission créée le {{ $mission->created_at->format('d/m/Y') }}</p>
                </div>
                <span class="text-sm font-semibold px-3 py-1 rounded-full {{ $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $statusLabels[$statusKey] ?? $statusKey }}
                </span>
            </div>

            {{-- Progression --}}
            @php
                $submitted = $mission->mouches->whereNotNull('pivot.submitted_at')->count();
                $total = $mission->mouches->count();
            @endphp
            <div class="mt-5 pt-5 border-t border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">Rapports reçus</p>
                    <p class="text-xs font-semibold text-gray-700">{{ $submitted }} / {{ $total }}</p>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full bg-[#004fa3] transition-all"
                         style="width: {{ $total > 0 ? round($submitted / $total * 100) : 0 }}%"></div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($mission->mouches as $mouche)
                        <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full
                            {{ $mouche->pivot->submitted_at ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            <span class="{{ $mouche->pivot->submitted_at ? 'text-emerald-500' : 'text-gray-400' }}">
                                {{ $mouche->pivot->submitted_at ? '✓' : '○' }}
                            </span>
                            {{ $mouche->first_name }} {{ $mouche->last_name }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Décision finale --}}
            @if ($mission->status->value === 'Decidee')
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Décision du manager</p>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold px-3 py-1 rounded-full
                            {{ $mission->decision?->value === 'Sanctionne' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $mission->decision?->label() ?? '—' }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $mission->decided_at?->format('d/m/Y') }}</span>
                    </div>
                    @if ($mission->manager_notes)
                        <p class="mt-3 text-sm text-gray-700 bg-gray-50 rounded-xl p-3">{{ $mission->manager_notes }}</p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Rapports des mouches --}}
        @if ($mission->rapports->isNotEmpty())
            <div class="space-y-4">
                <h2 class="text-sm font-semibold text-gray-700">Rapports soumis ({{ $mission->rapports->count() }})</h2>
                @foreach ($mission->rapports as $rapport)
                    @php $avg = $rapport->averageScore(); @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $rapport->mouche?->first_name }} {{ $rapport->mouche?->last_name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Observé le {{ $rapport->date_observation->format('d/m/Y') }}
                                    @if ($rapport->ligne) · Ligne {{ $rapport->ligne->nom }} @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold {{ $avg >= 4 ? 'text-emerald-600' : ($avg >= 3 ? 'text-amber-500' : 'text-red-600') }}">
                                    {{ $avg }}<span class="text-sm font-normal text-gray-400">/5</span>
                                </p>
                                <p class="text-xs text-gray-400">Note moyenne</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($criteriaLabels as $key => $label)
                                @php $score = $rapport->$key; @endphp
                                @if ($score !== null)
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-500 mb-1">{{ $label }}</p>
                                        <div class="flex items-center gap-1.5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <div class="w-full h-2 rounded-full {{ $i <= $score ? ($score >= 4 ? 'bg-emerald-400' : ($score >= 3 ? 'bg-amber-400' : 'bg-red-400')) : 'bg-gray-200' }}"></div>
                                            @endfor
                                            <span class="text-xs font-bold text-gray-700 ml-1 shrink-0">{{ $score }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if ($rapport->observation)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-400 mb-1">Observation</p>
                                <p class="text-sm text-gray-700">{{ $rapport->observation }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-10 text-center text-gray-400 text-sm">
                Aucun rapport soumis pour le moment.
            </div>
        @endif

        {{-- Formulaire de décision --}}
        @if ($mission->status->value === 'Completee')
            <div class="bg-white rounded-2xl border border-amber-200 shadow-sm p-6" x-data="{ decision: '' }">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Prendre une décision</h2>
                <p class="text-xs text-gray-400 mb-5">Tous les rapports sont reçus. Clôturez ou appliquez une sanction.</p>

                <form method="POST" action="{{ route('missions.decide', $mission) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition"
                               :class="decision === 'Cloture' ? 'border-emerald-400 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="decision" value="Cloture" x-model="decision" class="sr-only" required>
                            <span class="text-2xl">✓</span>
                            <span class="text-sm font-semibold text-gray-700">Classé sans suite</span>
                            <span class="text-xs text-gray-400 text-center">Aucune action disciplinaire</span>
                        </label>
                        <label class="flex flex-col items-center gap-2 p-4 rounded-xl border-2 cursor-pointer transition"
                               :class="decision === 'Sanctionne' ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="decision" value="Sanctionne" x-model="decision" class="sr-only">
                            <span class="text-2xl">⚠</span>
                            <span class="text-sm font-semibold text-gray-700">Appliquer une sanction</span>
                            <span class="text-xs text-gray-400 text-center">Sanction disciplinaire</span>
                        </label>
                    </div>

                    @error('decision')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    <div x-show="decision === 'Sanctionne'" x-cloak class="space-y-3">
                        <div>
                            <label for="sanction_type" class="block text-xs font-medium text-gray-600 mb-1.5">
                                Type de sanction <span class="text-red-500">*</span>
                            </label>
                            <select id="sanction_type" name="sanction_type"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/40">
                                <option value="">— Choisir —</option>
                                <option value="Avertissement">Avertissement</option>
                                <option value="Blâme">Blâme</option>
                                <option value="Mise à pied">Mise à pied</option>
                                <option value="Rétrogradation">Rétrogradation</option>
                                <option value="Licenciement">Licenciement</option>
                            </select>
                            @error('sanction_type')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sanction_description" class="block text-xs font-medium text-gray-600 mb-1.5">Description</label>
                            <textarea id="sanction_description" name="sanction_description" rows="2"
                                      class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/40 resize-none"
                                      placeholder="Motif de la sanction…">{{ old('sanction_description') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <label for="manager_notes" class="block text-xs font-medium text-gray-600 mb-1.5">Notes du manager</label>
                        <textarea id="manager_notes" name="manager_notes" rows="3"
                                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/40 resize-none"
                                  placeholder="Synthèse ou remarques…">{{ old('manager_notes') }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                :disabled="!decision"
                                class="px-5 py-2 bg-[#004fa3] text-white text-sm font-semibold rounded-lg hover:bg-[#003d80] transition disabled:opacity-40 disabled:cursor-not-allowed">
                            Valider la décision
                        </button>
                    </div>
                </form>
            </div>
        @endif

    </div>
</x-app-layout>
