# Changelog — Atelier WordPress / Premium Forum Core

Ce changelog décrit les artefacts de référence livrés avec le dépôt public. Il ne contient ni accès, ni données de production, ni exports de base.

## Référence de pré-bêta — Atelier 0.4.32 et Premium Forum Core 0.4.19

La référence installable actuelle est le tag `atelier-prebeta-rc2`, accompagné de `ARTIFACTS-SHA256.txt`. Les seules archives de ce lot sont `release/atelier-0.4.32-a11y-visible-labels.zip` et `release/premium-forum-core-0.4.19-csv-validation-compat.zip`.

| Domaine | Évolution contenue |
|---|---|
| H01 — import | PFC 0.4.19 valide les relations utilisateur, forum, sujet et parent de réponse pendant le dry run, avant toute écriture. |
| H05 — accessibilité | Atelier 0.4.32 aligne les noms accessibles de la marque, des votes et de la Collection avec les libellés visibles. |
| H08 — bbPress | La publication PFC emploie les primitives bbPress pertinentes et resynchronise les compteurs ciblés. |
| H09 — CI | PFC 0.4.19 intercepte les erreurs de date modernes sous `Throwable`, ce qui permet au dry run et au harnais hors WordPress de retourner un diagnostic plutôt qu’un fatal. |
| Référence | Manifeste SHA-256, matrice de tests, registre de risques, procédure d’installation/restauration et CI de vérification ajoutés; CI verte exigée. |

> **Limite de la référence.** Elle est apte à une recette isolée et à la préparation d’une bêta fermée sous conditions. Elle n’autorise pas une mise en production publique.

## Jalon historique — atelier-prebeta-rc1

Le tag `atelier-prebeta-rc1` est conservé comme trace immuable de la première tentative de gel. Sa CI a révélé un défaut de portabilité du validateur CSV sous PHP moderne lorsque le pack contient une date invalide. Il **ne doit pas être utilisé pour une installation de référence**. Le tag `atelier-prebeta-rc2` remplace cette candidate sans réécrire l’historique.

## Jalon historique — Atelier 0.4.31 et Premium Forum Core 0.4.16

Cette consolidation antérieure est conservée dans l’historique Git, mais **ne constitue plus la procédure d’installation actuelle**. Les versions PFC 0.4.12 à 0.4.15 sont des étapes intermédiaires de développement et ne sont pas publiées comme releases distinctes.

| Domaine | Évolution consolidée |
|---|---|
| Accessibilité et clavier | Contrastes ciblés, noms accessibles d’actions, sémantique de recherche et fermeture des suggestions par Échap, y compris après navigation par flèche. |
| Sécurité HTTP | Blocage explicite XML-RPC, indisponibilité des mots de passe d’application et en-têtes `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` et `Permissions-Policy` sur le public et la connexion. |
| Inscription | La correction e-mail 0.4.11 est conservée dans la release finale; l’expiration de code et le nettoyage du compte de recette sont validés avec données synthétiques. |
| Modération et import | Séparation membre/modérateur/administrateur testée; dry run, import journalisé et rollback ciblé sont validés avec un pack synthétique. |
| Exploitation | Les extensions et thèmes inactifs ont été nettoyés sur le staging; une sauvegarde complète locale de clôture a été créée. |

> **Limite de la release.** Deux sauvegardes locales ne remplacent ni une copie distante, ni une restauration isolée testée. La délivrabilité e-mail de production, le domaine final, les DNS d’authentification et les mesures terrain restent des prérequis avant ouverture publique.

## Jalon historique — Premium Forum Core 0.4.11

Ce jalon a stabilisé l’e-mail de recette. Il est historique; la référence d’installation actuelle est PFC 0.4.19 avec Atelier 0.4.32.

| Domaine | Évolution livrée |
|---|---|
| E-mail de recette | Flux validé avec Mailtrap Email Sandbox et une adresse synthétique : envoi, renvoi, confirmation et suppression du compte de recette. |
| Correctif PFC | La chaîne `sprintf` de l’e-mail initial ne déclenche plus l’interpolation PHP qui bloquait l’envoi. |
| Reprise de confirmation | `wp-login.php?action=verify` affiche le formulaire Atelier même après une navigation directe; l’adresse est saisie localement seulement si le contexte a été perdu. |
| Confidentialité | L’adresse n’est pas transportée dans l’URL et les erreurs de vérification ou de renvoi ne révèlent pas l’état d’un compte. |
| Nettoyage | Les diagnostics administrateur/transient provisoires de recette ont été retirés; les échecs restent traités sans exposer les détails SMTP au visiteur. |

> **Limite de la release.** Mailtrap Sandbox confirme le comportement de staging, non la délivrabilité de production. Le domaine expéditeur, SPF, DKIM, DMARC et des essais vers de vraies boîtes restent nécessaires avant toute ouverture publique.

## Jalon historique — Atelier 0.4.28 et Premium Forum Core 0.4.4

Ce jalon a introduit plusieurs parcours fonctionnels. Il est historique; les procédures actuelles doivent partir du tag de pré-bêta et du manifeste d’artefacts.

| Domaine | Évolution livrée |
|---|---|
| Inscription | Ajout d’un honeypot inaccessible au parcours clavier, limitation temporelle après validation des champs, contrôle serveur final du pseudo et nettoyage du compte si l’envoi du code échoue. |
| E-mail | Les envois et renvois de code traitent les exceptions et retours négatifs de `wp_mail()`; le renvoi est temporisé à une minute. |
| Modération | Les décisions publier, refuser et supprimer utilisent des formulaires `POST`, nonce, statut `pending` et contrôle de capacité. |
| Communauté | Notifications limitées aux réponses publiées, destinataires dédupliqués, suivi limité aux sujets publics, agrégation complète des votes reçus et nonce de profil explicite. |
| Intégrité | Nettoyage des votes, suivis et notifications lorsqu’un compte ou une contribution est supprimé; migration PFC unique pour les reliquats historiques. |
| Import | Limites de quatre fichiers, 5 Mo par fichier et 20 Mo par pack; dry run, journal et rollback conservés; mots de passe en clair rejetés. |
| SEO | Titres Atelier, meta descriptions, canonique explicite d’accueil, canoniques de sujets, `DiscussionForumPosting` et `BreadcrumbList`; aucune `QAPage` ni `acceptedAnswer` sans workflow réel. |
| Cache | Cache public réservé aux lecteurs non connectés, directive courte de cinq minutes pour les lectures, et exclusion explicite de la connexion et des sessions. |
| RTL et accessibilité | Police Noto Naskh Arabic, direction et alignement RTL pour les contenus arabes; lien d’évitement, noms de boutons, alternatives d’images et ancres internes contrôlés. |

> **Migration de production.** Retirer `noindex, nofollow` et revoir `robots.txt` uniquement lorsque le domaine final, HTTPS, les redirections et le SMTP auront été validés. Purger ensuite le cache applicatif/CDN et reprendre les contrôles SEO anonymes.
