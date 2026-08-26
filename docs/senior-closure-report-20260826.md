# Bilan senior de préproduction — Atelier

**Date :** 26 août 2026  
**Auteur :** Manus AI  
**Décision :** **préproduction consolidée, mais ouverture publique refusée à ce stade.**

## Conclusion exécutive

Le staging Atelier dispose désormais d’un socle opérationnel nettement plus robuste : release finale **Atelier 0.4.31** et **Premium Forum Core 0.4.16**, parcours d’inscription de recette, séparation des permissions, import réversible, protections HTTP, nettoyage des composants inactifs et sauvegardes locales de clôture sont prouvés. La copie publique GitHub a été assainie, validée puis synchronisée au commit `70d76f8`.

Cette avancée ne doit toutefois pas être confondue avec une autorisation de mise en production. La restauration sur environnement isolé, une copie de sauvegarde distante, la continuité cron côté hébergeur, le domaine final, la délivrabilité transactionnelle et une mesure représentative après stabilité de l’hébergement restent des prérequis impératifs.

> Les sauvegardes WordPress doivent couvrir **les fichiers et la base de données**; WordPress recommande en outre de conserver plusieurs copies dans des emplacements différents.[1]

| Niveau | Décision | Signification opérationnelle |
|---|---|---|
| **Fermé** | Risque traité et preuve enregistrée. | La recette peut s’appuyer sur ce mécanisme. |
| **Partiellement fermé** | Comportement démontré, mais continuité ou conditions production non prouvées. | La fonctionnalité reste sous surveillance. |
| **Bloqué externe** | Dépend d’un accès hôte, d’un domaine, d’un fournisseur ou de testeurs. | Aucun contournement artificiel ne doit être appliqué. |

## Risques fermés avec preuve

| Domaine | État | Preuve obtenue |
|---|---|---|
| Inscription de recette | Fermé sur staging | Envoi, renvoi, reprise de vérification, confirmation, expiration de code invalide et nettoyage d’un compte synthétique ont été validés avec Mailtrap Email Sandbox. |
| Permissions et modération | Fermé sur staging | Un membre peut proposer du contenu en attente sans exposition publique; un modérateur non administrateur peut consulter la file et supprimer le contenu en attente; les comptes de recette ont ensuite été supprimés. |
| Import et retour arrière | Fermé sur staging | Dry run sans écriture, import journalisé puis rollback immédiat d’un pack synthétique : seuls les objets créés par le job ont été retirés. |
| Surface XML-RPC et mots de passe d’application | Fermé dans PFC 0.4.16 | XML-RPC est explicitement refusé; les mots de passe d’application sont indisponibles. |
| En-têtes HTTP | Fermé sur les contextes contrôlés | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` et `Permissions-Policy` sont présents sur le public et la connexion. |
| Cache et sessions | Fermé sur les routes contrôlées | Lectures publiques cacheables avec durée courte; connexion et session privées, sans cache. |
| Accessibilité ciblée | Fermé pour les défauts prouvés | Contrastes, noms accessibles, recherche et fermeture Échap des suggestions ont été corrigés dans Atelier 0.4.31. |
| Nettoyage WordPress | Fermé | Les extensions inactives sont absentes; les thèmes historiques ont été retirés; seul Atelier actif et Twenty Twenty-Five de secours subsistent. |
| Sauvegarde locale de clôture | Fermé localement | Deux jeux complets UpdraftPlus existent, dont un créé après nettoyage avec base et fichiers inclus. |
| Relais public | Fermé | Code, fixtures synthétiques, documentation assainie et deux archives finales ont passé les scans connus; le dépôt public est synchronisé. |

## Risques partiellement fermés

| Domaine | Situation observée | Décision attendue |
|---|---|---|
| Performance | Une baseline Lighthouse de laboratoire couvre cinq pages, en mobile et bureau. Les scores performance vont de 82 à 95 en mobile et de 65 à 75 en bureau; le CLS synthétique reste entre 0 et 0,02. | Ne pas annoncer un respect des Core Web Vitals : mesurer LCP, INP et CLS sur une infrastructure stable et représentative, puis ajouter une mesure terrain. [2] |
| Accessibilité globale | Les défauts automatisés exploitables ont été corrigés et le clavier de recherche a été rejoué. | Réaliser une recette mobile complète, les erreurs de formulaires, le RTL et un test avec des personnes non techniques, dont au moins une personne âgée. |
| Planification | Santé du site ne signale plus le retard Action Scheduler et aucune tâche échouée n’était visible. | Vérifier le cron serveur dans l’hébergement et contrôler l’exécution sous trafic nul sur au moins 24 heures. WP-Cron est déclenché par des chargements de page, non en continu.[3] |
| Sauvegarde | Les jeux locaux complets existent. | Configurer une destination distante, planifier les sauvegardes après validation cron et démontrer une restauration isolée. |
| Stabilité réseau | L’audit a rencontré une fenêtre intermittente d’erreurs HTTP/2 et TLS, après laquelle le staging est redevenu disponible. | Obtenir un diagnostic de l’hébergeur avant d’interpréter toute contre-mesure de performance comme définitive. |

## Risques bloqués par des décisions externes

| Blocage | Pourquoi il ne doit pas être contourné | Prochaine étape sûre |
|---|---|---|
| Domaine final | Le staging temporaire ne peut pas représenter l’identité publique Atelier. | Acheter ou relier le domaine, vérifier le certificat et choisir la canonique. |
| HSTS | HSTS est une politique de domaine et d’hébergement; l’ajouter au thème serait trompeur. | Activer seulement après HTTPS stable sur le domaine final, avec stratégie de sous-domaines validée. |
| E-mail production | Mailtrap Sandbox capture les e-mails de recette et ne démontre pas la réception par de vraies boîtes. | Choisir le fournisseur transactionnel, authentifier SPF/DKIM/DMARC et tester Gmail, Outlook et un domaine tiers. [4] |
| Stockage distant | Une archive stockée seulement sur le serveur ne protège pas contre une perte hôte. | Fournir ou choisir le stockage distant, puis valider un envoi et une restauration. |
| Hôte / cron | Le panneau de l’hébergeur n’était pas accessible pendant la recette. | Vérifier la tâche cron, les journaux, les sauvegardes hôte et les erreurs HTTP/2/TLS depuis le panneau. |

## Gardes de lancement obligatoires

L’ouverture doit suivre une séquence stricte. Les quatre premières étapes sont des **portes bloquantes**, non des améliorations facultatives.

1. **Sauvegarde et restauration.** Configurer une copie distante, créer un point complet, restaurer dans un WordPress isolé et vérifier pages, permaliens, connexion, bbPress et PFC. WordPress précise qu’une restauration complète requiert les fichiers et la base, et recommande de garder ces composants ensemble.[1]
2. **Hébergement.** Diagnostiquer la fenêtre HTTP/2/TLS, vérifier un cron serveur et confirmer l’absence de tâche due ou échouée à J+1.
3. **Domaine et messagerie.** Relier le domaine final, sécuriser HTTPS, définir HSTS côté hôte après audit, configurer l’expéditeur transactionnel et authentifier le domaine. Le `noindex, nofollow` reste intact sur le staging.
4. **Recette représentative.** Rejouer performance mobile/bureau, accessibilité clavier/mobile/RTL, inscription réelle contrôlée, modération, import/rollback et cache après purge.
5. **Bêta fermée.** Ouvrir à un groupe réduit, avec journal d’incidents, modération disponible et fenêtre d’observation d’une à deux semaines.
6. **Ouverture publique.** Après décision formelle, retirer `noindex` uniquement sur le domaine final, soumettre sitemap/Search Console et surveiller erreurs, e-mails et indexation.

Le balisage structuré reste volontairement prudent : aucun `QAPage` ni `acceptedAnswer` n’est émis tant qu’un workflow réel d’acceptation n’existe pas. Google réserve ce balisage aux pages centrées sur une question et ses réponses, pas à toute discussion de forum.[5]

## Référence du relais public

Le relais public ne comprend que le thème, le plugin, des fixtures `example.test`, des documents aseptisés et les archives `Atelier 0.4.31` et `PFC 0.4.16`. Aucun URL privé, token, mot de passe, cookie, sauvegarde, base SQL ou capture d’administration n’y est inclus. La référence actuelle est le commit public `70d76f8` : `Consolidate PFC 0.4.16 and Atelier 0.4.31 preproduction hardening`.

## Références

[1]: https://developer.wordpress.org/advanced-administration/security/backup/ "WordPress — Backups"
[2]: https://developers.google.com/search/docs/appearance/core-web-vitals "Google Search Central — Core Web Vitals"
[3]: https://developer.wordpress.org/plugins/cron/ "WordPress Developer Resources — Cron"
[4]: https://mailtrap.io/email-sandbox/ "Mailtrap — Email Sandbox"
[5]: https://developers.google.com/search/docs/appearance/structured-data/qapage "Google Search Central — QAPage structured data"
