<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('profile.edit') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Droits et légal — RGPD</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-[#004fa3] rounded-2xl p-6 text-white">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-1">Vos droits sur vos données personnelles</h3>
                        <p class="text-white/80 text-sm leading-relaxed">
                            Conformément au Règlement Général sur la Protection des Données (RGPD — Règlement UE 2016/679),
                            vous disposez de droits sur vos données personnelles traitées par RATP Réseaux de Surface.
                            Retrouvez ci-dessous l'ensemble des actions disponibles.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#004fa3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800">Données que nous collectons</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ([
                        ['label' => 'Identité', 'value' => $user->first_name . ' ' . $user->last_name, 'icon' => 'user'],
                        ['label' => 'Adresse email', 'value' => $user->email, 'icon' => 'mail'],
                        ['label' => 'Matricule', 'value' => $user->matricule ?? '—', 'icon' => 'badge'],
                        ['label' => 'Rôle', 'value' => $user->role->value, 'icon' => 'role'],
                        ['label' => 'Date d\'entrée', 'value' => $user->contract_start_date?->format('d/m/Y') ?? '—', 'icon' => 'calendar'],
                        ['label' => 'Compte créé le', 'value' => $user->created_at->format('d/m/Y'), 'icon' => 'calendar'],
                    ] as $item)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="shrink-0 w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400">{{ $item['label'] }}</p>
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $item['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-gray-400">
                    Base légale : exécution du contrat de travail (Art. 6.1.b RGPD) — Conservation : durée du contrat + 5 ans.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="shrink-0 w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">Exporter mes données</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Droit à la portabilité — Art. 20 RGPD</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                        Téléchargez une copie de toutes vos données personnelles au format JSON ou CSV
                        (profil, historique des dossiers traités, notifications).
                    </p>
                    <div class="flex gap-2">
                        <button type="button"
                                onclick="alert('Fonctionnalité disponible prochainement.')"
                                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Exporter en JSON
                        </button>
                        <button type="button"
                                onclick="alert('Fonctionnalité disponible prochainement.')"
                                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Exporter en CSV
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="shrink-0 w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#004fa3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">Rectifier mes données</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Droit de rectification — Art. 16 RGPD</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                        Vous pouvez modifier vos informations personnelles (nom, prénom, email) directement
                        depuis votre profil à tout moment.
                    </p>
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center justify-center gap-1.5 px-3 py-2 bg-[#004fa3] text-white text-xs font-medium rounded-lg hover:bg-[#003d80] transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Modifier mon profil
                    </a>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="shrink-0 w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">S'opposer au traitement</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Droit d'opposition — Art. 21 RGPD</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                        Demandez la limitation ou l'opposition au traitement de certaines de vos données
                        pour des finalités spécifiques.
                    </p>
                    <button type="button"
                            onclick="alert('Votre demande sera transmise au Délégué à la Protection des Données (DPO). Fonctionnalité disponible prochainement.')"
                            class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Contacter le DPO
                    </button>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="shrink-0 w-9 h-9 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">Droit à l'effacement</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Droit à l'oubli — Art. 17 RGPD</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                        Demandez la suppression de vos données personnelles. Cette demande sera examinée
                        dans un délai d'un mois conformément à la réglementation.
                    </p>
                    <button type="button"
                            onclick="alert('Votre demande d\'effacement sera transmise au DPO et traitée dans un délai d\'un mois. Fonctionnalité disponible prochainement.')"
                            class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Demander l'effacement
                    </button>
                </div>

            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800">Politique de conservation des données</h3>
                </div>
                <div class="space-y-3">
                    @foreach ([
                        ['type' => 'Données de profil employé', 'duree' => 'Durée du contrat + 5 ans', 'base' => 'Obligation légale'],
                        ['type' => 'Historique des signalements traités', 'duree' => '3 ans', 'base' => 'Intérêt légitime'],
                        ['type' => 'Données de connexion (sessions)', 'duree' => '2 heures (session active)', 'base' => 'Sécurité des systèmes'],
                        ['type' => 'Logs applicatifs', 'duree' => '14 jours', 'base' => 'Sécurité des systèmes'],
                        ['type' => 'Avis de satisfaction des usagers', 'duree' => '2 ans', 'base' => 'Intérêt légitime'],
                    ] as $row)
                        <div class="flex items-start justify-between gap-4 py-2.5 border-b border-gray-50 last:border-0">
                            <span class="text-sm text-gray-700">{{ $row['type'] }}</span>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-semibold text-gray-800">{{ $row['duree'] }}</span>
                                <p class="text-xs text-gray-400">{{ $row['base'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-5">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800">Délégué à la Protection des Données (DPO)</h4>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Pour toute question relative à vos données personnelles ou pour exercer vos droits,
                            vous pouvez contacter notre DPO à l'adresse suivante :
                            <span class="font-medium text-[#004fa3]">dpo@ratp.fr</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1.5">
                            Vous disposez également du droit d'introduire une réclamation auprès de la
                            <a href="https://www.cnil.fr" target="_blank" class="text-[#004fa3] hover:underline">CNIL</a>
                            si vous estimez que vos droits ne sont pas respectés.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
