# AJDCB API

Backend Laravel de l'Association des Jeunes de la Diaspora Congolaise au Bénin (AJDCB). Sert le site vitrine public et l'espace d'administration ([ajdcb.app](https://github.com/GuivenM/ajdcb.app)) via une API REST versionnée (`/api/v1/...`).

## Stack

- **Laravel 12** / PHP 8.2+
- **Laravel Sanctum** — auth par token pour l'espace admin
- **fedapay/fedapay-php** — paiements en ligne (cotisations, événements payants)
- **intervention/image** — traitement d'images

## Installation

```bash
composer install
cp .env.example .env   # à créer si absent — voir "Variables d'environnement" ci-dessous
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

`storage:link` est indispensable : toutes les images/documents uploadés (photos de membres, logos partenaires, documents du guide…) sont servis via le disque `public` (`storage/app/public/...` → `public/storage/...`).

## Variables d'environnement importantes

| Variable | Rôle |
|---|---|
| `APP_URL` | Doit correspondre **exactement** à l'adresse et au port réels du serveur (ex: `http://localhost:8000` si lancé via `artisan serve`). Sert à générer les URLs de fichiers (`Storage::disk('public')->url()`) — un mauvais port ici casse l'affichage de toutes les images. |
| `FRONTEND_URL` | URL du frontend `ajdcb.app`, utilisée dans les emails et les redirections post-paiement. |
| `MAIL_ADMIN_ADDRESS` | Adresse recevant les notifications (nouvelle adhésion, nouveau message). Défaut : `contact@ajdcb.org`. |
| `FEDAPAY_ENVIRONMENT` | `sandbox` ou `live`. |
| `FEDAPAY_SECRET_KEY` / `FEDAPAY_PUBLIC_KEY` | Clés API FedaPay. |
| `FEDAPAY_WEBHOOK_SECRET` | Secret pour vérifier la signature `X-FEDAPAY-SIGNATURE` du webhook. |
| `FEDAPAY_CALLBACK_URL` | URL de retour après paiement (défaut : `{FRONTEND_URL}/paiement/retour`). |
| `SANCTUM_STATEFUL_DOMAINS` / CORS | À adapter si le frontend n'est pas sur `localhost:5173`. |

## Modèle de données

| Modèle | Rôle |
|---|---|
| `Adhesion` | Candidature d'adhésion — reflète le formulaire complet (nationalité, pièces d'identité, état civil, statut pro étudiant/entrepreneur, compétences, langues, engagement associatif, déclaration sur l'honneur). Statuts : `en_attente` / `approuvee` / `rejetee`. |
| `Membre` | Membre effectif de l'association. Lié à son `Adhesion` d'origine (`adhesion_id`). Statuts : `actif` / `inactif` / `en_attente_paiement` (créé automatiquement à l'approbation d'une adhésion, passe à `actif` dès le premier paiement de cotisation confirmé). |
| `Cotisation` | Une ligne par membre et par mois (`mois` au format `AAAA-MM`, unique par membre). Statuts : `payee` / `impayee` / `anterieure_adhesion` (mois avant l'arrivée du membre — jamais compté comme une dette). |
| `Paiement` | Transaction FedaPay (cotisation ou événement payant). Alimentée par `PaiementController` + `FedaPayService`. |
| `Actualite` | Articles/annonces éditoriales du site (types : actualité, événement, éducation, culture). |
| `Action` | Bilan/programme d'activités de l'association, organisé par commission (`solidarite`, `education`, `culture`, `communication` — les 4 commissions du règlement intérieur, Article 8). |
| `Evenement` | Événements calendaires structurés : dates, lieu, capacité, prix/billetterie, organisateur — distinct des `Actualite` de type "événement" qui ne sont que des articles. |
| `GuideSection` / `GuideSousSection` / `GuideDocument` | Guide pratique à 3 niveaux, avec documents téléchargeables (PDF/Word/Excel/PowerPoint). |
| `Partenaire` | Institutions/ONG/entreprises partenaires, avec niveau de partenariat (or/argent/bronze/institutionnel/technique). |
| `Message` | Messages du formulaire de contact public, avec `objet` catégorisé (question, partenariat, adhésion, urgence, information, réclamation, autre). |
| `User` | Comptes admin. Rôles : `super_admin`, `admin`, `moderateur`. |

### Le parcours adhésion → membre → cotisation

1. Un candidat remplit le formulaire public complet (`POST /adhesions`).
2. Un admin approuve (`PUT /adhesions/{id}/traiter`) → un `Membre` est créé automatiquement, statut `en_attente_paiement`.
3. Le membre paie sa première cotisation (1 000 FCFA, Article 2 du règlement intérieur) — manuellement via `POST /cotisations/marquer`, ou en ligne via FedaPay (voir point d'attention ci-dessous).
4. Dès qu'une cotisation est marquée payée pour un membre `en_attente_paiement`, celui-ci passe `actif`.

### Rôles et permissions

Vérifiés par le middleware `role:...` sur les routes protégées. Convention constante dans tout le projet :
- **Lecture** (`index`/`show`) : les 3 rôles.
- **Créer/modifier** : `admin` et `super_admin` (Actualités accepte aussi `moderateur` en création).
- **Supprimer** : `super_admin` uniquement.

## Structure des routes (`routes/api.php`)

Toutes préfixées `/api/v1/...`.

**Publiques** (`Route::prefix('v1')`, hors auth) :
`POST /messages`, `POST /adhesions`, lecture (`GET`) de `actualites`, `actions`, `membres`, `evenements`, `guide`, `partenaires`.

**Protégées** (`auth:sanctum` + `prefix('v1')`) : CRUD complet pour chaque ressource ci-dessus, plus :
- `GET /membres-admin/tous` — seule route qui renvoie aussi les membres **inactifs** (la route publique `/membres` ne renvoie que les actifs).
- `PUT /adhesions/{id}/traiter` — approuver/rejeter, crée automatiquement un `Membre`.
- `GET /cotisations`, `POST /cotisations/marquer`, `GET /cotisations/statistiques`, `GET /cotisations/membre/{id}` — suivi mensuel des cotisations.

### ⚠️ Point d'attention : routes `Paiement` non enregistrées

`PaiementController` (méthodes `initierCotisation`, `initierEvenement`, `webhook`) et `FedaPayService` sont **implémentés mais leurs routes ne sont pas déclarées dans `routes/api.php`**. Les endpoints documentés en commentaire dans le contrôleur (`POST /v1/paiements/cotisation`, `POST /v1/paiements/evenements/{id}`, `POST /paiements/webhook`) ne répondent donc pas encore. `FedaPayButton.tsx` côté frontend les appelle déjà — il faut ajouter ces routes pour que le paiement en ligne fonctionne de bout en bout.

## Stockage de fichiers

Tous les uploads (photos, logos, documents) utilisent le disque `public` explicitement :
```php
$request->file('photo')->store('membres', 'public');
```
et les accesseurs de modèle (`photo_url`, `image_url`, `logo_url`, `fichier_url`) génèrent l'URL via `Storage::disk('public')->url($path)`. Ne pas utiliser `->store('public/...')` sur le disque par défaut — Laravel 11+ a changé la racine du disque `local` vers `storage/app/private`, ce qui casse ce raccourci historique.

**Piège Windows/WAMP** : `storage:link` crée un lien symbolique ; si la commande échoue silencieusement, relancer le terminal en administrateur ou activer le Mode développeur Windows.

## Notes de fiabilité

- Les modèles utilisent `protected $appends` pour exposer leurs accesseurs calculés (`nom_complet`, `photo_url`, `statut_label`, etc.) dans les réponses JSON — un accesseur qui n'y figure pas est invisible côté API même s'il existe dans le code.
- `AdhesionController::store()` construit ses données **uniquement** à partir des champs validés (`$validator->validated()`), jamais de `$request->all()` — évite qu'un candidat s'auto-approuve en injectant `statut`/`traite_par`.
- `CotisationController::index()`/`statistiques()` excluent les mois antérieurs à la date d'adhésion d'un membre — ne jamais recompter tous les membres actifs sans vérifier `created_at` par rapport au mois consulté.
