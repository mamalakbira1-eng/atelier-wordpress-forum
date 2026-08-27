# Atelier — procès-verbal de validation reproductible

**Référence visée :** `atelier-prebeta-rc2`.
**Périmètre :** vérifications statiques et d’intégrité effectuées sur le miroir assaini du dépôt public. Aucune installation WordPress, restauration, mutation de préproduction, envoi réel ni consultation de sauvegarde n’a été exécuté pour cette validation.

## Contrôles réalisés

| Contrôle | Commande de référence | Résultat |
|---|---|---|
| Hash des archives | `sha256sum --check ARTIFACTS-SHA256.txt` | Les deux archives de référence sont conformes. |
| Intégrité ZIP | `unzip -t release/*.zip` | Les deux archives sont lisibles. |
| Racines d’installation | `unzip -Z1` sur chaque archive | `atelier/` pour le thème et `premium-forum-core/` pour l’extension. |
| Parité source/archive | Extraction temporaire puis `diff -qr` | Les répertoires contenus dans les archives sont identiques aux sources publiées. |
| Syntaxe PHP | `find atelier premium-forum-core -name '*.php' -print0 | xargs -0 -n1 php -l` | 24 fichiers analysés sans erreur sur PHP 8.3. |
| Harnais CSV | `php tools/test-pfc-validation.php test-fixtures/valid test-fixtures/invalid` | `pass=true`; le pack invalide déclenche notamment la détection de relation absente, de mot de passe en clair et de données de date/vote invalides sans fatal. |
| Marqueurs privés connus | Recherche ciblée des littéraux interdits dans le miroir public | Aucun marqueur privé connu trouvé. |

La première candidate `atelier-prebeta-rc1` a échoué en CI parce qu’une date invalide sous PHP moderne levait une exception non couverte par le validateur. PFC 0.4.19 corrige ce point H09 en interceptant `Throwable`; `atelier-prebeta-rc2` remplace rc1 sans réécrire son historique. Ces contrôles sont aussi définis dans la [CI d’intégrité](../.github/workflows/artifact-integrity.yml). Celle-ci doit être observée verte sur le commit et le tag rc2 réels avant toute décision de bêta.

## Limite explicitement conservée

Le sandbox de validation ne fournit ni Docker ni WP-CLI; il ne permet donc pas d’établir une instance WordPress isolée complète à partir du dépôt sans ajouter une infrastructure non prévue. Une restauration exigera également une sauvegarde de laboratoire transmise par un canal privé autorisé, qui ne peut pas être publiée dans GitHub.

> **Conséquence :** les contrôles statiques et d’intégrité sont validés; l’installation propre, la restauration isolée, le cron hébergeur et la chaîne CDN/rate-limit restent non validés. Les risques R01 à R03 demeurent ouverts et bloquent la bêta fermée.

La [procédure d’installation et de restauration](clean-install-and-recovery-procedure-20260827.md) définit le protocole à exécuter sur une instance appropriée. Les [tests à rejouer](test-matrix-20260827.md) et le [registre des risques](risk-register-20260827.md) restent les sources de décision.
