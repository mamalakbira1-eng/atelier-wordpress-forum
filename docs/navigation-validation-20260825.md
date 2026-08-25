## Test initial des filtres

Le clic sur « Message initial » cible bien `#message-initial`. Le clic sur « Contributions repérées » réordonne les réponses, mais une carte à 0 vote reste visible : le diagnostic est une règle CSS bbPress/Atelier qui impose `display` même quand l’attribut HTML `hidden` est présent. Le correctif doit ajouter une règle d’autorité `[hidden]{display:none!important}` limitée aux cartes de réponse. Les ancres affichées sont `#topic-root`, `#message-initial`, `#reponses` et `#machine-reading`.
