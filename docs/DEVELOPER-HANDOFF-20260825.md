# Relais développeur — Atelier WordPress

## État de référence

Atelier est un forum WordPress public à vocation éditoriale, construit sur **WordPress 7.1**, **PHP 8.3** et **bbPress 2.6**. La référence source est le thème **Atelier 0.4.28** dans `atelier-0428/` et le plugin **Premium Forum Core 0.4.11** dans `premium-forum-core/`. Le dépôt public est une relève de code; il ne contient pas l’installation WordPress, les réglages de l’hébergeur, la base, les cookies, les comptes réels ou les identifiants.

La documentation à lire en premier est, dans cet ordre : `README.md`, `CHANGELOG.md`, `docs/email-temporary-domain-options-20260826.md`, `docs/senior-audit-findings-20260826.md`, `docs/google-seo-audit-basis-20260826.md` et `docs/prompts-audit-simulation-claude.md`.

## Architecture et responsabilités

| Couche | Dossier | Responsabilités |
|---|---|---|
| Présentation | `atelier/` | Accueil et archives, templates bbPress, navigation, recherche, login Atelier, design RTL, cache HTTP des lectures anonymes. |
| Règles métier | `premium-forum-core/` | Inscription, pseudo, e-mail, modération, import CSV, rôles, votes, suivis, notifications, meta SEO et JSON-LD. |
| Forum | bbPress | Types forum, sujet et réponse, compteurs, permissions de base et formulaires de contribution. |
| Données importées | `fixtures/`, `.test-sandbox/` | Données synthétiques et jeux de validation seulement; ne pas les confondre avec une sauvegarde WordPress. |
| Livraison | `release/` | Archives installables dont la racine doit exactement correspondre au répertoire WordPress attendu. |

## Fonctionnalités prouvées sur staging

L’accueil, les espaces, les archives de sujets, les profils, la recherche à suggestions, les sujets, le tri de réponses, les votes, les suivis, l’enregistrement, les badges Membre SVIP/Modérateur et l’espace membre sont rendus et testés. Les contenus arabes utilisent une direction RTL, alignement à droite et Noto Naskh Arabic pour les messages et réponses. Le rendu public est distinct de la session administrateur.

L’inscription demande identité, pseudo suggéré, e-mail, mot de passe et confirmation. Le pseudo est revalidé côté serveur. La demande de code est protégée par un honeypot et une limitation appliquée après validation des champs; le retour négatif de `wp_mail()` nettoie le compte incomplet et rend une erreur contrôlée. Le formulaire `action=verify` peut être repris en GET sans e-mail dans l’URL, avec une saisie locale de l’adresse si nécessaire. La confirmation et le renvoi ont été validés en Mailtrap Email Sandbox avec une adresse synthétique; après confirmation, les métadonnées pending, hash, expiration et tentatives sont supprimées.

Easy WP SMTP et Mailtrap Email Sandbox constituent uniquement un mail catcher de staging. Aucun e-mail réel n’a été utilisé. Cette preuve ne remplace pas la délivrabilité de production, qui reste conditionnée par le domaine final, un fournisseur transactionnel, SPF, DKIM, DMARC et des essais vers de vraies boîtes de réception.

La file de modération centralise les sujets et réponses en attente. Les décisions publier, refuser et supprimer sont des formulaires `POST` avec nonce et capacité appropriée. Les notifications internes ne sont créées que pour les réponses `publish`, sont dédupliquées, et les suivis exigent un sujet `publish`. Les artefacts PFC communautaires sont nettoyés lorsqu’un compte ou une contribution disparaît.

## SEO, lisibilité machine et cache

Le HTML sépare titre, forum, auteur, rôle, message initial, réponses, dates, sources et actions. Les sujets publics émettent `DiscussionForumPosting` et `BreadcrumbList`; il n’existe pas de `QAPage` ni d’`acceptedAnswer` sans workflow d’acceptation réel. L’accueil et les sujets ont des titres de marque, descriptions adaptées et canoniques auto-référents. Les URL d’import historiques gardent les dates importées lorsque la donnée est valide.

Le cache public est limité aux lecteurs anonymes. Les pages de connexion et les sessions restent privées et non cachées. Après toute mise à jour de thème/plugin touchant au rendu ou aux métadonnées, purger LiteSpeed/CDN puis contrôler une requête fraîche et une requête chaude : un cache préexistant peut sinon continuer de diffuser un ancien `<head>`.

## Procédure de modification et de déploiement

Commencez par les contrôles locaux ci-dessous. Construisez ensuite une archive avec la bonne racine, testez-la par `unzip -t`, puis installez-la via l’interface WordPress fraîche. Ne réutilisez jamais un lien WordPress de remplacement contenant un nonce expiré. Enfin, testez le rendu public en navigation privée et consignez le résultat.

```bash
find atelier premium-forum-core -name '*.php' -print0 | xargs -0 -n1 php -l
php tools/test-pfc-validation.php .test-sandbox/packs/valid .test-sandbox/packs/invalid
unzip -t release/<archive>.zip
python3 prepare_public_repo.py
```

Avant tout push public, régénérez `atelier-wordpress-public` et recherchez notamment des secrets, mots de passe, cookies, adresses privées, exports, tokens, URL d’administration et domaine de staging. Un changement de code doit être accompagné d’un changelog, d’une preuve de test et d’un numéro de version cohérent.

## Limites et priorités restantes

| Priorité | Élément | Condition de clôture |
|---|---|---|
| P1 | Délivrabilité de production | Mailtrap Sandbox est validé pour le staging; choisir l’expéditeur final, authentifier le domaine avec SPF/DKIM/DMARC et tester de vraies boîtes avant ouverture publique. |
| P2 | Performance réelle | Mesure CWV mobile et desktop sur production ou préproduction représentative; ne pas inférer LCP, INP ou CLS de requêtes `curl`. |
| P2 | Charge et intégrations | Test autorisé à faible charge, santé WordPress et conflits avec les extensions réellement prévues. |
| P2 | Production SEO | Domaine final, HTTPS, redirections, sitemap/robots, retrait du noindex seulement après recette et purge cache. |
| P3 | Workflow Q&A | N’ajouter `QAPage`/`acceptedAnswer` qu’après conception du rôle qui accepte une réponse, audits de visibilité et test de données structurées. |

## Règles non négociables du dépôt

Ne publiez jamais de mots de passe, clés, tokens, cookies, `wp-config.php`, exports SQL, journaux contenant des données membres, identifiants personnels, captures d’administration ou URL privées. Utilisez `example.test` dans les fixtures. Les comptes et contenus de recette doivent être supprimés ou rendus manifestement synthétiques après une simulation.
