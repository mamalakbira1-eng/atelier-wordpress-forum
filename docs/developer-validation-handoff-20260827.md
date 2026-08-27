# Atelier — dossier de validation pour le prochain développeur

**Révision publique de référence :** [`atelier-prebeta-rc1`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc1)
**Dépôt :** [mamalakbira1-eng/atelier-wordpress-forum](https://github.com/mamalakbira1-eng/atelier-wordpress-forum)

Ce document est le point de départ de la reprise technique. Il rassemble exclusivement des liens vers le code, les releases, les fixtures et les comptes rendus **publics et assainis**. Il ne donne pas accès à un environnement, à un compte, à une sauvegarde, à une adresse personnelle, à un secret ni à des données de session.

> **Statut de lancement : non.** Le projet reste un environnement de préproduction. Toute tentative de publication, de retrait du `noindex`, de restauration sur l’instance active, d’envoi réel d’e-mail ou de changement de domaine sort du périmètre de cette validation.

## Parcours de lecture conseillé

| Ordre | Élément à lire | Objectif de validation |
|---:|---|---|
| 1 | [README](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/README.md), [CHANGELOG](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/CHANGELOG.md) et [référence canonique](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/canonical-reference-20260827.md) | Comprendre le lot gelé, les versions et la structure du dépôt. |
| 2 | [Manifeste SHA-256](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/ARTIFACTS-SHA256.txt), [procès-verbal de validation reproductible](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/reproducibility-validation-20260827.md) et [procédure d’installation/restauration](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/clean-install-and-recovery-procedure-20260827.md) | Vérifier les artefacts et préparer une instance isolée. |
| 3 | [Synthèse publique de recette](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/public-senior-recipe-summary-20260827.md), [matrice de tests](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/test-matrix-20260827.md) et [registre des risques](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/risk-register-20260827.md) | Revoir les preuves, les tests à rejouer et les écarts ouverts. |
| 4 | [Rapport de clôture senior](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/senior-closure-report-20260826.md), [constats d’audit](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/senior-audit-findings-20260826.md) et [pack des 20 audits et simulations](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/prompts-claude-fable-senior-20260826.md) | Rejouer ou compléter la recette sans inventer de scénarios non justifiés. |

## Code à contrôler

Le thème reste responsable du rendu, de l’accessibilité visuelle, de la navigation, de la recherche progressive et des gabarits bbPress. PFC porte les règles métier d’inscription, modération, interactions, import et SEO.

| Zone | Lien direct | Contrôle attendu |
|---|---|---|
| Thème Atelier — racine | [`atelier/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc1/atelier) | Vérifier structure WordPress, hooks et version déclarée. |
| Bootstrap du thème | [`atelier/functions.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/atelier/functions.php) | Vérifier assets, cache, RTL, recherche et intégration PFC. |
| En-tête et marque | [`atelier/header.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/atelier/header.php) | Confirmer le nom accessible de la marque, corrigé par H05. |
| Gabarit de réponse | [`atelier/bbpress/loop-single-reply.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/atelier/bbpress/loop-single-reply.php) | Confirmer le libellé visible/inclusif des votes de réponses (H05). |
| Gabarit du sujet | [`atelier/bbpress/content-single-topic.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/atelier/bbpress/content-single-topic.php) | Confirmer la carte Collection et les structures de lecture machine (H05). |
| Interactions client | [`atelier/assets/js/atelier-interactions.js`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/atelier/assets/js/atelier-interactions.js) | Vérifier cohérence des libellés après interaction et navigation clavier. |
| PFC — racine | [`premium-forum-core/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc1/premium-forum-core) | Vérifier dépendance bbPress, version et protections de surface HTTP. |
| Bootstrap PFC | [`premium-forum-core.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/premium-forum-core/premium-forum-core.php) | Confirmer la version 0.4.18 et le chargement des modules. |
| Import CSV | [`class-pfc-importer.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/premium-forum-core/includes/class-pfc-importer.php) | Vérifier la validation des relations avant écriture (H01). |
| Modération PFC | [`class-pfc-moderation.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/premium-forum-core/includes/class-pfc-moderation.php) | Vérifier `bbp_approve_reply()` et la resynchronisation ciblée des compteurs (H08). |

## Artefacts de déploiement et de test

| Catégorie | Lien direct | Utilisation de validation |
|---|---|---|
| Release Atelier 0.4.32 | [`atelier-0.4.32-a11y-visible-labels.zip`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/release/atelier-0.4.32-a11y-visible-labels.zip) | Vérifier l’intégrité du paquet correspondant à H05. |
| Release PFC 0.4.18 | [`premium-forum-core-0.4.18-import-references.zip`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/release/premium-forum-core-0.4.18-import-references.zip) | Vérifier l’intégrité du paquet correspondant à H01 et incluant H08. |
| Manifeste | [`ARTIFACTS-SHA256.txt`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/ARTIFACTS-SHA256.txt) | Vérifier les hashes avant installation. |
| Fixtures de CI | [`test-fixtures/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc1/test-fixtures) | Exécuter les packs minimalistes valide et invalide, incluant H01. |
| Jeux de fixtures d’import | [`fixtures/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc1/fixtures) | Revoir le format d’import synthétique sans mots de passe. |
| Manifeste de validation | [`validation-manifest.json`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/fixtures/validation-manifest.json) | Vérifier les assertions attendues du pack de test. |

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

[1] [Tag canonique de pré-bêta](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc1)

[2] [Synthèse publique de recette senior](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc1/docs/public-senior-recipe-summary-20260827.md)
[3] [Dépôt Atelier WordPress Forum](https://github.com/mamalakbira1-eng/atelier-wordpress-forum)
