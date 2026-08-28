# Atelier RC4 — statut de validation local

**Périmètre :** laboratoire local uniquement. Les e-mails réels, Hostinger, CDN, cron hébergeur et recette humaine ne sont pas validés par ce document.

## Décision consolidée

La matrice finale contient 40 objectifs : **31 PASS**, **6 BLOCKED**, **2 BLOCKED_HUMAN** et **1 BLOCKED_EXTERNAL**. Le statut de la campagne reste **NO-GO**, car les preuves locales manquantes ne peuvent pas être converties en validations par simple présence de code.

## Correctifs implémentés

Le lot ajoute une politique PFC d’uninstall non destructive : trois options techniques sont retirées, tandis que les tables et données métier sont conservées. Il ajoute également un seam d’interruption strictement protégé par `PFC_LOCAL_TESTING === true`, inutilisable par défaut en production, ainsi qu’un harnais qui exerce le vrai chemin dry-run → execute → rollback. Enfin, le provisioning idempotent crée les quatre pages éditoriales absentes, dont `mon-espace`, sans remplacer une page existante.

## Preuves obtenues

| Objectif | Statut | Cible | Preuve | Exit |
|---|---|---|---|---:|
| `DEP-CL-03` | PASS | politique écrite, 3 options retirées, tables et contenus conservés | `DEP-CL-03-uninstall.json` | 0 |
| `IMP-CL-05` | PASS | interruption après exactement 2 objets, job `rolled_back`, 0 objet marqué restant | `IMP-CL-05-import-interruption.json` | 0 |
| `SEC-CL-05` — route | PASS partiel | provisioning idempotent de 4 pages, `mon-espace` résolu | `SEC-CL-05-mon-espace-provisioning.json` | 0 |

Le PASS de provisioning ne vaut pas encore PASS complet du cache privé cross-user : les dix observations HTTP sur cinq URLs privées avec deux utilisateurs doivent encore être capturées si cet objectif reste applicable.

## Blocages conservés

Le statut global reste **NO-GO** tant que les objectifs encore absents de preuves ne sont pas rejoués. La matrice générée est rattachée au SHA final publié et conserve les statuts `BLOCKED`, `BLOCKED_HUMAN` et `BLOCKED_EXTERNAL` séparément. Les points non convertis artificiellement sont `IMP-CL-02`, `IMP-CL-03`, `OPS-CL-04`, `OPS-CL-05`, le cache privé cross-user complet, les prérequis humains d’accessibilité et le contrôle externe. Un timeout de harnais ou une absence d’environnement ne doit pas être requalifié en PASS.

## Nettoyage

Le job d’interruption a été rollbacké et les objets marqués par le job sont à zéro. Le nettoyage final a ensuite retourné `OPS-CL-06=PASS` avec tous les compteurs de résidus et répertoires temporaires à zéro. Les preuves ne contiennent pas de cookies ni de secrets.
