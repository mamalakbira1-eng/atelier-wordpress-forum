# Validation staging — 25 août 2026

Source : https://springgreen-panther-771782.hostingersite.com/wp-login.php?atelier_validation=login_0424c

La page wp-login.php charge bien `https://springgreen-panther-771782.hostingersite.com/wp-content/themes/atelier-0.4.4/login.css?ver=0.4.24` et affiche l’introduction Atelier, mais la réponse HTTP est 500 et contient « Il y a eu une erreur critique sur ce site. » Le formulaire reste visible, avec identifiant, mot de passe, mémorisation et bouton « Se connecter ».

Source : https://springgreen-panther-771782.hostingersite.com/forums/topic/%d9%83%d9%8a%d9%81-%d9%86%d8%ad%d8%A7%d9%81%d8%b8-%d8%b9%d9%84%d9%89-%d9%88%d8%B6%d9%88%d8%AD-%d8%a7%d9%84%d9%85%d8%b9%d8%b1%d9%81%d8%a9-%d8%af%d8%a7%d8%ae%d9%84-%d9%81%d8%b1%d9%8a%d9%82-%d8%a7%d9%84%d8%b9%d9%85%d9%84/

Le lien canonique ouvert depuis la liste publique fonctionne. Le sujet arabe affiche un titre RTL, un message initial arabe et trois réponses arabes. La liste publique indique 3 réponses et 42 votes utiles.

Constat technique : la suppression du filtre récursif `lostpassword_url` et du hook `login_footer` dans le code local n’a pas encore supprimé le 500 du staging lors du test suivant ; le prochain diagnostic doit donc isoler les extensions ou un autre callback chargé sur wp-login.php. Le plugin actif Premium Forum Core est visible dans `wp-admin/plugins.php` et dépend de bbPress.

Source : https://springgreen-panther-771782.hostingersite.com/wp-admin/plugins.php

La page Extensions indique 7 extensions au total, 6 activées et 1 désactivée. Premium Forum Core apparaît activé, version 0.3.0, avec la description « SEO LLM-first, profils, compteurs historiques et migration CSV sécurisée pour bbPress. »

Source : https://springgreen-panther-771782.hostingersite.com/wp-admin/themes.php

Le thème actif est Atelier dans le dossier `atelier-0.4.4`. Les copies `atelier-0410` à `atelier-0424` sont aussi installées ; les cartes de thèmes ne fournissent pas toutes une image de prévisualisation.

Source : https://springgreen-panther-771782.hostingersite.com/wp-admin/update.php?action=upload-theme

WordPress a confirmé la comparaison de l’archive de remplacement : thème installé Atelier version 0.4.24 et thème téléversé Atelier version 0.4.24. L’archive contient le dossier `atelier-0.4.4`.

Source : https://springgreen-panther-771782.hostingersite.com/wp-login.php?atelier_validation=hooks_off

Après un remplacement de diagnostic sans hooks login dans le code local, la réponse testée par curl restait HTTP 500 et contenait encore le message critique, mais il faut confirmer que WordPress a réellement finalisé le remplacement de l’archive avant d’en tirer une conclusion définitive.


## Résultats définitifs après correction

- Avec Atelier et Premium Forum Core réactivés, `wp-login.php` répond HTTP 200 sans message d’erreur critique. Les marqueurs `atelier-login-intro` et `atelier-login-back` sont présents.
- Le titre, le message initial et les réponses arabes utilisent `Noto Naskh Arabic`, `Tahoma`, `Arial`, `sans-serif`, avec `dir="rtl"` et alignement à droite sur les blocs arabes.
- Après réactivation de Premium Forum Core, la barre affiche bien Répondre, Partager, Voter utile et Suivre ; le sujet affiche 42 votes et les réponses affichent 18, 27 et 11 votes utiles.
- LiteSpeed Cache, Hostinger Tools et Hostinger Reach ont été restaurés. Hostinger Easy Onboarding reste désactivée comme avant le diagnostic.
- Le lien direct arabe peut renvoyer une ancienne 404 mise en cache ; le lien canonique ouvert depuis la liste publique fonctionne et le cache LiteSpeed a été purgé lors des réactivations.

## Validation parité React 0.4.26 — 25 août 2026

URL contrôlée : https://springgreen-panther-771782.hostingersite.com/forums/topic/regles-repondre-forum-metier/?atelier_validation=react_parity_0426

Le thème `atelier-0426` a été installé puis activé. WordPress a confirmé le remplacement et LiteSpeed a affiché la purge des caches. La page publique expose les quatre actions dans l’ordre de la preview React : Répondre, Suivre, Partager, Enregistrer. Le message initial affiche séparément le repère « Message initial », l’auteur et le rang, la date, le contenu, les upvotes et la source stable. La colonne droite expose la carte Lecture claire, les repères du sujet, la figure, les sujets à poursuivre avec liens bbPress et la carte Collection.

Le panneau « Lecture claire » n’est pas un sujet : il explique la structure éditoriale et lisible par machine du sujet, puis son bouton copie le lien canonique. Les sujets de la section « À poursuivre » pointent vers leurs permaliens bbPress. Le titre arabe conserve Noto Naskh Arabic mais son plafond a été réduit dans la version 0.4.26.
