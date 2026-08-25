# Rapport de simulation locale — Atelier / PFC

**Date : 25 août 2026**

## Conclusion

La sandbox contient les sources complètes du thème Atelier et du plugin Premium Forum Core, mais elle ne contient pas une installation WordPress exécutable. Aucun `wp-config.php`, noyau WordPress, instance MySQL/MariaDB active, WP-CLI, Docker ou Podman n’a été trouvé dans les répertoires inspectés. Il est donc possible de tester la syntaxe et la logique pure du parseur, mais pas de simuler honnêtement une session WordPress complète ou une authentification réelle en local.

## Import CSV PFC

Le validateur PFC a été exécuté dans un harnais PHP isolé qui réutilise la classe `PFC_Importer` et ses fonctions de lecture/validation CSV, avec uniquement des stubs contrôlés pour les fonctions WordPress nécessaires.

| Scénario | Résultat |
|---|---:|
| Pack valide `users.csv`, `forums.csv`, `topics.csv`, `replies.csv` | Réussi |
| Fichiers lus | 4 |
| Lignes validées | 204 |
| Utilisateurs / forums / sujets / réponses | 100 / 4 / 20 / 80 |
| Erreurs sur pack valide | 0 |
| Pack invalide avec mot de passe en clair | Rejeté |
| Pack invalide avec date incorrecte | Détectée |
| Pack invalide avec `upvotes_count=abc` | Détectée |
| Assertions du harnais | 4/4 réussies |

Le test confirme que le dry run ne valide pas un pack contenant un champ `password` en clair, une date de sujet invalide ou un compteur de votes non numérique. Le champ `password_hash` prévu par le modèle n’a pas été traité comme un mot de passe en clair ; le code de production réserve le rejet aux champs `password` et `plain_password`, conformément à son commentaire de sécurité.

## Connexion Atelier

La couche d’intégration de la page de connexion a été inspectée et tous les fichiers PHP du thème et du plugin passent `php -l` sans erreur. Les hooks présents sont `login_enqueue_scripts`, `login_headerurl`, `login_headertext` et `login_footer`. La feuille `login.css` contient le style Atelier et les règles responsive.

En revanche, une connexion réelle n’a pas été simulée localement, car le runtime WordPress et la base de données nécessaires ne sont pas présents dans la sandbox. Tester une authentification avec des stubs PHP ne permettrait pas de vérifier correctement les cookies de session, `wp_signon()`, les redirections, les erreurs WordPress, les rôles ou la persistance utilisateur.

## Vérifications de syntaxe

Tous les fichiers PHP du thème Atelier et du plugin Premium Forum Core inspectés passent la vérification de syntaxe PHP. Les artefacts temporaires du test restent hors du dépôt GitHub et ne contiennent aucun identifiant réel.

## Suite recommandée

Pour obtenir un test d’intégration complet, il faut fournir ou installer une instance WordPress locale avec PHP, une base MySQL/MariaDB, bbPress, le plugin PFC et le thème Atelier. À défaut, la connexion et l’import peuvent continuer à être testés sur le staging réel avec des comptes et des packs de démonstration dédiés, mais ce ne serait plus une simulation dans la sandbox.

## Recette réelle sur staging — job PFC 9

Après confirmation de l’utilisateur et résolution du CAPTCHA, le pack minimal de recette a été téléversé dans Premium Forum Core. Le dry run a été déclaré valide avec le message « aucune donnée n’a été écrite », puis l’import a été exécuté avec succès.

| Objet créé | Valeur de recette |
|---|---|
| Job PFC | 9 |
| Utilisateur | `atelier-recette-u1` — Atelier Recette |
| Forum | Atelier — Recette import |
| Sujet | Recette PFC Atelier 0427 |
| Réponse | Réponse de contrôle |
| Journal | 4 objets créés et journalisés |
| URL publique | `/forums/topic/recette-pfc-atelier-0427/` |

La page publique confirme le forum, le sujet, l’auteur, le rang Éclaireur, la date du 25 août 2026, les 7 votes historiques et la réponse avec 3 votes utiles.

La page de connexion Atelier a également été ouverte. Un essai volontairement invalide avec l’identifiant de démonstration `atelier-recette-u1` et un mot de passe fictif a produit le message d’erreur personnalisé attendu, sans déconnecter la session administrateur. Le parcours « Mot de passe oublié » affiche aussi l’interface Atelier et le lien de retour au forum. Un succès de connexion n’a pas été simulé, car l’importateur génère volontairement un mot de passe aléatoire et marque le compte comme nécessitant une réinitialisation ; aucun mot de passe réel n’a été créé ou transmis.
