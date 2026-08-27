# Atelier — référence canonique de pré-bêta

**Statut :** référence candidate de pré-bêta, non autorisée en production.

**Référence immuable :** `refs/tags/atelier-prebeta-rc3` une fois le tag publié.
**Règle de gel :** le tag, les sources, les archives ZIP, le manifeste SHA-256, la matrice de tests et la documentation de cette page constituent un même lot. Toute modification impose un nouveau commit, de nouveaux hash et un nouveau tag; aucune archive ne peut être remplacée silencieusement.

## Périmètre logiciel associé

| Composant | Version de référence | Répertoire WordPress attendu | Source de preuve |
|---|---:|---|---|
| Thème Atelier | 0.4.32 | `wp-content/themes/atelier/` | Métadonnées `atelier/style.css`, archive et manifeste. |
| Premium Forum Core | 0.4.19 | `wp-content/plugins/premium-forum-core/` | Métadonnées `premium-forum-core.php`, archive et manifeste. |
| bbPress | 2.6.14 | Extension dépendante | Version exercée lors de la recette fonctionnelle. |
| WordPress | 7.1 | Noyau de préproduction | Version exercée lors de la recette fonctionnelle. |
| PHP | 8.3.30 | Runtime de préproduction | Runtime exercé lors de la recette fonctionnelle. |

Les versions WordPress, bbPress et PHP ci-dessus sont des **références de recette connues**, non une promesse de compatibilité universelle. Toute instance propre doit consigner la version réellement installée et rejouer les tests pertinents si elle diffère.

## Artefacts gelés

| Artefact | Chemin | Taille | SHA-256 | Installation |
|---|---|---:|---|---|
| Thème | `release/atelier-0.4.32-a11y-visible-labels.zip` | 266 502 octets | `76d25ea1c68c327095e842432279db18d9b2a147600249279137796a1aa484f4` | Import WordPress; racine ZIP `atelier/`. |
| Extension | `release/premium-forum-core-0.4.19-csv-validation-compat.zip` | 28 017 octets | `f72e6b3787651e8a4078f42c7a01643e63d24086e7821dc5f9410830b08141a3` | Import WordPress; racine ZIP `premium-forum-core/`. |

La commande ci-dessous est la vérification minimale des artefacts :

```bash
git checkout atelier-prebeta-rc3
sha256sum --check ARTIFACTS-SHA256.txt
unzip -t release/atelier-0.4.32-a11y-visible-labels.zip
unzip -t release/premium-forum-core-0.4.19-csv-validation-compat.zip
```

## Réconciliation documentaire

Les documents qui présentaient Atelier 0.4.31 et PFC 0.4.16 décrivent une **étape historique** de consolidation. Ils ne sont plus la procédure d’installation courante. La référence d’installation et de validation est exclusivement celle de cette page, du manifeste `ARTIFACTS-SHA256.txt`, de la procédure d’installation propre et de la matrice de tests associée.

| Écart historique | Décision de référence |
|---|---|
| Atelier 0.4.31 | Conservé dans l’historique Git comme jalon antérieur; non installable comme référence canonique actuelle. |
| PFC 0.4.16 | Conservé dans l’historique Git comme jalon antérieur; non installable comme référence canonique actuelle. |
| Atelier 0.4.32 | Référence installable portant le correctif H05 de libellé accessible. |
| PFC 0.4.18 | Jalon historique : la CI a révélé un défaut de compatibilité de validation de date; non retenu comme référence d’installation. |
| PFC 0.4.19 | Référence installable portant H01, H08 et H09 : validation de date compatible avec les erreurs modernes. |

## Décision d’usage

> **NO-GO production.** Cette référence permet une installation de laboratoire, une recette contrôlée et la préparation d’une bêta fermée. Elle ne permet pas de retirer `noindex, nofollow`, d’envoyer des e-mails réels, de restaurer sur une instance active ou d’ouvrir un domaine public.

Les conditions de passage à une bêta fermée sont détaillées dans le [procès-verbal de validation reproductible](reproducibility-validation-20260827.md), le [registre des risques](risk-register-20260827.md), la [procédure d’installation et restauration](clean-install-and-recovery-procedure-20260827.md) et la [matrice de tests](test-matrix-20260827.md).
