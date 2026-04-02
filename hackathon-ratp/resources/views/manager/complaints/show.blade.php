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
                                Clôturer sans suite
                            </button>
                        </form>
                    </div>
                    <p class="mt-3 text-xs text-gray-400">
                        Clôturer sans suite = aucune action. Transmettre au RH = ouverture d'une procédure disciplinaire.
                    </p>
                </div>

                @if ($complaint->sanction === null)
                    <x-sanction-form :complaint="$complaint" />
                @endif
            @else
                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6">
                    <p class="text-sm text-gray-500">
                        Ce dossier est traité par
                        <span class="font-medium text-gray-700">{{ $complaint->managerAgent->first_name }} {{ $complaint->managerAgent->last_name }}</span>
                        (manager de remplacement). Vous y avez accès en lecture seule car le chauffeur fait partie de votre équipe.
                    </p>
                </div>
            @endif
        @elseif ($complaint->negative === false)
            {{-- Signalement positif visible en lecture seule pour le manager --}}
            <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-6 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-emerald-800">Signalement positif</p>
                    <p class="text-sm text-emerald-600 mt-0.5">Ce dossier est un signalement positif concernant l'un de vos chauffeurs. Il est traité par le service RH.</p>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
