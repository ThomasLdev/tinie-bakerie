---
name: tinie-design-system
description: Design system Tinie Bakerie — conventions de composants Twig + CSS pur (layers, tokens, nesting). À utiliser dès que l'on crée, modifie ou compose un composant du front public, qu'on touche aux templates Twig sous `templates/components/`, qu'on écrit du CSS dans `assets/styles/`, ou que l'on planifie une nouvelle page du blog. Active aussi sur les questions "comment ajouter un bouton/carte/section ?", "où vivent les tokens ?", "comment switche la palette ?". Ne PAS activer pour EasyAdmin (`templates/admin/**`) — ce skill ne couvre que le front public.
---

# Tinie Bakerie — Design System

Refonte design en cours sur la branche `DESIGN/redo-entire-design-system`. Composants Twig + CSS pur moderne, **pas de Tailwind**, pas de SASS (sauf mur réel).

## Sources de vérité

- **Design source** : `doc/design/Food blog/` (HTML+CSS exportés depuis Claude Design — `index.html`, `recettes.html`, `article.html`, `assets/styles.css`).
- **Tokens** : `assets/styles/tokens.css` — toutes les custom properties + 3 palettes (`peach` défaut, `sage`, `cream`) + 2 densités (`aerated` défaut, `compact`).
- **Architecture CSS** : `assets/styles/app.css` orchestre l'ordre des `@layer` et importe les fichiers.

## Architecture CSS

```
assets/styles/
├── app.css           # entrypoint : @layer order + @imports
├── tokens.css        # custom properties + palettes + densités
├── reset.css         # reset moderne (box-sizing, dvh, prefers-reduced-motion)
├── base.css          # body, h1-h4, p, :focus-visible
├── layout.css        # .wrap, .section
├── utilities.css     # .sr-only, .sep, .kicker, .eyebrow
└── components/       # un fichier par composant Twig (créé à la demande)
    ├── button.css
    ├── chip.css
    ├── card.css
    └── ...
```

**Ordre des layers** (du moins prioritaire au plus prioritaire) :
`reset → tokens → base → layout → components → utilities`

## Règles d'or CSS

- **Pas de Tailwind**, pas de classes utilitaires atomiques. Composer en classes sémantiques par composant.
- **Custom properties** pour tout ce qui est tokenizable (couleurs, espaces, rayons, ombres, fonts, transitions). Ne jamais hardcoder une couleur hex en dehors de `tokens.css`.
- **Nesting natif** autorisé (Chrome 112+, Safari 16.5+, Firefox 117+).
- **Logical properties** : `padding-inline`, `margin-block`, etc. plutôt que left/right.
- **`clamp()`** pour la typo fluide, **`color-mix(in oklab, ...)`** pour les variations de couleur, **container queries** quand le composant a besoin de réagir à son conteneur plutôt qu'au viewport.
- **`@layer` partout** — chaque fichier importé est wrappé dans son layer via `app.css`. Ne pas écrire de styles hors layer (sinon ils gagnent toutes les batailles de spécificité).
- **`prefers-reduced-motion`** : déjà géré dans `reset.css`. Si tu ajoutes une animation, vérifie qu'elle est neutralisée.

## Composants Twig

**Stack** : `symfony/ux-twig-component` (déjà via `ux-live-component`) + `symfony/ux-toolkit` (CLI dev).

**Localisation** : `templates/components/<Name>.html.twig`. PascalCase pour le nom de fichier et l'import.

**Syntaxe** : préférer `<twig:Button variant="primary">Voir la recette</twig:Button>` à `{% component 'Button' %}`.

**Backing PHP** (optionnel) : `src/Twig/Components/<Name>.php` avec `#[AsTwigComponent]` quand on a besoin de logique, validation des props, ou propriétés calculées. Sinon, anonymous component (Twig pur).

**CSS associé** : pour chaque composant `<X>.html.twig`, créer `assets/styles/components/<x>.css` (kebab-case) et l'importer dans `app.css` :

```css
@import "./components/x.css" layer(components);
```

**Classe racine** : nom du composant en kebab-case (`<X>` → classe `.x` ou `.x-card`). Pas de prefix BEM strict, mais nesting interne pour les éléments enfants (`.card { & .card-img { ... } }`).

**Attributs** : toujours forwarder `{{ attributes }}` sur la racine pour permettre l'override depuis le call-site.

## Palettes & densités

Switch via attributs `data-palette` et `data-density` sur `<html>` (déjà posé dans `base.html.twig`). Le user peut switcher en runtime avec un panel "Tweaks" — ce panel n'est **pas** activé par défaut dans le projet (était dans `doc/design/Food blog/assets/shared.js` à porter en Stimulus si désiré).

Valeurs supportées :
- `data-palette` : `peach` (défaut), `sage`, `cream`
- `data-density` : `aerated` (défaut), `compact`

## Workflow d'ajout d'un composant

1. Identifier le composant dans `doc/design/Food blog/*.html` — repérer le markup et le CSS de référence.
2. Créer `templates/components/<Name>.html.twig` avec props sémantiques (pas de "div soup").
3. Si props complexes : créer `src/Twig/Components/<Name>.php` avec `#[AsTwigComponent]`.
4. Créer `assets/styles/components/<name>.css` dans le layer `components`.
5. Ajouter l'import dans `app.css`.
6. Tester en isolation puis dans une page.
7. Si interactif → `data-controller="..."` Stimulus ou `<twig:Component />` Live selon nature (voir skill `symfony-ux`).

## Playbook d'intégration (design → Symfony)

Quand on porte une page de `doc/design/Food blog/` vers la stack Symfony, ce skill fait office de **chef d'orchestre** : il décrit *ce qu'il faut faire*, et délègue *comment le faire* aux skills UX spécialisés. Suivre l'ordre ci-dessous évite de re-décider à chaque page.

### Scénario A — Porter une nouvelle page complète

1. **Lire la source** : ouvrir le `*.html` dans `doc/design/Food blog/` + `assets/styles.css` correspondant. Repérer les blocs récurrents (header, hero, card grid, footer) → ce sont des candidats composants.
2. **Diff tokens** : si la page introduit des couleurs/espaces/rayons absents de `tokens.css`, les ajouter d'abord (jamais en dur dans un fichier composant).
3. **Découper en composants** : chaque bloc récurrent → un `<twig:X />`. Déléguer la création à **`twig-component`** (props, slots, anonymous vs PHP backing).
4. **Layout de page** : composer dans `templates/<page>.html.twig` en utilisant `.wrap` / `.section` (layout.css) + les composants. Pas de CSS spécifique à la page sauf justification.
5. **Assets** : importer chaque `components/<x>.css` dans `app.css`. Ne pas oublier le layer.
6. **Vérifier les 3 palettes × 2 densités** avant de considérer fini (le panel Tweaks ou un override `<html data-palette="sage" data-density="compact">` temporaire suffit).

### Scénario B — Rendre un composant interactif

Décider d'abord *quel type d'interaction* avant de choisir le skill :

| Besoin | Skill à appeler | Indice |
|---|---|---|
| Toggle UI pur côté client (dropdown, modal, tabs, copy-to-clipboard, tweaks panel) | **`stimulus`** | Aucun aller-retour serveur, juste du DOM |
| Re-render serveur sur input (recherche live, filtres recettes, validation form inline, dependent select) | **`live-component`** | L'état vit côté PHP, le HTML est re-rendu |
| Navigation partielle / lazy-load section / pagination sans reload | **`turbo`** (Frame ou Stream) | On charge un fragment HTML déjà rendu |
| Hésitation entre les trois | **`symfony-ux`** | Decision tree général |

Règle de pouce : si le composant a juste besoin de réagir au DOM sans toucher au serveur → Stimulus. S'il a un état métier qui doit se re-projeter → Live. S'il s'agit d'un fragment de page rechargeable → Turbo Frame.

### Scénario C — Ajouter une icône

Toujours `<twig:ux:icon name="lucide:..." />` (ou autre set Iconify). Pas de SVG inline copié-collé du design source. Voir **`ux-icons`** pour aliases et lock en prod.

### Scénario D — Carte / liste avec données dynamiques

1. Composant Twig pur pour la présentation (`<twig:RecipeCard recipe={...} />`) — délègue à `twig-component`.
2. La liste qui filtre/cherche → wrapper LiveComponent qui rend N `<twig:RecipeCard>` — délègue à `live-component`.
3. Si la liste est juste paginée sans filtres dynamiques → Turbo Frame autour de la liste suffit.

### Quand ce skill ne suffit pas

- Tu ne sais pas quel skill UX appeler après avoir lu la table ci-dessus → délègue à **`symfony-ux`**.
- Tu veux pousser la qualité visuelle au-delà du design source (micro-interactions, polish) → **`frontend-design`**.
- Question sur une API précise d'un package (`#[LiveProp]`, `data-action`, `<turbo-frame>`) → skill du package directement, pas ce skill-ci.

## Anti-patterns

- ❌ Classes utilitaires atomiques type `mt-4 px-2 text-sm`. Sémantique d'abord.
- ❌ Hardcoder des couleurs hors `tokens.css`.
- ❌ Écrire du CSS hors layer.
- ❌ Inline styles dans les Twig (sauf data-uri ou cas vraiment unique).
- ❌ Toucher à `templates/admin/**` ou aux configs EasyAdmin.
- ❌ Ré-introduire Tailwind, postcss, ou un préprocesseur. SASS uniquement après démonstration de blocage.

## Liens skills associés

- `twig-component` (smnandre) — props, blocks, anonymous components
- `live-component` (smnandre) — pour les composants réactifs (search, filtres)
- `stimulus` (smnandre) — pour le panel Tweaks ou un dropdown
- `turbo` (smnandre) — déjà actif sur le projet, frame/stream pour les sections paginées
- `ux-icons` (smnandre) — préférer `<twig:ux:icon name="lucide:..." />` aux SVG inline
- `symfony-ux` (smnandre) — décision tree quand on hésite entre les outils
