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

        @if ($complaint->step->value === 'ManagerReview')
            @if ($isAssignedManager)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-5">Action</h2>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <form method="POST" action="{{ route('complaints.forward-rh', $complaint) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full sm:w-auto px-5 py-2.5 bg-[#004fa3] hover:bg-[#003d80] text-white text-sm font-semibold rounded-xl transition shadow-sm">
                                Transmettre au service RH
                            </button>
                        </form>
                        <form method="POST" action="{{ route('complaints.close', $complaint) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full sm:w-auto px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl border border-gray-300 transition">
                                Clôturer le dossier
                            </button>
                        </form>
                    </div>
                    <p class="mt-3 text-xs text-gray-400">
                        Clôturer = aucune suite. Transmettre au RH = ouverture d'une procédure disciplinaire.
                    </p>
                </div>
            @else
                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6">
                    <p class="text-sm text-gray-500">
                        Ce dossier est traité par
                        <span class="font-medium text-gray-700">{{ $complaint->managerAgent->first_name }} {{ $complaint->managerAgent->last_name }}</span>
                        (manager de remplacement). Vous y avez accès en lecture seule car le chauffeur fait partie de votre équipe.
                    </p>
                </div>
            @endif
        @endif

    </div>
</x-app-layout>
