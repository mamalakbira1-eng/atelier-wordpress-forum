# Rapport final de recette senior RC4 — Atelier

Date : 2026-08-28
Branche : `fix/rc4-go-local`
SHA GitHub courant au moment de la clôture : `989f70d7ca87574eab1b375dbe25c1588d02348a`
Site de test : https://springgreen-panther-771782.hostingersite.com/

## Décision exécutive

Le lot obtient **37 PASS sur 40 objectifs**, avec **2 BLOCKED_HUMAN** et **1 BLOCKED_EXTERNAL**. La décision calculée par le constructeur de matrice est **NO-GO global**, sans échec technique local masqué. Le déploiement ciblé du thème est néanmoins validé : la version 0.4.34 est active sur Hostinger et les deux défauts demandés sont corrigés.

> Règle appliquée : un objectif dont l’exécution est empêchée par un accès, un humain, un poste réel ou une configuration externe reçoit l’exit code `2` et le statut `BLOCKED`; il ne peut pas être transformé en PASS par interprétation.

## Résultats mesurés

| Contrôle | Cible | Observation | Exit | Statut |
|---|---:|---|---:|---|
| Matrice RC4 consolidée | 40 objectifs | 37 PASS, 2 BLOCKED_HUMAN, 1 BLOCKED_EXTERNAL | 0 pour le générateur | NO-GO |
| Lint PHP | 100 % des fichiers Atelier et runners RC4 | Tous les fichiers passent `php -l` | 0 | PASS |
| Validateur PFC | Pack valide sans erreur et pack invalide rejeté | 4/4 fichiers valides, 0 erreur ; pack invalide : 7 erreurs détectées | 0 | PASS |
| Archive RC4 | 1 archive et 1 hash | `atelier-0.4.34-rc4.zip`, SHA-256 `4f10580b0b052eab3eea90ebdd7db8f6cae73570693e861a170cb03e7d94a989` | 0 | PASS |
| Propreté Git | 0 fichier non committé | `git status --porcelain` vide | 0 | PASS |
| Version active WordPress | Atelier 0.4.34 | L’administration affiche `Version : 0.4.34`, état `Activé : Atelier` | 0 | PASS |
| Page forums | Index et espaces visibles | `/forums/` affiche `Tous les forums` et 7 espaces publics | 0 | PASS |
| Sujet arabe 202 | RTL et typographie réduite | `font-size: 32px`, `line-height: 41.6px`, `direction: rtl`, feuille `atelier-rc4/style.css` | 0 | PASS |

## Objectifs encore bloqués

| ID | Exit | Cause exacte | Condition de fermeture |
|---|---:|---|---|
| `A11Y-CL-04` | 2 | Le zoom navigateur réel à 200 % doit être réalisé par une recette humaine | Checklist signée sur 5 pages à 200 %, sans perte de contenu ni fonctionnalité |
| `A11Y-CL-05` | 2 | La recette humaine complète clavier, zoom, lecteur d’écran et mobile n’est pas disponible dans le laboratoire | Un testeur et un poste réel produisent la checklist et les captures demandées |
| `GATE-CL-02` | 2 | Les six prérequis d’exploitation réels ne sont pas tous observables dans la preuve courante : CDN, cron hébergeur, IP réelle, rate-limit et observabilité | Produire les 6 observations externes sur Hostinger/CDN, ou marquer chaque sous-contrôle séparément |

## Vérification distante ciblée

WordPress a accepté et installé l’archive `atelier-0.4.34-rc4.zip`. La carte des thèmes affiche **Atelier — Version 0.4.34 — Activé : Atelier**. La page `/forums/` n’est plus vide et affiche les espaces bbPress. Le sujet identifié par le lien d’administration `post=202` conserve son titre arabe avec direction RTL et mesure calculée `32px`.

Ces observations prouvent le déploiement et les deux corrections visibles. Elles ne prouvent pas à elles seules la restauration distante, le cron Hostinger, le comportement complet du CDN, le rate-limit réel, la performance production ou la recette humaine.

## Artefacts et traçabilité

Le CDC strictement mesurable est `cdc-rc4-objectifs-strictement-mesurables.md`. L’addendum des scénarios WordPress réel est `rc4-senior-real-wordpress-objectives-20260828.md`. La matrice calculée est `rc4-matrix-final.json`. L’archive et son hash sont dans `release/atelier-0.4.34-rc4.zip` et `release/atelier-0.4.34-rc4.sha256`. Les captures publiques sont conservées dans le dossier de preuves de la campagne.

## Conclusion

Un développeur senior clôturerait ce lot avec un **GO technique ciblé** pour la version du thème, l’archive et les corrections `/forums/` et RTL, mais maintiendrait un **NO-GO de mise en service générale** jusqu’à la fermeture des deux objectifs humains et du gate externe. Cette décision est cohérente avec les preuves disponibles et ne masque aucune limitation d’environnement.
