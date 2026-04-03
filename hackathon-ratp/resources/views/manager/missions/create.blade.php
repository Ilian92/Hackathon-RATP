<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('missions.index') }}" class="text-gray-400 hover:text-gray-600 transition">←</a>
            <h1 class="text-lg font-semibold text-gray-900">Nouvelle mission mouche</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <form method="POST" action="{{ route('missions.store') }}" class="space-y-6">
            @csrf

            {{-- Chauffeur cible --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-800">Chauffeur à contrôler</h2>

                @if ($drivers->isEmpty())
                    <p class="text-sm text-gray-400">Aucun chauffeur assigné à votre équipe.</p>
                @else
                    <div>
                        <label for="driver_user_id" class="block text-xs font-medium text-gray-600 mb-1.5">
                            Chauffeur <span class="text-red-500">*</span>
                        </label>
                        <select id="driver_user_id" name="driver_user_id" required
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#004fa3]/40">
                            <option value="">— Sélectionner un chauffeur —</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_user_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->last_name }} {{ $driver->first_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver_user_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            {{-- Mouches assignées automatiquement --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Mouches assignées</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Les agents sont sélectionnés automatiquement selon leur disponibilité (moins de missions actives en priorité).
                    </p>
                </div>

                @if ($autoMouches->isEmpty())
                    <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600">
                        Aucun agent mouche disponible. La mission ne peut pas être créée.
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($autoMouches as $mouche)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-[#004fa3]/5 border border-[#004fa3]/15">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full bg-[#004fa3]/20 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-[#004fa3]">
                                            {{ strtoupper(substr($mouche->first_name, 0, 1) . substr($mouche->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-800">{{ $mouche->last_name }} {{ $mouche->first_name }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $mouche->active_missions }} mission{{ $mouche->active_missions > 1 ? 's' : '' }} active{{ $mouche->active_missions > 1 ? 's' : '' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('missions.index') }}"
                   class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">
                    Annuler
                </a>
                <button type="submit"
                        @if ($autoMouches->isEmpty()) disabled @endif
                        class="px-5 py-2 bg-[#004fa3] text-white text-sm font-semibold rounded-lg hover:bg-[#003d80] transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Lancer la mission
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
