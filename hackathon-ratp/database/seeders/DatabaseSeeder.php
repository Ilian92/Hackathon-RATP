<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Enums\MissionMoucheDecision;
use App\Enums\MissionMoucheStatus;
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
use App\Models\MissionMouche;
use App\Models\Planning;
use App\Models\RapportMouche;
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

    /** @var list<string> */
    private array $aiNegativeJustifications = [
        "L'usager décrit un comportement déplacé de la part du chauffeur. Incident isolé sans contexte aggravant apparent.",
        'Signalement de non-respect du code de la route en zone urbaine. Vérification avec les caméras embarquées recommandée.',
        "Plainte pour refus de prise en charge à l'arrêt. Contexte à préciser lors de l'évaluation Com.",
        "Comportement irrespectueux signalé lors de la validation d'un titre de transport. Premier signalement apparent.",
        'Retard non justifié signalé. Impact sur les correspondances des usagers selon le rapport.',
        'Conduite jugée brusque par plusieurs passagers. Freinage brusque non justifié selon les témoins.',
        "Refus d'aide à un passager en difficulté signalé. Contexte à vérifier avec les équipes terrain.",
        "Téléphone utilisé pendant la conduite selon le témoignage de l'usager. Comportement à risque identifié.",
        "Musique forte dans l'habitacle gênant les passagers. Manquement au confort des usagers.",
        'Départ anticipé du terminus laissant des passagers sur le quai selon le signalement.',
        "Porte fermée prématurément lors de la montée d'un passager. Incident signalé avec témoins.",
        "Attitude agressive lors d'un contrôle de titre de transport. Deux témoignages concordants reçus.",
        'Propos déplacés envers un passager âgé rapportés par des témoins présents dans le bus.',
        "Non-respect de l'arrêt demandé, contraignant le passager à descendre à l'arrêt suivant.",
        'Vitesse jugée excessive en zone résidentielle selon deux usagers indépendants.',
        'Comportement discriminatoire signalé par un usager. Récit à corroborer avec les caméras embarquées.',
        "Incident lors de la validation d'un pass Navigo. Le chauffeur aurait refusé l'accès sans motif valable.",
        'Conduite erratique signalée en heure de pointe. Plusieurs passagers debout auraient chuté.',
        "Absence d'annonce des arrêts signalée sur une portion du trajet. Impact sur les usagers non-voyants.",
        "Le chauffeur aurait quitté son poste sans raison apparente pendant l'arrêt, retardant le départ.",
    ];

    /** @var list<string> */
    private array $aiPositiveJustifications = [
        "Aide spontanée à une personne en situation de handicap pour monter dans le bus. Comportement exemplaire salué par d'autres passagers.",
        'Ponctualité remarquable maintenue malgré des perturbations de circulation importantes. Plusieurs usagers ont tenu à le signaler.',
        'Geste bienveillant envers une personne âgée qui avait oublié son titre de transport. Attitude professionnelle et humaine saluée.',
        "Signalement d'un bagage oublié par un passager, remis aux autorités compétentes. Initiative responsable et honnête.",
        "Comportement calme et professionnel lors d'une situation conflictuelle entre passagers. Intervention exemplaire ayant évité une escalade.",
        'Accueil chaleureux et informatif pour des touristes désorientés. Image très positive pour la RATP.',
        "Le chauffeur a attendu un usager courant vers l'arrêt malgré le départ imminent. Geste apprécié et signalé par plusieurs témoins.",
        "Conduite douce et attentionnée lors du transport d'une classe scolaire. Félicitations transmises par l'enseignante.",
    ];

    public function run(): void
    {
        // ─── Centres de bus ───────────────────────────────────────────────────────
        $centreLagny = CentreBus::factory()->create(['name' => 'Dépôt de Lagny',  'address' => '1 Rue du Dépôt, Lagny-sur-Marne']);
        $centreThiais = CentreBus::factory()->create(['name' => 'Dépôt de Thiais', 'address' => '45 Avenue du Maréchal Joffre, Thiais']);

        // ─── Utilisateurs ─────────────────────────────────────────────────────────
        $managers = User::factory(2)->role(UserRole::Manager)->create();
        $drivers = User::factory(5)->chauffeur()->create();
        $coms = User::factory(2)->role(UserRole::Com)->create();
        $rhs = User::factory(1)->role(UserRole::RH)->create();
        $avocats = User::factory(1)->role(UserRole::Avocat)->create();

        $testManagerGeneric = User::factory()->role(UserRole::Manager)->create([
            'first_name' => 'Test',
            'last_name' => 'Manager',
            'email' => 'test@ratp.fr',
        ]);
        $testRh = User::factory()->role(UserRole::RH)->create([
            'first_name' => 'Claire',
            'last_name' => 'Moreau',
            'email' => 'rh.test@ratp.fr',
            'matricule' => 'RATP-RH001',
        ]);
        $testCom = User::factory()->role(UserRole::Com)->create([
            'first_name' => 'Marie',
            'last_name' => 'Laurent',
            'email' => 'com.test@ratp.fr',
            'matricule' => 'RATP-COM001',
        ]);
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

        // ─── Liaisons centres / managers ──────────────────────────────────────────
        $centreLagny->users()->attach($testManager->id);
        $centreLagny->users()->attach($testManagerGeneric->id);
        $centreLagny->users()->attach($managers->first()->id);
        $centreThiais->users()->attach($managers->last()->id);

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

        // ─── Équipes chauffeurs ────────────────────────────────────────────────────
        $teamDrivers = User::factory(3)->chauffeur()->create();
        foreach ($teamDrivers as $driver) {
            $driver->managers()->attach($testManager->id);
        }
        $testDriver->managers()->attach($testManager->id);
        foreach ($drivers as $driver) {
            $driver->managers()->attach($managers->random()->id);
        }

        // Gratifications et sanctions historiques (sans lien à une plainte)
        foreach ($drivers as $driver) {
            Gratification::factory(fake()->numberBetween(0, 2))->create(['user_id' => $driver->id]);
            Sanction::factory(fake()->numberBetween(0, 2))->create(['user_id' => $driver->id]);
        }

        // ─── Types de plaintes ────────────────────────────────────────────────────
        $complaintTypes = ComplaintType::factory(8)->create();

        // ─── Bus ──────────────────────────────────────────────────────────────────
        $buses = collect([
            'AB-001-CD', 'EF-002-GH', 'IJ-003-KL', 'MN-004-OP',
            'QR-005-ST', 'UV-006-WX', 'YZ-007-AB', 'CD-008-EF',
            'GH-009-IJ', 'KL-010-MN',
        ])->map(fn (string $code) => Bus::firstOrCreate(['code' => $code]));

        // ─── Lignes ───────────────────────────────────────────────────────────────
        $arrets = Arret::factory(40)->create();

        $lignesLagny = collect();
        foreach (['66', '68', '92', 'N52', '115'] as $nom) {
            $ligne = Ligne::create(['nom' => $nom, 'centre_bus_id' => $centreLagny->id]);
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

        // ─── Plannings (90 jours) ─────────────────────────────────────────────────
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

        // ─── Clients ──────────────────────────────────────────────────────────────
        $clients = Client::factory(30)->create();

        // ─── Plaintes aléatoires avec pré-analyse IA ──────────────────────────────
        // 32 plaintes négatives (niveaux variés, tous en attente ComReview)
        $negativeComplaints = Complaint::factory(32)
            ->recycle($buses)
            ->recycle($complaintTypes)
            ->recycle($drivers)
            ->recycle($clients)
            ->create(['negative' => true, 'step' => ComplaintStep::ComReview, 'status' => ComplaintStatus::EnCours]);

        foreach ($negativeComplaints as $i => $complaint) {
            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => null,
                'level' => fake()->randomElement([1, 1, 2, 2, 3, 4]),
                'justification' => $this->aiNegativeJustifications[$i % count($this->aiNegativeJustifications)],
            ]);
        }

        // 10 plaintes positives (signalements positifs, tous en attente ComReview)
        $positiveComplaints = Complaint::factory(10)
            ->recycle($buses)
            ->recycle($complaintTypes)
            ->recycle($drivers)
            ->recycle($clients)
            ->create(['negative' => false, 'step' => ComplaintStep::ComReview, 'status' => ComplaintStatus::EnCours]);

        foreach ($positiveComplaints as $i => $complaint) {
            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => null,
                'level' => fake()->randomElement([2, 3, 3, 4]),
                'justification' => $this->aiPositiveJustifications[$i % count($this->aiPositiveJustifications)],
            ]);
        }

        // 8 plaintes graves sans qualification (pas encore analysées par l'IA)
        Complaint::factory(8)
            ->recycle($buses)
            ->recycle($complaintTypes)
            ->recycle($drivers)
            ->recycle($clients)
            ->create(['negative' => null, 'step' => ComplaintStep::ComReview, 'status' => ComplaintStatus::EnCours]);

        // ─── Avis de satisfaction ─────────────────────────────────────────────────
        Satisfaction::factory(50)->recycle($clients)->recycle($drivers)->create();

        // ═══════════════════════════════════════════════════════════════════════════
        // DONNÉES COMPLÈTES POUR LE CHAUFFEUR DE TEST (chauffeur.test@ratp.fr)
        // ═══════════════════════════════════════════════════════════════════════════
        $testBus = $buses->first();

        // 4 plaintes négatives à divers stades
        $c1 = Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::EnCours,
            'negative' => true,
            'step' => ComplaintStep::ComReview,
            'description' => "Le chauffeur a brûlé un feu rouge à l'intersection de la rue de la Paix.",
            'incident_time' => now()->subDays(5),
        ]);
        Severity::create([
            'complaint_id' => $c1->id,
            'user_id' => null,
            'level' => 3,
            'justification' => 'Infraction au code de la route signalée par deux usagers et un piéton. Vérification GPS recommandée.',
        ]);

        $c2 = Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::EnCours,
            'negative' => true,
            'step' => ComplaintStep::RHReview,
            'com_user_id' => $testCom->id,
            'rh_user_id' => null,
            'description' => 'Comportement agressif envers un passager lors de la validation du titre de transport.',
            'incident_time' => now()->subDays(12),
        ]);
        Severity::create([
            'complaint_id' => $c2->id,
            'user_id' => $testCom->id,
            'level' => 4,
            'justification' => 'Comportement confirmé par les caméras embarquées. Troisième signalement similaire en 6 mois, ce qui aggrave la note.',
        ]);

        $c3 = Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::Abouti,
            'negative' => true,
            'step' => ComplaintStep::Closed,
            'com_user_id' => $testCom->id,
            'rh_user_id' => $testRh->id,
            'description' => 'Départ du terminus en avance, laissant plusieurs passagers sur le quai.',
            'incident_time' => now()->subDays(45),
        ]);
        Severity::create([
            'complaint_id' => $c3->id,
            'user_id' => $testCom->id,
            'level' => 3,
            'justification' => 'Comportement agressif répété envers les usagers, malgré un avertissement antérieur. Procédure disciplinaire justifiée.',
        ]);
        Sanction::create([
            'user_id' => $testDriver->id,
            'complaint_id' => $c3->id,
            'type' => 'Avertissement',
            'description' => 'Départ anticipé répété malgré les rappels. Avertissement formel enregistré au dossier.',
            'sanctioned_at' => now()->subDays(40)->toDateString(),
        ]);

        $c4 = Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::Clos,
            'negative' => true,
            'step' => ComplaintStep::Closed,
            'com_user_id' => $testCom->id,
            'description' => 'Musique trop forte dans le bus, gênant les passagers.',
            'incident_time' => now()->subDays(90),
        ]);
        Severity::create([
            'complaint_id' => $c4->id,
            'user_id' => $testCom->id,
            'level' => 0,
            'justification' => "Après vérification, l'incident est dû à un malentendu. Dossier classé sans suite.",
        ]);

        // 1 signalement positif clôturé avec gratification pour le chauffeur de test
        $cPos = Complaint::factory()->create([
            'user_id' => $testDriver->id,
            'bus_id' => $testBus->id,
            'complaint_type_id' => $complaintTypes->random()->id,
            'client_id' => $clients->random()->id,
            'status' => ComplaintStatus::Abouti,
            'negative' => false,
            'step' => ComplaintStep::Closed,
            'com_user_id' => $testCom->id,
            'rh_user_id' => $testRh->id,
            'description' => "Le chauffeur a aidé une personne malvoyante à trouver le bon arrêt et l'a accompagnée jusqu'à la porte du bus. Geste très apprécié.",
            'incident_time' => now()->subDays(30),
        ]);
        Severity::create([
            'complaint_id' => $cPos->id,
            'user_id' => $testCom->id,
            'level' => 4,
            'justification' => 'Comportement exemplaire confirmé par trois usagers présents. Signalement positif de haut niveau, mérite une gratification.',
        ]);
        Gratification::create([
            'user_id' => $testDriver->id,
            'complaint_id' => $cPos->id,
            'amount' => 150,
            'reason' => 'Aide exemplaire à une personne malvoyante, saluée par plusieurs passagers. Initiative remarquable.',
            'awarded_at' => now()->subDays(25)->toDateString(),
        ]);

        // Gratifications historiques (non liées à une plainte)
        Gratification::create([
            'user_id' => $testDriver->id,
            'amount' => 200,
            'reason' => 'Excellent taux de satisfaction client sur le trimestre',
            'awarded_at' => now()->subMonths(3)->toDateString(),
        ]);
        Gratification::create([
            'user_id' => $testDriver->id,
            'amount' => 100,
            'reason' => 'Ponctualité exemplaire sur le trimestre',
            'awarded_at' => now()->subMonths(8)->toDateString(),
        ]);

        // Sanction historique (non liée à une plainte)
        Sanction::create([
            'user_id' => $testDriver->id,
            'type' => 'Avertissement',
            'description' => 'Utilisation du téléphone pendant la conduite',
            'sanctioned_at' => now()->subMonths(6)->toDateString(),
        ]);

        // Avis de satisfaction
        Satisfaction::factory(12)->create(['user_id' => $testDriver->id, 'note' => fake()->numberBetween(6, 10)]);
        Satisfaction::factory(3)->create(['user_id' => $testDriver->id, 'note' => fake()->numberBetween(0, 5)]);

        // ═══════════════════════════════════════════════════════════════════════════
        // DONNÉES POUR LE COMPTE COM DE TEST (com.test@ratp.fr)
        // ═══════════════════════════════════════════════════════════════════════════
        // 5 dossiers pré-analysés par l'IA, disponibles (non réclamés)
        $availableForCom = [
            ['negative' => true,  'level' => 1, 'justification' => 'Incident isolé, premier signalement de ce type pour ce chauffeur. Aucun antécédent similaire constaté sur les 12 derniers mois.'],
            ['negative' => true,  'level' => 3, 'justification' => "Comportement signalé lors d'un contrôle de titre. Troisième signalement similaire en 6 mois identifié dans les données."],
            ['negative' => null,  'level' => null, 'justification' => null],
            ['negative' => true,  'level' => 2, 'justification' => 'Témoignages concordants de deux usagers. Impact réel mais sans mise en danger directe des passagers.'],
            ['negative' => false, 'level' => 3, 'justification' => 'Comportement proactif et bienveillant envers un usager en difficulté. Signal positif fort selon les témoins.'],
        ];

        foreach ($availableForCom as $data) {
            $c = Complaint::factory()->create([
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => ComplaintStep::ComReview,
                'status' => ComplaintStatus::EnCours,
                'negative' => $data['negative'],
                'com_user_id' => null,
            ]);
            if ($data['level'] !== null) {
                Severity::create([
                    'complaint_id' => $c->id,
                    'user_id' => null,
                    'level' => $data['level'],
                    'justification' => $data['justification'],
                ]);
            }
        }

        // 3 dossiers pris en charge par Marie Laurent (ComReview, com_user_id set)
        $mineForCom = [
            ['negative' => true,  'level' => 2, 'justification' => 'Refus de priorité signalé. Incident filmé par la caméra intérieure. Niveau modéré, aucun antécédent constaté.'],
            ['negative' => true,  'level' => 4, 'justification' => 'Mise en danger avérée de passagers vulnérables. Infraction au code de la route confirmée par le GPS embarqué.'],
            ['negative' => false, 'level' => 4, 'justification' => "Geste altruiste remarquable envers un groupe scolaire en difficulté. Félicitations exprimées par la directrice de l'école."],
        ];

        foreach ($mineForCom as $data) {
            $c = Complaint::factory()->create([
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => ComplaintStep::ComReview,
                'status' => ComplaintStatus::EnCours,
                'negative' => $data['negative'],
                'com_user_id' => $testCom->id,
            ]);
            Severity::create([
                'complaint_id' => $c->id,
                'user_id' => null,
                'level' => $data['level'],
                'justification' => $data['justification'],
            ]);
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // DONNÉES POUR LE COMPTE RH DE TEST (rh.test@ratp.fr)
        // ═══════════════════════════════════════════════════════════════════════════
        $rhComplaintsData = [
            // Disponibles (non réclamées) — négatives
            [
                'negative' => true,
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => null,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(3),
                'description' => "Agression physique d'un passager filmée par les caméras embarquées. Mise en danger immédiate constatée.",
                'severity' => ['level' => 4, 'justification' => "Agression physique d'un passager filmée par les caméras embarquées. Mise en danger immédiate constatée."],
            ],
            [
                'negative' => true,
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => null,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(7),
                'description' => "Conduite sous l'emprise d'alcool suspectée, test positif confirmé par le dépôt.",
                'severity' => ['level' => 3, 'justification' => "Conduite sous l'emprise d'alcool suspectée, test positif confirmé par le dépôt. Antécédents similaires."],
            ],
            [
                'negative' => true,
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => null,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(10),
                'description' => "Refus délibéré de prise en charge d'un passager en fauteuil roulant, comportement discriminatoire avéré.",
                'severity' => ['level' => 4, 'justification' => "Refus délibéré de prise en charge d'un passager en fauteuil roulant, comportement discriminatoire avéré."],
            ],
            // Disponible — positive
            [
                'negative' => false,
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => null,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(5),
                'description' => "Le chauffeur a gardé son calme et géré seul une situation d'urgence médicale à bord, attendant les secours et rassurant les passagers.",
                'severity' => ['level' => 4, 'justification' => "Gestion exemplaire d'une urgence médicale à bord. Comportement professionnel et humain unanimement salué par les témoins présents."],
            ],
            // Prises en charge par Claire Moreau — négatives
            [
                'negative' => true,
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(14),
                'description' => 'Troisième signalement pour propos déplacés envers des usagers.',
                'severity' => ['level' => 3, 'justification' => 'Troisième signalement pour propos déplacés envers des usagers. Escalade progressive constatée sur 8 mois.'],
            ],
            [
                'negative' => true,
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(18),
                'description' => 'Accident causé par excès de vitesse, blessé léger parmi les passagers.',
                'severity' => ['level' => 4, 'justification' => 'Accident causé par excès de vitesse, blessé léger parmi les passagers. Rapport de police joint au dossier.'],
            ],
            // Prise en charge par Claire Moreau — positive
            [
                'negative' => false,
                'step' => ComplaintStep::RHReview,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(8),
                'description' => "Comportement exceptionnel lors d'une panne en pleine nuit : le chauffeur a attendu avec les passagers et organisé leur prise en charge.",
                'severity' => ['level' => 3, 'justification' => "Initiative remarquable lors d'une panne nocturne. Le chauffeur est resté avec les passagers et a coordonné les secours. Niveau 3 positif."],
            ],
            // Clôturées par Claire Moreau — négatives avec sanction
            [
                'negative' => true,
                'step' => ComplaintStep::Closed,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::Abouti,
                'incident_time' => now()->subDays(45),
                'description' => 'Comportement agressif répété envers les usagers, malgré un avertissement antérieur.',
                'severity' => ['level' => 3, 'justification' => 'Comportement agressif répété envers les usagers, malgré un avertissement antérieur. Procédure disciplinaire justifiée.'],
                'sanction' => ['type' => 'Mise à pied', 'description' => 'Mise à pied de 3 jours suite à des comportements agressifs répétés envers les usagers malgré les avertissements.'],
            ],
            [
                'negative' => true,
                'step' => ComplaintStep::Closed,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::Abouti,
                'incident_time' => now()->subDays(60),
                'description' => 'Falsification de feuille de route confirmée par les relevés GPS.',
                'severity' => ['level' => 4, 'justification' => 'Falsification de feuille de route confirmée par les relevés GPS. Faute grave caractérisée.'],
                'sanction' => ['type' => 'Blâme', 'description' => 'Blâme officiel pour falsification de feuille de route. Dossier transmis à la direction.'],
            ],
            // Clôturée par Claire Moreau — positive avec gratification
            [
                'negative' => false,
                'step' => ComplaintStep::Closed,
                'rh_user_id' => $testRh->id,
                'status' => ComplaintStatus::Abouti,
                'incident_time' => now()->subDays(35),
                'description' => "Le chauffeur a retrouvé et remis un portefeuille contenant des papiers d'identité à leur propriétaire via le dépôt. Geste irréprochable.",
                'severity' => ['level' => 3, 'justification' => 'Honnêteté et sens du service exemplaires. Signalement positif unanime. Le propriétaire a tenu à remercier personnellement le chauffeur.'],
                'gratification' => ['amount' => 100, 'reason' => "Honnêteté exemplaire : remise d'un portefeuille contenant des papiers d'identité à son propriétaire. Comportement irréprochable."],
            ],
        ];

        foreach ($rhComplaintsData as $data) {
            $driver = $drivers->random();
            $complaint = Complaint::factory()->create([
                'user_id' => $driver->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => $data['step'],
                'rh_user_id' => $data['rh_user_id'],
                'com_user_id' => $testCom->id,
                'status' => $data['status'],
                'negative' => $data['negative'],
                'incident_time' => $data['incident_time'],
                'description' => $data['description'],
            ]);

            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $data['severity']['level'],
                'justification' => $data['severity']['justification'],
            ]);

            if (isset($data['sanction'])) {
                Sanction::create([
                    'user_id' => $driver->id,
                    'complaint_id' => $complaint->id,
                    'type' => $data['sanction']['type'],
                    'description' => $data['sanction']['description'],
                    'sanctioned_at' => now()->subDays(5)->toDateString(),
                ]);
            }

            if (isset($data['gratification'])) {
                Gratification::create([
                    'user_id' => $driver->id,
                    'complaint_id' => $complaint->id,
                    'amount' => $data['gratification']['amount'],
                    'reason' => $data['gratification']['reason'],
                    'awarded_at' => now()->subDays(30)->toDateString(),
                ]);
            }
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // DONNÉES POUR LE COMPTE MANAGER DE TEST (manager.test@ratp.fr)
        // ═══════════════════════════════════════════════════════════════════════════
        $managerComplaintsData = [
            // En attente de décision (ManagerReview) — négatifs
            [
                'driver' => $testDriver,
                'negative' => true,
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(3),
                'description' => 'Refus de priorité à un passager PMR. Incident confirmé par deux témoins présents dans le bus.',
                'severity' => ['level' => 2, 'justification' => 'Refus de priorité à un passager PMR. Incident confirmé par deux témoins présents dans le bus.'],
            ],
            [
                'driver' => $teamDrivers[0],
                'negative' => true,
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(6),
                'description' => 'Retard injustifié de 15 minutes au terminus.',
                'severity' => ['level' => 1, 'justification' => 'Retard injustifié de 15 minutes au terminus. Premier incident signalé pour ce chauffeur.'],
            ],
            [
                'driver' => $teamDrivers[1],
                'negative' => true,
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(9),
                'description' => 'Comportement irrespectueux envers une usagère. Témoignage corroboré par les caméras embarquées.',
                'severity' => ['level' => 2, 'justification' => 'Comportement irrespectueux envers une usagère. Témoignage corroboré par les caméras embarquées.'],
            ],
            [
                'driver' => $teamDrivers[2],
                'negative' => true,
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(11),
                'description' => 'Départ anticipé de 3 minutes, laissant un passager sur le quai.',
                'severity' => ['level' => 1, 'justification' => 'Départ anticipé de 3 minutes, laissant un passager sur le quai. Erreur isolée sans antécédent.'],
            ],
            // Transmis au RH — négatifs graves
            [
                'driver' => $testDriver,
                'negative' => true,
                'step' => ComplaintStep::RHReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(20),
                'description' => 'Troisième signalement pour excès de vitesse en zone scolaire.',
                'severity' => ['level' => 3, 'justification' => "Troisième signalement pour excès de vitesse en zone scolaire. Le manager a jugé l'escalade RH nécessaire."],
            ],
            [
                'driver' => $teamDrivers[0],
                'negative' => true,
                'step' => ComplaintStep::RHReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(30),
                'description' => 'Insultes à caractère discriminatoire envers un usager, confirmées par enregistrement sonore embarqué.',
                'severity' => ['level' => 4, 'justification' => 'Insultes à caractère discriminatoire envers un usager, confirmées par enregistrement sonore embarqué.'],
            ],
            // Transmis au RH — positif (le manager voit en lecture seule)
            [
                'driver' => $teamDrivers[1],
                'negative' => false,
                'step' => ComplaintStep::RHReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(15),
                'description' => "Le chauffeur a signalé et sécurisé un passager inconscient dans son bus avant l'arrivée des secours.",
                'severity' => ['level' => 4, 'justification' => 'Initiative remarquable et courage face à une urgence médicale. Comportement exemplaire méritant une gratification selon le Com.'],
            ],
            // Clôturés par le manager
            [
                'driver' => $teamDrivers[1],
                'negative' => true,
                'step' => ComplaintStep::Closed,
                'status' => ComplaintStatus::Clos,
                'incident_time' => now()->subDays(50),
                'description' => 'Incident mineur résolu par entretien oral.',
                'severity' => ['level' => 1, 'justification' => 'Incident mineur résolu par entretien oral. Chauffeur averti et sensibilisé.'],
            ],
            [
                'driver' => $teamDrivers[2],
                'negative' => true,
                'step' => ComplaintStep::Closed,
                'status' => ComplaintStatus::Clos,
                'incident_time' => now()->subDays(70),
                'description' => 'Après vérification, la plainte est non fondée.',
                'severity' => ['level' => 0, 'justification' => 'Après vérification, la plainte est non fondée. Le chauffeur a respecté le protocole en vigueur.'],
            ],
        ];

        foreach ($managerComplaintsData as $data) {
            $complaint = Complaint::factory()->create([
                'user_id' => $data['driver']->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => $data['step'],
                'status' => $data['status'],
                'negative' => $data['negative'],
                'com_user_id' => $testCom->id,
                'manager_user_id' => $testManager->id,
                'incident_time' => $data['incident_time'],
                'description' => $data['description'],
            ]);

            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $data['severity']['level'],
                'justification' => $data['severity']['justification'],
            ]);
        }

        // ─── Dossiers redirigés vers testManagerGeneric (Sophie absente) ──────────
        $redirectedComplaintsData = [
            [
                'driver' => $teamDrivers[0],
                'negative' => true,
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(4),
                'description' => 'Porte fermée sans attendre une passagère en cours de montée.',
                'severity' => ['level' => 2, 'justification' => 'Porte fermée sans attendre une passagère en cours de montée. Incident filmé par la caméra intérieure.'],
            ],
            [
                'driver' => $teamDrivers[1],
                'negative' => true,
                'step' => ComplaintStep::ManagerReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(8),
                'description' => "Téléphone utilisé brièvement à l'arrêt moteur coupé.",
                'severity' => ['level' => 1, 'justification' => "Téléphone utilisé brièvement à l'arrêt moteur coupé. Comportement isolé, aucun antécédent."],
            ],
            [
                'driver' => $testDriver,
                'negative' => true,
                'step' => ComplaintStep::Closed,
                'status' => ComplaintStatus::Clos,
                'incident_time' => now()->subDays(35),
                'description' => 'Incident verbal mineur résolu par entretien téléphonique.',
                'severity' => ['level' => 1, 'justification' => 'Incident verbal mineur résolu par entretien téléphonique. Chauffeur sensibilisé, aucune suite disciplinaire.'],
            ],
            [
                'driver' => $teamDrivers[2],
                'negative' => true,
                'step' => ComplaintStep::RHReview,
                'status' => ComplaintStatus::EnCours,
                'incident_time' => now()->subDays(25),
                'description' => "Frein d'urgence actionné sans raison valable, provoquant des chutes parmi les passagers debout.",
                'severity' => ['level' => 3, 'justification' => "Frein d'urgence actionné sans raison valable, provoquant des chutes parmi les passagers debout. Deuxième signalement similaire."],
            ],
        ];

        foreach ($redirectedComplaintsData as $data) {
            $complaint = Complaint::factory()->create([
                'user_id' => $data['driver']->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => $data['step'],
                'status' => $data['status'],
                'negative' => $data['negative'],
                'com_user_id' => $testCom->id,
                'manager_user_id' => $testManagerGeneric->id,
                'incident_time' => $data['incident_time'],
                'description' => $data['description'],
            ]);

            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $data['severity']['level'],
                'justification' => $data['severity']['justification'],
            ]);
        }

        // ─── Cas manager inactif (pour tester le remplacement) ────────────────────
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
            'negative' => true,
            'com_user_id' => $testCom->id,
            'incident_time' => now()->subDays(2),
            'description' => 'Comportement irrespectueux envers une personne âgée lors de la montée dans le bus. Plusieurs témoins présents.',
        ]);

        Severity::create([
            'complaint_id' => $inactiveManagerComplaint->id,
            'user_id' => null,
            'level' => 2,
            'justification' => "Incident confirmé par trois témoins. Le chauffeur a refusé d'attendre qu'une passagère âgée soit assise avant de redémarrer. Aucun antécédent similaire dans les 12 derniers mois.",
        ]);

        // ═══════════════════════════════════════════════════════════════════════════
        // DONNÉES SUPPLÉMENTAIRES POUR LES DASHBOARDS
        // ═══════════════════════════════════════════════════════════════════════════

        // ─── Complète l'historique des chauffeurs de l'équipe de Sophie ───────────
        $teamHistory = [
            // testDriver — plusieurs signalements négatifs anciens (clôturés)
            ['driver' => $testDriver, 'negative' => true, 'level' => 2, 'daysAgo' => 55, 'step' => ComplaintStep::Closed, 'status' => ComplaintStatus::Abouti, 'sanction' => ['type' => 'Avertissement', 'description' => 'Conduite brusque signalée par plusieurs passagers. Entretien effectué.', 'daysAgo' => 50]],
            ['driver' => $testDriver, 'negative' => true, 'level' => 1, 'daysAgo' => 80, 'step' => ComplaintStep::Closed, 'status' => ComplaintStatus::Clos, 'sanction' => null],
            // teamDrivers[0] — historique varié
            ['driver' => $teamDrivers[0], 'negative' => true, 'level' => 3, 'daysAgo' => 40, 'step' => ComplaintStep::Closed, 'status' => ComplaintStatus::Abouti, 'sanction' => ['type' => 'Mise à pied', 'description' => "Mise à pied d'un jour pour comportement discriminatoire envers un usager.", 'daysAgo' => 36]],
            ['driver' => $teamDrivers[0], 'negative' => true, 'level' => 1, 'daysAgo' => 75, 'step' => ComplaintStep::Closed, 'status' => ComplaintStatus::Clos, 'sanction' => null],
            ['driver' => $teamDrivers[0], 'negative' => false, 'level' => 3, 'daysAgo' => 60, 'step' => ComplaintStep::Closed, 'status' => ComplaintStatus::Abouti, 'sanction' => null, 'gratification' => ['amount' => 80, 'reason' => "Aide spontanée à une famille avec poussette lors d'une avarie d'ascenseur.", 'daysAgo' => 55]],
            // teamDrivers[1] — peu de signalements
            ['driver' => $teamDrivers[1], 'negative' => true, 'level' => 2, 'daysAgo' => 65, 'step' => ComplaintStep::Closed, 'status' => ComplaintStatus::Clos, 'sanction' => null],
            // teamDrivers[2] — historique propre, un positif
            ['driver' => $teamDrivers[2], 'negative' => false, 'level' => 4, 'daysAgo' => 45, 'step' => ComplaintStep::Closed, 'status' => ComplaintStatus::Abouti, 'sanction' => null, 'gratification' => ['amount' => 200, 'reason' => "Comportement exemplaire lors d'une urgence médicale à bord. Gestes de premiers secours prodigués.", 'daysAgo' => 40]],
        ];

        foreach ($teamHistory as $data) {
            $complaint = Complaint::factory()->create([
                'user_id' => $data['driver']->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => $data['step'],
                'status' => $data['status'],
                'negative' => $data['negative'],
                'com_user_id' => $testCom->id,
                'manager_user_id' => $testManager->id,
                'incident_time' => now()->subDays($data['daysAgo']),
            ]);
            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $data['level'],
                'justification' => $this->aiNegativeJustifications[array_rand($this->aiNegativeJustifications)],
            ]);
            if (isset($data['sanction'])) {
                Sanction::create([
                    'user_id' => $data['driver']->id,
                    'complaint_id' => $complaint->id,
                    'type' => $data['sanction']['type'],
                    'description' => $data['sanction']['description'],
                    'sanctioned_at' => now()->subDays($data['sanction']['daysAgo'])->toDateString(),
                ]);
            }
            if (isset($data['gratification'])) {
                Gratification::create([
                    'user_id' => $data['driver']->id,
                    'complaint_id' => $complaint->id,
                    'amount' => $data['gratification']['amount'],
                    'reason' => $data['gratification']['reason'],
                    'awarded_at' => now()->subDays($data['gratification']['daysAgo'])->toDateString(),
                ]);
            }
        }

        // ─── Dossiers traités ce mois par Marie Laurent (Com) ─────────────────────
        $comTreatedThisMonth = [
            ['negative' => true, 'level' => 1, 'nextStep' => ComplaintStep::ManagerReview],
            ['negative' => true, 'level' => 2, 'nextStep' => ComplaintStep::ManagerReview],
            ['negative' => true, 'level' => 3, 'nextStep' => ComplaintStep::RHReview],
            ['negative' => true, 'level' => 4, 'nextStep' => ComplaintStep::RHReview],
            ['negative' => true, 'level' => 0, 'nextStep' => ComplaintStep::Closed],
            ['negative' => true, 'level' => 2, 'nextStep' => ComplaintStep::ManagerReview],
            ['negative' => false, 'level' => 3, 'nextStep' => ComplaintStep::RHReview],
            ['negative' => false, 'level' => 4, 'nextStep' => ComplaintStep::RHReview],
            ['negative' => true, 'level' => 1, 'nextStep' => ComplaintStep::ManagerReview],
            ['negative' => true, 'level' => 2, 'nextStep' => ComplaintStep::ManagerReview],
            ['negative' => true, 'level' => 3, 'nextStep' => ComplaintStep::RHReview],
            ['negative' => false, 'level' => 2, 'nextStep' => ComplaintStep::RHReview],
        ];

        foreach ($comTreatedThisMonth as $data) {
            $complaint = Complaint::factory()->create([
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'user_id' => $drivers->random()->id,
                'step' => $data['nextStep'],
                'status' => $data['nextStep'] === ComplaintStep::Closed ? ComplaintStatus::Clos : ComplaintStatus::EnCours,
                'negative' => $data['negative'],
                'com_user_id' => $testCom->id,
                'incident_time' => now()->subDays(fake()->numberBetween(2, 20)),
                'updated_at' => now()->startOfMonth()->addDays(fake()->numberBetween(0, max(0, now()->day - 1))),
            ]);
            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $data['level'],
                'justification' => $data['negative']
                    ? $this->aiNegativeJustifications[array_rand($this->aiNegativeJustifications)]
                    : $this->aiPositiveJustifications[array_rand($this->aiPositiveJustifications)],
            ]);
        }

        // ─── Dossiers clôturés ce mois par Claire Moreau (RH) ─────────────────────
        $rhClosedThisMonth = [
            ['negative' => true, 'level' => 3, 'sanction' => ['type' => 'Avertissement', 'description' => 'Comportement agressif répété. Avertissement formel notifié.']],
            ['negative' => true, 'level' => 4, 'sanction' => ['type' => 'Mise à pied', 'description' => 'Mise à pied de 5 jours pour infraction grave au code de la route.']],
            ['negative' => true, 'level' => 2, 'sanction' => ['type' => 'Avertissement', 'description' => 'Propos déplacés envers un usager. Rappel des règles de conduite professionnelle.']],
            ['negative' => true, 'level' => 3, 'sanction' => ['type' => 'Blâme', 'description' => 'Récidive de conduite sans ceinture de sécurité. Blâme inscrit au dossier.']],
            ['negative' => false, 'level' => 4, 'gratification' => ['amount' => 250, 'reason' => "Intervention héroïque lors d'un accident de circulation à bord. Comportement exemplaire unanimement salué."]],
            ['negative' => false, 'level' => 3, 'gratification' => ['amount' => 120, 'reason' => "Accueil chaleureux et assistance remarquable envers des touristes lors d'une grève des transports."]],
            ['negative' => true, 'level' => 1, 'sanction' => null],
        ];

        foreach ($rhClosedThisMonth as $data) {
            $driver = $drivers->random();
            $complaint = Complaint::factory()->create([
                'user_id' => $driver->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => ComplaintStep::Closed,
                'status' => isset($data['sanction']) && $data['sanction'] !== null ? ComplaintStatus::Abouti : (isset($data['gratification']) ? ComplaintStatus::Abouti : ComplaintStatus::Clos),
                'negative' => $data['negative'],
                'com_user_id' => $testCom->id,
                'rh_user_id' => $testRh->id,
                'incident_time' => now()->subDays(fake()->numberBetween(20, 45)),
                'updated_at' => now()->startOfMonth()->addDays(fake()->numberBetween(0, max(0, now()->day - 1))),
            ]);
            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $data['level'],
                'justification' => $data['negative']
                    ? $this->aiNegativeJustifications[array_rand($this->aiNegativeJustifications)]
                    : $this->aiPositiveJustifications[array_rand($this->aiPositiveJustifications)],
            ]);
            if (! empty($data['sanction'])) {
                Sanction::create([
                    'user_id' => $driver->id,
                    'complaint_id' => $complaint->id,
                    'type' => $data['sanction']['type'],
                    'description' => $data['sanction']['description'],
                    'sanctioned_at' => now()->startOfMonth()->addDays(fake()->numberBetween(0, max(0, now()->day - 1)))->toDateString(),
                ]);
            }
            if (isset($data['gratification'])) {
                Gratification::create([
                    'user_id' => $driver->id,
                    'complaint_id' => $complaint->id,
                    'amount' => $data['gratification']['amount'],
                    'reason' => $data['gratification']['reason'],
                    'awarded_at' => now()->startOfMonth()->addDays(fake()->numberBetween(0, max(0, now()->day - 1)))->toDateString(),
                ]);
            }
        }

        // ─── Dossiers clôturés ce mois par Sophie Lefèvre (Manager) ───────────────
        $managerClosedThisMonth = [
            ['driver' => $testDriver, 'negative' => true, 'level' => 1, 'status' => ComplaintStatus::Clos],
            ['driver' => $teamDrivers[0], 'negative' => true, 'level' => 0, 'status' => ComplaintStatus::Clos],
            ['driver' => $teamDrivers[1], 'negative' => true, 'level' => 2, 'status' => ComplaintStatus::Clos],
            ['driver' => $teamDrivers[2], 'negative' => true, 'level' => 1, 'status' => ComplaintStatus::Clos],
            ['driver' => $testDriver, 'negative' => true, 'level' => 2, 'status' => ComplaintStatus::Abouti, 'sanction' => ['type' => 'Avertissement', 'description' => 'Deuxième incident en moins de deux mois. Avertissement formel.']],
        ];

        foreach ($managerClosedThisMonth as $data) {
            $complaint = Complaint::factory()->create([
                'user_id' => $data['driver']->id,
                'bus_id' => $buses->random()->id,
                'complaint_type_id' => $complaintTypes->random()->id,
                'client_id' => $clients->random()->id,
                'step' => ComplaintStep::Closed,
                'status' => $data['status'],
                'negative' => $data['negative'],
                'com_user_id' => $testCom->id,
                'manager_user_id' => $testManager->id,
                'incident_time' => now()->subDays(fake()->numberBetween(25, 50)),
                'updated_at' => now()->startOfMonth()->addDays(fake()->numberBetween(0, max(0, now()->day - 1))),
            ]);
            Severity::create([
                'complaint_id' => $complaint->id,
                'user_id' => $testCom->id,
                'level' => $data['level'],
                'justification' => $this->aiNegativeJustifications[array_rand($this->aiNegativeJustifications)],
            ]);
            if (! empty($data['sanction'])) {
                Sanction::create([
                    'user_id' => $data['driver']->id,
                    'complaint_id' => $complaint->id,
                    'type' => $data['sanction']['type'],
                    'description' => $data['sanction']['description'],
                    'sanctioned_at' => now()->startOfMonth()->addDays(fake()->numberBetween(0, max(0, now()->day - 1)))->toDateString(),
                ]);
            }
        }

        // ─── Avis de satisfaction supplémentaires ─────────────────────────────────
        $allTeamDrivers = collect([$testDriver])->merge($teamDrivers);
        foreach ($allTeamDrivers as $driver) {
            Satisfaction::factory(fake()->numberBetween(8, 20))->create(['user_id' => $driver->id]);
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // DONNÉES MOUCHE
        // ═══════════════════════════════════════════════════════════════════════════

        // ─── Agents mouche ────────────────────────────────────────────────────────
        $testMouche = User::factory()->role(UserRole::Mouche)->create([
            'first_name' => 'Isabelle',
            'last_name' => 'Dumont',
            'email' => 'mouche.test@ratp.fr',
            'matricule' => 'RATP-MCH001',
        ]);
        $mouche2 = User::factory()->role(UserRole::Mouche)->create([
            'first_name' => 'Marc',
            'last_name' => 'Petit',
            'email' => 'mouche2.test@ratp.fr',
            'matricule' => 'RATP-MCH002',
        ]);
        $mouche3 = User::factory()->role(UserRole::Mouche)->create([
            'first_name' => 'Nathalie',
            'last_name' => 'Renaud',
            'email' => 'mouche3.test@ratp.fr',
            'matricule' => 'RATP-MCH003',
        ]);
        $centreLagny->users()->attach([$testMouche->id, $mouche2->id, $mouche3->id]);

        $ligne1 = $toutesLesLignes->first();

        // ─── Mission 1 : En cours, 1 rapport sur 3 soumis ─────────────────────────
        // testMouche et mouche3 n'ont pas encore soumis → testMouche peut remplir son rapport
        $mission1 = MissionMouche::create([
            'driver_user_id' => $testDriver->id,
            'manager_user_id' => $testManager->id,
            'status' => MissionMoucheStatus::EnCours,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);
        $mission1->mouches()->attach([
            $testMouche->id => ['submitted_at' => null],
            $mouche2->id => ['submitted_at' => now()->subDays(2)],
            $mouche3->id => ['submitted_at' => null],
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission1->id,
            'mouche_user_id' => $mouche2->id,
            'ligne_id' => $ligne1->id,
            'date_observation' => now()->subDays(3)->toDateString(),
            'ponctualite' => 3,
            'conduite' => 2,
            'politesse' => 2,
            'tenue' => 4,
            'securite' => 3,
            'gestion_conflit' => null,
            'observation' => "Le chauffeur était distrait à plusieurs reprises. J'ai observé deux freinages brusques non justifiés. Tenue correcte mais attitude peu accueillante envers les passagers.",
        ]);

        // ─── Mission 2 : Complétée, en attente de décision manager ────────────────
        // Tous les rapports sont reçus → Sophie doit décider
        $mission2 = MissionMouche::create([
            'driver_user_id' => $teamDrivers[0]->id,
            'manager_user_id' => $testManager->id,
            'status' => MissionMoucheStatus::Completee,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(1),
        ]);
        $mission2->mouches()->attach([
            $testMouche->id => ['submitted_at' => now()->subDays(2)],
            $mouche2->id => ['submitted_at' => now()->subDays(3)],
            $mouche3->id => ['submitted_at' => now()->subDays(1)],
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission2->id,
            'mouche_user_id' => $testMouche->id,
            'ligne_id' => $ligne1->id,
            'date_observation' => now()->subDays(3)->toDateString(),
            'ponctualite' => 1,
            'conduite' => 2,
            'politesse' => 1,
            'tenue' => 3,
            'securite' => 2,
            'gestion_conflit' => 1,
            'observation' => 'Comportement très problématique. Le chauffeur a refusé de laisser monter un passager en fauteuil roulant, invoquant un prétendu manque de place alors que le bus était presque vide. Ton agressif et irrespectueux.',
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission2->id,
            'mouche_user_id' => $mouche2->id,
            'ligne_id' => $ligne1->id,
            'date_observation' => now()->subDays(4)->toDateString(),
            'ponctualite' => 2,
            'conduite' => 2,
            'politesse' => 1,
            'tenue' => 3,
            'securite' => 2,
            'gestion_conflit' => null,
            'observation' => "Conduite brusque, plusieurs passagers debout ont dû s'agripper. Le chauffeur n'a fait aucune annonce aux arrêts. Attitude froide et peu coopérative.",
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission2->id,
            'mouche_user_id' => $mouche3->id,
            'ligne_id' => null,
            'date_observation' => now()->subDays(2)->toDateString(),
            'ponctualite' => 2,
            'conduite' => 3,
            'politesse' => 2,
            'tenue' => 2,
            'securite' => 3,
            'gestion_conflit' => 2,
            'observation' => "J'ai observé une altercation avec un passager lors du contrôle d'un titre de transport. Le chauffeur a haussé la voix de façon disproportionnée. Tenue négligée.",
        ]);

        // ─── Mission 3 : Décidée — classé sans suite ──────────────────────────────
        $mission3 = MissionMouche::create([
            'driver_user_id' => $teamDrivers[1]->id,
            'manager_user_id' => $testManager->id,
            'status' => MissionMoucheStatus::Decidee,
            'decision' => MissionMoucheDecision::Cloture,
            'manager_notes' => 'Les trois rapports convergent vers un comportement globalement correct. Les observations relevées sont ponctuelles et ne justifient pas de mesure disciplinaire. Dossier classé sans suite.',
            'decided_at' => now()->subDays(5),
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(5),
        ]);
        $mission3->mouches()->attach([
            $testMouche->id => ['submitted_at' => now()->subDays(8)],
            $mouche2->id => ['submitted_at' => now()->subDays(9)],
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission3->id,
            'mouche_user_id' => $testMouche->id,
            'ligne_id' => $toutesLesLignes->skip(1)->first()?->id,
            'date_observation' => now()->subDays(9)->toDateString(),
            'ponctualite' => 4,
            'conduite' => 4,
            'politesse' => 3,
            'tenue' => 5,
            'securite' => 4,
            'gestion_conflit' => null,
            'observation' => "Rien de particulier à signaler. Le chauffeur a conduit de façon professionnelle et respectueuse. Légère impatience lors d'une validation de titre de transport, mais sans incident.",
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission3->id,
            'mouche_user_id' => $mouche2->id,
            'ligne_id' => $toutesLesLignes->skip(1)->first()?->id,
            'date_observation' => now()->subDays(10)->toDateString(),
            'ponctualite' => 5,
            'conduite' => 4,
            'politesse' => 4,
            'tenue' => 5,
            'securite' => 5,
            'gestion_conflit' => null,
            'observation' => 'Bon professionnalisme général. Le chauffeur a aidé une personne âgée à trouver sa place. Aucun manquement observé lors de mon trajet.',
        ]);

        // ─── Mission 4 : Décidée — sanction appliquée ─────────────────────────────
        $mission4 = MissionMouche::create([
            'driver_user_id' => $teamDrivers[2]->id,
            'manager_user_id' => $testManager->id,
            'status' => MissionMoucheStatus::Decidee,
            'decision' => MissionMoucheDecision::Sanctionne,
            'manager_notes' => 'Les rapports des trois mouches concordent sur un comportement irrespectueux et une conduite dangereuse. Au regard des antécédents et de la gravité des faits, un blâme formel a été prononcé.',
            'decided_at' => now()->subDays(15),
            'created_at' => now()->subDays(35),
            'updated_at' => now()->subDays(15),
        ]);
        $mission4->mouches()->attach([
            $testMouche->id => ['submitted_at' => now()->subDays(18)],
            $mouche2->id => ['submitted_at' => now()->subDays(19)],
            $mouche3->id => ['submitted_at' => now()->subDays(17)],
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission4->id,
            'mouche_user_id' => $testMouche->id,
            'ligne_id' => $toutesLesLignes->skip(2)->first()?->id,
            'date_observation' => now()->subDays(19)->toDateString(),
            'ponctualite' => 2,
            'conduite' => 1,
            'politesse' => 2,
            'tenue' => 3,
            'securite' => 1,
            'gestion_conflit' => 1,
            'observation' => "Conduite très dangereuse, vitesse excessive en zone résidentielle. Le chauffeur a grillé un feu orange appuyé et n'a pas cédé la priorité à un piéton sur un passage clouté. J'ai personnellement assisté à une altercation avec un passager qui a failli dégénérer.",
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission4->id,
            'mouche_user_id' => $mouche2->id,
            'ligne_id' => $toutesLesLignes->skip(2)->first()?->id,
            'date_observation' => now()->subDays(20)->toDateString(),
            'ponctualite' => 2,
            'conduite' => 1,
            'politesse' => 1,
            'tenue' => 2,
            'securite' => 2,
            'gestion_conflit' => null,
            'observation' => "Chauffeur visiblement de mauvaise humeur. A refusé de répondre à une question d'une passagère. Freinage brusque à deux reprises. Tenue et comportement en dessous des standards attendus.",
        ]);
        RapportMouche::create([
            'mission_mouche_id' => $mission4->id,
            'mouche_user_id' => $mouche3->id,
            'ligne_id' => null,
            'date_observation' => now()->subDays(18)->toDateString(),
            'ponctualite' => 3,
            'conduite' => 2,
            'politesse' => 2,
            'tenue' => 2,
            'securite' => 2,
            'gestion_conflit' => 2,
            'observation' => "Comportement globalement insuffisant. Le chauffeur a ignoré plusieurs sonnettes d'arrêt avant de finalement s'arrêter. Un passager a protesté et s'en est suivi un échange tendu.",
        ]);
        Sanction::create([
            'user_id' => $teamDrivers[2]->id,
            'mission_mouche_id' => $mission4->id,
            'type' => 'Blâme',
            'description' => 'Blâme formel suite au rapport de mission mouche. Conduite dangereuse et comportement irrespectueux répétés confirmés par trois agents indépendants.',
            'sanctioned_at' => now()->subDays(15)->toDateString(),
        ]);
    }
}
