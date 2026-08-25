# Atelier — disponibilité du pseudo et modération

## Pseudo

Le navigateur propose un identifiant à partir du prénom et du nom. Une vérification AJAX interroge WordPress et affiche « Pseudo disponible » ou « Ce pseudo est déjà utilisé ». Lorsque le nom est pris, le serveur renvoie jusqu’à trois alternatives suffixées (`pseudo2`, `pseudo3`, `pseudo4`) que le visiteur peut sélectionner en un clic.

Cette vérification n’est pas une réservation. Le serveur revalide toujours `username_exists()` au moment du POST d’inscription ; cette seconde vérification est la source d’autorité en cas de concurrence.

## Rôles

PFC crée le rôle `atelier_moderator`, libellé **Modérateur Atelier**, avec lecture, édition des contributions des autres membres, publication, suppression et capacité `pfc_moderate`. Les administrateurs reçoivent également `pfc_moderate` et gardent toutes leurs capacités natives de gestion des comptes, réglages et extensions.

Depuis **Comptes → Ajouter un compte**, un administrateur peut créer un compte puis lui attribuer le rôle **Modérateur Atelier**. Le rôle est distinct du rang public `pfc_rank` : le rang est éditorial, tandis que le rôle contrôle les permissions.

## File de validation

Le menu **À valider** est disponible pour les administrateurs et les modérateurs. Il liste les sujets et réponses dont le statut est `pending`, avec auteur, date, extrait et trois décisions : **Publier** (`publish`), **Refuser** (`draft`) et **Supprimer** (`trash`). Chaque action est protégée par un nonce et une vérification de capacité.

Les nouvelles contributions des membres ordinaires passent automatiquement en attente. Les modérateurs et administrateurs peuvent publier directement. L’auteur ne valide ni ne refuse ses propres réponses ; seul un utilisateur doté de `pfc_moderate` intervient dans la file éditoriale.

## Données structurées

`acceptedAnswer` ne doit être ajouté qu’après une décision éditoriale explicite d’un modérateur ou administrateur. Le statut `publish` seul ne constitue pas une réponse acceptée et ne doit pas déclencher `QAPage` automatiquement.

## Limites à traiter avant production

La configuration SMTP doit être validée pour garantir l’envoi du code d’inscription. Un anti-spam, une limitation d’abus sur l’endpoint de disponibilité et les pages RGPD/CGU restent nécessaires avant ouverture publique.
