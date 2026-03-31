<x-guest-layout>
    <div>
        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-900">Votre avis</h1>
            <p class="mt-1 text-sm text-gray-500">Bus {{ $busCode }} — {{ \Carbon\Carbon::parse($scannedAt)->format('d/m/Y à H:i') }}</p>
        </div>

        <form method="POST" action="{{ route('satisfaction.store') }}" x-data="{
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
            <input type="hidden" name="bus_code" value="{{ $busCode }}">
            <input type="hidden" name="scanned_at" value="{{ $scannedAt }}">
            <input type="hidden" name="note" :value="rating">

            {{-- Email --}}
            <div class="mb-5">
                <x-input-label for="email" value="Votre adresse email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Star rating --}}
            <div class="mb-5">
                <x-input-label value="Note (étoiles)" />
                <div class="mt-2 flex justify-center gap-1">
                    @for ($star = 1; $star <= 5; $star++)
                        <div class="relative w-10 h-10 cursor-pointer"
                             @mouseleave="hovered = 0">
                            {{-- Left half (demi-étoile) --}}
                            <div class="absolute inset-y-0 left-0 w-1/2 z-10"
                                 @mouseenter="hovered = {{ ($star - 1) * 2 + 1 }}"
                                 @click="setRating({{ ($star - 1) * 2 + 1 }})">
                            </div>
                            {{-- Right half (étoile pleine) --}}
                            <div class="absolute inset-y-0 right-0 w-1/2 z-10"
                                 @mouseenter="hovered = {{ $star * 2 }}"
                                 @click="setRating({{ $star * 2 }})">
                            </div>
                            {{-- Star SVG --}}
                            <svg class="w-10 h-10 transition-colors duration-75"
                                 viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="half-{{ $star }}" x1="0" x2="1" y1="0" y2="0">
                                        <stop offset="50%"
                                              :stop-color="starFill({{ $star }}) !== 'empty' ? '#f59e0b' : '#d1d5db'" />
                                        <stop offset="50%"
                                              :stop-color="starFill({{ $star }}) === 'full' ? '#f59e0b' : '#d1d5db'" />
                                    </linearGradient>
                                </defs>
                                <path fill="url(#half-{{ $star }})"
                                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    @endfor
                </div>
                <p class="mt-1 text-center text-sm text-gray-500">
                    <span x-show="rating === 0">Sélectionnez une note</span>
                    <span x-show="rating > 0" x-text="(rating / 2).toFixed(1) + ' / 5 étoiles'"></span>
                </p>
                <x-input-error :messages="$errors->get('note')" class="mt-2" />
            </div>

            {{-- Description --}}
            <div class="mb-6">
                <x-input-label for="description" value="Commentaire (optionnel)" />
                <textarea id="description" name="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('qrcode.show', ['bus' => $busCode]) }}"
                   class="text-sm text-gray-500 hover:text-gray-700">← Retour</a>
                <x-primary-button x-bind:disabled="rating === 0">
                    Envoyer mon avis
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
