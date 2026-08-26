# Runbook de préproduction — Atelier

**Date :** 26 août 2026  
**Périmètre :** staging WordPress dédié à la recette  
**Statut :** procédure opérationnelle préparée; restauration isolée et stockage distant non encore prouvés.

## Objet et règle de décision

Ce document encadre les opérations nécessaires avant une bêta fermée, puis avant toute ouverture publique. Il ne contient ni URL d’administration, ni identifiant, ni jeton, ni sauvegarde, ni donnée personnelle. Il ne doit pas être interprété comme une autorisation de retirer le `noindex, nofollow` du staging.

> Une sauvegarde de préproduction n’est considérée comme fiable que si elle contient à la fois les fichiers et la base, si elle existe hors du serveur principal, et si une restauration est démontrée dans un environnement isolé. WordPress rappelle que les deux ensembles sont nécessaires et recommande de les traiter comme un même jeu de sauvegarde.[1]

| Élément | État actuel | Condition de fermeture |
|---|---|---|
| Sauvegarde complète locale | Deux jeux complets UpdraftPlus réussis : base, extensions, thèmes, téléversements, MU-plugins et autres fichiers; le second suit le nettoyage de clôture. | Conserver le point de clôture jusqu’à la prochaine sauvegarde validée. |
| Copie distante | Absente; l’interface UpdraftPlus n’affiche qu’un jeu local. | Configurer un stockage indépendant du serveur et vérifier un envoi réussi. |
| Restauration | Non testée. | Restaurer le jeu complet dans un WordPress isolé et consigner le résultat. |
| Planification | Santé du site ne signale plus de retard Action Scheduler, mais UpdraftPlus indique qu’aucune sauvegarde fichiers ou base n’est planifiée. | Configurer un planning seulement après validation du stockage distant et de la continuité cron. |
| E-mail | Mailtrap Email Sandbox valide le parcours de recette. | Basculer vers un expéditeur transactionnel et un domaine final authentifié avant le public. |
| Indexation | Staging volontairement non indexable. | Ne lever le blocage qu’après validation domaine, HTTPS, canonique, redirections et Search Console. |

## Procédure de sauvegarde de clôture

Avant toute opération à risque, mise à jour majeure ou ouverture de bêta, l’opérateur crée une sauvegarde complète et vérifie dans l’interface UpdraftPlus que les composants **base de données**, **extensions**, **thèmes**, **téléversements** et **autres fichiers** sont présents. Les fichiers et la base doivent rester associés au même point temporel; WordPress recommande de les conserver ensemble pour garantir une restauration cohérente.[1]

La politique cible après disponibilité du stockage distant est la suivante : une sauvegarde base quotidienne, une sauvegarde fichiers hebdomadaire, une rétention d’au moins cinq jeux complets, avec une copie locale courte et une copie distante chiffrée. La fréquence devra être revue si la communauté devient active; WordPress recommande une fréquence qui suit le niveau d’activité et plusieurs copies récentes dans des emplacements différents.[1]

| Contrôle après sauvegarde | Preuve attendue | Décision si échec |
|---|---|---|
| Fin de tâche | État de réussite sans erreur dans UpdraftPlus. | Ne pas poursuivre l’opération à risque; corriger puis relancer. |
| Intégrité | Tous les composants attendus sont listés. | Considérer le point incomplet et produire un nouveau jeu. |
| Copie distante | Objet visible dans le stockage indépendant. | Laisser le risque ouvert; ne pas déclarer la sauvegarde de production prête. |
| Journal | Date, auteur, raison et identifiant interne du point consignés sans lien ni secret. | Documenter immédiatement. |

## Restauration testée dans un environnement isolé

La restauration ne sera **pas** exécutée sur le staging actif tant qu’un environnement isolé est disponible. Pour ne pas exfiltrer de données, le jeu de sauvegarde reste dans le périmètre d’hébergement administré et n’est ni téléchargé dans le sandbox ni copié dans le dépôt public.

| Étape | Action contrôlée | Critère d’acceptation |
|---|---|---|
| 1. Isoler | Créer un WordPress vide avec base distincte, accès restreint et indexation désactivée. | Aucun trafic ou compte de production ne peut atteindre l’instance. |
| 2. Préparer | Installer la même version majeure de WordPress et UpdraftPlus, puis rendre le jeu de sauvegarde accessible uniquement à l’opérateur. | L’outil voit le jeu complet sans exposition publique. |
| 3. Restaurer | Restaurer les fichiers puis la base via l’assistant; cet ordre suit la recommandation WordPress.[1] | L’assistant se termine sans erreur et les composants attendus sont présents. |
| 4. Vérifier | Contrôler accueil, forums, une discussion, connexion, administration, PFC, bbPress, permaliens et absence d’erreur PHP visible. | Toutes les pages de recette attendues répondent et aucun secret n’est exposé. |
| 5. Mesurer | Noter début, fin, volume, écart et incidents; en déduire un objectif réaliste de délai de reprise. | Le délai de reprise et la perte maximale acceptable sont explicités. |
| 6. Nettoyer | Détruire l’instance isolée ou la réinitialiser; conserver seulement la preuve de test non sensible. | Aucun clone abandonné n’est publiquement accessible. |

UpdraftPlus décrit une restauration depuis le jeu de sauvegarde via son assistant; elle doit être surveillée jusqu’à son achèvement plutôt que supposée réussie.[2]

## Surveillance de WP-Cron et Action Scheduler

WP-Cron exécute les tâches dues lors des chargements de page; il ne s’exécute pas constamment comme un cron système. Un site peu visité ou fortement mis en cache peut donc retarder des tâches planifiées.[3] L’alerte visible précédemment pour `action_scheduler_run_queue` ne remonte plus dans Santé du site, mais la continuité du mécanisme reste à observer.

Tant que l’hébergement n’est pas accessible, aucune constante `DISABLE_WP_CRON` et aucune planification hôte ne doit être ajoutée à l’aveugle. Dès accès au panneau de l’hébergeur, l’opérateur devra :

1. vérifier si une tâche cron serveur existe déjà et à quelle fréquence elle appelle WordPress;
2. si aucune tâche n’existe, ajouter une exécution sécurisée de `wp-cron.php` toutes les cinq minutes selon la méthode documentée par l’hébergeur, sans publier l’URL dans un document public;
3. seulement après validation, désactiver le déclenchement web de WP-Cron si cette stratégie est choisie;
4. contrôler à J+1 les tâches Action Scheduler, les rapports UpdraftPlus, les journaux e-mail et Santé du site;
5. conserver une alerte opérationnelle si une tâche due ou échouée réapparaît.

## Procédure d’incident de préproduction

| Gravité | Exemple | Première action | Décision de reprise |
|---|---|---|---|
| P1 | Site indisponible, erreur PHP publique, fuite de contenu ou contournement d’accès. | Mettre l’accès public en pause selon la procédure hôte, préserver les journaux et créer un point de sauvegarde si possible. | Restaurer uniquement après diagnostic et validation de la cause. |
| P2 | Inscription, modération, import ou e-mail de recette indisponible. | Bloquer le parcours affecté, relever l’heure et la dernière modification, reproduire avec données synthétiques. | Corriger sur staging, exécuter la recette ciblée puis la non-régression minimale. |
| P3 | Régression visuelle, performance ou accessibilité sans perte de données. | Isoler la page, vérifier cache et dernière release, documenter la mesure. | Corriger uniquement avec une preuve de régression, puis recontrôler. |

Un retour arrière n’est autorisé que depuis un point de sauvegarde identifié et après une note d’incident contenant la décision, l’heure, le périmètre et la validation post-restauration. Il ne doit jamais dépendre d’une archive locale non vérifiée.

## Checklist de domaine final et bêta fermée

L’ouverture reste bloquée tant que les éléments suivants ne sont pas validés. Les paramètres DNS et l’envoi réel ne doivent pas être simulés sur le domaine temporaire de staging.

| Domaine de contrôle | Avant bêta fermée | Avant ouverture publique |
|---|---|---|
| Domaine et HTTPS | Domaine final lié, certificat valide, HTTP vers HTTPS, canonique et HSTS définis au niveau hôte après audit. | Contrôle de redirection et absence de contenu mixte. |
| E-mail transactionnel | Fournisseur de production configuré hors Git; expéditeur sur le domaine final. | SPF, DKIM et DMARC publiés; réception vérifiée auprès de plusieurs fournisseurs. |
| Sauvegarde | Copie distante, planning validé et restauration isolée testée. | Revue de la rétention et exercice de restauration récent. |
| Recherche | Sitemap, robots et canonique préparés; staging toujours `noindex`. | Search Console configurée; retrait contrôlé de `noindex` uniquement sur le domaine final. |
| Sécurité | Comptes à privilège limités, plugins/thèmes inactifs absents, mises à jour contrôlées. | HSTS et surveillance hôte confirmés; procédure incident accessible à l’équipe. |
| Bêta | Groupe fermé, modération disponible, suivi des erreurs et des e-mails. | Deux semaines sans anomalie critique ou décision explicite documentée. |

## Références

[1]: https://developer.wordpress.org/advanced-administration/security/backup/ "WordPress — Backups"
[2]: https://teamupdraft.com/documentation/updraftplus/topics/restoration/faqs/how-to-restore-a-wordpress-site-with-updraftplus/ "UpdraftPlus — How do I restore my site?"
[3]: https://developer.wordpress.org/plugins/cron/ "WordPress Developer Resources — Cron"
