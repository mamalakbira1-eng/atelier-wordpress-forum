# Atelier — registre des risques de pré-bêta

Ce registre est volontairement court et actionnable. Un risque ne peut être clos que par la preuve indiquée; un changement de statut est accompagné d’une date, d’un lien vers la preuve et d’un responsable technique désigné pour l’environnement concerné.

| ID | Risque | Niveau | Propriétaire attendu | Échéance | Preuve nécessaire pour clôture | Décision actuelle |
|---|---|---|---|---|---|---|
| R01 | La sauvegarde n’a pas été restaurée sur une instance isolée. | Haute | Responsable hébergement | Avant bêta fermée | Procès-verbal de restauration complète, contrôles post-restauration et nettoyage. | Ouvert; **bloquant bêta**. |
| R02 | Le cron hébergeur et l’exécution sans trafic ne sont pas observés. | Haute | Responsable hébergement | Avant bêta fermée | Preuve d’exécution planifiée, tâches dues/échouées, comportement sans visite. | Ouvert; **bloquant bêta**. |
| R03 | L’adresse IP exploitée pour le rate-limit peut être altérée derrière CDN/proxy. | Haute | Responsable infrastructure | Avant bêta fermée | Chaîne d’en-têtes contrôlée et test de limitation depuis plusieurs clients autorisés. | Ouvert; **bloquant bêta**. |
| R04 | La recette accessibilité humaine est incomplète. | Moyenne | Responsable qualité/accessibilité | Avant bêta fermée | Rapport mobile, zoom, clavier et lecteur d’écran avec défauts traités ou acceptés. | Ouvert. |
| R05 | Lighthouse est seulement une mesure de laboratoire. | Moyenne | Responsable performance | Pendant préparation bêta | Instrumentation minimisée de LCP, INP et CLS; stratégie de conservation des données. | Ouvert. |
| R06 | Domaine, HTTPS final, expéditeur transactionnel et DNS e-mail ne sont pas prêts. | Haute | Responsable produit/infrastructure | Avant production | Domaine final, SPF, DKIM, DMARC, tests autorisés, redirections et contrôle SEO. | Ouvert; **bloquant production**. |
| R07 | H06/H07 : performance bureau et poids de connexion demandent un suivi causal. | Moyenne | Responsable performance | Avant bêta élargie | Mesure répétée sur infrastructure stable et optimisation seulement si cause démontrée. | Ouvert, non bloquant laboratoire. |
| R08 | H02 : disponibilité publique du pseudo peut favoriser l’énumération. | Faible | Responsable sécurité | Avant bêta élargie | Analyse des limites edge, taux et messages; décision de risque documentée. | Ouvert, accepté provisoirement. |

> Les mots « bloquant bêta » et « bloquant production » désignent des conditions de décision, pas des prédictions de défaut. Aucun risque ne doit être fermé par une hypothèse, une capture privée ou une modification non reproduite.
