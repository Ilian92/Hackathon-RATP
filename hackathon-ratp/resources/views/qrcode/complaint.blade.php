<x-guest-layout>
    <div>
        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-900">Déposer une plainte</h1>
            <p class="mt-1 text-sm text-gray-500">
                Bus <span class="font-medium text-[#004fa3]">{{ $busCode }}</span>
                — {{ \Carbon\Carbon::parse($scannedAt)->format('d/m/Y à H:i') }}
            </p>
        </div>

        <form method="POST" action="{{ route('complaint.store', $token) }}">
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

            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('qrcode.landing', $token) }}"
                   class="text-sm text-[#004fa3] hover:text-[#003d80] font-medium">← Retour</a>
                <x-primary-button>
                    Envoyer la plainte
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
