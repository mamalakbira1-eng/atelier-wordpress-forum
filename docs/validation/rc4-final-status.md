# Atelier RC4 — statut de validation local

**Périmètre :** laboratoire local uniquement. Les e-mails réels, Hostinger, CDN, cron hébergeur et recette humaine ne sont pas validés par ce document.

## Décision consolidée

La matrice finale contient 40 objectifs : **37 PASS**, **2 BLOCKED_HUMAN** et **1 BLOCKED_EXTERNAL**. Le statut de la campagne reste **NO-GO**, car les prérequis humains et externes ne peuvent pas être convertis en validations locales par simple présence de code.

## Correctifs implémentés

Le lot ajoute une politique PFC d’uninstall non destructive : trois options techniques sont retirées, tandis que les tables et données métier sont conservées. Il ajoute également un seam d’interruption strictement protégé par `PFC_LOCAL_TESTING === true`, inutilisable par défaut en production, ainsi qu’un harnais qui exerce le vrai chemin dry-run → execute → rollback. Enfin, le provisioning idempotent crée les quatre pages éditoriales absentes, dont `mon-espace`, sans remplacer une page existante.

## Preuves obtenues

| Objectif | Statut | Cible | Preuve | Exit |
|---|---|---|---|---:|
| `DEP-CL-03` | PASS | politique écrite, 3 options retirées, tables et contenus conservés | `DEP-CL-03-uninstall.json` | 0 |
| `IMP-CL-05` | PASS | interruption après exactement 2 objets, job `rolled_back`, 0 objet marqué restant | `IMP-CL-05-import-interruption.json` | 0 |
| `SEC-CL-05` — route/cache | PASS | provisioning idempotent de 4 pages et 10 observations HTTP cross-user sans fuite | `SEC-CL-05-mon-espace-provisioning.json`, `SEC-CL-05-private-cache.json` | 0 |
| `IMP-CL-02` | PASS | 5 Mo moins un accepté, 5 Mo plus un refusé, delta base nul | `IMP-CL-02-size-limit.json` | 0 |
| `IMP-CL-03` | PASS | collision sur objets existants : 4 correspondances, 0 création | `IMP-CL-03-existing-duplicates.json` | 0 |
| `A11Y-CL-02` | PASS | 6 pages × 20 Tab, zéro focus invisible et zéro tabindex positif | `A11Y-CL-02-keyboard.json` | 0 |
| `MOD-CL-02` | PASS | 15 assertions de capacités conformes | `MOD-CL-02-capabilities.json` | 0 |
| `OPS-CL-04` | PASS | restauration isolée, accueil/topic HTTP 200, versions vérifiées, instance détruite | `OPS-CL-04-restore-current.json` | 0 |
| `OPS-CL-05` | PASS | RPO 176 ms, RTO 7086 ms sur le même cycle | `OPS-CL-05-rpo-rto-current.json` | 0 |

## Blocages conservés

Le statut global reste **NO-GO** uniquement en raison de deux contrôles humains et d’un contrôle externe. Les objectifs techniques locaux d’import, de cache, d’accessibilité automatisée, de capacités et de restauration sont désormais rattachés à des preuves PASS. Les contrôles humains `A11Y-CL-04` et `A11Y-CL-05` ainsi que `GATE-CL-02` restent explicitement séparés. Un timeout de harnais ou une absence d’environnement ne doit pas être requalifié en PASS.

## Nettoyage

Le job d’interruption a été rollbacké et les objets marqués par le job sont à zéro. Le nettoyage final a ensuite retourné `OPS-CL-06=PASS` avec tous les compteurs de résidus et répertoires temporaires à zéro. Les preuves ne contiennent pas de cookies ni de secrets.
