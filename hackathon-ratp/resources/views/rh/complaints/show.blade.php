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

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <x-complaint-detail :complaint="$complaint" />

        @if ($drivers->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-6">
                <h2 class="text-sm font-semibold text-amber-600 uppercase tracking-wide mb-1">Chauffeur non identifié</h2>
                <p class="text-sm text-gray-500 mb-4">Le chauffeur concerné par cet incident n'a pas pu être déterminé automatiquement. Identifiez-le manuellement.</p>
                <form method="POST" action="{{ route('complaints.identify-driver', $complaint) }}" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label for="driver_id" class="block text-xs font-medium text-gray-600 mb-1">Chauffeur concerné</label>
                        <select id="driver_id" name="driver_id" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm">
                            <option value="">-- Sélectionner un chauffeur --</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->last_name }} {{ $driver->first_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('driver_id')" class="mt-2" />
                    </div>
                    <button type="submit"
                            class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition shadow-sm whitespace-nowrap">
                        Identifier
                    </button>
                </form>
            </div>
        @endif

        @if ($complaint->step->value === 'RHReview')
            @if ($complaint->rh_user_id === null)
                <div class="bg-white rounded-2xl shadow-sm border border-[#004fa3]/20 p-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-medium text-gray-900">Ce dossier est disponible</p>
                        <p class="text-sm text-gray-500 mt-0.5">
                            @if ($complaint->negative === false)
                                Prenez-le en charge pour récompenser le chauffeur.
                            @else
                                Prenez-le en charge pour ouvrir la procédure disciplinaire.
                            @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('complaints.claim', $complaint) }}">
                        @csrf
                        <x-primary-button>Prendre en charge</x-primary-button>
                    </form>
                </div>
            @elseif ($complaint->rh_user_id === auth()->id())
                @if ($complaint->negative === false)
                    {{-- Signalement positif : gratification --}}
                    @if ($complaint->gratification === null)
                        <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 p-6">
                            <h2 class="text-sm font-semibold text-emerald-600 uppercase tracking-wide mb-5">Récompenser le chauffeur</h2>
                            <form method="POST" action="{{ route('complaints.gratify', $complaint) }}">
                                @csrf
                                <div class="mb-4">
                                    <x-input-label for="reason" value="Motif de la gratification" />
                                    <textarea id="reason" name="reason" rows="3" required
                                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                              placeholder="Décrivez la raison de cette récompense…">{{ old('reason') }}</textarea>
                                    <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                                </div>
                                <div class="mb-5">
                                    <x-input-label for="amount" value="Montant (€, optionnel)" />
                                    <input type="number" id="amount" name="amount" min="0" max="10000"
                                           value="{{ old('amount', 0) }}"
                                           class="mt-1 block w-40 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit"
                                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                                        Enregistrer la gratification
                                    </button>
                                    <form method="POST" action="{{ route('complaints.close', $complaint) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl border border-gray-300 transition">
                                            Clôturer sans gratification
                                        </button>
                                    </form>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-6">
                            <p class="text-sm font-semibold text-emerald-700 mb-1">Gratification enregistrée</p>
                            <p class="text-sm text-emerald-600">{{ $complaint->gratification->reason }}</p>
                            @if ($complaint->gratification->amount > 0)
                                <p class="mt-1 text-sm text-emerald-600 font-medium">{{ number_format($complaint->gratification->amount, 0, ',', ' ') }} €</p>
                            @endif
                        </div>
                    @endif
                @else
                    {{-- Signalement négatif : sanction --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-5">Action disciplinaire</h2>
                        <form method="POST" action="{{ route('complaints.close', $complaint) }}">
                            @csrf
                            <p class="text-sm text-gray-600 mb-4">
                                Clôturer sans sanction marquera la plainte comme <strong>aboutie</strong> et mettra fin à la procédure sans action disciplinaire.
                            </p>
                            <button type="submit"
                                    class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl border border-gray-300 transition">
                                Clôturer sans sanction
                            </button>
                        </form>
                    </div>

                    @if ($complaint->sanction === null)
                        <x-sanction-form :complaint="$complaint" />
                    @endif
                @endif
            @else
                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6">
                    <p class="text-sm text-gray-500">
                        Ce dossier est pris en charge par
                        <span class="font-medium text-gray-700">{{ $complaint->rhAgent->first_name }} {{ $complaint->rhAgent->last_name }}</span>.
                    </p>
                </div>
            @endif
        @endif

    </div>
</x-app-layout>
