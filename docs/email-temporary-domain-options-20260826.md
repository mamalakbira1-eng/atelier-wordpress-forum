# E-mail de recette sans domaine définitif — 26 août 2026

## Décision recommandée

Utiliser **Mailtrap Email Sandbox** pour la recette actuelle d’Atelier. Son plan gratuit propose un sandbox, un utilisateur et 50 e-mails de test par mois. Il capture les messages sortants dans une boîte de test : cela permet de contrôler le code d’inscription, son contenu, le renvoi, l’expiration et les en-têtes, sans acheter de domaine ni contacter de vrais membres. Le Sandbox ne livre pas aux boîtes e-mail réelles : ce n’est donc pas une validation de délivrabilité de production.

La configuration prévue est Easy WP SMTP → **Autre SMTP** → identifiants SMTP du sandbox Mailtrap. Les secrets restent dans l’extension WordPress et ne sont jamais écrits dans GitHub ou les documents.

## État de recette validé le 26 août 2026

Easy WP SMTP est installé et le staging utilise l’expéditeur forcé `noreply@atelier.test` avec le nom `Atelier — Recette e-mail`. Le suffixe `.test` est réservé aux tests et évite de prétendre utiliser un domaine non contrôlé. Il ne faut ni configurer SPF, DKIM ou DMARC pour cet expéditeur de recette, ni interpréter les alertes liées à un ancien expéditeur `atelier.com` comme un prérequis de staging.

Le test manuel Easy WP SMTP a été reçu dans Mailtrap Email Sandbox avec `sandbox.smtp.mailtrap.io`, TLS/STARTTLS et le port 2525. Le parcours Premium Forum Core a ensuite été validé exclusivement avec une adresse synthétique : création du compte pending, envoi du code, renvoi d’un nouveau code, reprise directe de l’écran `action=verify`, confirmation, redirection de succès, puis suppression du compte de recette sans contenu. Aucun code, identifiant SMTP, cookie ou adresse réelle n’est conservé dans le code, les archives ou la documentation.

Le défaut applicatif relevé pendant la recette était distinct du transport SMTP : une chaîne traduisible de l’e-mail initial employait des guillemets doubles autour de spécificateurs `sprintf`, ce qui provoquait l’erreur PHP `Unknown format specifier ','`. Le correctif passe la chaîne à des guillemets simples. La release consolidée **Premium Forum Core 0.4.11** ajoute aussi la reprise sûre du formulaire de vérification en GET, avec saisie locale de l’adresse si le contexte a été perdu, sans adresse dans l’URL ni révélation de l’état d’un compte. Les diagnostics temporaires de recette ont été retirés après validation.

> **Limite importante.** Cette preuve confirme le flux applicatif de préproduction, pas la délivrabilité publique. Mailtrap Sandbox capture les e-mails de test et ne remplace ni un fournisseur transactionnel de production ni des essais vers des boîtes réelles. [1] [2]

## Options étudiées

| Option | Gratuité constatée | Adaptée au domaine temporaire ? | Décision |
|---|---|---|---|
| Mailtrap Email Sandbox | 50 messages test/mois, 1 sandbox, 1 utilisateur. | Oui, pour tester le flux applicatif sans livraison réelle. | Recommandée maintenant. |
| Mailtrap Email API/SMTP | 4 000 e-mails/mois, 150/jour, 1 domaine sur le plan gratuit. | À étudier après achat d’un domaine, car la production exige une identité expéditrice maîtrisée. | Option ultérieure. |
| Resend | 3 000 e-mails/mois et 100/jour sur le plan gratuit. | Non recommandé tant qu’aucun domaine expéditeur final n’est disponible. | Option production possible après vérification de domaine. |
| Brevo | Offre gratuite, mais un expéditeur sans domaine authentifié est remplacé par une adresse conforme gérée par Brevo. | Insuffisant pour construire une identité Atelier durable. | À différer jusqu’au domaine final. |

## Transition vers la production

Après achat du domaine, choisir un fournisseur transactionnel, créer une boîte expéditrice dédiée, puis authentifier le domaine avec SPF, DKIM et DMARC. Rejouer l’inscription vers des boîtes Gmail, Outlook et une adresse de domaine, observer les en-têtes et les résultats, puis seulement ouvrir le forum au public. Les services recommandent précisément un domaine personnalisé et authentifié pour éviter le filtrage ou le remplacement d’expéditeur.

## Références

[1]: https://mailtrap.io/pricing/ "Mailtrap Pricing"
[2]: https://mailtrap.io/email-sandbox/ "Mailtrap Email Sandbox"
[3]: https://resend.com/docs/knowledge-base/what-is-resend-pricing "Resend — What is Resend Pricing"
[4]: https://help.brevo.com/hc/en-us/articles/14925263522578-Comply-with-Gmail-Yahoo-and-Microsoft-s-requirements-for-email-senders "Brevo — sender requirements"
