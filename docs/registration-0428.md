# Atelier — inscription premium 0.4.28

## Fonctionnalité

Atelier propose désormais un accès membre depuis la page de connexion WordPress. Le lien « Créer mon accès Atelier » ouvre un formulaire dédié avec prénom, nom, adresse e-mail, pseudo suggéré et éditable, mot de passe et confirmation.

Le pseudo est proposé côté navigateur à partir du prénom et du nom, après translittération simple des accents. Le membre peut le modifier avant l’envoi. Le serveur revalide toujours les champs, l’unicité du pseudo et de l’adresse e-mail, ainsi qu’un mot de passe d’au moins dix caractères.

Après création, le compte reste bloqué par la méta PFC `_pfc_email_pending`. Un code numérique à six chiffres est hashé, expire après quinze minutes et est envoyé avec `wp_mail()`. Cinq essais maximum sont autorisés. Le renvoi du code renouvelle le hash, la durée et le compteur d’essais. Après validation, les métadonnées temporaires sont supprimées et l’utilisateur est redirigé vers la connexion Atelier.

## Validation staging

Le plugin `Premium Forum Core 0.4.0` a été installé après désactivation de l’ancienne copie 0.3.0, afin d’éviter les collisions de classes PHP. Le thème `Atelier 0.4.28` a ensuite été activé.

| Contrôle | Résultat |
|---|---|
| Page de connexion | Affiche « Créer mon accès Atelier ↗ » |
| Page d’inscription | Champs et CTA premium visibles |
| Suggestion de pseudo | `Élodie` + `Ben Amar` → `elodie.benamar` |
| Validation serveur | Deux mots de passe différents refusés |
| Page de confirmation | Routage `action=verify` implémenté |
| Renvoi de code | Routage `action=resend` implémenté |
| Blocage avant confirmation | Filtre `authenticate` implémenté |
| Responsive | Grille à une colonne sous 700 px |
| Syntaxe PHP locale | Aucune erreur |

## E-mail

La réception réelle du code dépend de la configuration d’envoi du WordPress. `wp_mail()` doit être relié à un SMTP ou à un service transactionnel correctement configuré. Le code n’est jamais affiché dans le HTML, dans l’URL ou dans les messages d’erreur.

Le staging possède déjà des comptes de recette importés, mais aucun mot de passe réel n’a été créé ou exposé pendant cette validation. La création d’un compte avec une adresse contrôlée et la vérification du code reçu constituent le dernier test d’intégration e-mail à effectuer lorsque SMTP est confirmé.
