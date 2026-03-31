<x-guest-layout>
    <div>
        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-900">Déposer une plainte</h1>
            <p class="mt-1 text-sm text-gray-500">Bus {{ $busCode }} — {{ \Carbon\Carbon::parse($scannedAt)->format('d/m/Y à H:i') }}</p>
        </div>

        <form method="POST" action="{{ route('complaint.store') }}">
            @csrf
            <input type="hidden" name="bus_code" value="{{ $busCode }}">
            <input type="hidden" name="scanned_at" value="{{ $scannedAt }}">

            {{-- Email --}}
            <div class="mb-4">
                <x-input-label for="email" value="Votre adresse email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Type de plainte --}}
            <div class="mb-4">
                <x-input-label for="complaint_type_id" value="Type de plainte" />
                <select id="complaint_type_id" name="complaint_type_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach ($complaintTypes as $type)
                        <option value="{{ $type->id }}" @selected(old('complaint_type_id') == $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('complaint_type_id')" class="mt-2" />
            </div>

            {{-- Chauffeur --}}
            <div class="mb-4">
                <x-input-label for="driver_id" value="Chauffeur concerné" />
                <select id="driver_id" name="driver_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Sélectionner --</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}" @selected(old('driver_id') == $driver->id)>
                            {{ $driver->first_name }} {{ $driver->last_name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('driver_id')" class="mt-2" />
            </div>

            {{-- Gravité --}}
            <div class="mb-4">
                <x-input-label for="severity" value="Niveau de gravité" />
                <select id="severity" name="severity" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Sélectionner --</option>
                    <option value="0" @selected(old('severity') == '0')>0 — Mineur</option>
                    <option value="1" @selected(old('severity') == '1')>1 — Faible</option>
                    <option value="2" @selected(old('severity') == '2')>2 — Modéré</option>
                    <option value="3" @selected(old('severity') == '3')>3 — Grave</option>
                    <option value="4" @selected(old('severity') == '4')>4 — Critique</option>
                </select>
                <x-input-error :messages="$errors->get('severity')" class="mt-2" />
            </div>

            {{-- Description --}}
            <div class="mb-6">
                <x-input-label for="description" value="Description de l'incident" />
                <textarea id="description" name="description" rows="4" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('qrcode.show', ['bus' => $busCode]) }}"
                   class="text-sm text-gray-500 hover:text-gray-700">← Retour</a>
                <x-primary-button>
                    Envoyer la plainte
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
