# Audit visuel Atelier — 25 août 2026

La page publique servie par le staging après Atelier 0.3.0 confirme que les contenus, les images et la recherche sont présents dans le HTML initial. Le premier rendu du hero a cependant exposé une zone de vide bleu nuit : le cadrage `object-position: 74%` isolait la marge sombre de l’illustration. Le cadrage est corrigé vers `28%`, de façon à exposer la composition papier et le signal vermillon.

Les illustrations initiales PNG pèsent plusieurs mégaoctets ; le thème passera donc à des variantes WebP redimensionnées. Ce choix conserve la direction éditoriale tout en réduisant fortement le poids transféré pour l’image LCP et la carte chargée en différé.

## Contrôle public après déploiement

L’URL fraîche `?atelier_validation=20260825_1316` confirme que le HTML public référence bien `atelier-hero.webp` et `atelier-knowledge-map.webp`, et que le champ de recherche, les métriques et les liens ne retombent plus dans le style natif bleu. En revanche, le cadrage réellement affiché reste principalement bleu nuit : l’illustration source ne produit pas encore le panneau éditorial lumineux attendu sur ce ratio horizontal. L’étape suivante est donc de composer un vrai visuel hero horizontal à partir du motif, au lieu de simplement recadrer l’image verticale existante.

## Contrôle final Atelier 0.3.1

Les captures réalisées dans un navigateur sans session, en **1440 × 960** et **375 × 812**, valident le rendu final du premier écran. Sur desktop, le bloc textuel ivoire, le visuel d’archives cadré sur la colonne de papiers et le ruban vermillon forment une composition lisible, avec la recherche exposée dans l’en-tête. Sur mobile, la navigation textuelle se replie sans masquer la marque ni la recherche, le hero s’empile proprement et l’image conserve la colonne de papiers sans écraser le titre.

Les chemins de preuve sont `validation-screenshots/home-desktop-20260825.png` et `validation-screenshots/home-mobile-20260825.png`. Les réponses HTTP publiques donnent `200` pour l’accueil, la recherche et la discussion. Les illustrations sont servies en `image/webp`, avec **66 122 octets** pour le hero et **153 298 octets** pour la carte de connaissance.
