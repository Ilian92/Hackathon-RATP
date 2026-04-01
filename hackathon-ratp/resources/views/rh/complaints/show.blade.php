<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('rh.complaints.index') }}" class="text-gray-400 hover:text-gray-600 transition">
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

        @if ($complaint->step->value === 'RHReview')
            @if ($complaint->rh_user_id === null)
                <div class="bg-white rounded-2xl shadow-sm border border-[#004fa3]/20 p-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-medium text-gray-900">Ce dossier est disponible</p>
                        <p class="text-sm text-gray-500 mt-0.5">Prenez-le en charge pour ouvrir la procédure disciplinaire.</p>
                    </div>
                    <form method="POST" action="{{ route('rh.complaints.claim', $complaint) }}">
                        @csrf
                        <x-primary-button>Prendre en charge</x-primary-button>
                    </form>
                </div>
            @elseif ($complaint->rh_user_id === auth()->id())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-5">Action disciplinaire</h2>
                    <form method="POST" action="{{ route('rh.complaints.close', $complaint) }}">
                        @csrf
                        <p class="text-sm text-gray-600 mb-4">
                            Clôturer ce dossier marquera la plainte comme <strong>aboutie</strong> et mettra fin à la procédure.
                        </p>
                        <button type="submit"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            Clôturer — Plainte aboutie
                        </button>
                    </form>
                </div>
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
