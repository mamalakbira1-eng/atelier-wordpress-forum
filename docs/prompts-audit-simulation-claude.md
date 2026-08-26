# Prompts d’audit et de simulation — Atelier WordPress

## Mode d’emploi

Ces prompts sont conçus pour faire auditer le thème **Atelier**, le plugin **Premium Forum Core**, bbPress et leur intégration WordPress par Claude. Ils doivent être utilisés sur une copie du dépôt GitHub et sur un environnement de staging isolé, jamais directement sur la production.

Pour chaque audit, fournir à Claude le code concerné, les versions exactes de WordPress/PHP/bbPress, les journaux pertinents et les résultats de tests. Exiger des preuves précises : chemin du fichier, numéro de ligne, requête, réponse HTTP, capture ou métrique. Claude ne doit jamais déclarer un point « conforme » uniquement parce qu’il n’a pas trouvé de problème.

> **Règle de sortie obligatoire pour chaque anomalie :** sévérité `P0/P1/P2/P3`, impact, preuve, scénario de reproduction, cause probable distinguée de la cause prouvée, correction proposée, risque de régression et test de non-régression.

Les tests doivent utiliser des noms, adresses e-mail et contenus synthétiques. Aucun mot de passe, cookie, token, export SQL réel ou donnée personnelle ne doit être copié dans Claude ou dans le dépôt public.

---

# Partie I — 10 prompts d’audit senior

## Prompt 1 — Audit global de niveau production

```text
Tu es un architecte WordPress senior chargé d’un audit de préproduction. Audite l’intégralité du thème Atelier, du plugin Premium Forum Core et de leur intégration bbPress.

Contexte cible : WordPress 7.1, PHP 8.3, bbPress 2.6, LiteSpeed Cache, forum public fortement orienté SEO et lisibilité LLM, contenu français et arabe RTL. Le dépôt contient le thème, le plugin, les fixtures, les archives release et la documentation.

Analyse séparément : architecture, sécurité, qualité PHP, hooks WordPress/bbPress, UX, accessibilité, performance, SEO, JSON-LD, lisibilité LLM, import CSV, modération, notifications, inscription et compatibilité cache.

Ne modifie aucun fichier. Commence par cartographier les composants et les flux. Ensuite, établis un tableau P0/P1/P2/P3 avec pour chaque constat le fichier, la ligne, la preuve, l’impact, la cause prouvée ou seulement probable, le correctif minimal et le test de non-régression. Termine par une roadmap en trois étapes : bloquant avant staging, nécessaire avant production, amélioration ultérieure.

Interdis-toi toute affirmation non vérifiée. Si une information manque, écris « non vérifiable avec les éléments fournis » et donne le test permettant de la vérifier.
```

## Prompt 2 — Audit sécurité WordPress, PHP et données

```text
Réalise un audit de sécurité offensif mais non destructif du thème Atelier et de Premium Forum Core. Ne lance aucune action destructive et ne tente pas d’exfiltrer de secrets.

Contrôle notamment : nonces, capacités et contrôles de rôles, validation et échappement des entrées, SQL direct, uploads, CSV, XSS stockée et réfléchie, CSRF, IDOR, fuite d’e-mails, enumeration de pseudos, rate limiting, brute force, réinitialisation de mot de passe, vérification e-mail, codes temporaires, logs, erreurs PHP, headers, cookies, cache de pages privées et exposition de données dans JSON-LD.

Examine chaque endpoint AJAX, chaque action POST, chaque route publique et chaque écran administratif. Vérifie que les modérateurs ne peuvent pas dépasser leurs capacités et que les administrateurs conservent la visibilité complète.

Retourne : 1) matrice menace → surface → contrôle existant → lacune ; 2) findings P0/P1/P2/P3 avec preuves de code ; 3) tests de sécurité reproductibles en staging ; 4) correctifs précis. Ne propose jamais de désactiver une protection pour faire passer un test.
```

## Prompt 3 — Architecture, scalabilité et maintenabilité

```text
Évalue la scalabilité et la maintenabilité d’Atelier/PFC pour un forum passant de quelques centaines à plusieurs centaines de milliers de sujets, réponses, membres, votes et notifications.

Analyse le modèle de données WordPress, les user meta/post meta, les requêtes bbPress, les boucles de sujets, les compteurs, les votes, les suivis, les notifications, les imports CSV, les journaux et les actions de modération. Identifie les N+1 queries, les autoloads excessifs, les transients mal invalidés, les opérations synchrones trop longues et les risques de concurrence.

Pour chaque risque, indique l’ordre de grandeur concerné, le chemin de code, la requête ou boucle responsable, la stratégie compatible avec WordPress et bbPress, ainsi que les compromis. Distingue une optimisation raisonnable d’une réécriture prématurée.

Produis une architecture cible pragmatique : ce qui reste dans WordPress, ce qui doit être indexé, ce qui peut devenir asynchrone et ce qui nécessite une migration progressive. Fournis un plan de mesure avant toute optimisation.
```

## Prompt 4 — Compatibilité WordPress et bbPress

```text
Audite la compatibilité réelle du thème Atelier et du plugin Premium Forum Core avec WordPress 7.1 et bbPress 2.6.

Vérifie les templates bbPress surchargés, les hooks et filtres utilisés, les priorités, les signatures de callbacks, les types de post forum/topic/reply, les statuts, les capacités, les permaliens, les formulaires natifs, les profils, les archives et les mécanismes de pagination. Cherche les conflits possibles avec un autre thème, un plugin de cache, un plugin SEO et les extensions WordPress classiques.

Pour chaque surcharge, explique ce qui dépend d’une API stable et ce qui dépend d’un détail interne fragile. Vérifie les chemins de fallback lorsque bbPress est désactivé ou mis à jour.

Livre une matrice de compatibilité et un plan de tests pour : installation neuve, activation, désactivation, changement de thème, changement de permaliens, cache actif, utilisateur non connecté, membre, modérateur et administrateur.
```

## Prompt 5 — Audit SEO selon les recommandations Google

```text
Audite le thème et le plugin selon les recommandations officielles de Google Search Central, sans promettre de classement et sans inventer de signal SEO.

Contrôle : indexabilité, robots.txt, sitemaps, canonicals, titres, meta descriptions, hiérarchie H1-H6, liens internes, pagination, facettes et recherche interne, URLs bbPress, contenus dupliqués, pages privées ou en attente, performance Core Web Vitals, mobile, données structurées, images, liens et textes d’ancrage.

Vérifie particulièrement que les sujets en attente de modération ne sont pas exposés aux moteurs, que les dates historiques importées ne sont pas remplacées par la date d’import, et que les données structurées reflètent uniquement le contenu réellement visible.

Pour chaque point, donne la recommandation officielle concernée, la preuve dans le code ou le HTML rendu, le risque SEO, la correction et la méthode de validation avec un crawler ou l’outil de test adapté. Distingue impératif technique, bonne pratique et hypothèse.
```

## Prompt 6 — Lisibilité LLM, HTML sémantique et JSON-LD

```text
Évalue Atelier comme une interface « LLM-first ». Le but est que le contenu d’un sujet soit facilement segmentable et correctement interprétable par un humain, un moteur de recherche ou un modèle de langage.

Analyse le HTML rendu et le code source. Vérifie la séparation explicite entre titre, forum, auteur, rôle, message initial, réponses, dates, source, votes, statut de modération et actions. Contrôle les landmarks, les titres, les attributs lang/dir, les identifiants stables, les liens canoniques, les textes d’interface et l’ordre DOM.

Audite le JSON-LD : type choisi, cohérence avec le HTML visible, auteur, dates historiques, nombre de réponses, votes, fil d’Ariane et conditions de génération de QAPage/Question/Answer. `acceptedAnswer` ne doit exister que lorsqu’une réponse a réellement été acceptée par un modérateur selon la règle métier.

Fournis cinq exemples de HTML problématique et leur version améliorée. Propose aussi un protocole de lecture automatique : quelles informations un extracteur doit retrouver et dans quel ordre.
```

## Prompt 7 — Performance, légèreté et cache

```text
Réalise un audit de performance front-end et back-end en privilégiant un thème léger, sans dépendances inutiles et sans effets visuels coûteux.

Mesure ou estime séparément : poids HTML/CSS/JS, nombre de requêtes, images, polices, CSS critique, scripts bloquants, layout shifts, temps serveur, requêtes SQL, cache de page, cache objet, invalidation, AJAX et pages privées. Vérifie que la présence de LiteSpeed ne met pas en cache une page personnalisée, une notification, un nonce ou une donnée membre d’un autre utilisateur.

Contrôle le mobile et un desktop lent. Cherche les doublons CSS, les bibliothèques chargées globalement, les images trop grandes, les animations non nécessaires et les sélecteurs fragiles. Donne un budget de performance chiffré et un protocole reproductible.

Classe les optimisations par gain attendu, risque et coût. Ne recommande pas une technologie externe ou un moteur de recherche distribué sans démontrer le besoin par des mesures.
```

## Prompt 8 — Accessibilité et utilisabilité pour une personne de 85 ans

```text
Audite l’UI/UX comme si l’utilisateur principal était une personne de 85 ans, avec une vision ou une motricité diminuée, peu familière avec les conventions modernes et susceptible d’utiliser un zoom important.

Contrôle : taille et contraste des textes, densité, largeur des lignes, hiérarchie, libellés en langage clair, boutons reconnaissables, zones cliquables, focus clavier, ordre de tabulation, messages d’erreur, confirmation des actions irréversibles, états chargement/succès/échec, scroll desktop et mobile, zoom 200 %, navigation sans souris et lecteur d’écran.

Teste particulièrement : connexion, inscription, code e-mail, recherche, création d’un sujet, réponse, vote, suivi, partage, notifications, modération et import CSV. Vérifie que chaque icône a un libellé accessible et qu’aucune couleur n’est le seul moyen de comprendre un état.

Donne une note argumentée par parcours, les obstacles P0/P1/P2/P3, des formulations de microcopy plus simples et des corrections CSS/HTML précises. Préserve l’identité premium sans sacrifier la compréhension.
```

## Prompt 9 — UI/UX premium, responsive, français et arabe RTL

```text
Réalise un audit visuel et interactionnel du thème Atelier en le comparant au prototype de référence fourni séparément. Ne juge pas seulement l’esthétique : vérifie la cohérence des états et la compréhension des actions.

Contrôle l’accueil, la recherche et ses suggestions, les espaces, la page sujet, le rail d’index, le message initial, les cartes de réponses, la colonne droite, les votes, le suivi, le partage, le profil, l’espace membre, la connexion, l’inscription et la modération.

Vérifie le responsive à 320, 375, 768, 1024 et 1440 pixels, le zoom, les contenus longs, les titres arabes, les réponses arabes, les mélanges français/arabe, les dates et les nombres RTL. Repère les boutons encombrés, les liens qui ressemblent à des placeholders, les zones sans destination, les dépassements horizontaux et les hauteurs fixes.

Retourne une matrice composant → état normal/hover/focus/actif/erreur/chargement → problème → correction. Priorise les corrections visibles et structurantes plutôt que les micro-ajustements décoratifs.
```

## Prompt 10 — Qualité de livraison, tests, confidentialité et exploitation

```text
Audite la capacité du projet à être repris par un autre développeur et livré sans surprise.

Contrôle l’arborescence, les versions, les archives ZIP, les changelogs, les fixtures, les scripts, les tests, les migrations implicites, les dépendances, les règles Git, les fichiers ignorés, les secrets, les logs, les instructions d’installation et les différences entre code local, staging et release.

Vérifie que les fixtures ne contiennent pas de mots de passe, cookies, e-mails privés ou données réelles. Contrôle que la documentation distingue les tests locaux, les tests staging et les points non vérifiés. Vérifie également la procédure de rollback, la purge cache, la sauvegarde et la restauration.

Produis : 1) checklist de release ; 2) liste des écarts documentation/code ; 3) liste des tests manquants ; 4) proposition de CI minimale avec lint PHP, scan de secrets et validation des ZIP ; 5) rapport « prêt pour staging / prêt pour production » avec justification.
```

---

# Partie II — 10 prompts de simulation fonctionnelle

## Prompt 1 — Installation et activation propre

```text
Sur un WordPress de staging neuf, simule l’installation d’Atelier et de Premium Forum Core avec bbPress.

Utilise uniquement des données synthétiques. Vérifie les prérequis, l’activation, les dépendances, les pages créées, les permaliens, les rôles, les capabilities, les menus, les assets et les messages d’erreur. Teste aussi l’activation dans l’ordre inverse et la désactivation de bbPress.

Pour chaque étape, capture URL, statut HTTP, écran visible, log PHP, requêtes principales et résultat attendu/réel. Vérifie qu’une erreur de prérequis ne laisse pas une installation partiellement active. À la fin, fournis un rapport d’installation et une procédure de rollback.
```

## Prompt 2 — Inscription complète et vérification e-mail

```text
Simule le parcours complet d’inscription avec un compte synthétique : prénom, nom, e-mail example.test, pseudo proposé, mot de passe valide et confirmation.

Teste successivement : pseudo disponible, pseudo déjà utilisé, suggestion alternative, mot de passe trop court, confirmation différente, e-mail invalide, nonce expiré, double soumission, rechargement de page, code correct, code incorrect, code expiré, cinq tentatives dépassées et renvoi du code.

Intercepte l’e-mail dans une boîte de test ou un mail catcher, sans envoyer de message réel. Vérifie que le code n’est jamais affiché dans le HTML public ou les logs, que le compte reste bloqué avant vérification, que l’échec SMTP affiche un message propre et que le compte incomplet est supprimé.

Retourne la timeline complète et les preuves de chaque transition.
```

## Prompt 3 — Disponibilité du pseudo et concurrence

```text
Simule deux visiteurs qui choisissent simultanément le même pseudo, avec des délais réseau différents et des réponses AJAX arrivant dans le désordre.

Teste aussi les caractères accentués, les espaces, les caractères arabes, les majuscules, les chaînes très longues, les pseudos réservés, les pseudos proches et les tentatives de contourner la validation côté navigateur.

Vérifie que l’indication « disponible/déjà utilisé » est compréhensible, que les alternatives sont sélectionnables, que le serveur revalide toujours au submit et qu’un seul compte gagne en cas de concurrence. Aucun compte de test réel ne doit être conservé après la simulation.

Documente chaque requête, nonce, réponse et décision serveur.
```

## Prompt 4 — Matrice des rôles et modération

```text
Crée trois utilisateurs synthétiques : visiteur, membre ordinaire et modérateur Atelier, plus un administrateur séparé. Simule la création d’un sujet et d’une réponse dans chaque rôle.

Vérifie le cycle membre ordinaire → En attente → lecture par modérateur → publier, refuser, supprimer. Vérifie la visibilité publique avant et après chaque action, les notifications, les capacités WordPress/bbPress, les URLs directes et les tentatives d’accès par ID.

Teste qu’un membre ne peut pas publier ou modérer directement, qu’un modérateur ne peut pas administrer des utilisateurs ou modifier des réglages hors périmètre, et que l’administrateur voit tout. Contrôle les états concurrents : deux modérateurs valident en même temps, ou un sujet est supprimé pendant sa lecture.

Retourne une matrice rôle × action × résultat attendu × résultat réel.
```

## Prompt 5 — Parcours forum et interactions communautaires

```text
Sur des sujets synthétiques français et arabes, simule : ouverture d’un forum, recherche, ouverture d’un sujet, lecture du message initial, réponse, réponse à une réponse, vote utile, retrait du vote, suivi, retrait du suivi, partage et copie du lien.

Teste chaque action connecté/non connecté, la double activation, le rafraîchissement, le retour arrière, un réseau lent et une réponse AJAX en erreur. Vérifie les compteurs, l’état aria-pressed, les notifications, les permissions et l’absence de doublons.

Teste un sujet avec zéro, une, trois et cinquante réponses. Vérifie le tri par date/upvotes, la pagination ou le chargement progressif, les ancres stables et l’absence de perte de contenu.
```

## Prompt 6 — Import CSV, dry run, erreurs et rollback

```text
Prépare un petit jeu CSV synthétique couvrant forums, utilisateurs, sujets et réponses. Lance d’abord un dry run, puis l’import confirmé, puis un rollback ciblé.

Teste : colonnes réordonnées, BOM UTF-8, accents, arabe RTL, dates historiques valides, dates invalides, votes négatifs, votes non numériques, références inconnues, doublons, e-mails dupliqués, pseudo dupliqué, mot de passe en clair, champ password_hash autorisé selon la règle documentée, fichier vide, fichier trop grand et interruption en cours d’import.

Vérifie que le dry run ne modifie rien, que chaque erreur indique ligne/colonne, que l’import est idempotent selon la règle annoncée, que les dates du fichier sont conservées et que le rollback ne supprime pas des contenus étrangers au job.

Fournis le manifeste, le journal, les objets créés, les objets ignorés et les preuves avant/après.
```

## Prompt 7 — Crawl SEO et validation JSON-LD

```text
Avec un crawler de staging non indexant, parcours l’accueil, les forums, les sujets publiés, les sujets en attente, les profils, la recherche et les pages privées.

Vérifie les statuts HTTP, canonicals, robots, sitemap, titres, H1, liens internes, pagination, contenu dupliqué, données historiques, langue et direction du texte. Extrait le JSON-LD et compare chaque propriété au HTML visible.

Crée un sujet sans réponse acceptée, un sujet avec réponse publiée et un sujet avec réponse réellement acceptée par un modérateur. Confirme que `acceptedAnswer` et QAPage ne sont présents que dans le dernier cas conforme à la règle métier.

Retourne les divergences URL par URL, avec extrait HTML, JSON-LD, statut et correction recommandée.
```

## Prompt 8 — Accessibilité et compréhension par une personne âgée

```text
Simule une utilisation complète avec clavier uniquement, zoom 200 %, viewport 320 px, viewport desktop court, contraste réduit et lecteur d’écran.

Réalise les parcours connexion, inscription, code e-mail, recherche, ouverture d’un sujet, réponse, vote, suivi, notification et déconnexion. Vérifie que l’utilisateur comprend où il se trouve, quoi faire ensuite, si son action a réussi et comment revenir en arrière.

Note les problèmes de focus, scroll, ordre de tabulation, taille de cible, texte trop petit, icône ambiguë, contraste, animation, message d’erreur et confirmation. Pour chaque blocage, donne le chemin exact et une reproduction déterministe.
```

## Prompt 9 — Performance, cache et charge contrôlée

```text
Simule une navigation anonyme et authentifiée avec cache LiteSpeed actif, puis désactivé. Mesure l’accueil, une archive de forum, un sujet riche, un profil, l’espace membre, les notifications et la recherche.

Vérifie qu’aucun contenu privé, nonce, compteur personnel ou notification ne fuit entre utilisateurs. Mesure le poids, les requêtes, le temps serveur, les requêtes SQL principales, le LCP, CLS et INP si disponibles.

Réalise ensuite une charge contrôlée avec un nombre de visiteurs explicitement limité et documenté. Ne lance pas de test de stress sur un site public. Cherche les erreurs, timeouts, verrous, duplications d’actions et incohérences de compteurs. Termine par une recommandation de budget et d’optimisation basée sur les mesures.
```

## Prompt 10 — Régression, panne et reprise

```text
Construis une simulation de régression après mise à jour du thème ou du plugin. Commence par un état connu et sauvegardé, puis teste les parcours critiques : accueil, recherche, forum, sujet, réponse, inscription, vérification, vote, suivi, notification, modération et import.

Injecte uniquement des pannes contrôlées en staging : SMTP indisponible, AJAX HTTP 500, nonce expiré, cache obsolète, fichier CSV interrompu, utilisateur supprimé pendant une action et réponse lente. Vérifie que l’interface affiche une erreur compréhensible, ne perd pas le contenu saisi, ne crée pas de doublon et ne laisse pas de compte ou job incomplet.

Compare avant/après les statuts HTTP, HTML, JSON-LD, capacités et métriques. Teste ensuite la restauration de l’archive précédente et confirme que les données ne sont pas détruites. Retourne un rapport de régression avec décision : continuer, corriger avant release ou rollback.
```

---

# Format de rapport à exiger de Claude

Pour empêcher les conclusions vagues, utiliser cette structure pour chaque réponse :

| Champ | Exigence |
|---|---|
| Verdict | Conforme, non conforme, non vérifiable ou bloqué |
| Sévérité | P0, P1, P2 ou P3, avec justification |
| Preuve | Fichier/ligne, requête, réponse, capture ou métrique |
| Reproduction | Préconditions et étapes déterministes |
| Cause | Prouvée, probable ou inconnue ; ne pas mélanger les trois |
| Impact | Utilisateur, sécurité, SEO, performance, données ou exploitation |
| Correctif | Modification minimale et compatible avec WordPress/bbPress |
| Régression | Parcours à rejouer après correction |
| Décision | Accepter, corriger, surveiller ou bloquer la release |

## Références officielles

[1]: https://developers.google.com/search/docs/fundamentals/seo-starter-guide "Google Search Central — SEO Starter Guide"

[2]: https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data "Google Search Central — Introduction aux données structurées"

[3]: https://developers.google.com/search/docs/crawling-indexing/overview "Google Search Central — Crawling et indexation"

[4]: https://developers.google.com/search/docs/appearance/page-experience "Google Search Central — Page experience"

[5]: https://www.w3.org/WAI/standards-guidelines/wcag/ "W3C — Web Content Accessibility Guidelines"
