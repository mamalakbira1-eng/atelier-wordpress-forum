# Atelier — matrice de tests de la référence de pré-bêta

**Référence ciblée :** `atelier-prebeta-rc2`.
**Règle :** utiliser uniquement une instance autorisée et des données synthétiques. Les scénarios de sécurité, d’import ou de compteur sont à rejouer après toute modification de PFC, d’Atelier, de bbPress ou de la configuration de cache.

| ID | Domaine | Précondition | Action et preuve attendue | État de recette connu | Rejeu requis sur installation propre |
|---|---|---|---|---|---|
| T01 | Intégrité | Clone sur le tag canonique. | `sha256sum --check`, `unzip -t`, racines ZIP conformes. | Validé sur lot public. | Oui. |
| T02 | Syntaxe | PHP cible disponible. | Lint de tous les PHP Atelier/PFC sans erreur. | Validé sur PHP 8.3. | Oui. |
| T03 | Sécurité HTTP | Instance de préproduction. | XML-RPC refusé; actions privées refusées anonymement; en-têtes attendus présents. | Validé sur environnement de recette. | Oui. |
| T04 | Inscription | Capture e-mail de test, identités synthétiques. | Inscription, renvoi, vérification, expiration et nettoyage; aucune adresse réelle. | Validé antérieurement en sandbox. | Oui. |
| T05 | Anti-spam | Formulaire anonyme synthétique. | Honeypot refusé avant création de compte et sans envoi d’e-mail. | Validé. | Oui. |
| T06 | Modération | Membre, modérateur délégué, administrateur synthétiques. | Contribution pending, publication, refus et suppression; contrôle serveur des interdictions. | Validé. | Oui. |
| T07 | H08 compteurs | Réponse membre pending. | Publication par PFC, puis égalité entre cartes, compteur de sujet et agrégats de forum. | Validé; inclus dans PFC 0.4.19. | Oui. |
| T08 | Interactions | Deux membres synthétiques distincts. | Vote, anti-auto-vote, suivi, bascule, notification interne et réponse à une réponse. | Validé. | Oui. |
| T09 | Import valide | Pack synthétique valide. | Dry run, import contrôlé, journal et rollback ciblé. | Validé. | Oui. |
| T10 | H01 relations | Pack synthétique comportant une relation absente. | Dry run `needs_fix`, aucune création avant écriture. | Validé; inclus dans PFC 0.4.19. | Oui. |
| T11 | H09 validation date | Pack synthétique avec date et compteur invalides. | Dry run et harnais retournent des erreurs structurées sans fatal sous PHP cible. | Validé localement et exigé en CI sur rc2. | Oui. |
| T12 | Cache | Visiteur anonyme et écran de connexion. | Cache public de lecture; directives privées et `no-store` à la connexion. | Validé. | Oui. |
| T13 | Recherche/RTL | Sujet arabe synthétique publié. | Suggestion, flèche bas, Entrée, contenu RTL, alignement et police attendus. | Validé sur bureau. | Oui, avec mobile. |
| T14 | Accessibilité | Mobile et bureau, clavier, zoom, lecteur d’écran. | Focus visible, erreurs de formulaire compréhensibles, noms accessibles et libellés cohérents. | Lighthouse H05 validé; recette humaine incomplète. | Oui, obligatoire. |
| T15 | SEO/lisibilité | Visiteur anonyme. | Titre, description, canonique, fil d’Ariane, `DiscussionForumPosting`; pas de `QAPage` sans acceptation réelle. | Validé sur recette contrôlée. | Oui. |
| T16 | Restauration | Sauvegarde complète de laboratoire. | Restauration sur instance isolée puis T03, T06, T07 et T15. | Non validé. | Oui, bloquant bêta. |

Les détails narratifs de la recette sont disponibles dans la [synthèse publique](public-senior-recipe-summary-20260827.md). La matrice n’autorise pas l’exécution sur une instance active, ne remplace pas une sauvegarde complète et ne permet pas d’inférer une performance terrain depuis Lighthouse.
