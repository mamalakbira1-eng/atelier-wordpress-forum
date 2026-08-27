# Atelier — forum WordPress / bbPress, référence de pré-bêta

Atelier est un forum WordPress lisible par les humains et les systèmes, construit avec **bbPress**, le thème **Atelier 0.4.32** et le plugin **Premium Forum Core 0.4.18**. Le thème porte l’interface éditoriale, le responsive, le RTL et les correctifs d’accessibilité; PFC concentre les règles communautaires, d’inscription, d’import, de modération, de sécurité et de SEO.

> **Statut : NO-GO production.** Cette référence permet une installation de laboratoire et une recette de pré-bêta. Elle n’autorise ni retrait de `noindex, nofollow`, ni envoi d’e-mail réel, ni restauration sur une instance active.

## Artefacts de référence

| Composant | Archive installable | Répertoire attendu | Rôle |
|---|---|---|---|
| Thème | `release/atelier-0.4.32-a11y-visible-labels.zip` | `atelier/` | Interface publique, login Atelier, templates bbPress, RTL, cache public, parcours clavier et correctif H05. |
| Plugin | `release/premium-forum-core-0.4.18-import-references.zip` | `premium-forum-core/` | Inscription, modération, import CSV, votes, suivis, notifications, SEO, durcissement HTTP et correctifs H01/H08. |

Le tag `atelier-prebeta-rc1`, le fichier `ARTIFACTS-SHA256.txt`, les sources, les deux archives, la matrice de tests et les documents de reprise constituent un même lot gelé. Vérifiez les hash avant installation; une modification d’archive impose une nouvelle référence, jamais un remplacement silencieux.

## Installation et mise à jour

Installez WordPress et bbPress, puis importez les archives via **Apparence → Thèmes** et **Extensions → Ajouter → Téléverser une extension** dans une instance autorisée. Si WordPress annonce qu’un répertoire existe, choisissez le remplacement uniquement après sauvegarde de laboratoire. Vérifiez que les versions visibles sont Atelier 0.4.32 et PFC 0.4.18, purgez les caches concernés, puis contrôlez l’accueil et un sujet dans une fenêtre non connectée.

L’import d’historique se fait dans **Premium Forum → Importer**. Téléchargez les modèles fournis, exécutez d’abord un dry run, examinez les erreurs et le mapping, puis lancez l’import. Le rollback ne retire que les objets journalisés par un job sélectionné : il ne remplace jamais une sauvegarde complète de la base.

## Contrat CSV

Les en-têtes des modèles sont la référence. Les alias courants comprennent `pseudo → username`, `mail → email`, `topic_id → legacy_topic_id`, `author_id → legacy_author_id`, `body → content`, `date → created_at` et `votes → upvotes_count`. Le filtre `pfc_csv_header_mapping` permet d’étendre ce mapping. Les mots de passe en clair sont refusés; les comptes historiques doivent être migrés avec un mécanisme de réinitialisation de mot de passe adapté, jamais avec une valeur réutilisable.

Les limites appliquées sont de quatre fichiers, 5 Mo par fichier et 20 Mo pour le pack. L’import conserve les dates et compteurs historiques validés par les données source; il n’invente pas de votes individuels.

## Vérifications locales minimales

```bash
cd /chemin/vers/atelier-wordpress
git checkout atelier-prebeta-rc1
sha256sum --check ARTIFACTS-SHA256.txt
unzip -t release/atelier-0.4.32-a11y-visible-labels.zip
unzip -t release/premium-forum-core-0.4.18-import-references.zip
find atelier premium-forum-core -name '*.php' -print0 | xargs -0 -n1 php -l
php tools/test-pfc-validation.php test-fixtures/valid test-fixtures/invalid
```

Le harnais CSV doit indiquer `"pass": true`. Pour une instance isolée, reprendre ensuite la [matrice de tests](docs/test-matrix-20260827.md), la [procédure de restauration](docs/clean-install-and-recovery-procedure-20260827.md), le [registre des risques](docs/risk-register-20260827.md) et la [référence canonique](docs/canonical-reference-20260827.md).

## Sécurité et mise en production

La préproduction reste volontairement en `noindex, nofollow`. Le flux d’inscription a été validé avec une capture d’e-mails et des adresses synthétiques; cela ne prouve pas la délivrabilité publique. PFC 0.4.18 bloque XML-RPC, désactive les mots de passe d’application et applique des en-têtes de sécurité sur les pages publiques et de connexion. Avant production, configurez le fournisseur transactionnel, le domaine expéditeur, SPF, DKIM et DMARC, puis testez la réception vers des boîtes explicitement autorisées. Les secrets SMTP, cookies, mots de passe, exports SQL, journaux de membres et URLs privées ne doivent jamais rejoindre ce dépôt.

Le dépôt public ne contient que du code, des fixtures synthétiques en `example.test` et des archives assainies. Consultez le [dossier de validation développeur](docs/developer-validation-handoff-20260827.md), le [rapport de réconciliation](docs/version-reconciliation-20260827.md), la [synthèse de recette](docs/public-senior-recipe-summary-20260827.md) et `CHANGELOG.md` pour la procédure de reprise, les risques ouverts et l’historique de release.
