@props(['complaint'])

@php
    $sanctionTypes = [
        'Avertissement',
        'Mise à pied',
        'Blâme',
        'Rétrogradation',
        'Licenciement',
    ];
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-red-200 p-6">
    <h2 class="text-sm font-semibold text-red-600 uppercase tracking-wide mb-1">Appliquer une sanction</h2>
    <p class="text-sm text-gray-500 mb-5">
        L'enregistrement d'une sanction clôturera définitivement le dossier.
        @if ($complaint->sanction === null && $complaint->user_id === null)
            <span class="font-medium text-amber-600">Attention : aucun chauffeur n'est identifié sur ce dossier.</span>
        @endif
    </p>

    <form method="POST" action="{{ route('complaints.sanction', $complaint) }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="type" value="Type de sanction" />
            <select id="type" name="type" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                <option value="">-- Sélectionner --</option>
                @foreach ($sanctionTypes as $sanctionType)
                    <option value="{{ $sanctionType }}" @selected(old('type') === $sanctionType)>{{ $sanctionType }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>

        <div class="mb-5">
            <x-input-label for="description" value="Motif et détails" />
            <textarea id="description" name="description" rows="3" required
                      class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">{{ old('description') }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <button type="submit"
                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
            Enregistrer la sanction et clôturer
        </button>
    </form>
</div>
