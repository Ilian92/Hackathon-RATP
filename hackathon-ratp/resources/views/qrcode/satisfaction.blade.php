<x-guest-layout>
    <div>
        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-900">Votre avis</h1>
            <p class="mt-1 text-sm text-gray-500">
                Bus <span class="font-medium text-[#004fa3]">{{ $busCode }}</span>
                — {{ \Carbon\Carbon::parse($scannedAt)->format('d/m/Y à H:i') }}
            </p>
        </div>

        @if ($isThrottled || session('throttled'))
            <div class="rounded-xl bg-red-50 border border-red-200 p-5 text-center">
                <svg class="w-8 h-8 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <p class="font-semibold text-red-700 mb-1">Limite atteinte</p>
                <p class="text-sm text-red-600">Vous avez déjà soumis le nombre maximum d'avis autorisé ({{ \App\Http\Controllers\QrCodeController::SATISFACTION_LIMIT }}) au cours des dernières 24 heures. Merci de réessayer demain.</p>
                <a href="{{ route('qrcode.landing', $token) }}" class="mt-4 inline-block text-sm text-red-700 font-medium hover:underline">← Retour</a>
            </div>
        @else

        <form method="POST" action="{{ route('satisfaction.store', $token) }}" x-data="{
            rating: 0,
            hovered: 0,
            setRating(value) { this.rating = value; },
            starFill(star) {
                const active = this.hovered > 0 ? this.hovered : this.rating;
                if (active >= star * 2) return 'full';
                if (active >= star * 2 - 1) return 'half';
                return 'empty';
            }
        }">
            @csrf
            <input type="hidden" name="note" :value="rating">

            {{-- Email --}}
            <div class="mb-5">
                <x-input-label for="email" value="Votre adresse email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                <p class="mt-1.5 text-xs text-gray-400">
                    Votre adresse email est utilisée uniquement pour éviter les doublons. Elle ne sera pas conservée ni transmise à des tiers.
                </p>
            </div>

            {{-- Star rating --}}
            <div class="mb-5">
                <x-input-label value="Note" />
                <div class="mt-3 flex justify-center gap-1">
                    @for ($star = 1; $star <= 5; $star++)
                        <div class="relative w-11 h-11 cursor-pointer" @mouseleave="hovered = 0">
                            <div class="absolute inset-y-0 left-0 w-1/2 z-10"
                                 @mouseenter="hovered = {{ ($star - 1) * 2 + 1 }}"
                                 @click="setRating({{ ($star - 1) * 2 + 1 }})"></div>
                            <div class="absolute inset-y-0 right-0 w-1/2 z-10"
                                 @mouseenter="hovered = {{ $star * 2 }}"
                                 @click="setRating({{ $star * 2 }})"></div>
                            <svg class="w-11 h-11 transition-colors duration-75" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="half-{{ $star }}" x1="0" x2="1" y1="0" y2="0">
                                        <stop offset="50%" :stop-color="starFill({{ $star }}) !== 'empty' ? '#4bc0ad' : '#e5e7eb'" />
                                        <stop offset="50%" :stop-color="starFill({{ $star }}) === 'full' ? '#4bc0ad' : '#e5e7eb'" />
                                    </linearGradient>
                                </defs>
                                <path fill="url(#half-{{ $star }})"
                                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    @endfor
                </div>
                <p class="mt-2 text-center text-sm text-gray-500">
                    <span x-show="rating === 0">Sélectionnez une note</span>
                    <span x-show="rating > 0" class="font-medium text-[#38a090]" x-text="(rating / 2).toFixed(1) + ' / 5 étoiles'"></span>
                </p>
                <x-input-error :messages="$errors->get('note')" class="mt-2" />
            </div>

            {{-- Description --}}
            <div class="mb-6">
                <x-input-label for="description" value="Commentaire (optionnel)" />
                <textarea id="description" name="description" rows="3"
                          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#004fa3] focus:ring-[#004fa3] text-sm">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('qrcode.landing', $token) }}"
                   class="text-sm text-[#004fa3] hover:text-[#003d80] font-medium">← Retour</a>
                <x-primary-button x-bind:disabled="rating === 0">
                    Envoyer mon avis
                </x-primary-button>
            </div>
        </form>

        @endif
    </div>
</x-guest-layout>
