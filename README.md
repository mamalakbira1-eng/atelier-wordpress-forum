# Atelier — forum WordPress / bbPress

Atelier est un forum WordPress lisible par les humains et les systèmes, construit avec **bbPress**, le thème **Atelier 0.4.28** et le plugin **Premium Forum Core 0.4.4**. Le thème porte l’interface éditoriale, le responsive et le RTL; PFC concentre les règles communautaires, d’inscription, d’import, de modération et de SEO.

## Artefacts de référence

| Composant | Archive installable | Répertoire attendu | Rôle |
|---|---|---|---|
| Thème | `release/atelier-0.4.28-active-theme-senior-audit.zip` | `atelier-0428/` | Interface publique, login Atelier, templates bbPress, RTL et cache public. |
| Plugin | `release/premium-forum-core-0.4.4-senior-audit.zip` | `premium-forum-core/` | Inscription, modération, import CSV, votes, suivis, notifications et SEO. |

> **Point de déploiement critique.** Le thème réellement actif porte le nom de dossier `atelier-0428`. Une archive dont la racine est `atelier/` installe un autre thème et ne modifie pas le thème actif.

## Installation et mise à jour

Installez bbPress, puis importez les archives via **Apparence → Thèmes** et **Extensions → Ajouter → Téléverser une extension**. Si WordPress annonce qu’un répertoire existe, choisissez le remplacement uniquement après sauvegarde des fichiers et de la base. Vérifiez que les versions visibles sont Atelier 0.4.28 et PFC 0.4.4, purgez LiteSpeed/CDN, puis contrôlez l’accueil et un sujet dans une fenêtre non connectée.

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

Le staging reste volontairement en `noindex, nofollow`. Avant production, configurez un SMTP transactionnel ou un mail catcher, testez réception et délivrabilité, puis vérifiez SPF, DKIM, DMARC et le domaine expéditeur. Les secrets SMTP, cookies, mots de passe, exports SQL, journaux de membres et URLs privées ne doivent jamais rejoindre ce dépôt.

Le public GitHub ne contient que du code, des fixtures synthétiques en `example.test` et des archives assainies. Consultez `docs/DEVELOPER-HANDOFF-20260825.md` pour la procédure de reprise et `CHANGELOG.md` pour l’historique de release.
