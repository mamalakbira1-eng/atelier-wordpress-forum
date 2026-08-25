# Recette finale Atelier 0.4.26 — 25 août 2026

## Déploiement

L’archive `release/atelier-0.4.26-react-parity.zip` a été installée puis appliquée par remplacement sur le staging WordPress. Le thème actif est `atelier-0426`. La feuille CSS est servie avec un numéro de version dérivé de `filemtime`, ce qui évite que LiteSpeed ou le navigateur conserve l’ancienne séquence de la barre d’actions après un remplacement.

## Parité visuelle et structurelle

La page de discussion conserve la composition éditoriale en trois zones : rail d’index, contenu principal et colonne droite. Le contenu principal expose séparément le titre, le contexte, la source, la date historique, l’auteur, le rang, le message initial, les réponses et les votes utiles. La colonne droite présente « Lecture claire », les statistiques, l’illustration de connaissance, les sujets à poursuivre et la carte Collection.

La séquence calculée des actions est désormais stable et identique à la spécification React : **Répondre → Suivre → Partager → Enregistrer**. Le DOM, les valeurs CSS `order` et les coordonnées de rendu ont été contrôlés dans le navigateur avec la feuille `style.css?ver=1787689403`.

## RTL arabe

Le sujet arabe de démonstration fonctionne via son permalien canonique déclaré par WordPress :

`https://springgreen-panther-771782.hostingersite.com/forums/topic/%d9%83%d9%8a%d9%81-%d9%86%d8%ad%d8%a7%d9%81%d8%b8-%d8%b9%d9%84%d9%89-%d9%88%d8%b6%d9%88%d8%ad-%d8%a7%d9%84%d9%85%d8%b9%d8%b1%d9%81%d8%a9-%d8%af%d8%a7%d8%ae%d9%84-%d9%81%d8%b1%d9%8a%d9%82-%d8%a7%d9%84/`

Le titre, le message initial et les réponses arabes utilisent `Noto Naskh Arabic`, avec `dir="rtl"` sur les blocs de contenu. Le titre est plafonné à une taille éditoriale lisible et le rendu de la barre d’actions reste de gauche à droite pour respecter la séquence de l’interface.

## Interactions

Le bouton de suivi a été activé sur le sujet de recette et conserve l’état `Vous suivez` avec `aria-pressed="true"` après rechargement. Le bouton d’enregistrement a été activé, écrit `atelier-topic-202` dans LocalStorage avec la valeur `1`, puis conserve l’état `Enregistré` après rechargement. Le bouton de partage affiche `Lien copié` après copie du lien canonique. Aucun contenu de réponse n’a été publié pendant cette recette.

## Audit responsive basé sur les règles livrées

À partir de `max-width:700px`, la barre d’actions passe en grille à deux colonnes avec des boutons pleine largeur. Le titre RTL utilise une taille plafonnée et une hauteur de ligne augmentée. Le message initial et les réponses passent en une colonne, les métadonnées se réorganisent verticalement, l’aside descend sous le contenu et la carte Collection réduit son visuel. Ces règles sont présentes dans la CSS livrée ; une vérification visuelle sur un appareil physique reste recommandée avant mise en production.

## Synchronisation

Le dépôt public a été mis à jour sur `main` avec le commit `b00500b` : `Release Atelier 0.4.26 React parity and final validation`.

Le dépôt public exclut les identifiants staging et les mots de passe. Les fixtures utilisent des adresses `example.test` et l’archive de recette a été reconstruite sans colonne `password_hash`.
