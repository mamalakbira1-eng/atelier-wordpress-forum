# Atelier — dossier de validation pour le prochain développeur

**Révision publique de référence :** [`324b1d1`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/commit/324b1d1)
**Dépôt :** [mamalakbira1-eng/atelier-wordpress-forum](https://github.com/mamalakbira1-eng/atelier-wordpress-forum)

Ce document est le point de départ de la reprise technique. Il rassemble exclusivement des liens vers le code, les releases, les fixtures et les comptes rendus **publics et assainis**. Il ne donne pas accès à un environnement, à un compte, à une sauvegarde, à une adresse personnelle, à un secret ni à des données de session.

> **Statut de lancement : non.** Le projet reste un environnement de préproduction. Toute tentative de publication, de retrait du `noindex`, de restauration sur l’instance active, d’envoi réel d’e-mail ou de changement de domaine sort du périmètre de cette validation.

## Parcours de lecture conseillé

| Ordre | Élément à lire | Objectif de validation |
|---:|---|---|
| 1 | [README](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/README.md) et [CHANGELOG](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/CHANGELOG.md) | Comprendre le périmètre, les versions et la structure du dépôt. |
| 2 | [Synthèse publique de recette](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/docs/public-senior-recipe-summary-20260827.md) | Revoir le périmètre testé, les résultats, les défauts corrigés et les préconditions de lancement. |
| 3 | [Rapport de clôture senior](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/docs/senior-closure-report-20260826.md) et [constats d’audit](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/docs/senior-audit-findings-20260826.md) | Vérifier les décisions, les limites et les risques laissés volontairement ouverts. |
| 4 | [Pack des 20 audits et simulations](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/docs/prompts-claude-fable-senior-20260826.md) | Rejouer ou compléter la recette sans inventer de nouveaux scénarios non justifiés. |

## Code à contrôler

Le thème reste responsable du rendu, de l’accessibilité visuelle, de la navigation, de la recherche progressive et des gabarits bbPress. PFC porte les règles métier d’inscription, modération, interactions, import et SEO.

| Zone | Lien direct | Contrôle attendu |
|---|---|---|
| Thème Atelier — racine | [`atelier/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/main/atelier) | Vérifier structure WordPress, hooks et version déclarée. |
| Bootstrap du thème | [`atelier/functions.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/atelier/functions.php) | Vérifier assets, cache, RTL, recherche et intégration PFC. |
| En-tête et marque | [`atelier/header.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/atelier/header.php) | Confirmer le nom accessible de la marque, corrigé par H05. |
| Gabarit de réponse | [`atelier/bbpress/loop-single-reply.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/atelier/bbpress/loop-single-reply.php) | Confirmer le libellé visible/inclusif des votes de réponses (H05). |
| Gabarit du sujet | [`atelier/bbpress/content-single-topic.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/atelier/bbpress/content-single-topic.php) | Confirmer la carte Collection et les structures de lecture machine (H05). |
| Interactions client | [`atelier/assets/js/atelier-interactions.js`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/atelier/assets/js/atelier-interactions.js) | Vérifier cohérence des libellés après interaction et navigation clavier. |
| PFC — racine | [`premium-forum-core/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/main/premium-forum-core) | Vérifier dépendance bbPress, version et protections de surface HTTP. |
| Bootstrap PFC | [`premium-forum-core.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/premium-forum-core/premium-forum-core.php) | Confirmer la version 0.4.18 et le chargement des modules. |
| Import CSV | [`class-pfc-importer.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/premium-forum-core/includes/class-pfc-importer.php) | Vérifier la validation des relations avant écriture (H01). |
| Modération PFC | [`class-pfc-moderation.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/premium-forum-core/includes/class-pfc-moderation.php) | Vérifier `bbp_approve_reply()` et la resynchronisation ciblée des compteurs (H08). |

## Artefacts de déploiement et de test

| Catégorie | Lien direct | Utilisation de validation |
|---|---|---|
| Release Atelier 0.4.32 | [`atelier-0.4.32-a11y-visible-labels.zip`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/release/atelier-0.4.32-a11y-visible-labels.zip) | Vérifier l’intégrité du paquet correspondant à H05. |
| Release PFC 0.4.18 | [`premium-forum-core-0.4.18-import-references.zip`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/release/premium-forum-core-0.4.18-import-references.zip) | Vérifier l’intégrité du paquet correspondant à H01 et incluant H08. |
| Jeux de fixtures | [`fixtures/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/main/fixtures) | Revoir le format d’import synthétique sans mots de passe. |
| Utilisateurs synthétiques | [`fixtures/users.csv`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/fixtures/users.csv) | Confirmer l’absence de `password_hash` et l’usage d’adresses de test. |
| Forums, sujets et réponses | [`forums.csv`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/fixtures/forums.csv), [`topics.csv`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/fixtures/topics.csv), [`replies.csv`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/fixtures/replies.csv) | Contrôler les mappings et les cas de test. |
| Manifeste de validation | [`validation-manifest.json`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/fixtures/validation-manifest.json) | Vérifier les assertions attendues du pack de test. |

## Protocole de validation minimal

Le prochain développeur peut d’abord analyser ce dépôt hors ligne. S’il dispose ultérieurement d’un environnement de préproduction explicitement autorisé, il doit utiliser uniquement des données synthétiques et effectuer une sauvegarde vérifiée avant toute mutation. Le protocole minimal consiste à installer les releases actuelles, contrôler la syntaxe PHP, exécuter le dry run d’un pack valide et d’un pack à relation volontairement invalide, puis vérifier que le second ne peut pas lancer d’écriture.

Les contrôles fonctionnels à rejouer sont : membre vers contribution pending, modération déléguée vers publication/refus/suppression, cohérence des compteurs après publication, vote/suivi dédupliqués, notification interne, recherche clavier, rendu RTL et directives de cache. Il faut ensuite supprimer les artefacts de test. Les résultats doivent distinguer les mesures de laboratoire des données terrain et les preuves réalisées des hypothèses.

## Écarts à ne pas fermer par supposition

| Sujet | Décision attendue |
|---|---|
| Restauration | Considérer le risque ouvert tant qu’un jeu complet n’a pas été restauré dans une instance isolée. |
| Planification | Considérer le cron hôte non vérifié tant que son exécution sans trafic n’est pas observée. |
| Limitation IP | Ne pas conclure sur le comportement derrière CDN sans vérification de l’adresse réellement transmise. |
| Accessibilité | Lighthouse est utile mais ne remplace pas une recette humaine mobile, zoom et lecteur d’écran. |
| Performance | Les résultats disponibles sont des mesures de laboratoire; instrumenter les métriques terrain avant lancement. |
| E-mail et domaine | Ne pas envoyer de message réel ni retirer `noindex, nofollow` avant domaine final, DNS e-mail et contrôle SEO de lancement. |

## Références

[1] [Commit public de relais 324b1d1](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/commit/324b1d1)

[2] [Synthèse publique de recette senior](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/main/docs/public-senior-recipe-summary-20260827.md)
[3] [Dépôt Atelier WordPress Forum](https://github.com/mamalakbira1-eng/atelier-wordpress-forum)
