# Atelier — procédure d’installation propre, recette et restauration isolée

Cette procédure est destinée à une instance de laboratoire ou de préproduction **explicitement autorisée**. Elle ne doit jamais être appliquée à une instance active sans plan de retour arrière approuvé. Aucun secret, cookie, sauvegarde, URL privée ou destinataire réel ne doit être ajouté à ce dépôt.

## 1. Préparer l’instance isolée

Installez une instance WordPress neuve avec une version de PHP consignée. Ajoutez bbPress 2.6.14, puis installez les deux archives du tag `atelier-prebeta-rc2` uniquement après avoir vérifié `ARTIFACTS-SHA256.txt`. Confirmez les répertoires `atelier/` et `premium-forum-core/`, puis notez dans le procès-verbal les versions réellement visibles dans l’administration.

Maintenez l’instance hors indexation, utilisez un service de capture d’e-mails de test, créez seulement des identités synthétiques et empêchez tout envoi vers des destinataires non autorisés. Installez et configurez le cache/CDN nécessaire à la représentation de l’environnement cible, mais ne concluez pas sur le cron, l’IP visiteur ou les en-têtes edge sans une preuve propre à cette infrastructure.

## 2. Installer et valider le lot

```bash
git clone https://github.com/mamalakbira1-eng/atelier-wordpress-forum.git
cd atelier-wordpress-forum
git checkout atelier-prebeta-rc2
sha256sum --check ARTIFACTS-SHA256.txt
find atelier premium-forum-core -name '*.php' -print0 | xargs -0 -n1 php -l
php tools/test-pfc-validation.php test-fixtures/valid test-fixtures/invalid
```

Le clone ne contient pas le noyau WordPress, bbPress ni les réglages de l’infrastructure. Ces dépendances doivent être installées de façon déclarée dans l’instance isolée. Ensuite, exécutez T01 à T14 de la [matrice de tests](test-matrix-20260827.md) et conservez un procès-verbal assaini comportant la référence Git, les versions, le résultat et les écarts.

## 3. Restaurer sans toucher l’instance source

Avant une restauration, produisez une sauvegarde complète de **laboratoire** comprenant base et fichiers. Stockez-la dans un emplacement séparé et autorisé, hors GitHub. Créez une seconde instance WordPress isolée, avec envoi d’e-mail vers le seul captureur de test. Vérifiez le hash ou l’intégrité de la sauvegarde selon l’outil employé, puis restaurez-la uniquement dans cette seconde instance.

Après restauration, vérifiez à minima la connexion, les rôles, forums, sujets, réponses, compteurs, thème, PFC, tâches planifiées, URLs, cache et absence d’envoi réel. Rejouez T03, T06, T07 et T14, consignez les différences et supprimez les données synthétiques. Une restauration qui ne satisfait pas ces conditions conserve le risque R01 ouvert.

## 4. Nettoyer et décider

Après chaque campagne, supprimez les comptes synthétiques, sujets, réponses, votes, suivis, notifications et fichiers de test créés. Vérifiez la corbeille avant suppression définitive afin de préserver les éléments non liés. Mettez ensuite à jour la matrice de tests et le registre de risques.

La décision reste **NO-GO production** tant que les risques R01, R02, R03 et R06 sont ouverts. La bêta fermée ne peut être considérée qu’après une restauration isolée réussie, une exécution planifiée prouvée, un contrôle CDN/rate-limit établi et une validation fonctionnelle/accessibilité adaptée à son périmètre.
