<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Bus;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Satisfaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Utilisateurs par rôle
        $drivers = User::factory(5)->chauffeur()->create();
        User::factory(2)->role(UserRole::Manager)->create();
        User::factory(2)->role(UserRole::Com)->create();
        User::factory(1)->role(UserRole::RH)->create();
        User::factory(1)->role(UserRole::Avocat)->create();

        // Compte de test (mot de passe : password)
        User::factory()->role(UserRole::Manager)->create([
            'first_name' => 'Test',
            'last_name' => 'Manager',
            'email' => 'test@ratp.fr',
        ]);

        // Types de plaintes (les 8 types fixes)
        $complaintTypes = ComplaintType::factory(8)->create();

        // Bus
        $buses = Bus::factory(10)->create();

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

        // Avis de satisfaction
        Satisfaction::factory(50)
            ->recycle($clients)
            ->create();
    }
}
