# Base documentaire Google pour l’audit Atelier — 26 août 2026

## Sources officielles consultées

[1] Google Search Central, **SEO Starter Guide** : https://developers.google.com/search/docs/fundamentals/seo-starter-guide

[2] Google Search Central, **Q&A (QAPage) structured data** : https://developers.google.com/search/docs/appearance/structured-data/qapage

[3] Google Search Central, **Understanding Core Web Vitals and Google search results** : https://developers.google.com/search/docs/appearance/core-web-vitals

[4] Google Search Central, **Mobile site and mobile-first indexing best practices** : https://developers.google.com/search/docs/crawling-indexing/mobile/mobile-sites-mobile-first-indexing

## Points à vérifier dans Atelier

Google présente le SEO comme une aide à la compréhension, à l’exploration et à la découverte du contenu, sans garantie de classement. L’audit doit donc distinguer conformité technique, éligibilité éventuelle et performance réelle dans Search.

Le guide QAPage exige une page centrée sur une question unique et ses réponses, avec possibilité pour les utilisateurs de soumettre des réponses. Le balisage ne doit pas être appliqué indistinctement à toutes les pages d’un forum. Dans Atelier, `acceptedAnswer` ne doit apparaître qu’après acceptation réelle par le modérateur selon la règle métier ; une simple réponse publiée ne suffit pas.

Les Core Web Vitals indiqués par Google sont LCP inférieur ou égal à 2,5 secondes, INP inférieur à 200 millisecondes et CLS inférieur à 0,1 pour une bonne expérience. Ces seuils doivent être mesurés, non supposés, sur des pages représentatives et sur mobile comme sur desktop.

Google utilise la version mobile pour l’exploration et l’indexation mobile-first. L’audit doit donc vérifier que le mobile conserve le contenu principal, les titres, les métadonnées, les données structurées, les liens et les ressources équivalents au desktop, avec une mise en page responsive plutôt qu’une version appauvrie.
