<nav x-data="{ open: false }" class="bg-[#004fa3] shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="max-w-md mx-auto px-4 py-4 flex items-center justify-center">
                    <a href="/">
                        <img src="{{ asset('images/Image1 (1).png') }}" alt="RATP Réseaux de Surface" class="h-16 w-auto" />
                    </a>
                </div>
                <div class="hidden sm:-my-px sm:ms-10 sm:flex sm:gap-6">
                    @unless (in_array(Auth::user()->role, [\App\Enums\UserRole::Mouche, \App\Enums\UserRole::Chauffeur]))
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Tableau de bord
                        </x-nav-link>
                    @endunless
                    @if (in_array(Auth::user()->role, [\App\Enums\UserRole::Com, \App\Enums\UserRole::Manager, \App\Enums\UserRole::RH]))
                        <x-nav-link :href="route('complaints.index')" :active="request()->routeIs('complaints.*')">
                            Gestion des plaintes
                        </x-nav-link>
                    @endif
                    @if (Auth::user()->role === \App\Enums\UserRole::Manager)
                        <x-nav-link :href="route('missions.index')" :active="request()->routeIs('missions.*')">
                            Missions mouche
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white/80 hover:text-white rounded-md focus:outline-none transition duration-150 ease-in-out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')">
                            Profil
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                Déconnexion
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white/70 hover:text-white hover:bg-[#1a63b6] focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="h-0.5 bg-[#4bc0ad]"></div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[#003d80]">
        <div class="pt-2 pb-3 space-y-1">
            @unless (in_array(Auth::user()->role, [\App\Enums\UserRole::Mouche, \App\Enums\UserRole::Chauffeur]))
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    Tableau de bord
                </x-responsive-nav-link>
            @endunless
            @if (in_array(Auth::user()->role, [\App\Enums\UserRole::Com, \App\Enums\UserRole::Manager, \App\Enums\UserRole::RH]))
                <x-responsive-nav-link :href="route('complaints.index')" :active="request()->routeIs('complaints.*')">
                    Gestion des plaintes
                </x-responsive-nav-link>
            @endif
            @if (Auth::user()->role === \App\Enums\UserRole::Manager)
                <x-responsive-nav-link :href="route('missions.index')" :active="request()->routeIs('missions.*')">
                    Missions mouche
                </x-responsive-nav-link>
            @endif
        </div>
        <div class="pt-4 pb-1 border-t border-white/20">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                <div class="font-medium text-sm text-white/60">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profil
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        Déconnexion
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
