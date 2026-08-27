# Atelier — rapport de réconciliation des versions

## Constat initial

Le dépôt public présentait des documents de reprise faisant encore référence à Atelier 0.4.31 et PFC 0.4.16, alors que le code et les archives installables issus de la recette senior portent Atelier 0.4.32 et PFC 0.4.18. Cette divergence documentaire pouvait conduire un développeur à installer un lot antérieur ou à comparer des résultats contre de mauvaises versions.

## Vérifications effectuées

| Vérification | Résultat |
|---|---|
| Métadonnée `atelier/style.css` | Atelier 0.4.32. |
| Métadonnées PFC | PFC 0.4.18 dans l’en-tête et la constante de version. |
| Archive Atelier | Racine `atelier/`, métadonnée 0.4.32, hash inscrit dans le manifeste. |
| Archive PFC | Racine `premium-forum-core/`, métadonnées 0.4.18, hash inscrit dans le manifeste. |
| Rapport de recette | Correctifs H01, H05 et H08 associés à ce lot de releases. |

## Décision

La référence d’installation est désormais **Atelier 0.4.32 + PFC 0.4.18**, gelée par le tag `atelier-prebeta-rc1`, le manifeste SHA-256 et la matrice de tests. Le README et le CHANGELOG ne présentent plus 0.4.31/0.4.16 comme versions d’installation courante. Les mentions historiques sont conservées comme jalons documentaires, non comme instructions opérationnelles.

## Écarts conservés volontairement

Les versions WordPress 7.1, PHP 8.3.30 et bbPress 2.6.14 correspondent à l’environnement où la recette a été exercée. Elles restent à reproduire ou à justifier explicitement sur toute nouvelle instance. La restauration isolée, le cron hôte, la chaîne CDN/rate-limit, la recette accessibilité humaine et les mesures terrain restent des risques ouverts; les présenter comme « validés » serait incorrect.
