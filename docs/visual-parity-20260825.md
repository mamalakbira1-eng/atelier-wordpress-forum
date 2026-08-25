# Validation visuelle — page de discussion

La capture initiale WordPress montrait un sujet bbPress sans hero illustré, sans rail d’index, sans panneau de lecture claire et avec le lien « Mon espace » affiché à une taille de titre. La référence React montrait une page de discussion éditoriale avec bande hero, rail d’index, actions groupées, colonne de contenu, carte LLM-first, statistiques, image, sujets liés et compositeur de réponse sombre.

La version Atelier 0.4.7-parity installée sur le staging ajoute ces zones au template `content-single-topic.php` et les styles correspondants dans `style.css`. Après activation, la page publique expose le hero, le rail, les actions, le tri, la carte de lecture claire, les statistiques, l’image et les ressources liées. Le cache LiteSpeed a été purgé automatiquement par WordPress lors de l’activation.

URL contrôlée : https://springgreen-panther-771782.hostingersite.com/forums/topic/sujet-de-demonstration-contribution-dun-membre-svip/?atelier_check=047
