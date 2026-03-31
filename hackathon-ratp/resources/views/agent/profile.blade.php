<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-gray-900">Mon profil</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-agent-profile :user="$user" :satisfaction-stats="$satisfactionStats" />
    </div>
</x-app-layout>
