# CDC senior — Images dans les sujets et réponses bbPress avec conversion AVIF

**Projet :** Atelier / WordPress / bbPress / PFC  
**Version cible :** RC5 dédiée aux médias  
**Auteur :** Manus AI  
**Date :** 2026-08-28  
**Périmètre :** sujets bbPress et réponses bbPress créés ou modifiés par des utilisateurs authentifiés.

## 1. Décision d’architecture

La fonctionnalité est acceptée uniquement selon une séparation stricte des responsabilités. Le thème Atelier affiche le champ d’upload et le rendu. Le plugin PFC, ou une extension média dédiée chargée par PFC, porte les contrôles de sécurité, la réception multipart, la création des attachments, la conversion AVIF, la modération, la suppression et la vérification d’intégrité.

Le système doit refuser proprement une image invalide. Il ne doit jamais publier un fichier qui n’a pas passé les contrôles serveur. Il ne doit jamais supprimer l’original avant que l’AVIF soit effectivement créé, décodable, associé au bon contenu et couvert par un fallback ou une politique de restauration.

### Politique média obligatoire

| Paramètre | Valeur cible | Règle |
|---|---:|---|
| Images par publication | 3 maximum | Sujet et réponse confondus dans une même soumission ; rejet atomique au-delà de 3 |
| Taille maximale par image | 5 MiB | Contrôle côté navigateur indicatif et contrôle serveur obligatoire |
| Taille maximale totale | 12 MiB | Rejet atomique si dépassée |
| Formats d’entrée | JPEG, PNG, WebP | MIME réel vérifié par `finfo`/WordPress |
| SVG | Interdit par défaut | Aucun contournement par extension ou MIME falsifié |
| Formats exécutables | 0 | PHP, HTML, JS, XML actif et archives refusés |
| Dimensions maximales | 8000 × 8000 px | Protection contre décompression excessive |
| AVIF de sortie | Obligatoire si moteur compatible | MIME `image/avif`, fichier décodable et non vide |
| Original | Conservé jusqu’à validation | Suppression différée uniquement après contrôle complet |
| Qualité AVIF cible | 55–65 | Valeur configurable, mesurée dans la preuve |
| Quota quotidien par personne | 10 images / jour par défaut | Compteur serveur en UTC, configurable sans modifier le code ; rejet avant écriture au-delà de 10 | 
| Carrousel | 1 carrousel par publication si 2 ou 3 images | Une seule image reste une image simple ; 2–3 images sont navigables au clavier et au tactile |
| Texte alternatif | Obligatoire ou généré contrôlé | Jamais de nom de fichier brut comme seule valeur |

## 2. Flux fonctionnel attendu

L’utilisateur connecté sélectionne au maximum trois images depuis le formulaire de sujet ou de réponse. Le navigateur indique les limites mais ne constitue pas une barrière de sécurité. Le serveur vérifie le nonce, la capacité, le type réel, la taille, les dimensions, le nombre de fichiers et l’appartenance du parent.

Le fichier reçu est d’abord placé dans un espace temporaire non public. L’image est décodée, ré-encodée en AVIF et relue par le moteur image. Le système crée ensuite l’attachement lié au topic ou à la réponse, génère les métadonnées nécessaires et insère dans le contenu une référence contrôlée, idéalement par attachment ID ou shortcode interne plutôt que par HTML arbitraire.

Si le sujet ou la réponse est mis en attente par PFC, l’image reste non publique jusqu’à publication. Si la contribution est rejetée, supprimée ou abandonnée, les fichiers temporaires et attachments associés sont nettoyés selon une tâche idempotente. L’original ne peut être supprimé qu’après validation de l’AVIF et expiration du délai de sécurité configuré.

## 3. Carrousel et quota quotidien

Une publication peut contenir **0 à 3 images**. Lorsque la publication contient 2 ou 3 images, elles sont présentées dans un carrousel unique lié au sujet ou à la réponse. Le carrousel doit exposer un nom accessible, un compteur de position, un bouton précédent, un bouton suivant et un accès clavier direct à chaque image. Il doit fonctionner sans JavaScript sous forme de galerie verticale de secours.

Le quota recommandé est de **10 images par personne et par jour UTC**, tous sujets et réponses confondus. Le compteur doit être calculé côté serveur à partir des uploads acceptés et non à partir des clics ou des fichiers sélectionnés dans le navigateur. Une image refusée ne consomme pas le quota ; une image acceptée puis attachée à une publication le consomme. Une double soumission idempotente ne doit pas consommer deux fois le quota.

Le quota doit être appliqué avant l’écriture définitive. Si une soumission de trois images dépasse le quota restant, la soumission est rejetée atomiquement avec un message indiquant le quota restant ; aucun fichier temporaire ne doit rester après le rejet. Le seuil de 10 est une valeur de configuration documentée et doit pouvoir être modifiée par un administrateur autorisé, avec une valeur minimale de 0 et une valeur maximale de 100.

Le carrousel ne doit jamais charger trois images pleine résolution simultanément. Les images doivent utiliser des tailles dérivées, `loading="lazy"` sauf la première image visible, des dimensions explicites et un texte alternatif individuel. Le changement de slide ne doit pas déplacer le focus de manière inattendue et doit annoncer la position au lecteur d’écran.

## 4. Changements code par code

| Fichier / zone | Changement obligatoire | Test de sortie |
|---|---|---|
| `atelier/bbpress/form-topic.php` | Ajouter `enctype="multipart/form-data"`, champ `atelier_topic_images[]`, accept explicite, limite affichée et aide accessible | Le formulaire contient le champ, le label et le multipart correct |
| `atelier/bbpress/form-reply.php` | Ajouter le même champ pour `atelier_reply_images[]`, sans modifier les champs natifs bbPress | Le parcours de réponse conserve la publication bbPress |
| `atelier/functions.php` | Enqueue du CSS/JS d’aide uniquement ; aucune logique de confiance ou de stockage | Le thème reste activable sans PFC média |
| `atelier/assets/js/atelier-media.js` | Prévalidation UX : nombre, taille, aperçu local, suppression d’un aperçu, messages accessibles | Les contrôles UX ne remplacent jamais les refus serveur |
| `premium-forum-core/premium-forum-core.php` | Charger le module média après vérification de bbPress | Le plugin démarre avec ou sans moteur AVIF |
| `premium-forum-core/includes/class-pfc-media.php` | Centraliser nonce, capacité, réception, MIME, dimensions, conversion, attachment et insertion | Tests unitaires et intégration sur les 20 objectifs |
| `premium-forum-core/includes/class-pfc-moderation.php` | Lier les médias au post parent et déclencher le nettoyage lors du rejet, trash ou delete | Aucun média orphelin après 5 suppressions contrôlées |
| `premium-forum-core/includes/class-pfc-community.php` | Nettoyer les médias liés aux contenus supprimés si le hook communautaire est concerné | Suppression utilisateur/post sans fichier orphelin |
| `premium-forum-core/uninstall.php` | Politique explicite : ne pas supprimer les médias métier par défaut à la désinstallation ; option de nettoyage séparée et confirmée | Désinstallation sans perte implicite de contenu |
| `premium-forum-core/assets/admin.css` | Afficher l’état AVIF, les erreurs de conversion et le nombre de médias liés dans l’administration | L’administration ne révèle aucun chemin sensible |
| `atelier/style.css` | Styles de galerie, légende, focus, responsive et RTL | 320 px, 768 px et 1440 px sans débordement |
| `docs/validation/` | Ajouter preuves JSON, fixture, rapport et décision | Chaque objectif possède un artefact horodaté |

## 4. Objectifs strictement mesurables

Les codes de sortie sont normalisés : `0 = PASS`, `1 = FAIL`, `2 = BLOCKED`, `3 = ERREUR DE TEST/ENVIRONNEMENT DE TEST`. Un objectif BLOCKED n’est jamais PASS. Une erreur du runner doit être corrigée ou déclarée `3`, jamais ignorée.

| ID | Objectif chiffré | Preuve obligatoire | Exit | Critère PASS / FAIL | Règle si environnement empêché |
|---|---|---|---:|---|---|
| `IMG-01` | 2 formulaires, sujet et réponse, contiennent chacun 1 champ multipart avec label accessible | `IMG-01-forms.json` + HTML capturé | 0/1/2 | PASS si 2/2 champs, labels et `enctype` sont présents ; FAIL sinon | BLOCKED si bbPress ne charge pas les templates ; exit 2 |
| `IMG-02` | 3 rôles authentifiés testés ; membre autorisé, modérateur et administrateur suivent le parcours prévu | `IMG-02-roles.json` | 0/1/2 | PASS si 3/3 décisions de capacité sont correctes ; FAIL à toute élévation | BLOCKED si 3 comptes synthétiques isolés ne peuvent pas être créés |
| `IMG-03` | 10 soumissions sans nonce ou avec nonce invalide ; 10/10 refus, delta métier nul | `IMG-03-nonce.json` | 0/1/2 | PASS si 10 refus et 0 fichier créé ; FAIL à toute création | BLOCKED si l’endpoint ne peut pas être invoqué sans risque |
| `IMG-04` | 8 tentatives avec capacité insuffisante ou parent forgé ; 8/8 refus, 0 attachment étranger | `IMG-04-capability-parent.json` | 0/1/2 | PASS si 8/8 refus ; FAIL si une image est rattachée au mauvais parent | BLOCKED si rôles séparés indisponibles |
| `IMG-05` | 5 types valides testés : JPEG, PNG, WebP, JPEG renommé, MIME falsifié ; seuls les 3 types réels autorisés passent | `IMG-05-mime.json` | 0/1/2 | PASS si les 3 formats autorisés passent et 2 falsifications sont refusées | BLOCKED si `finfo` ou le moteur WordPress réel est indisponible |
| `IMG-06` | 5 limites testées : 1 octet sous la taille, taille exacte, 1 octet au-dessus, 1 pixel sous dimension, 1 pixel au-dessus | `IMG-06-boundaries.json` | 0/1/2 | PASS si les frontières correspondent à la politique et delta nul sur refus | BLOCKED si PHP/webserver rejette avant le handler ; exit 2 |
| `IMG-07` | 6 fichiers dangereux ou non autorisés : SVG, PHP, HTML, JS, ZIP, MIME incohérent ; 6/6 refus | `IMG-07-dangerous.json` | 0/1/2 | PASS si 6/6 refus et aucun fichier public ; FAIL à tout stockage exécutable | BLOCKED si la couche serveur filtre avant l’application sans trace exploitable |
| `IMG-08` | 10 images valides, dont 2 avec transparence et 2 en très grande définition sous limite ; 10/10 décodées | `IMG-08-decode.json` | 0/1/2 | PASS si toutes sont décodables et dimensions conservées selon politique | BLOCKED si Imagick/GD ne supporte pas l’entrée sans alternative sûre |
| `IMG-09` | 10/10 images converties en AVIF lorsque le moteur annonce le support ; MIME et signature vérifiés | `IMG-09-avif.json` | 0/1/2 | PASS si fichier AVIF existe, est décodable, non vide et associé au bon attachment | BLOCKED si aucun moteur AVIF réel n’est disponible ; l’option doit alors être désactivée proprement |
| `IMG-10` | 5 échecs de conversion injectés ; 5/5 rejets propres, aucun AVIF cassé, original conservé | `IMG-10-conversion-failure.json` | 0/1/2 | PASS si aucune publication partielle et fallback intact | BLOCKED si aucun seam d’injection sûr n’existe |
| `IMG-11` | 5 sujets et 5 réponses publiés avec image ; 10/10 images visibles sur la page canonique | `IMG-11-render.json` + captures | 0/1/2 | PASS si 10/10 références chargent HTTP 200 et sont liées au bon parent | BLOCKED si l’environnement ne permet pas la publication synthétique |
| `IMG-12` | 5 contributions `pending` avec image ; 0/5 fichier directement accessible avant modération | `IMG-12-pending-private.json` | 0/1/2 | PASS si 0 fuite publique et accès contrôlé pour l’auteur autorisé | BLOCKED si aucune politique de stockage privé n’est disponible |
| `IMG-13` | 5 publications approuvées ; 5/5 médias deviennent accessibles seulement après transition `publish` | `IMG-13-moderation-transition.json` | 0/1/2 | PASS si les 5 transitions rendent l’image disponible correctement | BLOCKED si le moteur de modération ne peut pas être piloté sans données réelles |
| `IMG-14` | 5 contenus rejetés ou supprimés ; après nettoyage, 0 fichier temporaire et 0 attachment orphelin | `IMG-14-cleanup.json` | 0/1/2 | PASS si le scan ciblé retourne zéro orphelin | BLOCKED si le cron/nettoyeur réel n’est pas déclenchable |
| `IMG-15` | 5 doubles soumissions identiques ; au maximum 1 attachment métier par fichier logique | `IMG-15-idempotence.json` | 0/1/2 | PASS si aucun doublon non justifié ; FAIL à tout double média | BLOCKED si la soumission atomique ne peut pas être simulée |
| `IMG-16` | 5 textes alternatifs fournis, 5 absents ; 10/10 images ont un `alt` non vide et approprié | `IMG-16-alt.json` | 0/1/2 | PASS si 10/10 `alt` sont présents, échappés et non constitués d’un chemin brut | BLOCKED si le rendu final n’est pas accessible |
| `IMG-17` | 4 vues à 320, 768, 1024 et 1440 px ; 16/16 combinaisons sans débordement ni bouton inaccessible | `IMG-17-responsive.json` + captures | 0/1/2 | PASS si 16/16 vues sont utilisables | BLOCKED si aucun navigateur contrôlé n’est disponible |
| `IMG-18` | 4 pages en LTR et 2 pages RTL ; 6/6 rendus sans inversion de légende, bouton ou ordre de galerie | `IMG-18-rtl.json` + captures | 0/1/2 | PASS si 6/6 rendus corrects | BLOCKED si aucune fixture RTL n’est disponible |
| `IMG-19` | 1 sauvegarde puis 1 restauration ; 10 invariants média vérifiés : attachment, parent, URL, MIME, dimensions, AVIF, alt, statut, visibilité, suppression | `IMG-19-restore.json` | 0/1/2 | PASS si 10/10 invariants concordent et 0 perte | BLOCKED si seconde instance ou sauvegarde complète manque |
| `IMG-20` | 1 audit final de sécurité et 1 audit de fichiers ; 0 secret, 0 fichier exécutable, 0 orphelin, 0 warning PHP | `IMG-20-final-audit.json` + logs | 0/1/2 | PASS si les 4 compteurs valent 0 et lint exit 0 | BLOCKED si les logs ou le système de fichiers réel sont inaccessibles |

| `IMG-21` | 6 soumissions de 0, 1, 2, 3, 4 et 5 images ; 4 images et 5 images sont rejetées atomiquement, aucune publication partielle | `IMG-21-publication-limit.json` | 0/1/2 | PASS si 0–3 passent selon la politique et 4–5 sont rejetées sans fichier résiduel ; FAIL à tout dépassement accepté | BLOCKED si le formulaire bbPress réel ne peut pas être soumis |
| `IMG-22` | 4 utilisateurs synthétiques, 10 images chacun sur une journée UTC ; 40 acceptées, puis la 11e image de chaque utilisateur est refusée | `IMG-22-daily-quota.json` | 0/1/2 | PASS si 40/40 acceptées et 4/4 dépassements refusés sans mutation ; FAIL si le quota est contournable ou partagé incorrectement | BLOCKED si l’horloge UTC ou quatre comptes isolés ne sont pas maîtrisables |
| `IMG-23` | 6 publications contenant 1, 2 ou 3 images ; 6 carrousels/rendus vérifiés sur clavier, tactile et sans JS | `IMG-23-carousel.json` + captures | 0/1/2 | PASS si 6/6 rendus ont nom accessible, précédent/suivant, position et fallback sans JS | BLOCKED si un navigateur clavier/tactile représentatif n’est pas disponible |
| `IMG-24` | 3 carrousels en LTR et 3 en RTL ; 6/6 sans inversion des commandes ni perte de focus | `IMG-24-carousel-rtl.json` | 0/1/2 | PASS si 6/6 parcours RTL/LTR sont cohérents | BLOCKED si aucune fixture RTL ou aucun test clavier n’est disponible |

## 6. Tests automatisés obligatoires

Le runner doit préparer une fixture synthétique non sensible contenant les formats autorisés, les formats falsifiés, les fichiers trop grands, les dimensions limites, les images transparentes et les contenus arabes. Chaque test doit enregistrer son entrée sous forme de hash ou de métadonnée non sensible, jamais le contenu d’un secret.

Les tests PHP doivent couvrir le service de validation, le calcul de taille totale, la génération du nom sûr, la conversion, la création de métadonnées, le nettoyage idempotent et la gestion d’exception. Les tests navigateur doivent couvrir les deux formulaires, les messages d’erreur accessibles, l’aperçu, la suppression d’un aperçu, la publication et le rendu canonique.

Les tests d’intégration doivent utiliser des utilisateurs synthétiques et un environnement WordPress jetable. Ils doivent comparer les compteurs avant et après chaque scénario. Toute mutation inattendue doit produire `FAIL`, même si la page finale semble fonctionner.

## 6. Contraintes de sécurité non négociables

Le champ `accept` côté navigateur ne constitue pas une validation. Le serveur doit vérifier le contenu réel du fichier et ne jamais faire confiance au nom, à l’extension ou au MIME fourni par le client. Les noms doivent être générés par WordPress ou par un générateur sûr. Les chemins absolus, clés, nonces et données privées ne doivent pas apparaître dans les notices publiques, les attributs HTML ou les logs accessibles aux membres.

Les images en attente ne doivent pas être servies directement par une URL prévisible. Si le stockage privé n’est pas possible sur Hostinger, l’implémentation doit refuser l’upload pour les contenus `pending` plutôt que publier temporairement une image non modérée.

La suppression de l’original est une opération différée. Elle doit être déclenchée par une tâche idempotente après vérification de l’AVIF, de son attachment, de ses métadonnées et de la possibilité de restaurer la référence. La désinstallation du plugin ne doit pas supprimer les médias de l’utilisateur par défaut.

## 7. Critères de release

La release image ne peut être marquée `GO` que si les objectifs `IMG-01` à `IMG-20` sont tous `PASS`, avec exit code `0`, et si aucun test existant RC4 ne régresse. Un `BLOCKED` sur `IMG-09` peut être accepté uniquement si l’interface indique clairement que la conversion AVIF est indisponible et si l’upload original est refusé ou conservé selon une décision explicitement approuvée ; il ne peut pas être présenté comme « AVIF validé ».

Un `FAIL` sur la sécurité, les permissions, la confidentialité des médias en attente, la liaison parent/enfant, la conversion vérifiée, le nettoyage ou la restauration impose `NO-GO`. Un `BLOCKED_HUMAN` doit rester séparé d’un `BLOCKED_EXTERNAL`. La décision finale doit inclure la liste complète des objectifs, leurs preuves, leurs exits et le SHA testé.

## 8. Livrables obligatoires

| Livrable | Contenu minimal |
|---|---|
| Code | Module média PFC, modifications des deux formulaires, JS/CSS, hooks de modération et nettoyage |
| Fixtures | Images synthétiques et fichiers négatifs, avec manifeste et hashes |
| Runners | PHP, Playwright ou scripts d’intégration reproductibles, sans secret en dur |
| Preuves | 20 JSON/MD, un par objectif, avec horodatage, SHA, mesures et exit code |
| Rapport | Tableau PASS/FAIL/BLOCKED et décision mécanique |
| Archive | ZIP du thème et ZIP du plugin, hashes SHA-256 |
| Runbook | Installation, configuration AVIF, rollback, restauration, nettoyage et désactivation d’urgence |

## Conclusion

Le périmètre est techniquement réalisable, mais la fonctionnalité ne doit pas être ajoutée comme un simple bouton d’upload dans le thème. La validation senior exige une implémentation PFC dédiée, une gestion sûre des médias en attente, une conversion AVIF vérifiée, une suppression différée de l’original et vingt preuves indépendantes. Toute impossibilité de l’environnement doit être affichée en `exit 2`, jamais masquée derrière un résultat favorable.
