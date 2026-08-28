# RC4 — Objectifs senior sur WordPress réel

## Règle de décision

Chaque objectif est évalué sur le SHA de release indiqué dans la preuve. `0` signifie PASS, `1` signifie FAIL après exécution valide, `2` signifie BLOCKED lorsque l’environnement, l’accès, le matériel humain ou une configuration externe empêche une exécution valide. Un BLOCKED n’est jamais converti en PASS. Toute preuve doit contenir l’objectif, le SHA ou la version distante, l’horodatage UTC, les entrées, les mesures observées, la commande ou la procédure et l’exit code.

| ID | Objectif strictement mesurable | Preuve obligatoire | Exit | PASS / FAIL / BLOCKED |
|---|---|---|---:|---|
| `REAL-01` | Inventorier 100 % des versions WordPress, PHP, bbPress, PFC, thème et extensions actives ; 0 version inconnue | `REAL-01-environment.json` avec capture de l’administration et inventaire exporté | 0/1/2 | PASS si 6 catégories sont renseignées et cohérentes ; FAIL si une version diffère de la baseline sans justification ; BLOCKED si l’administration n’est pas accessible |
| `REAL-02` | Vérifier 3 états de cache (froid, après purge, chaud) sur 4 URLs ; 12/12 réponses collectées | `REAL-02-cache-states.json` avec headers, cookies, taille et hash des corps | 0/1/2 | PASS si 12 observations existent et respectent la politique ; FAIL à toute fuite ou directive incorrecte ; BLOCKED si le CDN/cache réel n’est pas observable |
| `REAL-03` | Comparer 5 URLs privées avec 2 sessions synthétiques ; 10/10 réponses privées/no-store et 0 octet cross-user | `SEC-CL-05-private-cache.json` ou preuve distante équivalente | 0/1/2 | PASS si aucune donnée de A n’apparaît pour B ; FAIL à toute fuite ; BLOCKED si deux sessions isolées ne sont pas disponibles |
| `REAL-04` | Rejouer 18 actions membre/modérateur/admin ; 18/18 décisions conformes, 0 mutation interdite | `REAL-04-role-matrix.json` avec requêtes, réponses et deltas DB | 0/1/2 | PASS si 18/18 correspondent à la politique ; FAIL à la première autorisation incorrecte ; BLOCKED si les comptes synthétiques ne sont pas provisionnables |
| `REAL-05` | Rejouer 8 URLs forgées et 4 requêtes nonce/capacité invalides ; 12/12 refus, delta métier 0 | `REAL-05-forgery.json` | 0/1/2 | PASS si tous les refus sont observés ; FAIL si une donnée est révélée ou écrite ; BLOCKED si les endpoints ne sont pas atteignables |
| `REAL-06` | Exécuter 1 import nominal, le même import une seconde fois et 1 pack collision ; seconde exécution = 0 création, pack collision = 0 doublon | `REAL-06-import-idempotence.json` avec compteurs avant/après et IDs | 0/1/2 | PASS si les compteurs et décisions correspondent à la politique ; FAIL à tout doublon ou suppression ; BLOCKED si la fixture ou la sauvegarde manque |
| `REAL-07` | Tester exactement 5 242 879 et 5 242 881 octets ; frontière basse transportée, frontière haute refusée, delta métier 0 | `REAL-07-size-boundary.json` | 0/1/2 | PASS si les tailles exactes et les réponses sont prouvées ; FAIL si la frontière est incorrecte ; BLOCKED si PHP/webserver rejette avant le handler |
| `REAL-08` | Provoquer 1 interruption contrôlée après 2 objets puis rollback ; 0 objet marqué restant, 0 orphelin | `REAL-08-interruption-rollback.json` et log d’injection | 0/1/2 | PASS si l’état final est `rolled_back` et les compteurs valent 0 ; FAIL si un orphelin subsiste ; BLOCKED si aucun seam sûr n’existe |
| `REAL-09` | Vérifier 3 transitions de modération et 3 suppressions contrôlées ; 6/6 états et journaux conformes | `REAL-09-moderation.json` | 0/1/2 | PASS si les six états finaux concordent ; FAIL si un rôle contourne la règle ; BLOCKED si une copie isolée n’est pas disponible |
| `REAL-10` | Observer 11/11 événements cron dus dans une fenêtre définie ; 0 e-mail réel et 11 traces d’exécution | `REAL-10-cron.json` avec horodatages et logs | 0/1/2 | PASS si 11/11 événements s’exécutent ; FAIL si un événement manque ou produit un effet inattendu ; BLOCKED si le cron hébergeur n’est pas accessible |
| `REAL-11` | Mesurer 3 requêtes réseau derrière CDN et 3 scénarios de rate-limit ; IP, code HTTP et seuils documentés pour 6/6 observations | `REAL-11-cdn-ratelimit.json` | 0/1/2 | PASS si la chaîne proxy et la limite sont démontrées ; FAIL si contournement ou mauvaise IP ; BLOCKED si les réglages CDN/pare-feu sont hors accès |
| `REAL-12` | Vérifier 5 pages à 200 % et 5 pages au clavier ; 0 contenu essentiel perdu, 0 focus invisible | `REAL-12-human-a11y.md` avec checklist signée et captures | 0/1/2 | PASS si 10/10 parcours sont conformes ; FAIL si un défaut critique est observé ; BLOCKED_HUMAN, exit 2, si aucun testeur ou poste réel n’est disponible |
| `REAL-13` | Vérifier 5 pages SEO/RTL ; 5/5 canoniques, JSON-LD, robots/noindex et direction RTL conformes | `REAL-13-seo-rtl.json` | 0/1/2 | PASS si 5/5 pages sont conformes ; FAIL à toute URL canonique ou directive incorrecte ; BLOCKED si la configuration staging n’est pas accessible |
| `REAL-14` | Mesurer 4 URLs avec cache froid et chaud, 20 échantillons par état ; 160 mesures horodatées, aucune extrapolée en SLA | `REAL-14-performance.csv` et résumé JSON | 0/1/2 | PASS si 160/160 mesures sont présentes et le périmètre est séparé ; FAIL si des mesures manquent ou sont présentées comme SLA ; BLOCKED si CDN/charge ne sont pas contrôlables |
| `REAL-15` | Restaurer 1 sauvegarde sur une instance isolée et vérifier 10 invariants : accès, thème, plugin, versions, users, forums, topics, replies, réglages, routes publiques | `REAL-15-restore.json` avec temps monotones et comparaison avant/après | 0/1/2 | PASS si 10/10 invariants concordent ; FAIL à toute perte ou divergence ; BLOCKED si seconde instance ou export complet absent |
| `REAL-16` | Effectuer 1 rollback distant vers la version précédente puis 1 retour RC4 ; 2 transitions, 0 perte de données, `/forums/` et sujet 202 HTTP 200 après chaque transition | `REAL-16-rollback.json` avec horodatages et captures | 0/1/2 | PASS si les deux transitions sont vérifiées ; FAIL si une version ne démarre pas ou si une donnée disparaît ; BLOCKED si aucun rollback contrôlé n’est autorisé |

## État d’exécution RC4 au 28 août 2026

Le thème Atelier **0.4.34** est actif dans l’administration WordPress de test. La page `/forums/` affiche les espaces bbPress. Le sujet `post=202` est rendu en RTL avec `font-size: 32px`, `line-height: 41.6px`, et charge `/wp-content/themes/atelier-rc4/style.css`.

Les objectifs locaux du CDC existant sont recalculés à **37 PASS, 2 BLOCKED_HUMAN et 1 BLOCKED_EXTERNAL sur 40**, avec décision mécanique `NO-GO`. Les objectifs `REAL-02`, `REAL-03`, `REAL-10`, `REAL-11`, `REAL-12`, `REAL-15` et `REAL-16` exigent encore des preuves dédiées sur l’infrastructure ou avec un testeur humain ; ils restent `BLOCKED` tant que ces preuves ne sont pas produites.

La correction distante ciblée est donc validée, mais la mise en service générale reste conditionnée à la fermeture des objectifs bloqués. Cette distinction évite de confondre une page publique fonctionnelle avec une recette complète d’exploitation.

## Références internes

- Branche : `fix/rc4-go-local`
- Archive : `release/atelier-0.4.34-rc4.zip`
- SHA de l’archive : `4f10580b0b052eab3eea90ebdd7db8f6cae73570693e861a170cb03e7d94a989`
- Matrice 40 objectifs : `docs/validation/rc4-matrix-final.json`
- CDC local détaillé : `docs/validation/cdc-rc4-objectifs-strictement-mesurables.md`
