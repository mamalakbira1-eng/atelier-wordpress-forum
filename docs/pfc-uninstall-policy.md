# Politique de désinstallation Premium Forum Core

## Décision

Premium Forum Core applique une désinstallation **non destructive**. La désactivation ou la suppression de l’extension ne supprime ni les tables `pfc_*`, ni les votes, follows, notifications, journaux d’import, utilisateurs ou contenus bbPress.

Cette décision protège les données métier et permet une réinstallation ou une reprise contrôlée. Une purge doit être effectuée par une procédure de migration ou de rétention explicitement approuvée, jamais automatiquement par WordPress.

## Comportement technique

Le fichier `premium-forum-core/uninstall.php` supprime uniquement les options techniques suivantes : `pfc_schema_version`, `pfc_schema_last_error` et `pfc_community_data_version`. Les tables et les métadonnées métier sont conservées.

## Critères de vérification

| Contrôle | Cible | Preuve | Exit |
|---|---:|---|---:|
| Fichier uninstall présent | 1/1 | `premium-forum-core/uninstall.php` | 0 |
| Options techniques supprimées | 3/3 | Test dans une copie isolée | 0 |
| Tables et données conservées | 100 % | Comparaison avant/après | 0 |
| Suppression de données métier accidentelle | 0 | Requête d’intégrité | 0 |

## Limite

Cette politique ne constitue pas une purge RGPD ou une procédure de destruction de données. Toute suppression définitive doit faire l’objet d’une procédure séparée, autorisée et testée sur une copie.
