<x-guest-layout>
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#004fa3]/10 mb-4">
            <svg class="w-8 h-8 text-[#004fa3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 11h8m-9 4h10M5 3h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900">Bus <span class="text-[#004fa3]">{{ $bus->code }}</span></h1>
        <p class="mt-1 text-sm text-gray-500">Scanné le {{ \Carbon\Carbon::parse($scannedAt)->format('d/m/Y à H:i') }}</p>

        @if (session('success'))
            <div class="mt-4 mb-2 rounded-lg bg-[#4bc0ad]/10 border border-[#4bc0ad]/30 p-4 text-sm text-[#38a090] text-left">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <p class="mt-5 mb-7 text-gray-500 text-sm">Partagez votre expérience à bord de ce bus RATP.</p>

        <div class="flex flex-col gap-3">
            <a href="{{ route('satisfaction.create', ['bus' => $bus->code, 'scanned_at' => $scannedAt]) }}"
               class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-[#4bc0ad] hover:bg-[#38a090] text-white font-semibold rounded-xl transition duration-150 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                Donner mon avis
            </a>

            <a href="{{ route('complaint.create', ['bus' => $bus->code, 'scanned_at' => $scannedAt]) }}"
               class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-[#004fa3] hover:bg-[#003d80] text-white font-semibold rounded-xl transition duration-150 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Déposer une plainte
            </a>
        </div>
    </div>
</x-guest-layout>
