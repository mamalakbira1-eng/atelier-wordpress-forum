# CDC RC4 — objectifs strictement mesurables

## 1. Règles communes de validation

Ce document est le contrat de validation du lot RC4 Atelier dans le laboratoire local. Chaque objectif doit être exécuté sur un commit identifié par son SHA complet. Une preuve est recevable uniquement si elle contient au minimum l’identifiant de l’objectif, le SHA testé, l’horodatage UTC, l’environnement, les entrées utilisées, les mesures observées, la commande exécutée et l’exit code.

| Code | Signification | Règle |
|---:|---|---|
| `0` | PASS | Toutes les assertions chiffrées sont vraies et la preuve obligatoire existe, est lisible et rattachée au SHA testé. |
| `1` | FAIL | Le test a pu s’exécuter mais au moins une assertion produit est fausse, une régression est observée ou une preuve contredit la cible. |
| `2` | BLOCKED | Le test n’a pas pu être exécuté de façon valide à cause de l’environnement, d’un accès absent, d’un prérequis humain/externe ou d’une décision produit manquante. |
| `4` | CLEANUP_FAIL | Le test est terminé mais le laboratoire n’a pas été nettoyé conformément à la cible. |

Un objectif ne peut être marqué `PASS` qu’avec une preuve machine ou humaine explicitement référencée. Un timeout, une page absente, une dépendance indisponible, un accès réel absent ou un scénario non représentable produit `BLOCKED`, jamais `PASS`. Un objectif `BLOCKED` doit conserver le message d’erreur, la commande, le log et l’impact release. Les cookies, mots de passe, tokens, e-mails réels et données personnelles ne doivent jamais apparaître dans les preuves.

Les commandes ci-dessous sont des commandes de référence. Les chemins peuvent être adaptés au laboratoire, mais la commande réellement utilisée doit être conservée dans la preuve. Les tests doivent utiliser exclusivement des fixtures synthétiques et des bases isolées.

## 2. Référence, code et CI locale

| ID | Cible chiffrée | Commande/procédure | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---|---:|---|
| `REL-CL-01` | 1 SHA complet, 1 branche, 1 archive, 1 hash SHA-256 concordant | `git rev-parse HEAD`; calculer `sha256sum` de l’archive; comparer au manifeste | `REL-CL-01-baseline-current.json` | 0/1/2 | PASS si SHA, branche et hash sont présents et cohérents; FAIL si divergence; BLOCKED si le dépôt ou l’archive est inaccessible. |
| `REL-CL-02` | 100 % du diff entre baseline et commit final examiné; 0 fichier non classé | `git diff --name-status BASE..HEAD`; revue signée du diff | `REL-CL-02-diff-review-current.json` | 0/1/2 | PASS si chaque fichier est classé et justifié; FAIL si un fichier est oublié ou non expliqué; BLOCKED si la baseline n’est pas disponible. |
| `REL-CL-03` | 5/5 sous-tests techniques à exit 0 : lint PHP, diff check, archive, redaction, dépendances | Exécuter les cinq commandes du runner RC4 | `REL-CL-03-ci-local.json` | 0/1/2 | PASS si 5/5 retournent 0; FAIL dès qu’un sous-test retourne 1; BLOCKED si un outil obligatoire manque. |

## 3. Sécurité, permissions et exposition HTTP

| ID | Cible chiffrée | Commande/procédure | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---|---:|---|
| `SEC-CL-01` | 50/50 décisions rôle × action conformes; 0 accès non autorisé | Exécuter la matrice pour membre, modérateur, admin, keymaster et anonyme | `SEC-CL-01-role-action-matrix.json` | 0/1/2 | PASS si 50/50 décisions correspondent à la politique; FAIL si une autorisation est incorrecte; BLOCKED si un rôle ne peut pas être créé dans l’instance isolée. |
| `SEC-CL-02` | 36/36 requêtes sans nonce ou capacité refusées; delta DB = 0 | Rejouer les actions admin, import, communauté et modération avec nonce/capacité absents ou invalides | `SEC-CL-02-nonce-capability.json` | 0/1/2 | PASS si 36 refus et aucune écriture métier; FAIL si une requête passe ou écrit; BLOCKED si le handler HTTP ne peut pas être atteint. |
| `SEC-CL-03` | 8/8 IDs forgés refusés; compteurs avant/après identiques | Rejouer URLs, paramètres et identifiants d’objets appartenant à un autre utilisateur | `SEC-CL-03-http-forgery.json` | 0/1/2 | PASS si 8/8 refus et aucun delta interdit; FAIL si une donnée est révélée ou modifiée; BLOCKED si deux utilisateurs synthétiques ne peuvent pas être créés. |
| `SEC-CL-04` | 6/6 routes publiques avec statut attendu; 0 marqueur sensible | Tester routes inexistantes, erreur publique, XML-RPC, REST et endpoints anonymes | `SEC-CL-04-public-errors.json` | 0/1/2 | PASS si les six statuts sont ceux de la politique et si aucun secret, chemin local ou stack trace n’apparaît; FAIL sinon; BLOCKED si le serveur local est indisponible. |
| `SEC-CL-05` | 5 URLs privées × 2 utilisateurs = 10 observations; 10/10 réponses `private` ou `no-store`; 0 fuite cross-user | Créer deux comptes, appeler chaque URL déconnecté puis connecté avec cookies séparés, comparer corps et headers | `SEC-CL-05-private-cache.json` | 0/1/2 | PASS si 10/10 observations satisfont la politique et si le contenu de A n’apparaît jamais pour B; FAIL si une fuite ou directive publique existe; BLOCKED si une URL privée n’existe pas, si le cache réel est absent ou si l’environnement ne permet pas deux sessions. |

## 4. Import, idempotence, limites et rollback

| ID | Cible chiffrée | Commande/procédure | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---|---:|---|
| `IMP-CL-01` | 2/2 uploads multipart réels atteignent le handler; 0 fichier hors répertoire autorisé | Envoyer un pack valide puis un pack avec nom/chemin contrôlé; vérifier job et répertoire | `IMP-CL-01-multipart.json` | 0/1/2 | PASS si les deux uploads sont tracés et confinés; FAIL si un chemin arbitraire est accepté; BLOCKED si l’interface multipart n’est pas disponible. |
| `IMP-CL-02` | Tester exactement 5 242 879 et 5 242 881 octets; le fichier supérieur est refusé; delta objets métier = 0 | Générer deux fichiers de frontière, uploader séparément, exécuter dry-run et contrôler les compteurs | `IMP-CL-02-size-limit.json` | 0/1/2 | PASS si les deux tailles sont mesurées exactement, la politique est appliquée et aucun objet n’est créé par le fichier supérieur; FAIL si la frontière est incorrecte; BLOCKED si PHP/webserver refuse de transporter les deux tailles avant PFC. |
| `IMP-CL-03` | 4/4 collisions traitées : legacy ID, e-mail, pseudo, doublon du pack; 0 doublon; 0 suppression préexistante | Préparer un objet existant par cas, exécuter dry-run puis execute selon la politique, comparer les compteurs | `IMP-CL-03-existing-duplicates.json` | 0/1/2 | PASS si chaque collision est matchée ou rejetée avec décision traçable; FAIL si un doublon ou une suppression apparaît; BLOCKED si les fixtures d’objets existants ne peuvent pas être isolées. |
| `IMP-CL-04` | Importer le même pack deux fois; premier passage conforme; second passage = 0 nouvelle création et 4 correspondances | Exécuter dry-run/execute deux fois sur le même pack et comparer les IDs | `IMP-CL-04-idempotence.json` | 0/1/2 | PASS si le second passage crée 0 objet et valide 4 matches; FAIL si un doublon ou une mutation inattendue apparaît; BLOCKED si le pack ou la base de référence manque. |
| `IMP-CL-05` | Injecter une interruption après exactement 2 objets; rollback en 1 commande; 0 objet marqué restant; 0 orphelin | Activer uniquement le seam `PFC_LOCAL_TESTING`, exécuter import, constater `failed`, puis `rollback_import` | `IMP-CL-05-interruption.json` | 0/1/2 | PASS si la panne est après 2 objets, le job devient `rolled_back` et les objets marqués sont à zéro; FAIL si le seam est actif hors test, si le compte est différent ou si un orphelin reste; BLOCKED si aucun seam sûr et documenté ne peut être ajouté. |
| `IMP-CL-06` | Un job `needs_fix` reste `needs_fix`; 0 création et 0 mutation interdite | Soumettre un pack invalide, exécuter dry-run puis tenter execute | `IMP-CL-06-needs-fix-execute.json` | 0/1/2 | PASS si l’exécution est refusée sans écriture; FAIL si un objet est créé ou si le statut change illicitement; BLOCKED si les erreurs de validation ne sont pas observables. |

## 5. Modération, communauté et intégrité des données

| ID | Cible chiffrée | Commande/procédure | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---|---:|---|
| `MOD-CL-01` | 3/3 transitions publish, reject, trash conformes; 0 état interdit | Créer trois contenus synthétiques et appliquer chaque transition avec les rôles prévus | `MOD-CL-01-decisions.json` | 0/1/2 | PASS si les trois états finaux et journaux correspondent à la politique; FAIL si un rôle contourne la modération; BLOCKED si bbPress n’est pas actif dans l’instance. |
| `MOD-CL-02` | 15/15 assertions keymaster/modérateur/admin conformes | Exécuter les capacités sur forums, sujets, réponses, utilisateurs et import | `MOD-CL-02-capabilities.json` | 0/1/2 | PASS si les 15 assertions sont vraies; FAIL à la première capacité incorrecte; BLOCKED si les rôles bbPress ne sont pas provisionnables. |
| `COM-CL-01` | 8 transitions de vote sujet/réponse; retour exact à l’état initial | Voter, retirer le vote, changer de cible et rejouer la séquence avec deux utilisateurs | `COM-CL-01-votes.json` | 0/1/2 | PASS si les compteurs et relations reviennent à l’état initial; FAIL si double vote, compteur négatif ou accès croisé; BLOCKED si l’endpoint communautaire ne répond pas. |
| `COM-CL-02` | Follow on/off, notification unique, mark-read; 0 doublon après 2 appels identiques | Suivre, répondre, vérifier une notification, répéter, désabonner et marquer lu | `COM-CL-02-follow-notifications.json` | 0/1/2 | PASS si une seule notification est produite et si l’état final est correct; FAIL si doublon ou lecture croisée; BLOCKED si la concurrence simultanée exigée ne peut pas être testée, en conservant alors ce sous-cas séparément BLOCKED. |
| `COM-CL-03` | 0 orphelin dans votes, follows et notifications après suppression des objets référencés | Supprimer les contenus/utilisateurs synthétiques puis contrôler les trois tables | `COM-CL-03-orphans.json` | 0/1/2 | PASS si les trois requêtes d’orphelins retournent 0; FAIL si une relation morte reste; BLOCKED si la suppression isolée ne peut pas être exécutée sans toucher des données réelles. |

## 6. Dépendances et politique de désinstallation

| ID | Cible chiffrée | Commande/procédure | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---|---:|---|
| `DEP-CL-01` | 3 routes sans PFC testées; 0 fatal, 0 erreur PHP non gérée | Désactiver PFC dans une copie et charger les trois routes publiques | `DEP-CL-01-02-current.json` | 0/1/2 | PASS si les trois pages répondent sans fatal; FAIL si une dépendance obligatoire est appelée sans garde; BLOCKED si la copie ne permet pas de désactiver PFC. |
| `DEP-CL-02` | 3 routes sans bbPress testées; 0 fatal, 0 erreur PHP non gérée | Désactiver bbPress dans une copie et charger les trois routes publiques | `DEP-CL-01-02-current.json` | 0/1/2 | PASS si les trois pages répondent et si les fonctions bbPress sont protégées; FAIL sinon; BLOCKED si bbPress ne peut pas être désactivé isolément. |
| `DEP-CL-03` | 1 politique écrite; 1 exécution uninstall; 3 options techniques supprimées; 0 table et 0 contenu métier supprimé | Installer le plugin dans une copie, exécuter `uninstall.php`, comparer tables, posts, users et options avant/après | `DEP-CL-03-uninstall.json` | 0/1/2 | PASS si la politique et le test concordent; FAIL si une donnée métier disparaît ou si la mécanique contredit la documentation; BLOCKED si une copie isolée n’est pas disponible. |

## 7. Accessibilité, responsive et SEO/RTL

| ID | Cible chiffrée | Commande/procédure | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---|---:|---|
| `A11Y-CL-01` | Axe-core : 6/6 pages à 0 violation; Pa11y : 2/2 pages à 0 erreur bloquante; SEO : 5/5 assertions; RTL : 3/3 assertions | Exécuter axe-core, Pa11y et le scanner HTML/RTL sur les URLs déterminées | `A11Y-CL-01-axe.json`, `A11Y-CL-01-pa11y-summary.json`, `SEO-01-05.json`, `SEO-CL-02-rtl.json` | 0/1/2 | PASS si les 16 sous-assertions sont conformes; FAIL si une violation, erreur SEO ou anomalie RTL est confirmée; BLOCKED si un outil ou une URL nécessaire est indisponible. |
| `A11Y-CL-02` | Parcours clavier sur 5 pages; `/forums/` doit être exécutée séparément et retourner PASS, FAIL ou BLOCKED justifié; 0 focus perdu sur les pages exécutées | Playwright : Tab, Shift+Tab, Enter, Escape, ouverture/fermeture des composants; conserver trace et screenshot au timeout | `A11Y-CL-02-keyboard.json` | 0/1/2 | PASS si toutes les pages, dont `/forums/`, terminent sans anomalie; FAIL si le parcours s’exécute et révèle un défaut; BLOCKED si timeout Chromium persiste après diagnostic et trace complète. |
| `A11Y-CL-03` | 8/8 états responsive; overflow horizontal = 0; contenu essentiel visible | Tester largeurs et hauteurs définies dans le CDC, capturer métriques `scrollWidth <= clientWidth` | `A11Y-CL-03-responsive-normal.json` | 0/1/2 | PASS si 8/8 états sont conformes; FAIL dès qu’un overflow ou élément inaccessible apparaît; BLOCKED si le navigateur ne permet pas la capture fiable. |
| `A11Y-CL-04` | Zoom navigateur réel à 200 % sur 5 pages; 0 perte de contenu ou fonctionnalité essentielle | Recette humaine avec zoom système/navigateur et captures annotées | `A11Y-CL-04-zoom200.json` | 0/1/2 | PASS avec checklist signée et 5/5 pages conformes; FAIL si un défaut est observé; BLOCKED_HUMAN, exit 2, si aucun testeur ou poste réel n’est disponible. |
| `A11Y-CL-05` | 1 recette humaine complète couvrant clavier, zoom, lecteur d’écran et mobile; 0 défaut critique | Parcours par un testeur représentatif, checklist et anomalies numérotées | `A11Y-CL-05-manual-checklist.md` | 0/1/2 | PASS si la checklist est complète et sans défaut critique; FAIL si un défaut critique est confirmé; BLOCKED_HUMAN, exit 2, si la recette humaine n’est pas disponible. |

## 8. Performance locale et exploitation

| ID | Cible chiffrée | Commande/procédure | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---|---:|---|
| `PERF-CL-01` | 80 mesures : 40 anonymes + 40 connectées; aucune mesure manquante | Exécuter le runner HTTP avec warm-up documenté et sauvegarder chaque observation | `PERF-01-summary.json` et `PERF-01-local.csv` | 0/1/2 | PASS si 80/80 mesures existent et sont horodatées; FAIL si une mesure est perdue ou incohérente; BLOCKED si deux sessions isolées ne sont pas disponibles. |
| `PERF-CL-02` | 16 groupes sous les seuils locaux; TTFB < 250 ms pour chaque groupe | Agréger p50/p95 par route et état de session | `PERF-02-thresholds.json` | 0/1/2 | PASS si les 16 groupes sont sous les seuils; FAIL dès qu’un seuil est dépassé; BLOCKED si la mesure est polluée par une charge externe non contrôlable. |
| `PERF-CL-03` | 1 déclaration de périmètre; 0 chiffre local présenté comme SLA production | Vérifier le rapport et les métadonnées de mesure | `PERF-03-scope.json` | 0/1/2 | PASS si le rapport sépare explicitement local, CDN et terrain; FAIL si une extrapolation production est faite; BLOCKED si l’environnement de mesure n’est pas identifiable. |
| `OPS-CL-01` | 1 cron de test; 11/11 événements dus exécutés; 0 exécution réelle d’e-mail | Déclencher le runner cron en mode sandbox et vérifier les événements | `OPS-CL-01-cron.json` | 0/1/2 | PASS si 11/11 événements sont traités sans e-mail réel; FAIL si un événement manque ou produit un effet réel; BLOCKED si le cron hébergeur réel est requis. |
| `OPS-CL-02` | Runbook de 8/8 étapes avec précondition, commande, résultat et rollback | Revue documentaire et exécution en copie | `OPS-CL-02-runbook.json` | 0/1/2 | PASS si les 8 étapes sont présentes et testables; FAIL si une étape est ambiguë ou inexécutable; BLOCKED si un secret ou accès externe est indispensable. |
| `OPS-CL-03` | 1 rollback de code vers la version précédente puis retour RC4, dans une copie propre; 0 perte de données synthétiques | Cloner/restaurer deux versions et exécuter la procédure de rollback | `OPS-CL-03-code-rollback.json` | 0/1/2 | PASS si les deux transitions sont vérifiées et réversibles; FAIL si une version ne démarre pas ou si des données disparaissent; BLOCKED si aucune copie propre n’est disponible. |
| `OPS-CL-04` | 1 backup, 1 restauration isolée, 1 vérification post-restore; 0 erreur; même SHA rattaché | Exporter le laboratoire final, restaurer dans une seconde instance, contrôler versions, plugin, thème, users, forums, topics, replies, réglages et accès public | `OPS-CL-04-restore-current.json` | 0/1/2 | PASS si toutes les vérifications sont conformes et la preuve porte le SHA final; FAIL à toute divergence; BLOCKED si une seconde instance isolée ne peut pas être créée. |
| `OPS-CL-05` | 1 mesure RPO et 1 mesure RTO; temps backup, restore et vérification strictement positifs | Utiliser le backup/restore de `OPS-CL-04` avec horloge monotone | `OPS-CL-05-rpo-rto-current.json` | 0/1/2 | PASS si les deux mesures sont complètes, positives et rattachées au même SHA; FAIL si une mesure manque ou est incohérente; BLOCKED si l’instance de restauration n’est pas disponible. |
| `OPS-CL-06` | 8 compteurs de résidus à zéro : users, posts, jobs, items, votes, follows, notifications, répertoires temporaires | Exécuter `php tools/final_cleanup_rc4.php` puis contrôler les compteurs | `OPS-CL-06-cleanup.json` | 0/4/2 | PASS exit 0 si les 8 compteurs valent 0; CLEANUP_FAIL exit 4 si au moins un résidu subsiste; BLOCKED exit 2 si la base ou les uploads ne sont pas accessibles. |
| `OPS-CL-07` | 191 fichiers scannés; 0 correspondance de secret ou donnée sensible | Scanner preuves, logs, archives et rapports avec motifs contrôlés | `OPS-CL-07-redaction.json` | 0/1/2 | PASS si 191/191 fichiers sont scannés et 0 correspondance; FAIL si un secret apparaît; BLOCKED si un fichier ne peut pas être inspecté. |

## 9. Gates de décision

| ID | Cible chiffrée | Commande/procédure | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---|---:|---|
| `GATE-CL-01` | 10/10 conditions locales de readiness satisfaites; 0 FAIL; 0 CLEANUP_FAIL | Exécuter le gate local après la non-régression et le nettoyage | `GATE-CL-01-local-readiness.json` | 0/1/2 | PASS si les 10 conditions sont vraies; FAIL si une condition testable échoue; BLOCKED si une condition ne peut pas être exécutée. |
| `GATE-CL-02` | 6/6 prérequis réels disponibles : Hostinger, CDN, cron hébergeur, IP réelle, rate-limit, e-mail/observabilité selon périmètre | Contrôle sur environnement réel autorisé, sans extrapoler le local | `GATE-CL-02-external.json` | 0/1/2 | PASS si 6/6 sont observés; FAIL si l’environnement réel est accessible mais incorrect; BLOCKED_EXTERNAL, exit 2, si l’accès réel est absent ou hors périmètre. |
| `GATE-CL-03` | 1 décision finale cohérente avec 40 lignes; aucun PASS sans preuve; 0 contradiction de statut | Générer la matrice finale et appliquer le gate | `GATE-CL-03-decision.md` | 0/1/2 | PASS si la décision suit mécaniquement la matrice; FAIL si la décision contredit une ligne; BLOCKED si la matrice ou les preuves sont incomplètes. |

## 10. Gate de release

Le **GO local** est autorisé uniquement lorsque tous les objectifs locaux corrigeables sont `PASS`, que les preuves sont rattachées au même SHA, que `GATE-CL-01=PASS`, que `OPS-CL-06=PASS`, que `OPS-CL-07=PASS` et qu’aucune preuve contradictoire n’existe. Les objectifs `BLOCKED_HUMAN` et `BLOCKED_EXTERNAL` ne doivent pas être convertis en PASS local.

Le **GO release** exige en plus la fermeture des contrôles humains et externes. Tant qu’un objectif local obligatoire est `BLOCKED`, ou tant qu’un contrôle humain/externe requis est indisponible, la décision reste **NO-GO**. Une absence d’environnement doit être rapportée comme `BLOCKED` avec exit 2, jamais comme réussite implicite.

Pour le rapport corrigé fourni, la matrice de référence est : **29 PASS, 8 BLOCKED local, 2 BLOCKED_HUMAN et 1 BLOCKED_EXTERNAL, soit 40 objectifs**. Les contrôles SEO et RTL sont des sous-assertions de `A11Y-CL-01`, et ne constituent pas deux identifiants supplémentaires. Après une nouvelle campagne, ces nombres doivent être recalculés automatiquement à partir des lignes et non saisis manuellement.

## Références internes

[1]: `matrice-cdc-complementaire-rc4-finale.csv` — matrice source des 40 objectifs.
[2]: `rapport-7-blocked-et-plan-go-rc4.md` — détail des blocages et règles de fermeture.
[3]: `cdc-go-strictement-local-rc4.md` — définition du périmètre et du gate local.
