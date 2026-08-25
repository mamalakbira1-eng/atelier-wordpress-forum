# Release Atelier 0.4.4 / Premium Forum Core 0.3.0

Cette release rétablit les actions communautaires qui manquaient dans la preview et corrige les lacunes SEO/UX identifiées lors de la revue.

Le prototype React local expose désormais un bouton Répondre qui descend vers un formulaire de contribution, un partage Web Share API avec fallback Clipboard, un suivi, des votes interactifs sur le sujet et les réponses, des filtres de réponses réellement appliqués, ainsi que des badges de démonstration Membre SVIP et Modérateur.

Le thème WordPress ajoute des boutons de vote accessibles et réversibles sur les sujets et les réponses. Le plugin crée la table `wp_pfc_votes`, déduplique les votes par membre et contribution, bloque l’auto-vote, conserve les compteurs historiques importés et additionne les votes natifs dans l’espace membre. Le renouvellement de nonce existant est réutilisé pour les votes.

Le module SEO PFC génère désormais une meta description contextuelle pour l’accueil, les pages, les forums et les sujets. Les sujets et les forums reçoivent également un `BreadcrumbList` JSON-LD ; le forum dispose maintenant d’un fil d’Ariane visible dans le HTML.

Les archives installables sont `release/atelier-0.4.4.zip` et `release/premium-forum-core-0.3.0.zip`. Après installation, purger LiteSpeed Cache et tester avec deux comptes distincts : un compte qui vote et un compte auteur qui reçoit le compteur dans Mon espace.
