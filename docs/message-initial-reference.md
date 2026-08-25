# Référence React — Message initial

Les captures de référence montrent une structure asymétrique en deux zones. Le rail gauche contient uniquement l’avatar circulaire et le libellé « QUESTION POSÉE PAR ». Le nom de l’initiateur ne doit pas être placé dans ce rail.

Le contenu principal commence par le libellé « MESSAGE INITIAL ». Dans ce même contenu, une ligne de métadonnées place l’avatar/identité au-dessus du texte : nom de l’utilisateur en premier, rang juste dessous ou à côté selon la largeur, et date alignée à droite. Le texte du message commence sous cette identité et s’étend sur la largeur utile du corps principal, exactement comme le bloc d’une réponse, mais avec la bordure verticale vermillon du Message initial.

La ligne basse contient les upvotes à gauche et la source stable à droite. Le nom, le rang et le texte ne doivent pas être répartis en trois colonnes indépendantes comme dans le rendu actuel. En arabe, la même hiérarchie doit être conservée : le contenu et le titre sont RTL, tandis que la structure de la carte et la séquence des métadonnées restent lisibles.

Correctif attendu : conserver l’avatar dans la zone gauche avec « Question posée par », déplacer le nom/rang/date dans `.atelier-initial-post__body`, et faire suivre le texte sous cette ligne de métadonnées, sans modifier les réponses déjà conformes.
