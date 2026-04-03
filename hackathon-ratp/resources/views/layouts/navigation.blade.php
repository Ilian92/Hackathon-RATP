<nav x-data="{ open: false }" class="bg-[#004fa3] shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="max-w-md mx-auto px-4 py-4 flex items-center justify-center">
                    <a href="{{ Auth::check() ? route('profile') : '/' }}">
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

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">

                {{-- Notification bell --}}
                @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="relative inline-flex items-center justify-center p-2 rounded-md text-white/70 hover:text-white hover:bg-[#1a63b6] transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if ($unreadCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <div x-show="open" x-transition
                         class="absolute right-0 mt-2 w-80 rounded-2xl bg-white shadow-xl border border-gray-100 z-50 overflow-hidden">
                        @php $notifications = Auth::user()->notifications()->latest()->limit(8)->get(); @endphp
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <span class="text-xs font-semibold text-gray-700">Notifications</span>
                            @if ($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-[#004fa3] hover:underline">Tout marquer comme lu</button>
                                </form>
                            @endif
                        </div>
                        @if ($notifications->isEmpty())
                            <p class="px-4 py-6 text-xs text-gray-400 text-center">Aucune notification</p>
                        @else
                            <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                                @foreach ($notifications as $notif)
                                    @php
                                        $data = $notif->data;
                                        $isUnread = $notif->read_at === null;
                                        $colorMap = [
                                            'blue'  => 'text-[#004fa3] bg-[#004fa3]/10',
                                            'red'   => 'text-red-600 bg-red-100',
                                            'green' => 'text-emerald-600 bg-emerald-100',
                                            'amber' => 'text-amber-600 bg-amber-100',
                                        ];
                                        $iconColor = $colorMap[$data['color'] ?? 'blue'] ?? $colorMap['blue'];
                                    @endphp
                                    <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition {{ $isUnread ? 'bg-blue-50/40' : '' }}">
                                            <span class="shrink-0 mt-0.5 w-7 h-7 rounded-full flex items-center justify-center {{ $iconColor }}">
                                                @if (($data['icon'] ?? '') === 'warning')
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                @elseif (($data['icon'] ?? '') === 'check')
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                @elseif (($data['icon'] ?? '') === 'star')
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                @else
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                                @endif
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-gray-700 leading-snug {{ $isUnread ? 'font-medium' : '' }}">{{ $data['message'] ?? '' }}</p>
                                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if ($isUnread)
                                                <span class="shrink-0 mt-1.5 w-2 h-2 rounded-full bg-[#004fa3]"></span>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

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
