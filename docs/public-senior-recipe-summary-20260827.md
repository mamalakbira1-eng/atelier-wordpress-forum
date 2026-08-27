# Atelier — synthèse publique de recette senior

**Périmètre :** thème Atelier, extension Premium Forum Core (PFC), bbPress et environnement WordPress de préproduction.

**Statut :** recette fonctionnelle et correctifs ciblés terminés; **non autorisé au lancement public** tant que les prérequis d’exploitation listés ci-dessous ne sont pas levés.

## Principes de la campagne

La campagne a appliqué vingt contrôles spécialisés couvrant architecture, sécurité, permissions, intégrité d’import, cache, performances de laboratoire, accessibilité, SEO, lisibilité machine, inscription, modération, interactions communautaires, recherche et RTL. Les vérifications ont utilisé uniquement des données synthétiques et les mutations ont été nettoyées à la clôture. Les mesures Lighthouse sont des mesures de laboratoire; elles ne remplacent pas des données terrain.

| Domaine | Résultat établi |
|---|---|
| Accès et sécurité | XML-RPC refusé, mots de passe applicatifs désactivés, actions privées non accessibles anonymement, en-têtes de sécurité présents sur les routes contrôlées. |
| Cache et session | Pages publiques contrôlées avec cache de lecture; connexion contrôlée avec directives privées et `no-store`. |
| Modération | Chaîne membre → pending → publication, refus ou suppression validée avec un rôle modérateur délégué ne possédant pas l’administration des extensions. |
| Communauté | Votes, suivi, anti-auto-vote, notifications internes et réponse à une réponse validés; déduplication vérifiée par bascule d’état. |
| Import | Dry run, import, journalisation et rollback ciblé exercés avec jeux synthétiques. |
| Recherche et RTL | Suggestion de recherche au clavier et rendu arabe RTL validés sur le parcours bureau contrôlé. |
| SEO et lecture machine | HTML initial avec titres, description, canonique, `DiscussionForumPosting` et fil d’Ariane; aucun `QAPage` généré sans réponse effectivement acceptée. |

## Correctifs issus de preuves

Trois défauts ont été reproduits avant correction, puis rejoués après déploiement des versions ci-dessous.

| Référence | Correctif | Version | Contrôle de sortie |
|---|---|---|---|
| H01 | Le dry run contrôle désormais les références croisées utilisateur, forum, sujet et parent de réponse avant l’écriture. | PFC 0.4.18 | Le même pack relationnel invalide reste bloqué au dry run, sans création. |
| H05 | Les noms accessibles reprennent désormais les libellés visibles de la marque, des votes et de la Collection. | Atelier 0.4.32 | L’audit Lighthouse ciblé ne signale plus `label-content-name-mismatch` sur les cinq parcours contrôlés. |
| H08 | La publication par la file PFC utilise les primitives bbPress de transition et resynchronise les compteurs du sujet et du forum concernés. | PFC 0.4.17, inclus dans 0.4.18 | Égalité confirmée entre cartes de réponse publiques et compteur rendu après une publication pending. |

## Résultats de laboratoire

Le passage Lighthouse post-correctif a porté sur l’accueil, les forums, une discussion, la connexion et l’inscription, en mobile et bureau. Les résultats sont indicatifs : ils dépendent du cache, du réseau et de l’émulation du navigateur. Ils ne constituent ni une certification Core Web Vitals ni une promesse de performance en production.

| Mode | Performance observée | LCP observé | Écart H05 |
|---|---:|---:|---|
| Mobile | 86 à 99 | 1,57 à 2,98 s | Aucun échec sur les parcours contrôlés. |
| Bureau | 63 à 83 | 1,56 à 2,97 s | Aucun échec sur les parcours contrôlés. |

## Nettoyage et exploitation

Les comptes, contenus, votes, suivis et notifications créés pour la recette ont été supprimés à la fin de la campagne. Une sauvegarde complète post-recette a été effectuée vers un stockage distant déjà configuré; aucun fichier de sauvegarde, identifiant, journal ni donnée de session ne fait partie de ce dépôt.

> **Décision de lancement : non.** L’environnement reste protégé par `noindex, nofollow` et ne doit pas être ouvert au public à ce stade.

## Préconditions avant ouverture publique

| Priorité | Précondition | Critère de sortie |
|---|---|---|
| Haute | Restauration isolée | Restaurer un jeu complet dans une instance distincte et vérifier WordPress, forum, extension et connexion. |
| Haute | Exécution planifiée | Vérifier le cron hébergeur, sa continuité sans trafic et le comportement de la limitation IP derrière CDN. |
| Haute | Domaine et e-mail de production | Configurer le domaine final, HTTPS, expéditeur transactionnel, SPF, DKIM et DMARC; tester uniquement avec des destinataires expressément autorisés. |
| Haute | SEO de lancement | Vérifier redirections, canonique, sitemap, robots et Search Console, puis retirer `noindex, nofollow` dans une étape contrôlée. |
| Moyenne | Accessibilité humaine | Réaliser une recette mobile, zoom et lecteur d’écran avec des utilisateurs représentatifs, y compris un parcours senior. |
| Moyenne | Performance terrain | Instrumenter puis suivre LCP, INP et CLS réels avant et pendant une bêta fermée. |

## Références techniques publiques

- `atelier/` contient le thème Atelier à sa version corrigée.
- `premium-forum-core/` contient l’extension PFC à sa version corrigée.
- `fixtures/` rassemble des jeux d’import exclusivement synthétiques, sans mots de passe ni informations personnelles.
- `release/` contient les archives de release contrôlées avant déploiement.

Les détails opérationnels internes, environnements, sauvegardes, journaux et accès restent volontairement exclus de cette publication.
