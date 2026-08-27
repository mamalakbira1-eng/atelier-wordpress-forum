# Atelier — dossier de validation développeur, rc2

**Référence unique :** [`atelier-prebeta-rc2`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc2).

Ce dossier donne au prochain développeur les liens directs vers le lot gelé. Il est public et assaini : il ne contient ni environnement, ni accès, ni secret, ni sauvegarde, ni identité personnelle, ni URL privée.

> **Décision : NO-GO production.** Ce lot sert à installer et contrôler un laboratoire ou une préproduction autorisée. Il n’autorise ni envoi réel, ni retrait de `noindex, nofollow`, ni restauration sur une instance active, ni changement de domaine.

## Démarrage de validation

| Ordre | Lien de référence | Décision attendue |
|---:|---|---|
| 1 | [README](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/README.md), [CHANGELOG](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/CHANGELOG.md) et [réconciliation](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/docs/version-reconciliation-20260827.md) | Confirmer que la référence installable est Atelier 0.4.32 + PFC 0.4.19. |
| 2 | [Manifeste SHA-256](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/ARTIFACTS-SHA256.txt) et [référence canonique](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/docs/canonical-reference-20260827.md) | Vérifier les hash, racines ZIP, versions et règle de gel. |
| 3 | [Procès-verbal de reproductibilité](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/docs/reproducibility-validation-20260827.md) et [CI d’intégrité](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/actions/workflows/artifact-integrity.yml) | Confirmer la CI verte du commit/tag et l’absence de fatal sur le pack invalide. |
| 4 | [Procédure d’installation/restauration](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/docs/clean-install-and-recovery-procedure-20260827.md), [matrice de tests](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/docs/test-matrix-20260827.md) et [registre des risques](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/docs/risk-register-20260827.md) | Préparer l’installation isolée et ne fermer aucun risque sans la preuve définie. |

## Code et artefacts à revoir

| Zone | Lien direct | Vérification technique |
|---|---|---|
| Thème Atelier | [`atelier/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc2/atelier) | Version 0.4.32, gabarits bbPress, RTL, recherche et correctif H05. |
| En-tête, réponses, sujet | [`header.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/atelier/header.php), [`loop-single-reply.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/atelier/bbpress/loop-single-reply.php), [`content-single-topic.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/atelier/bbpress/content-single-topic.php) | Libellés visibles et noms accessibles cohérents. |
| Interactions client | [`atelier-interactions.js`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/atelier/assets/js/atelier-interactions.js) | Recherche clavier et libellés après interaction. |
| PFC | [`premium-forum-core/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc2/premium-forum-core) et [`bootstrap`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/premium-forum-core/premium-forum-core.php) | Version 0.4.19, dépendance bbPress et protections HTTP. |
| Import CSV | [`class-pfc-importer.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/premium-forum-core/includes/class-pfc-importer.php) | H01 : relations avant écriture; H09 : erreur de date contrôlée sans fatal. |
| Modération | [`class-pfc-moderation.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/premium-forum-core/includes/class-pfc-moderation.php) | H08 : transition bbPress et resynchronisation ciblée des compteurs. |
| Releases | [Atelier 0.4.32](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/release/atelier-0.4.32-a11y-visible-labels.zip) et [PFC 0.4.19](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/release/premium-forum-core-0.4.19-csv-validation-compat.zip) | Contrôler les hash avant toute installation. |
| Fixtures et harnais | [`test-fixtures/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc2/test-fixtures) et [`test-pfc-validation.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/tools/test-pfc-validation.php) | Rejouer le pack valide et le pack invalide, incluant relation absente et date invalide. |

## Commandes de vérification

```bash
git clone https://github.com/mamalakbira1-eng/atelier-wordpress-forum.git
cd atelier-wordpress-forum
git checkout atelier-prebeta-rc2
sha256sum --check ARTIFACTS-SHA256.txt
unzip -t release/atelier-0.4.32-a11y-visible-labels.zip
unzip -t release/premium-forum-core-0.4.19-csv-validation-compat.zip
find atelier premium-forum-core -name '*.php' -print0 | xargs -0 -n1 php -l
php tools/test-pfc-validation.php test-fixtures/valid test-fixtures/invalid
```

Le résultat attendu est une vérification de hash et ZIP réussie, aucun défaut de syntaxe PHP et `"pass": true` pour le harnais. Les tests de l’instance doivent ensuite suivre T03 à T16 de la matrice; les données de test sont synthétiques et doivent être supprimées après chaque campagne.

## Risques et critères de décision

La bêta fermée reste interdite tant que la restauration isolée (R01), le cron hôte (R02) et la chaîne CDN/rate-limit (R03) ne sont pas prouvés. L’accessibilité humaine, les métriques terrain et la préparation du domaine/e-mail final restent également ouverts selon le [registre des risques](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc2/docs/risk-register-20260827.md).

Le précédent tag `atelier-prebeta-rc1` est un jalon historique qui a échoué en CI; il ne doit pas être utilisé comme référence d’installation. La candidate rc2 remplace rc1 sans réécrire l’historique.
