# Atelier — forum WordPress premium, SEO/LLM-first

Ce dépôt contient la base de code transmissible du forum WordPress **Atelier** : thème custom compatible bbPress, extension Premium Forum Core, fixtures CSV de recette, archives installables, scripts de validation et documentation de reprise développeur.

## Sécurité

Le dépôt est public. Les mots de passe, cookies, tokens, clés API, fichiers de configuration et données personnelles ont été exclus. Les adresses des fixtures publiques utilisent le domaine réservé `example.test`. Les comptes réellement présents sur le staging ne doivent jamais être recréés avec un mot de passe communiqué dans GitHub.

## Versions transmises

| Élément | Version | Contenu |
|---|---:|---|
| Thème Atelier | 0.4.28 | UI premium, bbPress, RTL arabe, connexion et inscription |
| Premium Forum Core | 0.4.2 | Import CSV, SEO/JSON-LD, communauté, notifications, pseudo et modération |
| WordPress cible | 7.1 | Environnement de staging utilisé pour la recette |
| bbPress cible | 2.6 | Forums, sujets, réponses et profils |
| PHP cible | 8.3 | Syntaxe validée localement |

## Structure

| Dossier | Contenu |
|---|---|
| `atelier/` | Thème WordPress Atelier et templates bbPress. |
| `premium-forum-core/` | Plugin PFC : import CSV, SEO, inscription, notifications et modération. |
| `fixtures/` | Forums, sujets, réponses et membres de test sans mots de passe. |
| `release/` | Archives finales prêtes à installer sur un staging. |
| `docs/` | Validation, architecture, sécurité et relais développeur. |
| `tools/` | Scripts de génération et de validation. |
| `validation-screenshots/` | Captures de validation visuelle non sensibles. |

## Installation

Installer bbPress, puis téléverser `release/premium-forum-core-0.4.2-registration-mail-guard.zip` dans **Extensions → Ajouter une extension**. Téléverser ensuite `release/atelier-0.4.28-registration-moderation-smtp-guard.zip` dans **Apparence → Thèmes → Ajouter un thème**, puis activer Atelier. Vérifier les permaliens et purger LiteSpeed après activation.

L’import de recette se fait depuis **Premium Forum → Import CSV**. Commencer par un dry run, vérifier le mapping et le journal, puis lancer l’import uniquement après sauvegarde. Les fixtures sont un jeu de démonstration et ne doivent pas être importées sur un site de production sans validation éditoriale.

## Fonctionnalités principales

Le forum fournit une page d’accueil éditoriale, des espaces, une recherche progressive, des pages de sujets, un rail d’index, des cartes de réponses, les votes utiles, le suivi, le partage, les profils membres, l’espace personnel et une structure HTML explicite. Les contributions arabes détectées reçoivent une mise en page RTL et la typographie Noto Naskh Arabic.

L’inscription comprend le prénom, le nom, le pseudo suggéré, la disponibilité AJAX avec alternatives, la revalidation serveur, un mot de passe de dix caractères minimum et un code e-mail à six chiffres valable quinze minutes avec cinq tentatives maximum. Les nouveaux sujets et réponses des membres ordinaires peuvent être retenus dans la file centrale **À valider**.

## Correctif inscription et SMTP

L’erreur critique reproduite sur le staging se produisait après la création du compte, dans l’étape de génération ou d’envoi du code. Le handler intercepte maintenant les exceptions `Throwable`, protège le nettoyage et retourne un message contrôlé si `wp_mail()` échoue. Le retest a confirmé l’absence d’erreur critique et la suppression du compte incomplet.

Le staging retourne actuellement un échec d’envoi e-mail contrôlé : le SMTP transactionnel n’est pas encore configuré. Il faut configurer et tester SMTP avant l’ouverture publique. Voir `docs/DEVELOPER-HANDOFF-20260825.md` pour le détail et l’ordre de reprise.

## Rôles

`Membre SVIP` est un rôle communautaire de reconnaissance et ne donne pas automatiquement les droits de modération. `Modérateur Atelier` est séparé de l’administration WordPress et dispose des capacités de gouvernance configurées par PFC. Les comptes de recette et leurs mots de passe ne sont volontairement pas documentés dans ce dépôt public.

## Reprise développeur

Lire en premier `docs/DEVELOPER-HANDOFF-20260825.md`, puis `docs/registration-0428.md`, `docs/moderation-and-username-0428.md`, `docs/visual-parity-20260825.md` et les classes PFC correspondantes. Les données du staging — utilisateurs, notifications, suivis et contenus réellement publiés — ne sont pas versionnées ici.

Avant toute mise en production, configurer SMTP, ajouter un anti-spam, finaliser RGPD/CGU/confidentialité, vérifier l’accessibilité clavier et confirmer que `acceptedAnswer` n’apparaît dans le JSON-LD qu’après acceptation réelle par un modérateur.

## Validation locale

Tous les fichiers PHP transmis doivent être vérifiés avec `php -l`. Les fixtures publiques doivent rester limitées au domaine `example.test`. Le dépôt ne doit jamais recevoir d’export SQL, de mot de passe, de cookie de session ou d’adresse d’administration réelle.
