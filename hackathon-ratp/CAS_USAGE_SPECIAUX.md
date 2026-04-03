# Cas d'Usage Spéciaux — RATP Réseaux de Surface

Fonctionnalités avancées gérant les situations atypiques et cas limites du système.

---

## Points importants

| # | Cas d'usage |
|---|---|
| 1 | Manager indisponible — Fallback automatique |
| 2 | Double visibilité manager sur un dossier |
| 3 | Expiration du token QR Code (24h) |
| 4 | Anti-spam QR Code — Limitation par IP |
| 8 | Fallback multi-niveaux pour lier une plainte à un planning |
| 9 | Score interne pondéré des chauffeurs |
| 13 | Routage automatique selon sévérité et nature |
| 14 | Notifications ciblées par rôle et événement |

---

## 1. Manager indisponible — Fallback automatique

Lorsqu'un agent Com route un dossier vers un manager, le système détecte si le manager habituel du chauffeur est actif.

**Comportement :**
- Si le manager est **actif** → le dossier lui est assigné automatiquement
- Si le manager est **inactif** → l'agent Com voit une liste de managers alternatifs actifs du même centre bus
- Si **aucun manager actif** dans le centre bus → le dossier reste en attente sans assignation

**Implémentation :** Le formulaire de revue Com expose un sélecteur de manager de remplacement conditionnel, pré-filtré sur les managers actifs du même centre bus que le chauffeur.

---

## 2. Double visibilité manager sur un dossier

Un manager peut voir un dossier de deux façons différentes :
- Il est le **manager assigné** au dossier (responsable direct)
- Le dossier concerne un **chauffeur de son équipe** (même si assigné à un autre manager)

Cela permet au manager de garder une vue complète sur son équipe, même lorsqu'un dossier a été réassigné.

---

## 3. Expiration du token QR Code (24h)

Les QR codes placés dans les bus génèrent un token temporaire stocké en cache.

**Comportement :**
- Token valide **24 heures** après génération
- Passé ce délai, l'URL redirige vers une page d'expiration explicite (`/qrcode-expired`)
- Le passager doit rescanner un QR code actif

---

## 4. Anti-spam QR Code — Limitation par IP

Pour éviter les soumissions abusives via QR code :

**Règle :** Maximum **3 soumissions par adresse IP** sur une fenêtre glissante de 24 heures.

- Le compteur est basé sur l'IP, pas sur le token
- Dépasser la limite retourne une réponse d'erreur explicite
- La fenêtre est glissante (pas réinitialisée à minuit)

---

## 5. Prévention de la double soumission de rapport mouche

Un agent mouche ne peut soumettre qu'**un seul rapport par mission**.

**Implémentation :** La table pivot entre mouches et missions contient un champ `submitted_at`. Si ce champ est non nul, le formulaire de rapport est désactivé et l'agent voit son rapport existant en lecture seule.

---

## 6. Complétion automatique d'une mission mouche

Une mission mouche implique 3 agents. Le passage au statut **Complétée** est automatique.

**Déclencheur :** Lors de la soumission du dernier rapport manquant, le système vérifie si tous les agents ont soumis (`isComplete()`). Si oui, la mission passe automatiquement en statut `Complétée` sans action manuelle du manager.

---

## 7. Détection des trajets de nuit dans les plannings

Les plannings peuvent couvrir des trajets dont l'heure de fin est **inférieure à l'heure de début** (ex : 22h00 → 02h00, chevauchant minuit).

**Comportement :** Lors de la recherche du planning correspondant à une plainte, la requête inclut une logique `whereRaw('heure_fin < heure_debut')` pour détecter ces cas et les traiter correctement, évitant qu'un trajet de nuit soit ignoré à tort.

---

## 8. Fallback multi-niveaux pour lier une plainte à un planning

Quand un passager dépose une plainte publique, le système tente de l'associer à un bus et un chauffeur en service. Trois niveaux de fallback sont appliqués dans l'ordre :

1. **Match exact** : même ligne + même jour + plage horaire correspondant à l'heure de l'incident
2. **Même ligne, même jour** : si aucun match exact n'est trouvé (heure imprécise)
3. **Planning le plus récent sur la ligne** : si aucune correspondance le jour même

**Résultat :** Une plainte est toujours associée au meilleur planning disponible. Le champ `bus_id` est nullable pour les cas où aucun planning n'existe du tout sur la ligne.

---

## 9. Score interne pondéré des chauffeurs

Le score interne d'un chauffeur n'est pas une simple moyenne de satisfaction — il combine deux dimensions :

```
Score = 70% × (note satisfaction / 5) + 30% × (1 − min(plaintes abouties, 5) / 5)
```

**Logique :**
- **70 %** basé sur la satisfaction passagers (note moyenne sur 5)
- **30 %** basé sur l'absence de plaintes abouties (plafonnée à 5)

Un chauffeur avec une excellente satisfaction mais plusieurs sanctions voit son score pénalisé. Un chauffeur avec peu de plaintes mais une satisfaction moyenne reste bien noté.

---

## 10. Idempotence de la classification IA (API)

L'endpoint POST de l'API de classification (`/api/complaints/severity`) est **idempotent**.

**Comportement :** Si l'IA soumet une sévérité pour une plainte qui en a déjà une (retry n8n, double appel), le système met à jour l'entrée existante plutôt que d'échouer ou de créer un doublon (`updateOrCreate`).

---

## 11. Contraintes d'unicité métier en base de données

Plusieurs actions ne peuvent être effectuées qu'une seule fois par dossier, enforced en base :

| Entité | Contrainte |
|---|---|
| Sévérité | Une seule par plainte (`complaint_id` unique) |
| Sanction | Une seule par plainte |
| Gratification | Une seule par plainte |

Ces contraintes empêchent les doublons même en cas de double clic ou de soumission concurrente.

---

## 12. Profil client anonyme via email

Les passagers qui déposent une plainte publique ne créent pas de compte utilisateur.

**Comportement :** Le système crée (ou retrouve) un enregistrement `Client` basé uniquement sur l'adresse email, via `firstOrCreate`. Aucun mot de passe ni authentification n'est requis. Cela permet de regrouper les plaintes d'un même passager sans exposition de données personnelles.

---

## 13. Routage automatique selon sévérité et nature

Le routage d'un dossier après l'évaluation Com est entièrement automatique, sans décision manuelle :

| Sévérité | Nature | Destination |
|---|---|---|
| 0 | — | Classé sans suite (Fermé) |
| 1–2 | Négative | ManagerReview |
| 3–4 | Négative | RHReview (direct, sans passer par le manager) |
| Toute | Positive | RHReview (direct) |

Un dossier de niveau 3 ou 4 bypass complètement le manager pour aller directement aux RH, garantissant un traitement prioritaire des cas graves.

---

## 14. Notifications ciblées par rôle et événement

Le système de notifications ne diffuse pas à tous les utilisateurs — chaque notification est adressée à des destinataires précis selon l'événement :

- Un dossier clôturé notifie **uniquement** l'agent Com et le manager concernés (pas tous les Com/Managers)
- Une sanction notifie **uniquement** le chauffeur sanctionné
- Un dossier de niveau 3–4 notifie **tous** les agents Com disponibles (cas urgent)

Cela évite la surcharge de notifications et maintient la pertinence pour chaque utilisateur.
