## Référence et périmètre

- [ ] La modification part de `atelier-prebeta-rc1` ou indique explicitement une référence plus récente.
- [ ] Le périmètre ne retire ni `noindex, nofollow`, ni protections de préproduction, ni garde-fous d’e-mail.
- [ ] Aucun secret, cookie, sauvegarde, URL privée, export ou donnée personnelle n’est ajouté.

## Validation

- [ ] `sha256sum --check ARTIFACTS-SHA256.txt` passe, ou le manifeste et les archives sont mis à jour ensemble avec un nouveau tag.
- [ ] Le lint PHP et le harnais CSV passent.
- [ ] Les scénarios impactés de `docs/test-matrix-20260827.md` ont été rejoués sur une instance autorisée.
- [ ] Les comptes et contenus synthétiques ont été nettoyés après recette.

## Risque et décision

- [ ] Les risques impactés de `docs/risk-register-20260827.md` ont une preuve, un propriétaire, une échéance et une décision mis à jour.
- [ ] Toute mise à jour de thème/PFC s’accompagne d’une archive, hash, documentation et tag de référence cohérents.
