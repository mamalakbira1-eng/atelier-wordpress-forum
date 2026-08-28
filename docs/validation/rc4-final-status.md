# Atelier RC4 — statut de validation local

**Périmètre :** laboratoire local uniquement. Les e-mails réels, Hostinger, CDN, cron hébergeur et recette humaine ne sont pas validés par ce document.

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

Le statut global reste **NO-GO** tant que la matrice maître n’est pas régénérée sur le commit final et que les objectifs encore absents de preuves ne sont pas rejoués. Les points non convertis artificiellement sont `IMP-CL-02`, `IMP-CL-03`, `OPS-CL-04`, `OPS-CL-05`, les prérequis humains d’accessibilité et le contrôle externe. Un timeout de harnais ou une absence d’environnement ne doit pas être requalifié en PASS.

## Nettoyage

Le job d’interruption a été rollbacké et les objets marqués par le job sont à zéro. Le compte administrateur et les pages synthétiques utilisés par le laboratoire doivent être supprimés par le script de nettoyage final avant archivage de l’instance. Les preuves ne contiennent pas de cookies ni de secrets.
