# Atelier — forum WordPress premium, SEO/LLM-first

Ce dépôt contient la base de code transmissible du forum WordPress Atelier : thème custom compatible bbPress, extension Premium Forum Core, fixtures CSV de sujets/réponses/utilisateurs de recette, archives installables et documentation de validation.

## Sécurité

Le dépôt est destiné à être public. Les mots de passe, cookies, tokens, clés API, fichiers de configuration et données personnelles ont été exclus. Les adresses des fixtures publiques utilisent le domaine réservé `example.test`. Les comptes réellement présents sur le staging ne doivent jamais être recréés avec un mot de passe communiqué dans GitHub.

## Structure

| Dossier | Contenu |
|---|---|
| `atelier/` | Thème WordPress Atelier, templates bbPress, styles et interactions front-end. |
| `premium-forum-core/` | Plugin PFC : import CSV, SEO/JSON-LD, notifications, suivis et statistiques membres. |
| `fixtures/` | Forums, 21 sujets, 81 réponses et 100 membres de test sans colonne de mot de passe. |
| `release/` | Archives ZIP prêtes à installer sur un WordPress de staging. |
| `tools/` | Outils de génération utilisés pour la recette. |
| `docs/` | Notes de validation et vérification des rôles. |
| `validation-screenshots/` | Captures de validation visuelle du prototype déployé. |

## Installation

Installer bbPress, puis téléverser `release/premium-forum-core-0.3.0.zip` dans Extensions → Ajouter une extension. Téléverser ensuite `release/atelier-0.4.26-react-parity.zip` dans Apparence → Thèmes → Ajouter un thème, puis activer Atelier. Vérifier les permaliens et purger le cache LiteSpeed après activation.

L’import de recette se fait depuis Premium Forum → Import CSV. Commencer par un dry run, vérifier le mapping, puis lancer l’import. Les fixtures sont un jeu de démonstration ; ne pas les importer sur un site de production sans sauvegarde et sans validation éditoriale.

## Rôles

`Membre SVIP` est un rôle communautaire de reconnaissance : il publie des sujets et réponses et bénéficie des fonctions sociales PFC, mais ne possède pas les droits de modération. `Modérateur` est un rôle bbPress fonctionnel : il permet les actions de gouvernance disponibles selon les capacités bbPress configurées. Le compte de recette modérateur est volontairement séparé de l’administration WordPress.

## Reprise développeur

Le prochain développeur doit d’abord lire les fichiers de validation dans `docs/`, vérifier les hooks bbPress dans `premium-forum-core/includes/class-pfc-community.php`, puis tester les actions AJAX avec une page mise en cache. Les données du staging WordPress — utilisateurs, notifications, suivis et contenus réellement publiés — ne sont pas versionnées ici ; elles doivent être exportées séparément et de manière contrôlée si nécessaire.

## Statut

La recette staging du 25 août 2026 a validé l’import massif, le suivi de sujets, les notifications internes, le compteur de votes reçus, les badges SVIP/Modérateur, les pages de profil et le renouvellement de nonce contre le cache LiteSpeed.
