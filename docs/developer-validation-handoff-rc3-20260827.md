# Atelier — dossier de validation développeur, rc3

**Référence unique :** [`atelier-prebeta-rc3`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3).

Ce dossier contient les liens directs du lot gelé à contrôler. Il est volontairement public et assaini : aucun accès, secret, environnement, sauvegarde, identité personnelle ni URL privée n’y figure.

> **Décision : NO-GO production.** La candidate rc3 sert à installer et contrôler un laboratoire ou une préproduction autorisée. Elle n’autorise ni retrait de `noindex, nofollow`, ni e-mail réel, ni restauration sur une instance active, ni changement de domaine.

## Vérification du lot

| Étape | Référence directe | Contrôle attendu |
|---:|---|---|
| 1 | [README](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/README.md), [CHANGELOG](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/CHANGELOG.md) et [réconciliation](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/version-reconciliation-20260827.md) | Confirmer la référence actuelle : Atelier 0.4.32 + PFC 0.4.19. |
| 2 | [Référence canonique](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/canonical-reference-20260827.md) et [manifeste SHA-256](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/ARTIFACTS-SHA256.txt) | Vérifier versions, archives, racines ZIP et hash. |
| 3 | [CI d’intégrité](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/actions/workflows/artifact-integrity.yml) et [procès-verbal de reproductibilité](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/reproducibility-validation-20260827.md) | Vérifier une CI verte sur le commit et le tag rc3. |
| 4 | [Procédure isolée](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/clean-install-and-recovery-procedure-20260827.md), [matrice de tests](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/test-matrix-20260827.md) et [risques](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/risk-register-20260827.md) | Préparer l’environnement de laboratoire et ne fermer aucun risque sans preuve. |

## Liens de code et de releases

| Zone | Lien direct | Attendu |
|---|---|---|
| Thème | [`atelier/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3/atelier) | Atelier 0.4.32, H05, gabarits bbPress, RTL et recherche. |
| Interactions | [`atelier-interactions.js`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/atelier/assets/js/atelier-interactions.js) | Recherche clavier et libellés accessibles après interaction. |
| PFC | [`premium-forum-core/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3/premium-forum-core) | PFC 0.4.19 et protections de surface HTTP. |
| Import | [`class-pfc-importer.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/premium-forum-core/includes/class-pfc-importer.php) | H01 : relations avant écriture; H09 : erreurs de date sans fatal. |
| Modération | [`class-pfc-moderation.php`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/premium-forum-core/includes/class-pfc-moderation.php) | H08 : primitives bbPress et resynchronisation ciblée. |
| Release Atelier | [`atelier-0.4.32-a11y-visible-labels.zip`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/release/atelier-0.4.32-a11y-visible-labels.zip) | Vérifier SHA-256 puis installer dans `atelier/`. |
| Release PFC | [`premium-forum-core-0.4.19-csv-validation-compat.zip`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/release/premium-forum-core-0.4.19-csv-validation-compat.zip) | Vérifier SHA-256 puis installer dans `premium-forum-core/`. |
| Tests CSV | [`test-fixtures/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3/test-fixtures) et [harnais](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/tools/test-pfc-validation.php) | Rejouer les packs valide/invalide, incluant H01 et H09. |

## Commandes de contrôle

```bash
git clone https://github.com/mamalakbira1-eng/atelier-wordpress-forum.git
cd atelier-wordpress-forum
git checkout atelier-prebeta-rc3
sha256sum --check ARTIFACTS-SHA256.txt
unzip -t release/atelier-0.4.32-a11y-visible-labels.zip
unzip -t release/premium-forum-core-0.4.19-csv-validation-compat.zip
find atelier premium-forum-core -name '*.php' -print0 | xargs -0 -n1 php -l
php tools/test-pfc-validation.php test-fixtures/valid test-fixtures/invalid
```

Le résultat attendu est : hash et ZIP valides, aucune erreur de syntaxe PHP et `"pass": true` pour le harnais. La validation applicative doit ensuite rejouer T03 à T16 sur une instance autorisée, avec données synthétiques supprimées après recette.

## Décision et limites

Les tags `atelier-prebeta-rc1` et `atelier-prebeta-rc2` sont conservés comme traces d’échecs de CI et ne doivent pas être installés. Les risques de restauration isolée, cron hébergeur, CDN/rate-limit, accessibilité humaine, mesures terrain et configuration domaine/e-mail restent ouverts. Les conditions et preuves de clôture sont définies dans le [registre des risques](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/risk-register-20260827.md).
