<x-guest-layout>
    <div>
        <div class="mb-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#004fa3]/10 mb-4">
                <svg class="w-8 h-8 text-[#004fa3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Déposer une plainte</h1>
            <p class="mt-1 text-sm text-gray-500">Signalez un incident survenu dans un bus RATP.</p>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-[#4bc0ad]/10 border border-[#4bc0ad]/30 p-4 text-sm text-[#38a090]">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('home.store') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-4">
                <x-input-label for="email" value="Votre adresse email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                <p class="mt-1.5 text-xs text-gray-400">
                    Votre adresse email pourra être utilisée par le service RH dans le cadre du traitement de votre plainte si nécessaire.
                </p>
            </div>

            {{-- Ligne de bus --}}
            <div class="mb-4">
                <x-input-label for="ligne_id" value="Ligne de bus" />
                <select id="ligne_id" name="ligne_id" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm">
                    <option value="">-- Sélectionner une ligne --</option>
                    @foreach ($lignes as $ligne)
                        <option value="{{ $ligne->id }}" @selected(old('ligne_id') == $ligne->id)>
                            {{ $ligne->nom }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('ligne_id')" class="mt-2" />
            </div>

            {{-- Date et heure --}}
            <div class="mb-4 grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="date" value="Date de l'incident" />
                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                                  :value="old('date', now()->toDateString())"
                                  max="{{ now()->toDateString() }}" required />
                    <x-input-error :messages="$errors->get('date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="heure" value="Heure approximative" />
                    <x-text-input id="heure" name="heure" type="time" class="mt-1 block w-full"
                                  :value="old('heure')" required />
                    <x-input-error :messages="$errors->get('heure')" class="mt-2" />
                </div>
            </div>

            {{-- Type de plainte --}}
            <div class="mb-4">
                <x-input-label for="complaint_type_id" value="Type de plainte" />
                <select id="complaint_type_id" name="complaint_type_id" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach ($complaintTypes as $type)
                        <option value="{{ $type->id }}" @selected(old('complaint_type_id') == $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('complaint_type_id')" class="mt-2" />
            </div>

            {{-- Description --}}
            <div class="mb-6">
                <x-input-label for="description" value="Description de l'incident" />
                <textarea id="description" name="description" rows="4" required
                          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">
                Envoyer la plainte
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
