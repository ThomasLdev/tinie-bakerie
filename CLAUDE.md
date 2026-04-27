# Tinie Bakerie

Blog de recettes de pâtisserie. Symfony 8 + FrankenPHP (Docker, AssetMapper, Postgres). Front public autour de composants Twig + CSS pur ; EasyAdmin pour back office.

## Toujours déléguer aux skills spécialisés

Avant d'écrire du code, identifier le skill qui couvre la tâche et l'invoquer. Préférer toujours un skill à l'improvisation. Routage rapide :

| Tâche | Skill |
|---|---|
| Composants front public, tokens, CSS layers, conventions design system | `tinie-design-system` (orchestrateur projet — porte le playbook d'intégration) |
| Composant Twig réutilisable (props, blocks, anonymous) | `twig-component` |
| Réactivité serveur (search live, filtres, validation, polling) | `live-component` |
| Comportement client (toggle, dropdown, modal, clipboard) | `stimulus` |
| Navigation / fragments partiels / streams | `turbo` |
| Icônes (Lucide & co via `<twig:ux:icon>`) | `ux-icons` |
| Cartes interactives | `ux-map` |
| Hésitation entre les UX packages | `symfony-ux` |
| Code Anthropic SDK / API Claude | `claude-api` |

Si plusieurs skills s'appliquent, démarrer par `tinie-design-system` qui route ensuite vers les autres via son playbook.

## Dev local

- URL HTTPS : `https://local.tinie-bakerie.com` (cert mkcert trusted, dans `frankenphp/certs/` — gitignored, regénéré avec `mkcert -cert-file ... -key-file ... local.tinie-bakerie.com`).
- `docker compose up -d` ou `make up-dev` (le profile `dev` ajoute le conteneur node).
- Commandes Symfony : `docker compose exec php bin/console <cmd>`.
- Le `Makefile` expose les targets standards (`make help` pour la liste).

## Style de réponse attendu

- Réponses courtes, ton direct, pas de récap final inutile.
- Sur question exploratoire : 2–3 phrases avec une recommandation et le tradeoff principal, pas un plan figé.
- Suivre les conventions et anti-patterns portés par les skills plutôt que les redécrire ici.
