# Changelog — Atelier WordPress / Premium Forum Core

Ce changelog décrit les artefacts de référence livrés avec le dépôt public. Il ne contient ni accès, ni données de production, ni exports de base.

## Atelier 0.4.31 et Premium Forum Core 0.4.16 — consolidation préproduction, 26 août 2026

Les références finales deviennent le thème **Atelier 0.4.31**, livré dans `release/atelier-0.4.31-search-escape-complete.zip`, et **Premium Forum Core 0.4.16**, livré dans `release/premium-forum-core-0.4.16-security-headers-complete.zip`. Les versions PFC 0.4.12 à 0.4.15 sont des étapes intermédiaires de développement et ne sont pas publiées comme releases distinctes.

| Domaine | Évolution consolidée |
|---|---|
| Accessibilité et clavier | Contrastes ciblés, noms accessibles d’actions, sémantique de recherche et fermeture des suggestions par Échap, y compris après navigation par flèche. |
| Sécurité HTTP | Blocage explicite XML-RPC, indisponibilité des mots de passe d’application et en-têtes `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` et `Permissions-Policy` sur le public et la connexion. |
| Inscription | La correction e-mail 0.4.11 est conservée dans la release finale; l’expiration de code et le nettoyage du compte de recette sont validés avec données synthétiques. |
| Modération et import | Séparation membre/modérateur/administrateur testée; dry run, import journalisé et rollback ciblé sont validés avec un pack synthétique. |
| Exploitation | Les extensions et thèmes inactifs ont été nettoyés sur le staging; une sauvegarde complète locale de clôture a été créée. |

> **Limite de la release.** Deux sauvegardes locales ne remplacent ni une copie distante, ni une restauration isolée testée. La délivrabilité e-mail de production, le domaine final, les DNS d’authentification et les mesures terrain restent des prérequis avant ouverture publique.

## Premium Forum Core 0.4.11 — stabilisation e-mail de recette, 26 août 2026

La référence plugin devient **Premium Forum Core 0.4.11**, livrée dans `release/premium-forum-core-0.4.11-registration-stabilized.zip`. Le thème de référence reste Atelier 0.4.28.

| Domaine | Évolution livrée |
|---|---|
| E-mail de recette | Flux validé avec Mailtrap Email Sandbox et une adresse synthétique : envoi, renvoi, confirmation et suppression du compte de recette. |
| Correctif PFC | La chaîne `sprintf` de l’e-mail initial ne déclenche plus l’interpolation PHP qui bloquait l’envoi. |
| Reprise de confirmation | `wp-login.php?action=verify` affiche le formulaire Atelier même après une navigation directe; l’adresse est saisie localement seulement si le contexte a été perdu. |
| Confidentialité | L’adresse n’est pas transportée dans l’URL et les erreurs de vérification ou de renvoi ne révèlent pas l’état d’un compte. |
| Nettoyage | Les diagnostics administrateur/transient provisoires de recette ont été retirés; les échecs restent traités sans exposer les détails SMTP au visiteur. |

> **Limite de la release.** Mailtrap Sandbox confirme le comportement de staging, non la délivrabilité de production. Le domaine expéditeur, SPF, DKIM, DMARC et des essais vers de vraies boîtes restent nécessaires avant toute ouverture publique.

## Atelier 0.4.28 et Premium Forum Core 0.4.4 — audit senior, 26 août 2026

La release associe le thème WordPress **Atelier 0.4.28** — répertoire installable `atelier-0428/` — et **Premium Forum Core 0.4.4**. Les archives à privilégier sont `release/atelier-0.4.28-active-theme-senior-audit.zip` et `release/premium-forum-core-0.4.4-senior-audit.zip`.

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
