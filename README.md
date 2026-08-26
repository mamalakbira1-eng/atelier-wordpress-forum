# Atelier — forum WordPress / bbPress

Atelier est un forum WordPress lisible par les humains et les systèmes, construit avec **bbPress**, le thème **Atelier 0.4.31** et le plugin **Premium Forum Core 0.4.16**. Le thème porte l’interface éditoriale, le responsive, le RTL et les correctifs d’accessibilité; PFC concentre les règles communautaires, d’inscription, d’import, de modération, de sécurité et de SEO.

## Artefacts de référence

| Composant | Archive installable | Répertoire attendu | Rôle |
|---|---|---|---|
| Thème | `release/atelier-0.4.31-search-escape-complete.zip` | `atelier/` | Interface publique, login Atelier, templates bbPress, RTL, cache public et parcours clavier de recherche. |
| Plugin | `release/premium-forum-core-0.4.16-security-headers-complete.zip` | `premium-forum-core/` | Inscription, modération, import CSV, votes, suivis, notifications, SEO et durcissement HTTP. |

> **Point de déploiement critique.** La release de référence installe le thème dans le dossier `atelier/`. Vérifiez toujours la racine de l’archive avant remplacement : WordPress considère deux dossiers de thème différents comme deux thèmes différents.

## Installation et mise à jour

Installez bbPress, puis importez les archives via **Apparence → Thèmes** et **Extensions → Ajouter → Téléverser une extension**. Si WordPress annonce qu’un répertoire existe, choisissez le remplacement uniquement après sauvegarde des fichiers et de la base. Vérifiez que les versions visibles sont Atelier 0.4.31 et PFC 0.4.16, purgez LiteSpeed/CDN, puis contrôlez l’accueil et un sujet dans une fenêtre non connectée.

L’import d’historique se fait dans **Premium Forum → Importer**. Téléchargez les modèles fournis, exécutez d’abord un dry run, examinez les erreurs et le mapping, puis lancez l’import. Le rollback ne retire que les objets journalisés par un job sélectionné : il ne remplace jamais une sauvegarde complète de la base.

## Contrat CSV

Les en-têtes des modèles sont la référence. Les alias courants comprennent `pseudo → username`, `mail → email`, `topic_id → legacy_topic_id`, `author_id → legacy_author_id`, `body → content`, `date → created_at` et `votes → upvotes_count`. Le filtre `pfc_csv_header_mapping` permet d’étendre ce mapping. Les mots de passe en clair sont refusés; les comptes historiques doivent être migrés avec un mécanisme de réinitialisation de mot de passe adapté, jamais avec une valeur réutilisable.

Les limites appliquées sont de quatre fichiers, 5 Mo par fichier et 20 Mo pour le pack. L’import conserve les dates et compteurs historiques validés par les données source; il n’invente pas de votes individuels.

## Vérifications locales minimales

```bash
cd /chemin/vers/atelier-wordpress
find atelier premium-forum-core -name '*.php' -print0 | xargs -0 -n1 php -l
php tools/test-pfc-validation.php .test-sandbox/packs/valid .test-sandbox/packs/invalid
```

Le harnais CSV doit indiquer `"pass": true`. Pour le staging, reprendre ensuite les scénarios de `docs/prompts-audit-simulation-claude.md` et le rapport `docs/senior-audit-findings-20260826.md`.

## Sécurité et mise en production

Le staging reste volontairement en `noindex, nofollow`. Le flux d’inscription PFC 0.4.16 a été validé avec Mailtrap Email Sandbox et une adresse synthétique : envoi, renvoi, reprise directe de vérification, confirmation, expiration contrôlée et nettoyage du compte de recette. PFC 0.4.16 bloque XML-RPC, désactive les mots de passe d’application et applique des en-têtes de sécurité sur les pages publiques et de connexion. Ce mail catcher ne prouve pas la délivrabilité publique. Avant production, configurez le fournisseur transactionnel, le domaine expéditeur, SPF, DKIM et DMARC, puis testez la réception vers des boîtes réelles. Les secrets SMTP, cookies, mots de passe, exports SQL, journaux de membres et URLs privées ne doivent jamais rejoindre ce dépôt.

Le public GitHub ne contient que du code, des fixtures synthétiques en `example.test` et des archives assainies. Consultez `docs/DEVELOPER-HANDOFF-20260825.md`, `docs/preproduction-runbook-20260826.md` et `CHANGELOG.md` pour la procédure de reprise, les risques ouverts et l’historique de release.
