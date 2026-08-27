# Atelier — rapport de réconciliation des versions

## Constat initial

Le dépôt public présentait des documents de reprise faisant encore référence à Atelier 0.4.31 et PFC 0.4.16, alors que le code et les archives installables issus de la recette senior portaient Atelier 0.4.32 et PFC 0.4.18. La première candidate gelée a ensuite révélé en CI un défaut de portabilité du validateur de date. La référence corrective porte donc Atelier 0.4.32 et PFC 0.4.19. Ces divergences pouvaient conduire un développeur à installer un lot antérieur ou à comparer des résultats contre de mauvaises versions.

## Vérifications effectuées

| Vérification | Résultat |
|---|---|
| Métadonnée `atelier/style.css` | Atelier 0.4.32. |
| Métadonnées PFC | PFC 0.4.19 dans l’en-tête et la constante de version. |
| Archive Atelier | Racine `atelier/`, métadonnée 0.4.32, hash inscrit dans le manifeste. |
| Archive PFC | Racine `premium-forum-core/`, métadonnées 0.4.19, hash inscrit dans le manifeste. |
| CI de référence | Le cas CSV date invalide est rejeté sans fatal après correction H09; la CI doit être verte sur rc2. |
| Rapport de recette | Correctifs H01, H05 et H08 associés au lot; H09 assure la portabilité du validateur de date. |

## Décision

La référence d’installation est désormais **Atelier 0.4.32 + PFC 0.4.19**, gelée par le tag `atelier-prebeta-rc3`, le manifeste SHA-256 et la matrice de tests. Le tag `atelier-prebeta-rc1` est conservé comme trace d’une candidate dont la CI a révélé H09; `atelier-prebeta-rc2` comme trace d’une candidate dont le workflow référençait une archive retirée. Aucun des deux tags ne doit être installé. Le README et le CHANGELOG ne présentent plus 0.4.31/0.4.16 comme versions d’installation courante. Les mentions historiques sont conservées comme jalons documentaires, non comme instructions opérationnelles.

## Écarts conservés volontairement

Les versions WordPress 7.1, PHP 8.3.30 et bbPress 2.6.14 correspondent à l’environnement où la recette a été exercée. Elles restent à reproduire ou à justifier explicitement sur toute nouvelle instance. La restauration isolée, le cron hôte, la chaîne CDN/rate-limit, la recette accessibilité humaine et les mesures terrain restent des risques ouverts; les présenter comme « validés » serait incorrect.
