# Fonctionnalités — RATP Réseaux de Surface

Système de gestion des plaintes et des missions de contrôle qualité pour la RATP.

**Stack :** Laravel 13, PostgreSQL, Tailwind CSS v4, Alpine.js, Leaflet.js
**Rôles utilisateurs :** Com · Manager · RH · Chauffeur · Mouche

---

## Accès public (sans connexion)

### Dépôt de plainte
- Formulaire public accessible depuis la page d'accueil
- Sélection dynamique : ligne RATP → arrêt → type de plainte
- Détection automatique du planning correspondant (date, heure, trajet de nuit inclus)
- Fallback automatique si aucun planning exact : même ligne ce jour-là, puis planning le plus récent
- Association automatique au bus et au chauffeur en service
- Création d'un profil client anonyme via email

### QR Code bus
- Scan d'un QR code placé dans le bus
- Génération d'un token valide 24h
- Limitation anti-spam : max 3 soumissions par IP par tranche de 24h

### Avis de satisfaction via QR Code
- Formulaire de notation (note + description optionnelle)
- Lié au bus, au chauffeur en service et à l'email du passager

### Plainte via QR Code
- Formulaire de plainte déclenché depuis le bus
- Pré-rempli avec les données du planning en cours

---

## Workflow d'une plainte

```
Création (public / QR)
        ↓
    analyse IA
 (niveau 0–4 + positif/négatif)
         ↓
  Étape ComReview
  → L'agent Com évalue la sévérité (0–4) et la nature (positive / négative)
        ↓
  Niveau 0         → Classé sans suite (Fermé)
  Niveau 1–2 négatif → Étape ManagerReview
  Niveau 3–4 négatif → Étape RHReview (direct)
  Plainte positive  → Étape RHReview (direct)
        ↓
  ManagerReview
  → Sanction, transmission au RH ou clôture sans suite
        ↓
  RHReview
  → Sanction, gratification ou clôture
        ↓
  Fermée (Abouti / Clos)
  → Notifications envoyées au chauffeur, au Com et au Manager
```

---

## Rôle : Agent Com

### Dashboard
- KPIs : plaintes disponibles, mes dossiers en cours, traités ce mois
- Score de satisfaction global (tous chauffeurs)
- Tendance satisfaction sur 6 mois
- Répartition des plaintes par nature (positive / négative)
- Distribution des niveaux de sévérité assignés
- Top 5 des types de signalements traités
- Délai moyen d'attente en file non réclamée
- Nombre de dossiers bloqués depuis plus de 3 jours
- Volume mensuel de plaintes entrantes (6 derniers mois)
- Carte interactive des lignes (colorée par densité de plaintes)

### Gestion des plaintes
- Liste avec onglets : Disponibles · Mes dossiers · Traités
- Filtres : type, sévérité, chauffeur, dossiers importants (niv. 3–4)
- Tri par : date d'incident, sévérité, type, chauffeur, bus
- Prise en charge d'un dossier (claim)
- Affectation de la sévérité (0–4) avec justification
- Classification positive / négative
- Routage automatique selon la sévérité et la nature
- Sélection d'un manager de remplacement si le manager habituel est inactif
- Notifications automatiques au manager ou aux agents RH

---

## Rôle : Manager

### Dashboard
- KPIs : dossiers en attente, transmis au RH, clôturés ce mois, taille de l'équipe
- Alerte missions mouche en attente de décision
- Satisfaction moyenne de l'équipe avec tendance sur 6 mois
- Répartition des dossiers par étape, nature et sévérité
- Tableau de performance par chauffeur : signalements, sanctions, gratifications, note de satisfaction
- Ancienneté des dossiers en attente (0–3 j, 4–7 j, 8–14 j, +14 j)
- Délai moyen de résolution
- Volume mensuel (6 derniers mois)
- Carte interactive des lignes du centre bus

### Gestion des plaintes
- Périmètre : dossiers qui lui sont assignés + dossiers des chauffeurs de son équipe
- Onglets : En attente · En cours au RH · Clôturés
- Filtres : type, chauffeur, sévérité, nature
- Identification du chauffeur si inconnu
- Transmission au service RH
- Sanction directe (type + description) → dossier clôturé
- Clôture sans action

### Profil chauffeur
- Vue détaillée d'un chauffeur de l'équipe
- Historique des plaintes, sanctions et gratifications
- Note de satisfaction passagers (sur 5)
- Score interne : 70 % satisfaction + 30 % (5 − min(plaintes abouties, 5))

### Missions mouche
- Création d'une mission : sélection du chauffeur à surveiller, affectation automatique des 3 agents mouche les moins chargés
- Suivi : statuts En cours · Complétée · Décidée
- Détail : rapports soumis par chaque mouche, lignes observées
- Décision finale : Classé sans suite ou Sanctionné (avec création d'une sanction)

---

## Rôle : RH

### Dashboard
- KPIs : dossiers disponibles, mes dossiers en cours, clôturés ce mois, sanctions et gratifications ce mois
- Score de satisfaction global avec tendance sur 6 mois
- Répartition positif / négatif des dossiers clôturés
- Distribution des niveaux de sévérité
- Breakdown des types de sanctions prononcées
- Taux d'aboutissement (dossiers avec action / total clôturés)
- Délai moyen de résolution
- 5 dernières sanctions et gratifications
- Volume mensuel : reçus vs clôturés (6 derniers mois)
- Carte interactive des lignes du centre bus

### Gestion des plaintes
- Onglets : Disponibles · Mes dossiers · Clôturés
- Filtres : type, chauffeur, sévérité
- Prise en charge d'un dossier
- Identification du chauffeur si inconnu
- Sanction (type + description) → dossier clôturé
- Gratification (motif + montant optionnel) → réservé aux plaintes positives
- Clôture sans action
- Notifications automatiques au chauffeur, à l'agent Com et au Manager

---

## Rôle : Chauffeur

### Profil personnel
- Informations : matricule, statut, managers assignés, centres bus
- Dossiers visibles : uniquement ceux en étape RH ou clôturés
- Répartition des signalements (positifs / négatifs / aboutis / clos)
- Note de satisfaction passagers (sur 5)
- Score interne calculé
- Historique des gratifications et sanctions reçues

---

## Rôle : Mouche (agent de contrôle qualité)

### Dashboard mouche
- Missions en attente de rapport (avec lien direct vers le formulaire)
- Missions dont le rapport a été soumis
- Compteurs récapitulatifs

### Rapport de mission
- Formulaire de notation sur 5 critères obligatoires (1–5) : ponctualité, conduite, politesse, tenue, sécurité
- Critère optionnel : gestion des conflits
- Observation libre (3 000 caractères max)
- Sélection de la ligne observée
- Date d'observation (≤ aujourd'hui)
- Validation automatique : si toutes les mouches ont soumis → mission passée en statut Complétée

---

## Fonctionnalités transversales

### Analyse IA de la plainte
- Classification automatique à la création : sévérité (0–4) + nature (positive / négative)
- Modèle de langage entraîné sur les données historiques de plaintes

### Système de notifications (cloche en temps réel)
Chaque rôle reçoit des notifications ciblées :

| Événement | Destinataires |
|---|---|
| Nouveau dossier assigné | Manager |
| Dossier transmis directement au RH | Manager |
| Nouveau dossier transmis au RH | Agents RH |
| Dossier de niveau 3 ou 4 disponible | Agents Com |
| Dossier clôturé | Agent Com · Manager |
| Dossier traité et clôturé | Chauffeur |
| Sanction enregistrée | Chauffeur |
| Gratification enregistrée | Chauffeur |

- Affichage du nombre de non-lues sur la cloche
- Dropdown des 8 dernières notifications avec horodatage relatif
- Marquage individuel ou global comme lu
- Clic → redirection vers le dossier concerné

### Carte interactive des lignes
- Disponible sur les dashboards Manager, Com et RH
- Affichage des lignes du centre bus de l'employé
- Coloration verte → rouge selon la densité de plaintes
- Filtres : période (7 j / 30 j / 90 j / tout), nature, niveau de sévérité
- Popups par arrêt avec comptage des plaintes

### Gestion du profil
- Modification de l'email et du mot de passe
- Suppression du compte avec confirmation
- Page "Droits et légal" (RGPD) : export des données, rectification, opposition au traitement, droit à l'effacement, politique de conservation, contact DPO

### API pour intégration IA (n8n / automatisation)
- `GET /api/complaints/pending` — Récupère la plainte la plus ancienne en attente de classification (id, type, description)
- `POST /api/complaints/severity` — Enregistre la sévérité générée par l'IA (niveau 0–4, justification, positif/négatif)
- Authentification par token Bearer

---

## Tableau récapitulatif des accès

| Fonctionnalité | Public | Com | Manager | RH | Chauffeur | Mouche |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| Dépôt de plainte | ✓ | | | | | |
| Avis de satisfaction QR | ✓ | | | | | |
| Dashboard personnalisé | | ✓ | ✓ | ✓ | | |
| Prise en charge d'un dossier | | ✓ | | ✓ | | |
| Évaluation de la sévérité | | ✓ | | | | |
| Identification du chauffeur | | | ✓ | ✓ | | |
| Transmission au RH | | | ✓ | | | |
| Sanction | | | ✓ | ✓ | | |
| Gratification | | | | ✓ | | |
| Carte interactive | | ✓ | ✓ | ✓ | | |
| Notifications | | ✓ | ✓ | ✓ | ✓ | |
| Créer une mission mouche | | | ✓ | | | |
| Décider sur une mission | | | ✓ | | | |
| Soumettre un rapport mouche | | | | | | ✓ |
| Voir ses dossiers | | | | | ✓ | |
| API IA | | | | | | |
