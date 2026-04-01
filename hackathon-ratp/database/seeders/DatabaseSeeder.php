<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Arret;
use App\Models\Bus;
use App\Models\CentreBus;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Gratification;
use App\Models\Ligne;
use App\Models\Planning;
use App\Models\Sanction;
use App\Models\Satisfaction;
use App\Models\Severity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Centres de bus
        $centreLagny = CentreBus::factory()->create(['name' => 'Dépôt de Lagny', 'address' => '1 Rue du Dépôt, Lagny-sur-Marne']);
        $centreThiais = CentreBus::factory()->create(['name' => 'Dépôt de Thiais', 'address' => '45 Avenue du Maréchal Joffre, Thiais']);

        // Utilisateurs par rôle
        $managers = User::factory(2)->role(UserRole::Manager)->create();
        $drivers = User::factory(5)->chauffeur()->create();
        $coms = User::factory(2)->role(UserRole::Com)->create();
        $rhs = User::factory(1)->role(UserRole::RH)->create();
        $avocats = User::factory(1)->role(UserRole::Avocat)->create();

        // Compte de test manager (mot de passe : password)
        $testManagerGeneric = User::factory()->role(UserRole::Manager)->create([
            'first_name' => 'Test',
            'last_name' => 'Manager',
            'email' => 'test@ratp.fr',
        ]);

        // Compte de test RH (mot de passe : password)
        $testRh = User::factory()->role(UserRole::RH)->create([
            'first_name' => 'Claire',
            'last_name' => 'Moreau',
            'email' => 'rh.test@ratp.fr',
            'matricule' => 'RATP-RH001',
        ]);

        // Compte de test Com (mot de passe : password)
        $testCom = User::factory()->role(UserRole::Com)->create([
            'first_name' => 'Marie',
            'last_name' => 'Laurent',
            'email' => 'com.test@ratp.fr',
            'matricule' => 'RATP-COM001',
        ]);

        // Compte de test chauffeur avec toutes les données (mot de passe : password)
        $testManager = User::factory()->role(UserRole::Manager)->create([
            'first_name' => 'Sophie',
            'last_name' => 'Lefèvre',
            'email' => 'manager.test@ratp.fr',
            'matricule' => 'RATP-MGR001',
        ]);

        $testDriver = User::factory()->chauffeur()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'chauffeur.test@ratp.fr',
            'matricule' => 'RATP-CHF001',
            'contract_start_date' => '2019-03-15',
            'status' => UserStatus::Actif,
        ]);

        // Lier les managers aux centres de bus
        $centreLagny->users()->attach($testManager->id);
        $centreLagny->users()->attach($testManagerGeneric->id);
        $centreLagny->users()->attach($managers->first()->id);
        $centreThiais->users()->attach($managers->last()->id);

        // Lier les autres employés (Com, RH, Avocat) aux centres
        foreach ($coms as $com) {
            $centreLagny->users()->attach($com->id);
        }
        $centreLagny->users()->attach($testCom->id);
        foreach ($rhs as $rh) {
            $centreThiais->users()->attach($rh->id);
        }
        $centreThiais->users()->attach($testRh->id);
        foreach ($avocats as $avocat) {
            $centreLagny->users()->attach($avocat->id);
        }

        // Chauffeurs supplémentaires dans l'équipe de Sophie Lefèvre
        $teamDrivers = User::factory(3)->chauffeur()->create();
        foreach ($teamDrivers as $driver) {
            $driver->managers()->attach($testManager->id);
        }

        $testDriver->managers()->attach($testManager->id);

        // Lier les autres chauffeurs à leur manager
        foreach ($drivers as $driver) {
            $driver->managers()->attach($managers->random()->id);
        }

        // Gratifications et sanctions pour chaque chauffeur aléatoire
        foreach ($drivers as $driver) {
            Gratification::factory(fake()->numberBetween(0, 3))->create(['user_id' => $driver->id]);
            Sanction::factory(fake()->numberBetween(0, 2))->create(['user_id' => $driver->id]);
        }

        // Types de plaintes (les 8 types fixes)
        $complaintTypes = ComplaintType::factory(8)->create();

        // Bus
        $buses = Bus::factory(10)->create();

        // Lignes de bus rattachées aux centres
        $arrets = Arret::factory(40)->create();

        $lignesLagny = collect();
        foreach (['66', '68', '92', 'N52', '115'] as $nom) {
            $ligne = Ligne::create(['nom' => $nom, 'centre_bus_id' => $centreLagny->id]);
            // Attacher 6-10 arrêts ordonnés à chaque ligne
            $arretsDeLigne = $arrets->random(fake()->numberBetween(6, 10));
            foreach ($arretsDeLigne->values() as $ordre => $arret) {
                $ligne->arrets()->attach($arret->id, ['ordre' => $ordre + 1]);
            }
            $lignesLagny->push($ligne->load('arrets'));
        }

        $lignesThiais = collect();
        foreach (['42', '183', '150', '172', 'N01'] as $nom) {
            $ligne = Ligne::create(['nom' => $nom, 'centre_bus_id' => $centreThiais->id]);
            $arretsDeLigne = $arrets->random(fake()->numberBetween(6, 10));
            foreach ($arretsDeLigne->values() as $ordre => $arret) {
                $ligne->arrets()->attach($arret->id, ['ordre' => $ordre + 1]);
            }
            $lignesThiais->push($ligne->load('arrets'));
        }

        $toutesLesLignes = $lignesLagny->merge($lignesThiais);

        // Planning : un chauffeur par bus par jour sur 90 jours + aujourd'hui
        $allDrivers = $drivers->merge(collect([$testDriver]));
        $period = Carbon::today()->subDays(90);
        while ($period->lte(Carbon::today())) {
            foreach ($buses as $bus) {
                $ligne = $toutesLesLignes->random();
                $arretsDeLigne = $ligne->arrets->sortBy('pivot.ordre')->values();
                Planning::create([
                    'bus_id' => $bus->id,
                    'user_id' => $allDrivers->random()->id,
                    'date' => $period->toDateString(),
                    'ligne_id' => $ligne->id,
                    'arret_debut_id' => $arretsDeLigne->first()?->id,
                    'heure_debut' => fake()->time('H:i:s', '12:00:00'),
                    'arret_fin_id' => $arretsDeLigne->last()?->id,
                    'heure_fin' => fake()->time('H:i:s', '23:59:59'),
                ]);
            }
            $period->addDay();
        }

        // Clients
        $clients = Client::factory(30)->create();

        // Plaintes (en réutilisant les bus, types et chauffeurs existants)
        Complaint::factory(40)
            ->recycle($buses)
            ->recycle($complaintTypes)
            ->recycle($drivers)
            ->recycle($clients)
            ->create();

        // Quelques plaintes graves
        Complaint::factory(10)
            ->severe()
            ->recycle($buses)
            ->recycle($complaintTypes)
            ->recycle($drivers)
            ->recycle($clients)
            ->create();

        // Avis de satisfaction liés aux chauffeurs aléatoires
        Satisfaction::factory(50)
            ->recycle($clients)
            ->recycle($drivers)
            ->create();

        // --- Données complètes pour le profil de test (chauffeur.test@ratp.fr) ---

        // Un bus dédié au chauffeur de test
        $testBus = $buses->first();

        // Plaintes variées
        Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::EnCours,
            'description' => 'Le chauffeur a brûlé un feu rouge à l\'intersection de la rue de la Paix.',
            'incident_time' => now()->subDays(5),
        ]);

        Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::EnCours,
            'description' => 'Comportement agressif envers un passager lors de la validation du titre de transport.',
            'incident_time' => now()->subDays(12),
        ]);

        Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::Abouti,
            'description' => 'Départ du terminus en avance, laissant plusieurs passagers sur le quai.',
            'incident_time' => now()->subDays(45),
        ]);

        Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::Clos,
            'description' => 'Musique trop forte dans le bus, gênant les passagers.',
            'incident_time' => now()->subDays(90),
        ]);

        // Avis de satisfaction
        Satisfaction::factory(12)->create([
            'user_id' => $testDriver->id,
            'note' => fake()->numberBetween(6, 10),
        ]);
        Satisfaction::factory(3)->create([
            'user_id' => $testDriver->id,
            'note' => fake()->numberBetween(0, 5),
        ]);

        // Gratifications
        Gratification::factory()->create([
            'user_id' => $testDriver->id,
            'amount' => 200,
            'reason' => 'Excellent taux de satisfaction client',
            'awarded_at' => now()->subMonths(3),
        ]);
        Gratification::factory()->create([
            'user_id' => $testDriver->id,
            'amount' => 100,
            'reason' => 'Ponctualité exemplaire sur le trimestre',
            'awarded_at' => now()->subMonths(8),
        ]);

        // Sanctions
        Sanction::factory()->create([
            'user_id' => $testDriver->id,
            'type' => 'Avertissement',
            'description' => 'Utilisation du téléphone pendant la conduite',
            'sanctioned_at' => now()->subMonths(6),
        ]);

        // --- Données pour le compte Com de test (com.test@ratp.fr) ---
        // Quelques plaintes déjà évaluées par Marie Laurent
        $evaluatedComplaintIds = Severity::pluck('complaint_id');
        $evaluatedComplaints = Complaint::whereNotIn('id', $evaluatedComplaintIds)->take(5)->get();

        $severityData = [
            ['level' => 1, 'justification' => 'Incident isolé, premier signalement de ce type pour ce chauffeur. Aucun antécédent similaire constaté sur les 12 derniers mois.'],
            ['level' => 3, 'justification' => 'Comportement confirmé par les caméras embarquées. Troisième signalement similaire en 6 mois, ce qui aggrave la note.'],
            ['level' => 0, 'justification' => 'Après vérification, l\'incident est dû à un malentendu. Le chauffeur a respecté les procédures en vigueur.'],
            ['level' => 2, 'justification' => 'Témoignages concordants de deux usagers. Impact réel mais sans mise en danger directe des passagers.'],
            ['level' => 4, 'justification' => 'Mise en danger avérée de passagers vulnérables. Infraction au code de la route confirmée par le GPS embarqué.'],
        ];

        foreach ($evaluatedComplaints as $index => $complaint) {
            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $severityData[$index]['level'],
                'justification' => $severityData[$index]['justification'],
            ]);
        }

        // --- Données pour le compte RH de test (rh.test@ratp.fr) ---
        $rhComplaintsData = [
            // Disponibles (non réclamées)
            [
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => null,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(3),
                'severity' => ['level' => 4, 'justification' => 'Agression physique d\'un passager filmée par les caméras embarquées. Mise en danger immédiate constatée.'],
            ],
            [
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => null,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(7),
                'severity' => ['level' => 3, 'justification' => 'Conduite sous l\'emprise d\'alcool suspectée, test positif confirmé par le dépôt. Antécédents similaires.'],
            ],
            [
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => null,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(10),
                'severity' => ['level' => 4, 'justification' => 'Refus délibéré de prise en charge d\'un passager en fauteuil roulant, comportement discriminatoire avéré.'],
            ],
            // Prises en charge par Claire Moreau
            [
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(14),
                'severity' => ['level' => 3, 'justification' => 'Troisième signalement pour propos déplacés envers des usagers. Escalade progressive constatée sur 8 mois.'],
            ],
            [
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(18),
                'severity' => ['level' => 4, 'justification' => 'Accident causé par excès de vitesse, blessé léger parmi les passagers. Rapport de police joint au dossier.'],
            ],
            // Clôturées par Claire Moreau
            [
                'step' => ComplaintStep::Closed,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::Abouti,
                'incident_time' => now()->subDays(45),
                'severity' => ['level' => 3, 'justification' => 'Comportement agressif répété envers les usagers, malgré un avertissement antérieur. Procédure disciplinaire justifiée.'],
            ],
            [
                'step' => ComplaintStep::Closed,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::Abouti,
                'incident_time' => now()->subDays(60),
                'severity' => ['level' => 4, 'justification' => 'Falsification de feuille de route confirmée par les relevés GPS. Faute grave caractérisée.'],
            ],
        ];

        foreach ($rhComplaintsData as $data) {
            $severity = $data['severity'];

            $complaint = Complaint::factory()->create([
                'user_id' => $drivers->random()->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => $data['step'],
                'rh_user_id' => $data['rh_user_id'],
                'com_user_id' => $testCom->id,
                'status' => $data['status'],
                'incident_time' => $data['incident_time'],
            ]);

            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $severity['level'],
                'justification' => $severity['justification'],
            ]);
        }

        // --- Données pour le compte Manager de test (manager.test@ratp.fr) ---
        $managerComplaintsData = [
            // En attente de décision (ManagerReview)
            [
                'driver' => $testDriver,
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(3),
                'severity' => ['level' => 2, 'justification' => 'Refus de priorité à un passager PMR. Incident confirmé par deux témoins présents dans le bus.'],
            ],
            [
                'driver' => $teamDrivers[0],
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(6),
                'severity' => ['level' => 1, 'justification' => 'Retard injustifié de 15 minutes au terminus. Premier incident signalé pour ce chauffeur.'],
            ],
            [
                'driver' => $teamDrivers[1],
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(9),
                'severity' => ['level' => 2, 'justification' => 'Comportement irrespectueux envers une usagère. Témoignage corroboré par les caméras embarquées.'],
            ],
            [
                'driver' => $teamDrivers[2],
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(11),
                'severity' => ['level' => 1, 'justification' => 'Départ anticipé de 3 minutes, laissant un passager sur le quai. Erreur isolée sans antécédent.'],
            ],
            // Transmis au RH (niveau 3-4, déjà escaladés)
            [
                'driver' => $testDriver,
                'step' => ComplaintStep::RHReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(20),
                'severity' => ['level' => 3, 'justification' => 'Troisième signalement pour excès de vitesse en zone scolaire. Le manager a jugé l\'escalade RH nécessaire.'],
            ],
            [
                'driver' => $teamDrivers[0],
                'step' => ComplaintStep::RHReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(30),
                'severity' => ['level' => 4, 'justification' => 'Insultes à caractère discriminatoire envers un usager, confirmées par enregistrement sonore embarqué.'],
            ],
            // Clôturés par le manager (sans suite ou avertissement)
            [
                'driver' => $teamDrivers[1],
                'step' => ComplaintStep::Closed,
                'status' => ComplaintStatus::Clos,
                'incident_time' => now()->subDays(50),
                'severity' => ['level' => 1, 'justification' => 'Incident mineur résolu par entretien oral. Chauffeur averti et sensibilisé.'],
            ],
            [
                'driver' => $teamDrivers[2],
                'step' => ComplaintStep::Closed,
                'status' => ComplaintStatus::Clos,
                'incident_time' => now()->subDays(70),
                'severity' => ['level' => 0, 'justification' => 'Après vérification, la plainte est non fondée. Le chauffeur a respecté le protocole en vigueur.'],
            ],
        ];

        // --- Dossiers des chauffeurs de Sophie Lefèvre traités par un autre manager ---
        // Simule une période où Sophie était indisponible : ses chauffeurs ont été redirigés vers testManagerGeneric
        $redirectedComplaintsData = [
            [
                'driver' => $teamDrivers[0],
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(4),
                'severity' => ['level' => 2, 'justification' => 'Porte fermée sans attendre une passagère en cours de montée. Incident filmé par la caméra intérieure.'],
            ],
            [
                'driver' => $teamDrivers[1],
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(8),
                'severity' => ['level' => 1, 'justification' => 'Téléphone utilisé brièvement à l\'arrêt moteur coupé. Comportement isolé, aucun antécédent.'],
            ],
            [
                'driver' => $testDriver,
                'step' => ComplaintStep::Closed,
                'status' => ComplaintStatus::Clos,
                'incident_time' => now()->subDays(35),
                'severity' => ['level' => 1, 'justification' => 'Incident verbal mineur résolu par entretien téléphonique. Chauffeur sensibilisé, aucune suite disciplinaire.'],
            ],
            [
                'driver' => $teamDrivers[2],
                'step' => ComplaintStep::RHReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(25),
                'severity' => ['level' => 3, 'justification' => 'Frein d\'urgence actionné sans raison valable, provoquant des chutes parmi les passagers debout. Deuxième signalement similaire.'],
            ],
        ];

        foreach ($redirectedComplaintsData as $data) {
            $severity = $data['severity'];

            $complaint = Complaint::factory()->create([
                'user_id' => $data['driver']->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => $data['step'],
                'status' => $data['status'],
                'com_user_id' => $testCom->id,
                'manager_user_id' => $testManagerGeneric->id,
                'incident_time' => $data['incident_time'],
            ]);

            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $severity['level'],
                'justification' => $severity['justification'],
            ]);
        }

        // --- Cas de test : chauffeur avec manager inactif (pour tester le remplacement) ---
        $inactiveManager = User::factory()->role(UserRole::Manager)->create([
            'first_name' => 'Thomas',
            'last_name' => 'Blanc',
            'email' => 'manager.absent@ratp.fr',
            'matricule' => 'RATP-MGR002',
            'status' => UserStatus::EnVacances,
        ]);
        $centreLagny->users()->attach($inactiveManager->id);

        $driverOfInactiveManager = User::factory()->chauffeur()->create([
            'first_name' => 'Lucas',
            'last_name' => 'Martin',
            'email' => 'chauffeur.remplacant@ratp.fr',
            'matricule' => 'RATP-CHF002',
            'status' => UserStatus::Actif,
        ]);
        $driverOfInactiveManager->managers()->attach($inactiveManager->id);

        $inactiveManagerComplaint = Complaint::factory()->create([
            'user_id' => $driverOfInactiveManager->id,
            'bus_id' => $buses->random()->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'step' => ComplaintStep::ComReview,
            'status' => ComplaintStatus::EnCours,
            'com_user_id' => $testCom->id,
            'incident_time' => now()->subDays(2),
            'description' => 'Comportement irrespectueux envers une personne âgée lors de la montée dans le bus. Plusieurs témoins présents.',
        ]);

        Severity::create([
            'complaint_id' => $inactiveManagerComplaint->id,
            'user_id' => $testCom->id,
            'level' => 2,
            'justification' => 'Incident confirmé par trois témoins. Le chauffeur a refusé d\'attendre qu\'une passagère âgée soit assise avant de redémarrer. Aucun antécédent similaire dans les 12 derniers mois.',
        ]);

        foreach ($managerComplaintsData as $data) {
            $severity = $data['severity'];

            $complaint = Complaint::factory()->create([
                'user_id' => $data['driver']->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => $data['step'],
                'status' => $data['status'],
                'com_user_id' => $testCom->id,
                'manager_user_id' => $testManager->id,
                'incident_time' => $data['incident_time'],
            ]);

            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $severity['level'],
                'justification' => $severity['justification'],
            ]);
        }
    }
}
