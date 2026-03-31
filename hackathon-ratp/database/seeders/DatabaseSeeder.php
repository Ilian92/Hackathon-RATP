<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Bus;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Gratification;
use App\Models\Planning;
use App\Models\Sanction;
use App\Models\Satisfaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Utilisateurs par rôle
        $managers = User::factory(2)->role(UserRole::Manager)->create();
        $drivers = User::factory(5)->chauffeur()->create();
        User::factory(2)->role(UserRole::Com)->create();
        User::factory(1)->role(UserRole::RH)->create();
        User::factory(1)->role(UserRole::Avocat)->create();

        // Compte de test (mot de passe : password)
        $testManager = User::factory()->role(UserRole::Manager)->create([
            'first_name' => 'Test',
            'last_name' => 'Manager',
            'email' => 'test@ratp.fr',
        ]);

        // Lier les chauffeurs à leur manager
        foreach ($drivers as $driver) {
            $driver->managers()->attach($managers->random()->id);
        }

        // Gratifications et sanctions pour chaque chauffeur
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

        // Avis de satisfaction liés aux chauffeurs
        Satisfaction::factory(50)
            ->recycle($clients)
            ->recycle($drivers)
            ->create();
    }
}
