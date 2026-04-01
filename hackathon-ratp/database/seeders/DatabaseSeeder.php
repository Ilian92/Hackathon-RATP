<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Bus;
use App\Models\CentreBus;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Gratification;
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
        foreach ($avocats as $avocat) {
            $centreLagny->users()->attach($avocat->id);
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

        // Planning : un chauffeur par bus par jour sur 90 jours + aujourd'hui
        $period = Carbon::today()->subDays(90);
        while ($period->lte(Carbon::today())) {
            foreach ($buses as $bus) {
                Planning::create([
                    'bus_id' => $bus->id,
                    'user_id' => $drivers->random()->id,
                    'date' => $period->toDateString(),
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
    }
}
