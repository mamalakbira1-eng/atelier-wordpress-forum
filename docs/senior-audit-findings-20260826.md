# Rapport d’audit senior — Atelier WordPress / Premium Forum Core

**Date :** 26 août 2026
**Périmètre :** thème Atelier 0.4.28, Premium Forum Core 0.4.4, bbPress 2.6, WordPress 7.1, PHP 8.3, staging de recette.
**Auteur :** Manus AI

## Résumé exécutif

Le forum Atelier dispose maintenant d’un socle WordPress/bbPress cohérent pour une recette avancée : interface éditoriale, contenus français et arabes RTL, import CSV contrôlé, inscription sécurisée, modération centralisée, interactions communautaires et balisage conçu pour une lecture machine prudente. Les constats P1 et P2 prouvés au cours de l’audit ont été corrigés, validés localement puis déployés sur le staging.

Le niveau de préparation est **satisfaisant pour poursuivre la recette et préparer une préproduction**, mais **pas encore suffisant pour déclarer la production prête**. La barrière principale est la délivrabilité : le staging ne dispose pas d’un SMTP vérifiable. Les Core Web Vitals, la charge réelle, les conflits avec le jeu complet d’extensions et la recette mobile réelle restent à mesurer. Le staging demeure en `noindex, nofollow`, ce qui est la décision correcte avant migration.

> Le SEO aide Google à comprendre, explorer et découvrir le contenu; il ne constitue pas une promesse de classement. Les signaux SEO, les données structurées et les performances doivent donc être testés et maintenus, non simplement ajoutés au code. [1]

## Portée, méthode et limites

L’audit a associé revue de code, `php -l`, harnais CSV, construction et contrôle d’archives, déploiements WordPress, simulations de rôles et vérifications en session anonyme. Les scénarios couvrent inscription, erreur SMTP contrôlée, honeypot, pseudo, modération, publication, suppression, votes, suivi, notification, import, recherche, RTL, SEO, cache et accessibilité structurelle.

Les mesures HTTP présentées ci-dessous sont des instantanés de staging. Elles permettent de constater les en-têtes et le comportement de cache, mais ne remplacent ni Chrome UX Report, ni Lighthouse contrôlé, ni RUM. Les seuils LCP ≤ 2,5 s, INP < 200 ms et CLS < 0,1 doivent être mesurés sur des pages représentatives, en mobile comme en desktop. [3] [4]

## Versions et livrables de référence

| Élément | Référence validée | Observations |
|---|---|---|
| Thème | Atelier 0.4.28, racine `atelier-0428/` | Archive installable : `release/atelier-0.4.28-active-theme-senior-audit.zip`. |
| Plugin | Premium Forum Core 0.4.4 | Archive installable : `release/premium-forum-core-0.4.4-senior-audit.zip`. |
| Forum | bbPress 2.6 | Types forum, topic, reply et compteurs natifs conservés. |
| Staging | WordPress 7.1 / PHP 8.3 | Dissuasion d’indexation active pendant toute la recette. |
| Dépôt public | Code, fixtures synthétiques, docs et releases | Aucun secret, export ou accès staging ne doit être ajouté. |

## Constats corrigés et preuves de non-régression

| ID | Gravité initiale | Domaine | Correctif appliqué | Preuve de validation |
|---|---|---|---|---|
| P1-REG-001 | P1 | Inscription / SMTP | Exceptions et retour `false` de `wp_mail()` gérés lors de l’envoi et du renvoi; compte incomplet supprimé; délai de renvoi de 60 s. | Échec SMTP simulé : erreur contrôlée et absence de compte résiduel. |
| P1-REG-002 | P1 | Anti-abus | Honeypot hors navigation et limite horaire appliquée après validation des champs. | Soumission bot rejetée sans compte; erreurs invalides répétées sans consommation indue du quota. |
| P1-MOD-001 | P1 | Publication / notification | Seules les réponses `publish` notifient; un suivi exige un sujet `publish`. | Chaîne contribution en attente → modération → visibilité publique rejouée. |
| P1-IMP-001 | P1 | Import CSV | Maximum 4 fichiers, 5 Mo/fichier et 20 Mo/pack; mots de passe en clair refusés. | Harnais local valide les jeux de données acceptés et rejetés. |
| P2-MOD-002 | P2 | Actions de modération | Les actions sont des formulaires `POST` avec nonce, statut attendu et capacité `edit_post`. | Publier, refuser et supprimer testés dans la file; sujet de recette supprimé ensuite. |
| P2-COM-002 | P2 | Notifications | Destinataires dédupliqués par réponse; nettoyage lors de suppression d’objet ou de compte. | Un membre suiveur a reçu exactement une notification; artefacts de recette nettoyés. |
| P2-COM-003 | P2 | Votes reçus | Agrégation sans limite artificielle de 100 contributions. | Espace membre chargé après correctif, sans erreur de requête. |
| P2-COM-004 | P2 | Profil | Nonce utilisateur explicite avant modification de rang. | Validation syntaxique et déploiement PFC 0.4.3+. |
| P2-SEO-001 | P2 | Marque et canonique | Titres Atelier, fil `Atelier`, canonique d’accueil explicite et sujet canonique sans paramètre. | Rendu anonyme frais : un canonique auto-référent pour l’accueil; sujet avec canonique, titre et schémas attendus. |
| P2-RTL-001 | P2 | Arabe | `dir=rtl`, alignement à droite et Noto Naskh Arabic sur message initial et réponses. | Styles calculés : direction RTL, police arabe, interlignage ≈ 38 px. |

## Fonctionnalités validées

L’accueil, les espaces, les archives, les profils, l’espace membre et les sujets bbPress sont exploités dans le thème. Les actions répondre, suivre, partager, enregistrer et voter sont présentes; le tri de réponses propose pertinence, date et votes. Les badges Membre SVIP et Modérateur sont visibles lorsque le rôle concerné est affecté.

La recherche d’en-tête fournit des suggestions dès la saisie, puis les résultats concernés. En visiteur anonyme, la requête arabe `المعرفة` a proposé le sujet RTL correspondant avec type, date, votes et lien public. Les sujets arabes publics conservent leur direction et leur typographie dans le message initial et les réponses.

L’import CSV dispose d’un mapping, d’un dry run, d’un journal et d’un rollback ciblé. Les données historiques validées conservent dates, rangs et agrégats d’upvotes sans fabriquer de votes individuels. La portée du rollback est volontairement limitée aux objets journalisés par le job sélectionné.

## SEO, données structurées et lecture machine

Le sujet de référence émet un titre de marque, une balise canonique sans paramètre de recette, `DiscussionForumPosting` et `BreadcrumbList`. Le premier élément du fil est `Atelier`. Aucun `QAPage` ni `acceptedAnswer` n’est émis, ce qui correspond à l’absence d’un workflow réel d’acceptation de réponse.

Cette retenue est importante : Google réserve QAPage aux pages centrées sur une question et ses réponses soumises par les utilisateurs. Le balisage ne doit pas être appliqué automatiquement à toutes les pages d’un forum, et une réponse publiée n’est pas une réponse acceptée. [2]

L’accueil émet une meta description et un canonique auto-référent. Le staging émet `meta robots="noindex, nofollow"`; cette directive doit rester active jusqu’à la recette du domaine de production. Le contenu, les métadonnées, les données structurées, les liens et les ressources devront rester équivalents en mobile-first. [4]

## Cache, performance et confidentialité de session

| Page contrôlée en visiteur anonyme | Statut / cache | En-têtes observés | Mesure ponctuelle |
|---|---|---|---|
| Accueil | `200`, LiteSpeed `hit` | `public, max-age=300, s-maxage=300, must-revalidate` | TTFB ≈ 2,25 s; HTML ≈ 34,6 kB. |
| Sujet public | Premier `miss`, puis `hit` chaud | Même directive publique | TTFB ≈ 1,94–2,07 s; HTML ≈ 52,4 kB. |
| Connexion | `200`, non cacheable | `no-store, private` et contrôle LiteSpeed `no-cache` | TTFB ≈ 2,30 s; HTML ≈ 9,9 kB. |

Le comportement de cache est cohérent : lectures publiques cacheables, session et connexion privées. Toutefois, les valeurs TTFB de staging ne suffisent pas à prouver la conformité CWV. Avant production, mesurer LCP, INP et CLS avec outils de terrain ou laboratoire sur une infrastructure comparable à la production. [3]

## Accessibilité et permissions

Le contrôle du rendu anonyme a confirmé `lang="fr-FR"`, un lien d’évitement vers le contenu principal, aucune image sans `alt`, aucun bouton sans nom accessible et aucune ancre interne vers une cible absente. La navigation anonyme vers l’action « Suivre » redirige vers la connexion sans modifier le sujet. Les actions d’écriture et de suivi restent donc protégées au lieu d’apparaître comme des succès illusoires.

La validation structurelle ne remplace pas un audit complet avec lecteur d’écran, navigation clavier exhaustive, contraste mesuré et tests utilisateurs âgés. Ces étapes restent recommandées avant production, notamment pour les formulaires, la modération et les états asynchrones.

## Risques résiduels et décisions

| Priorité | Risque ou limite | Décision requise |
|---|---|---|
| P1 | SMTP non configuré : code de vérification non reçu sur staging. | Configurer mail catcher/SMTP transactionnel, tester réception et vérifier SPF/DKIM/DMARC avant production. |
| P2 | CWV non mesurés en conditions représentatives. | Lancer une mesure mobile/desktop et RUM ou équivalent après déploiement préproduction. |
| P2 | Tests de charge et compatibilité complète des extensions non exécutés. | Effectuer une charge limitée et autorisée, puis consulter Santé du site et les logs. |
| P2 | Cache peut servir un ancien `<head>` après mise à jour. | Purger LiteSpeed/CDN après chaque mise à jour d’actifs, de SEO ou de templates; revalider requête fraîche et chaude. |
| P3 | Aucun workflow de réponse acceptée. | Ne pas implémenter QAPage/acceptedAnswer avant conception de la règle métier, autorisations et audit UI. |

## Checklist de migration production

1. Sauvegarder la base et les fichiers, puis installer les archives aux racines exactes.
2. Vérifier WordPress, PHP, bbPress et extensions de sécurité sur l’environnement cible.
3. Configurer et valider le SMTP, le domaine expéditeur, SPF, DKIM et DMARC sans exposer les secrets.
4. Valider HTTPS, domaine canonique, redirections, permaliens et cache/CDN.
5. Retirer `noindex, nofollow` et ajuster `robots.txt` uniquement après les contrôles ci-dessus.
6. Purger LiteSpeed/CDN et contrôler accueil, archive, sujet, profil, connexion et inscription en visiteur anonyme.
7. Vérifier les données structurées visibles, les canoniques, l’équivalence mobile/desktop et l’absence de `QAPage` indû.
8. Exécuter les scénarios import, modération, permissions, notification et nettoyage avec comptes synthétiques.
9. Mesurer les Core Web Vitals sur des pages représentatives et documenter les résultats.
10. Régénérer le dépôt public, exécuter le scan de secrets et ne pousser qu’après revue humaine du diff.

## Références

[1]: https://developers.google.com/search/docs/fundamentals/seo-starter-guide "Google Search Central — SEO Starter Guide"
[2]: https://developers.google.com/search/docs/appearance/structured-data/qapage "Google Search Central — QAPage structured data"
[3]: https://developers.google.com/search/docs/appearance/core-web-vitals "Google Search Central — Core Web Vitals"
[4]: https://developers.google.com/search/docs/crawling-indexing/mobile/mobile-sites-mobile-first-indexing "Google Search Central — Mobile-first indexing"
