# Pack Claude Fable — audits et simulations senior pour Atelier

**Usage :** copiez un prompt à la fois dans Claude Fable, après lui avoir donné accès au thème, au plugin Premium Forum Core et à un **staging WordPress non public**. Remplacez les valeurs entre crochets uniquement par des données non sensibles. Ce pack est volontairement plus exigeant qu’une liste de vérifications : chaque audit doit produire des preuves, un niveau de sévérité, une correction minimale et un test de non-régression.

## Garde-fous communs à ajouter avant chaque prompt

> Tu travailles exclusivement sur un **staging WordPress**. Ne publie rien, n’envoie aucun e-mail réel, ne divulgue aucun mot de passe, token, cookie, URL d’administration, sauvegarde, export SQL ou donnée membre. Utilise seulement des comptes, contenus et adresses e-mail synthétiques. Ne conclus jamais « sécurisé », « conforme », « accessible » ou « performant » sans preuve reproduisible. Avant toute action destructive, crée un point de retour arrière et explicite son périmètre.

Le compte-rendu demandé doit toujours distinguer le fait observé, la cause probable, la preuve, le risque et l’action recommandée. Les références Google doivent être vérifiées dans la documentation officielle, notamment les règles de SEO utile, d’indexation mobile-first, de données structurées et de Core Web Vitals.[1] [2] [3]

| Sévérité | Signification | Délai attendu |
|---|---|---|
| **P0** | Fuite, élévation de privilège, perte de données ou indisponibilité critique. | Arrêt et correction immédiate. |
| **P1** | Risque de sécurité, d’accès, de transaction ou de contenu public erroné. | Corriger avant bêta. |
| **P2** | Régression importante de qualité, SEO, performance, accessibilité ou exploitation. | Corriger avant ouverture publique. |
| **P3** | Amélioration documentée sans risque immédiat. | Planifier après stabilité. |

## Dix prompts d’audit spécialisé

### Audit 1 — Architecture WordPress, bbPress et Premium Forum Core

```text
Agis comme un architecte WordPress senior chargé d’un audit statique et dynamique du thème Atelier et du plugin Premium Forum Core reliés à bbPress.

Objectif : vérifier que les responsabilités sont correctement séparées entre thème, plugin, noyau WordPress et bbPress, sans duplication fragile ni dépendance cachée.

Examine : structure des fichiers, hooks et priorités, chargement d’assets, templates bbPress surchargés, options et métadonnées, migrations, capacités, désinstallation, compatibilité WordPress/PHP/bbPress, comportements lorsqu’un plugin dépendant est absent, et risques de mise à jour.

Ne modifie aucun code avant le rapport. Produis :
1. une carte de responsabilités par couche ;
2. les dépendances implicites et couplages problématiques ;
3. les écarts avec les conventions WordPress ;
4. une liste P0 à P3 avec fichiers et lignes concernés ;
5. pour chaque correction proposée, le test de non-régression exact à exécuter.

Ne donne pas de généralités. Toute conclusion doit citer un hook, un fichier, un template, une option ou un comportement observé.
```

### Audit 2 — Sécurité applicative et surface d’attaque

```text
Agis comme un ingénieur sécurité WordPress senior. Audite le thème Atelier et Premium Forum Core sur staging, en lecture d’abord, puis avec tests non destructifs et données synthétiques seulement.

Vérifie : contrôle de capacités, nonces et méthodes POST, validation/sanitation/escaping, XSS stockée et réfléchie, CSRF, open redirect, énumération de comptes, upload de fichiers, import CSV/ZIP, traversal, SSRF, injections SQL, XML-RPC, mots de passe d’application, REST API exposée, cache de pages privées, cookies, en-têtes HTTP, clickjacking, erreurs PHP publiques, et fuite de secrets dans le code ou les journaux.

Contrôle aussi l’anti-abus : honeypot, limitation de fréquence, renvoi de code, tentatives de vérification, votes, suivis et actions de modération.

Livre un tableau : vulnérabilité, preuve de reproduction, impact, précondition, sévérité, correctif minimal et test de régression. N’utilise jamais un vrai e-mail ni un vrai mot de passe. Si un contrôle existe mais n’est pas prouvé, classe-le « non vérifié », pas « sécurisé ».
```

### Audit 3 — Authentification, rôles, modération et anti-abus

```text
Agis comme un spécialiste IAM et communautés WordPress. Audite de bout en bout les rôles Administrateur, Keymaster bbPress, Modérateur Atelier, Participant et visiteur anonyme.

Vérifie la matrice exacte de permissions pour : inscription, confirmation, connexion, création de sujet/réponse, état pending, lecture d’un contenu pending, publication, refus, suppression, modification de rôle, vote, suivi, notification, import et rollback. Vérifie notamment qu’un membre ne peut jamais atteindre une action de modération par URL ou requête forgée, et qu’un modérateur ne reçoit pas de privilège d’administrateur WordPress.

Teste avec des comptes synthétiques distincts et nettoie-les à la fin. Vérifie que les erreurs ne révèlent pas l’existence d’un compte, que les codes expirés sont refusés et que les actions sensibles sont protégées par nonce et capacité serveur.

Livre : une matrice rôles × actions, les écarts prouvés, les traces de test sans données personnelles, et une recommandation de politique de mots de passe, de révocation et de journalisation.
```

### Audit 4 — Performance, cache et capacité de montée en charge

```text
Agis comme un ingénieur performance WordPress. Mesure le staging Atelier, en navigation anonyme et connectée, sur accueil, liste de forums, archive de sujets, sujet riche, recherche, connexion et inscription.

Utilise Lighthouse mobile et bureau ou un équivalent fiable. Relève au minimum LCP, CLS, TBT, poids transféré, TTFB, ressources bloquantes, CSS/JS inutilisés, requêtes lentes et taille HTML. Ne présente pas ces données de laboratoire comme des Core Web Vitals terrain.

Vérifie aussi le cache : requête froide puis chaude, cache public pour les lecteurs anonymes, absence de cache sur connexion/session/admin, invalidation après modification, cache des assets et cohérence de l’en-tête HTML. Identifie les causes concrètes : extension, police, image, requête WordPress, asset ou script.

Pour chaque problème P1/P2 : donne la mesure avant, le correctif minimal, le risque de régression fonctionnelle, la mesure après et le seuil de sortie. Propose une stratégie réaliste de capacité pour un forum avec sujets, réponses, votes et recherches sans ajouter de service lourd non justifié.
```

### Audit 5 — Accessibilité et UX pour une personne âgée

```text
Agis comme un expert WCAG et UX inclusive. Audite Atelier pour une personne de 85 ans sur mobile et bureau, avec navigateur clavier, zoom 200 %, taille de police augmentée, contraste renforcé et contenu français/arabe.

Vérifie : ordre de tabulation, visibilité du focus, lien d’évitement, titres hiérarchisés, libellés et erreurs de formulaires, messages asynchrones, boutons nommés, contraste réel, taille/espacement des cibles tactiles, lecture par lecteur d’écran, menus, recherche à suggestions, modales, validation d’inscription, connexion, réponse, vote, suivi et modération.

Teste le contenu arabe RTL : direction, ordre visuel et clavier, alignement, police, ponctuation, mélange arabe/français et absence de chevauchement sur petit écran.

Livre un rapport WCAG pragmatique : élément, reproduction, impact utilisateur, critère concerné, sévérité, correction HTML/CSS/JS minimale et test manuel de confirmation. Ne te limite pas à un score automatisé.
```

### Audit 6 — UI, UX éditoriale et robustesse des interactions

```text
Agis comme un directeur UX senior pour un forum éditorial premium mais sobre. Audite l’interface WordPress réelle Atelier, pas seulement le code ni une maquette.

Évalue : lisibilité du sujet initial versus réponses, hiérarchie auteur/date/rang, densité des actions, compréhension des votes, suivi, partage et réponse, navigation entre accueil/forums/sujet/profil/recherche, messages vides, erreurs, états pending, conception mobile, cohérence français/RTL, et prévention des clics accidentels.

Vérifie que toute commande visible produit une action cohérente ou indique clairement qu’une connexion est requise; cherche les liens morts, les filtres trompeurs, les libellés vagues et les états qui semblent réussir sans modification réelle.

Fournis un audit avec captures ou éléments précis, parcours utilisateur, niveau de gravité, correctifs priorisés. Évite les recommandations décoratives : chaque proposition doit améliorer compréhension, confiance, vitesse ou accessibilité.
```

### Audit 7 — SEO Google, indexation et données structurées

```text
Agis comme un spécialiste technique Google Search pour un forum bbPress. Audite le HTML réellement rendu, les en-têtes, robots, sitemaps, canoniques, pagination, titres, meta descriptions, liens internes, archives et données structurées.

Vérifie particulièrement : équivalence mobile/desktop, noindex de staging, canonique auto-référente, URLs de sujets, dates historiques importées, balises de langue, contenu indexable sans dépendance JavaScript, couverture de sitemap, maillage forum→sujet→réponse, et risques de contenu dupliqué.

Valide les JSON-LD avec une prudence stricte : DiscussionForumPosting et BreadcrumbList seulement si les données visibles correspondent; aucun QAPage ni acceptedAnswer sans workflow réel d’acceptation. Compare chaque conclusion à la documentation Google actuelle.

Livre : inventaire des pages, extraits HTML/JSON-LD prouvant chaque constat, erreurs P0-P3, priorités de préproduction et checklist spécifique au passage staging→domaine final. N’exige jamais de retirer noindex sur le domaine temporaire.
```

### Audit 8 — Lisibilité LLM et qualité sémantique machine

```text
Agis comme un architecte de contenu machine-readable. Audite le forum Atelier pour vérifier qu’un LLM, un moteur de recherche et une technologie d’assistance peuvent distinguer sans ambiguïté le forum, le sujet, l’auteur, la date, le message initial, les réponses, les votes agrégés, les sources et les actions.

Analyse le HTML initial, les titres, landmarks, listes, articles, microdonnées/JSON-LD, attributs lang/dir, dates machine, liens canoniques, contenu caché, doublons et texte injecté au client. Vérifie que les interfaces décoratives n’interrompent pas le fil éditorial et que le texte arabe conserve sa langue et direction.

Teste les cas suivants : sujet court, sujet riche, contenu arabe, import historique et contenu pending non public. Vérifie que les contenus privés, actions d’administration et données personnelles ne sont pas accessibles aux crawlers.

Livre un schéma de lecture machine, des extraits de preuve, les ambiguïtés observées, les améliorations sémantiques prioritaires et leur impact sur SEO/accessibilité/LLM. Ne prétends pas qu’un balisage garantit un classement ou une réponse de LLM.
```

### Audit 9 — Intégrité des données, import CSV et rollback

```text
Agis comme un ingénieur données WordPress senior. Audite Premium Forum Core et bbPress sur les imports CSV, mapping, dry run, journal, idempotence, erreurs, rollback et nettoyage des artefacts communautaires.

Utilise uniquement un pack synthétique. Vérifie : encodage, en-têtes, dates, rangs, votes agrégés, liens forums/sujets/réponses/utilisateurs, utilisateurs existants, doublons, mots de passe en clair rejetés, limites de taille, ZIP malveillant, fichier manquant, ordre de dépendances, reprise après erreur et permissions d’import.

Exécute dry run, import réel puis rollback ciblé sur staging. Compare les compteurs avant/après et prouve que le rollback ne retire que les objets créés par le job, jamais les contenus préexistants. Vérifie ensuite l’absence de votes, suivis ou notifications orphelins.

Livre une matrice de validation de données, les chiffres avant/après anonymisés, les erreurs attendues et inattendues, la sévérité des écarts, et une procédure de restauration si le rollback applicatif ne suffit pas.
```

### Audit 10 — Exploitation, sauvegarde, mises à jour et reprise d’incident

```text
Agis comme un responsable fiabilité WordPress. Audite la capacité d’Atelier à être exploité avant une bêta fermée.

Vérifie : nombre de comptes administrateurs, extensions/thèmes inactifs, versions WordPress/PHP/bbPress/PFC/Atelier, Santé du site, WP-Cron, Action Scheduler, cache, logs disponibles, politique de mises à jour, sauvegarde locale, sauvegarde distante, rétention, chiffrement/accès au stockage, restauration isolée, plan d’incident, HTTPS et en-têtes de sécurité.

Ne télécharge aucune sauvegarde vers ton environnement de travail. Ne tente aucune restauration sur le staging actif. Si un environnement isolé existe, teste une restauration contrôlée et vérifie accueil, permaliens, administration, bbPress et PFC.

Livre un runbook de reprise, avec RPO/RTO estimés seulement s’ils sont mesurés, une liste de risques P0-P3, les alertes à surveiller, le calendrier de contrôle, et la décision nette : prêt/non prêt pour une bêta fermée.
```

## Dix prompts de simulation réelle sur WordPress

### Simulation 1 — Parcours visiteur anonyme et lecture éditoriale

```text
Sur le staging Atelier, joue le rôle d’un visiteur anonyme. Parcours accueil, forums, archive, recherche, sujet riche, profil public et pages de connexion/inscription sans créer de compte.

Vérifie les statuts HTTP, pages 404 attendues, navigation, liens, pagination, boutons de sujet, redirections vers connexion pour les actions privées, canonique, robots, cache public et absence de données pending. Teste au moins une discussion française et une discussion arabe.

Rapporte chaque résultat sous forme : action, résultat attendu, résultat observé, preuve, sévérité, correctif éventuel. Ne change aucune donnée.
```

### Simulation 2 — Inscription complète avec données synthétiques

```text
Exécute une inscription Atelier complète avec identité, pseudo et e-mail synthétiques uniquement. Vérifie suggestions de pseudo, disponibilité côté client et serveur, mots de passe, validation des champs, honeypot, limitation de fréquence, création pending, envoi dans le mail catcher, renvoi temporisé, reprise action=verify, confirmation et nettoyage des métadonnées.

Teste aussi un code invalide ou expiré sans jamais copier le code réel dans un rapport. Ne contacte aucune boîte réelle. Supprime le compte de recette après validation.

Livre le parcours nominal, les échecs contrôlés, la preuve qu’aucune adresse ne passe dans l’URL, les données créées puis supprimées et les anomalies avec sévérité.
```

### Simulation 3 — Connexion, récupération et résistance aux erreurs

```text
Avec un compte synthétique confirmé, teste connexion, déconnexion, mot de passe incorrect, identifiant inexistant, retour vers la page demandée, mot de passe oublié, session expirée et accès à Mon espace.

Vérifie que les erreurs restent utiles sans révéler si un compte existe, que la session ne fuit pas dans le cache, que les formulaires sont utilisables au clavier/mobile et que les liens WordPress natifs ne cassent pas le design Atelier.

N’envoie aucun e-mail à une adresse réelle. Supprime ou neutralise tout compte temporaire à la fin. Produis les preuves et anomalies.
```

### Simulation 4 — Membre : contribution pending et confidentialité

```text
Crée un membre synthétique avec le rôle participant. Crée un sujet puis une réponse qui doivent entrer en attente de validation.

Vérifie : affichage honnête pour l’auteur, absence totale de visibilité anonyme, 404 ou refus sur le permalien public, impossibilité d’ouvrir la file de modération, protection des actions par nonce, comportement de l’éditeur, pièces jointes si elles existent et nettoyage après le test.

Teste ensuite une tentative d’accès direct à une URL de modération et une requête forgée non destructive. Rapporte le résultat attendu/observé et les preuves sans divulguer de nonce.
```

### Simulation 5 — Modérateur : publication, refus et suppression

```text
Crée un modérateur synthétique qui n’est pas administrateur WordPress. Avec un membre synthétique, prépare plusieurs contributions pending.

Depuis le compte modérateur, vérifie l’accès à la file À valider, la publication, le refus et la suppression. Après chaque décision, contrôle la visibilité publique, les redirections, les notifications déclenchées seulement au bon statut, l’absence de privilège administrateur et la protection POST+nonce.

Nettoie comptes et contenus de recette. Livre une matrice membre/modérateur/admin avec chaque action et son résultat.
```

### Simulation 6 — Import réel, dry run et rollback ciblé

```text
Prépare un pack CSV synthétique de taille raisonnable : utilisateurs, forums, sujets et réponses, avec dates historiques, rangs et compteurs d’upvotes agrégés. N’inclus aucun mot de passe réutilisable.

Exécute le dry run, analyse les erreurs, lance l’import réel seulement si le dry run est propre, puis exécute immédiatement le rollback du job. Vérifie les compteurs avant/après, les utilisateurs matched/créés, les relations bbPress, les journaux et les permissions d’import.

Essaie aussi un pack invalide : mot de passe en clair, date invalide, fichier trop volumineux ou relation manquante. Le rapport doit démontrer que les données invalides sont rejetées et que le rollback ne supprime pas de contenu préexistant.
```

### Simulation 7 — Votes, suivis et notifications sans double comptage

```text
Avec trois comptes synthétiques séparés, teste vote sur sujet, vote sur réponse, annulation éventuelle, suivi de sujet, réponse à un sujet suivi et réponse à une réponse. Vérifie les restrictions visiteur/anonyme, les nonces, l’unicité d’un vote par utilisateur et l’absence de double notification.

Contrôle l’espace membre : upvotes reçus, notifications, liens et compteurs. Vérifie ensuite le nettoyage des artefacts après suppression d’une contribution ou d’un compte synthétique.

N’utilise aucune adresse e-mail réelle. Rapporte les compteurs avant/après et tout écart de confidentialité ou d’intégrité.
```

### Simulation 8 — Recherche, filtres, clavier, mobile et RTL

```text
Teste la recherche Atelier en français et en arabe : saisie, suggestions, flèche bas, Entrée, Échap, focus rendu au champ, absence de soumission involontaire et navigation vers le résultat.

Teste ensuite index de discussion, ancres, filtres Tout voir/Contributions/Dernières réponses, tri date/votes, pagination, responsive mobile, zoom 200 %, taille de police augmentée et contenu RTL mélangé au français.

Vérifie qu’aucun lien ne mène à une page vide, qu’aucun texte n’est tronqué et que les contrôles restent accessibles au clavier. Fournis une liste de défauts UI/UX accompagnés d’étapes de reproduction exactes.
```

### Simulation 9 — Cache, sécurité HTTP et accès privé

```text
Sur des pages publiques, compare une requête fraîche puis chaude; contrôle les directives cache, les en-têtes de sécurité, HTTPS et les canoniques. Sur connexion, inscription, profil connecté, modération et import, vérifie l’absence de cache public et de fuite d’état entre utilisateurs.

Teste XML-RPC de manière non destructive, disponibilité des mots de passe d’application, affichage d’erreurs PHP, redirection HTTP→HTTPS, frame embedding et les en-têtes de type nosniff/referrer policy. Ne teste pas d’attaque par déni de service.

Rapporte les en-têtes observés, la page concernée, la conformité attendue et toute exception. Ne mets ni URL privée, ni cookie, ni valeur de session dans le rapport.
```

### Simulation 10 — Sauvegarde distante et restauration de reprise

```text
Vérifie la chaîne de reprise Atelier. Contrôle qu’un jeu UpdraftPlus complet contient base, extensions, thèmes, téléversements, MU-plugins et autres fichiers, et qu’il est transféré vers un stockage distant autorisé.

Si un environnement WordPress isolé est explicitement disponible, restaure uniquement dans cette instance, jamais sur le staging actif. Vérifie ensuite accueil, forums, une discussion, connexion, administration, bbPress, Premium Forum Core, permaliens, cache et absence d’erreurs visibles. Mesure le délai réel et documente les écarts.

Si aucun environnement isolé n’existe, ne force pas une restauration : classe le test « bloqué », explique le prérequis exact et vérifie uniquement l’intégrité, la présence distante et les composants du jeu. Ne télécharge jamais la sauvegarde dans un environnement de travail ou un dépôt Git.
```

## Format de restitution imposé à Claude Fable

Demandez ce format à la fin de chaque prompt afin de pouvoir comparer les audits.

```text
1. Résumé exécutif : décision nette et principaux risques.
2. Périmètre réellement testé : code, rôles, URLs, appareils, données synthétiques.
3. Tableau des constats : ID, sévérité, preuve, impact, correctif minimal, test de régression.
4. Constats non vérifiés : préciser exactement pourquoi ils ne le sont pas.
5. Plan de correction ordonné : P0/P1, puis P2, puis P3.
6. Éléments supprimés ou nettoyés après test.
7. Décision : prêt / prêt sous conditions / non prêt pour la prochaine étape.
```

## Références

[1]: https://developers.google.com/search/docs/fundamentals/seo-starter-guide "Google Search Central — SEO Starter Guide"
[2]: https://developers.google.com/search/docs/appearance/core-web-vitals "Google Search Central — Core Web Vitals"
[3]: https://developers.google.com/search/docs/crawling-indexing/mobile/mobile-sites-mobile-first-indexing "Google Search Central — Mobile-first indexing"
