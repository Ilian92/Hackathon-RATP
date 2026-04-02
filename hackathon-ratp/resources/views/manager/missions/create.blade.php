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

            {{-- Mouches assignées --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Mouches assignées</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Sélectionnez jusqu'à 3 agents mouche pour cette mission.</p>
                </div>

                @if ($mouches->isEmpty())
                    <p class="text-sm text-gray-400">Aucun agent mouche disponible dans le système.</p>
                @else
                    @error('mouche_ids')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    <div class="space-y-2">
                        @foreach ($mouches as $mouche)
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer transition">
                                <input type="checkbox"
                                       name="mouche_ids[]"
                                       value="{{ $mouche->id }}"
                                       {{ in_array($mouche->id, old('mouche_ids', [])) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-[#004fa3] focus:ring-[#004fa3]/40"
                                       x-data
                                       @change="
                                           const checked = document.querySelectorAll('input[name=\'mouche_ids[]\']:checked');
                                           if (checked.length > 3) { $el.checked = false; }
                                       ">
                                <span class="text-sm text-gray-800">{{ $mouche->last_name }} {{ $mouche->first_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400">Maximum 3 mouches par mission.</p>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('missions.index') }}"
                   class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">
                    Annuler
                </a>
                <button type="submit"
                        class="px-5 py-2 bg-[#004fa3] text-white text-sm font-semibold rounded-lg hover:bg-[#003d80] transition">
                    Lancer la mission
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
