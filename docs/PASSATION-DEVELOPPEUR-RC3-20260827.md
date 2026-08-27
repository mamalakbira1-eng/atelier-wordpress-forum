# Atelier — passation développeur et état de validation RC3

**Date de passation :** 27 août 2026
**Référence canonique d’installation :** [`atelier-prebeta-rc3`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3)
**Commit résolu :** [`8ae6ff4916d5fe2f3ed4f8b0bec32f38b6c557e2`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/commit/8ae6ff4916d5fe2f3ed4f8b0bec32f38b6c557e2)
**Décision actuelle :** **NO-GO production**.

> Le tag `atelier-prebeta-rc3` est le seul lot d’installation proposé. Il est immuable. Toute correction ultérieure doit être publiée dans une nouvelle référence, sans modifier ce tag ni les tags historiques.

## 1. Réponse courte : qu’est-ce qui est sur GitHub ?

Le dépôt public contient **tout ce qui est nécessaire pour reprendre, vérifier et installer les composants personnalisés Atelier**, ainsi que les preuves statiques de reproductibilité. Il ne contient volontairement pas une copie complète de WordPress, bbPress ou des extensions tierces, qui doivent être installés depuis leurs sources officielles et dont les versions sont à relever dans l’environnement de laboratoire.

| Catégorie | Présence publique | Référence |
|---|---|---|
| Sources du thème Atelier | Oui | [`atelier/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3/atelier) |
| Sources du plugin Premium Forum Core | Oui | [`premium-forum-core/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3/premium-forum-core) |
| Archives installables gelées | Oui | [`release/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3/release) |
| Manifeste SHA-256 | Oui | [`ARTIFACTS-SHA256.txt`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/ARTIFACTS-SHA256.txt) |
| CI, harnais et fixtures CSV synthétiques | Oui | [workflow](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/.github/workflows/artifact-integrity.yml), [harnais](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/tools/test-pfc-validation.php), [fixtures](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3/test-fixtures) |
| Documentation, matrice de tests et risques | Oui | [`docs/`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3/docs) |
| Cœur WordPress, bbPress, extensions tierces | Non, intentionnellement | Installer depuis les sources officielles sur l’instance de laboratoire. |
| Sauvegardes, exports de base, fichiers média, journaux bruts | Non, intentionnellement | Transfert privé autorisé uniquement, jamais dans GitHub. |
| Comptes, mots de passe, cookies, clés SMTP/API, configurations d’hébergement | Non, intentionnellement | Canal privé et interface sécurisée de l’environnement seulement. |

## 2. Lot gelé à utiliser exclusivement

| Composant | Version | Archive | SHA-256 | Destination WordPress |
|---|---:|---|---|---|
| Thème Atelier | 0.4.32 | [`atelier-0.4.32-a11y-visible-labels.zip`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/release/atelier-0.4.32-a11y-visible-labels.zip) | `76d25ea1c68c327095e842432279db18d9b2a147600249279137796a1aa484f4` | `wp-content/themes/atelier/` |
| Premium Forum Core | 0.4.19 | [`premium-forum-core-0.4.19-csv-validation-compat.zip`](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/release/premium-forum-core-0.4.19-csv-validation-compat.zip) | `f72e6b3787651e8a4078f42c7a01643e63d24086e7821dc5f9410830b08141a3` | `wp-content/plugins/premium-forum-core/` |

Les racines internes aux ZIP sont respectivement `atelier/` et `premium-forum-core/`. Il est interdit de reconstruire, remplacer ou renommer silencieusement une archive RC3 : tout changement impose une nouvelle version, un nouveau manifeste, une nouvelle CI et un nouveau tag.

## 3. Preuves déjà obtenues

La référence RC3 a été clonée de façon indépendante puis validée sans dépendre d’une installation WordPress. Ces contrôles démontrent l’intégrité du lot de code et d’archives ; ils ne remplacent pas une recette applicative.

| Contrôle | Résultat vérifié | Preuve |
|---|---|---|
| Résolution du tag | RC3 pointe sur le commit annoncé | [Tag RC3](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/tree/atelier-prebeta-rc3) |
| Manifestes et archives | Les deux SHA-256, les tailles, les ZIP et les racines concordent | [Manifeste](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/ARTIFACTS-SHA256.txt) |
| Parité des sources et ZIP | Aucune différence entre les dossiers source et les archives extraites | [Procès-verbal](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/reproducibility-validation-20260827.md) |
| Syntaxe PHP | 24 fichiers PHP validés avec le lint de la cible PHP 8.3 | [CI](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/actions/workflows/artifact-integrity.yml) |
| Harnais CSV | Pack valide accepté ; pack invalide rejeté ; résultat `"pass": true` | [Harnais](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/tools/test-pfc-validation.php) |
| CI distante | Deux exécutions liées au commit RC3 ont conclu `success` | [Exécution principale](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/actions/runs/33070624115) |
| Scan de publication | Aucune signature forte de secret, adresse personnelle courante ou marqueur opérationnel privé relevé dans le lot suivi | Revue de passation privée, non publiée par sécurité. |

## 4. Correctifs inclus et à revalider dans WordPress

| Référence | Composant | État statique RC3 | Validation dynamique à exécuter |
|---|---|---|---|
| H01 | Import PFC | Les relations croisées sont contrôlées avant écriture. | Import invalide : comptage base avant/après inchangé et journal de rejet. |
| H05 | Thème Atelier | Recherche clavier, noms accessibles et règles RTL présents dans les sources. | Clavier, Échap, flèches, focus, lecteur d’écran, mobile et contenu arabe réel. |
| H08 | Modération PFC | Les compteurs bbPress topic/forum sont resynchronisés après transition. | Publication, refus et suppression : comparer réponses publiques et compteurs visibles. |
| H09 | Import PFC | Les dates invalides sont capturées comme erreurs contrôlées, sans fatal. | Import réel d’un CSV invalide : aucun fatal et aucune écriture partielle non expliquée. |

## 5. Procédure de démarrage du prochain développeur

Le développeur doit utiliser une **nouvelle instance WordPress de laboratoire autorisée**, hors indexation, distincte de toute instance active et configurée avec un captureur d’e-mails de test. Aucun compte, e-mail, sujet, vote, fichier ou sauvegarde réel ne doit être employé.

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

Ensuite, suivre dans cet ordre :

1. Lire le [README](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/README.md), la [référence canonique](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/canonical-reference-20260827.md) et la [réconciliation des versions](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/version-reconciliation-20260827.md).
2. Installer WordPress, bbPress et les dépendances nécessaires depuis leurs sources officielles, puis installer les deux ZIP RC3 après vérification de leurs hashes.
3. Relever dans une fiche d’environnement les versions, le serveur web, la base, le cache, le CDN éventuel, les limites d’upload, les tâches planifiées et tout réglage non versionné.
4. Appliquer la [procédure d’installation propre et de restauration isolée](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/clean-install-and-recovery-procedure-20260827.md).
5. Rejouer **T03 à T16** de la [matrice de tests](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/test-matrix-20260827.md), uniquement avec des données synthétiques, et détruire ces données à la fin de la campagne.
6. Restaurer une sauvegarde complète de laboratoire sur une **seconde** instance isolée. Une sauvegarde déclarée existante ne vaut pas preuve de restauration.
7. Contrôler cron hôte, IP réelle derrière CDN, rate-limit, cache, journaux, accessibilité humaine et performances laboratoire ; consigner comme « non vérifié » tout contrôle inaccessible.

## 6. Ce qui n’est pas encore validé

Les contrôles ci-dessous ne sont pas fermés par la CI ni par les validations statiques. Ils doivent rester ouverts jusqu’à production des preuves indiquées dans le [registre des risques](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/risk-register-20260827.md).

| Risque / contrôle | Statut à la passation | Preuve indispensable |
|---|---|---|
| Installation propre RC3 | Non exécutée dans la présente itération | Journal d’installation et fiche d’environnement. |
| T03–T16 dans une installation RC3 neuve | Non exécutée dans la présente itération | Matrice complétée, journaux d’import, permissions et nettoyage. |
| Restauration isolée | Ouvert | Restauration réussie sur une seconde instance et checklist post-restauration. |
| Cron hébergeur | Ouvert | Exécutions observées, journaux et absence de tâche échouée. |
| CDN, IP réelle et rate-limit | Ouvert | Test externe avec IP transmise confirmée et comportement documenté. |
| Accessibilité humaine | Ouvert | Recette mobile, zoom, clavier et lecteur d’écran avec utilisateurs représentatifs. |
| Performance terrain | Ouvert | Instrumentation minimisée et observations de bêta fermée. |
| Domaine final et e-mail transactionnel | Ouvert, hors production | HTTPS, expéditeur autorisé, SPF/DKIM/DMARC et tests explicitement autorisés. |

## 7. Restrictions non négociables

Ne pas retirer `noindex, nofollow`. Ne pas changer le domaine. Ne pas envoyer d’e-mail réel. Ne pas restaurer sur une instance active. Ne pas importer de données réelles. Ne pas ajouter à GitHub des sauvegardes, exportations SQL, images de navigateur, journaux bruts, cookies, comptes, mots de passe, clés SMTP/API ou détails d’hébergement.

Les accès administrateur et les sauvegardes requis pour le laboratoire doivent être transmis au prochain développeur via un canal privé approuvé. Le dépôt GitHub public reste la source de vérité pour le code, les artefacts gelés et les documents reproductibles — jamais pour les secrets ou les données d’exploitation.

## 8. Décision de passation

> **Référence RC3 : validée statiquement et par CI. Validation applicative et opérationnelle : non encore prouvée. Décision : NO-GO production, et pas de GO bêta fermée avant installation propre, permissions, import, restauration isolée et risques fondamentaux documentés.**

Les tags `atelier-prebeta-rc1` et `atelier-prebeta-rc2` sont conservés pour la traçabilité de leurs échecs de CI respectifs, mais ils ne doivent jamais être installés. Toute anomalie nouvelle doit être documentée avant correction ; la correction doit ensuite être portée par un nouveau commit et une nouvelle référence, jamais par une modification silencieuse de RC3.

## Références

1. [Dépôt public Atelier](https://github.com/mamalakbira1-eng/atelier-wordpress-forum)
2. [Dossier de validation développeur RC3](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/developer-validation-handoff-rc3-20260827.md)
3. [Procès-verbal de reproductibilité RC3](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/reproducibility-validation-20260827.md)
4. [Procédure de laboratoire et restauration isolée](https://github.com/mamalakbira1-eng/atelier-wordpress-forum/blob/atelier-prebeta-rc3/docs/clean-install-and-recovery-procedure-20260827.md)
