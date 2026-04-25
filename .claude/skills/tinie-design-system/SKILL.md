---
name: tinie-design-system
description: Design system Tinie Bakerie — conventions de composants Twig + CSS pur (layers, tokens, nesting). À utiliser dès que l'on crée, modifie ou compose un composant du front public, qu'on touche aux templates Twig sous `templates/components/`, qu'on écrit du CSS dans `assets/styles/`, ou que l'on planifie une nouvelle page du blog. Active aussi sur les questions "comment ajouter un bouton/carte/section ?", "où vivent les tokens ?", "comment switche la palette ?". Ne PAS activer pour EasyAdmin (`templates/admin/**`) — ce skill ne couvre que le front public.
---

# Tinie Bakerie — Design System

Refonte design en cours sur la branche `DESIGN/redo-entire-design-system`. Composants Twig + CSS pur moderne, **pas de Tailwind**, pas de SASS (sauf mur réel).

## Sources de vérité

- **Design source** : `doc/design/` (HTML+CSS exportés depuis Claude Design — `index.html`, `recettes.html`, `article.html`, `assets/styles.css`).
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
- **Custom properties** pour tout ce qui est tokenizable (couleurs, espaces, rayons, ombres, fonts, transitions). Ne jamais hardcoder une couleur hex/rgb/rgba en dehors de `tokens.css`. **Contrat de theming** : changer le thème du site = modifier uniquement `tokens.css` (ou ajouter une nouvelle palette `[data-palette="..."]`). Si tu écris `#fff`, `rgba(...)`, ou un `font-family` literal dans un fichier composant, c'est un anti-pattern — créer un token (`--on-accent`, `--overlay`, `--shadow-X`, etc.) et le référencer.
- **Nesting natif** autorisé (Chrome 112+, Safari 16.5+, Firefox 117+).
- **Logical properties** : `padding-inline`, `margin-block`, etc. plutôt que left/right.
- **`clamp()`** pour la typo fluide, **`color-mix(in oklab, ...)`** pour les variations de couleur, **container queries** quand le composant a besoin de réagir à son conteneur plutôt qu'au viewport.
- **`@layer` partout** — chaque fichier importé est wrappé dans son layer via `app.css`. Ne pas écrire de styles hors layer (sinon ils gagnent toutes les batailles de spécificité).
- **`prefers-reduced-motion`** : déjà géré dans `reset.css`. Si tu ajoutes une animation, vérifie qu'elle est neutralisée.

## Test du retrait

Pour chaque déclaration CSS écrite : **« si je l'enlève, est-ce que ça casse ? »**. Non = inutile, ne pas l'écrire (ou la supprimer si déjà là). Réflexe à appliquer mentalement, pas une checklist à dérouler. Souvent en cause : valeurs par défaut du navigateur, doublons avec `reset.css` / `base.css`, redéclarations de tokens.

## CSS d'abord, JS en dernier recours

**Règle ferme** : si un comportement est faisable raisonnablement en CSS moderne, ne **jamais** l'implémenter en JS. Moins de JS = moins de bugs, moins de poids, meilleure perf, meilleure a11y native, moins de coût de maintenance.

Avant d'écrire un Stimulus controller, vérifier qu'aucune de ces solutions CSS/HTML natives ne fait le job :

| Besoin | CSS/HTML natif (préférer) | JS (seulement si impossible) |
|---|---|---|
| Disclosure / accordéon | `<details>` + `<summary>` | Toggle Stimulus |
| Modal / dialog | `<dialog>` + `showModal()` (1 ligne JS) | Custom Stimulus modal |
| Popover, dropdown, tooltip | API `popover` (`popover="auto"` + `popovertarget`) ou `:focus-within` | Stimulus dropdown |
| Toggle (switch / accordion) | `<input type="checkbox">` + `:checked` + sibling | Stimulus toggle |
| « Si élément a un enfant focusé » | `:focus-within` | `focusin`/`focusout` listeners |
| « Si parent contient X » | `:has(...)` | querySelector + manipulation classe |
| Bloquer focus sur arbre | `inert` attribut | `tabindex="-1"` boucle |
| Scroll snap (carrousel simple) | `scroll-snap-type` + `scroll-snap-align` | Swiper / scroll listener |
| Animation au scroll | `animation-timeline: scroll()` ou `view()` | IntersectionObserver |
| Réagir à la taille du conteneur | `@container` queries | ResizeObserver |
| Transition entrée/sortie d'élément | `@starting-style` + `transition-behavior: allow-discrete` | classes ajoutées en JS |
| Compteurs / numérotation | `counter-reset` + `counter-increment` | innerHTML JS |
| Truncation multi-ligne | `-webkit-line-clamp` | mesure + JS |
| Smooth scroll vers ancre | `scroll-behavior: smooth` | `scrollIntoView({behavior})` |
| Texte fluide selon viewport | `clamp()` + `vw` | `resize` listener + calcul |
| Forme/UI input native | `accent-color`, `color-scheme` | `style.setProperty` |
| Layout adaptatif sans MQ | `flex-wrap`, `auto-fit/auto-fill`, `min()`/`max()`/`clamp()` | matchMedia listeners |
| View transitions entre pages | API `view-transition` (déclarative) | manuelles via Turbo events |

**Quand passer en JS** :
- Logique métier (état applicatif, données, requêtes serveur).
- Interactions impossibles en CSS (focus trap réel, gestion de séquences clavier complexes, communication entre composants distants).
- Bugs de support navigateur où la solution CSS n'est pas encore stable sur la cible.

**Coût de la décision** : si tu hésites entre CSS et JS et que la solution CSS demande 2 minutes de recherche supplémentaires (`WebFetch` MDN ou moderncss.dev), prends ces 2 minutes — l'économie sur la durée de vie du composant est énorme.

## Unités : px vs rem

Règle stricte (accessibilité + respect des préférences utilisateur) :

| Type de valeur | Unité | Pourquoi |
|---|---|---|
| `font-size` (toutes balises) | **`rem`** | Scale avec `Settings > Font size` du navigateur. Hardcoder en `px` casse WCAG 1.4.4. |
| `padding`, `margin`, `gap` (texte/composant) | **`rem`** | Respiration proportionnelle quand l'user agrandit le texte. |
| `line-height` | sans unité (`1.5`) | Multiplicateur du `font-size` parent. Ne jamais en `px`. |
| `letter-spacing` | `em` | Proportionnel à la taille du texte de l'élément. |
| `border-width`, `border-radius`, `outline` | `px` | Primitives visuelles, ne doivent pas grossir avec le texte. |
| `box-shadow` (offset/blur/spread) | `px` | Idem. |
| `min-height`/`min-width` de **hit targets** (boutons, inputs) | `px` (≥ 44px) | Cible physique tactile, indépendante du texte. |
| Largeur/hauteur d'**icônes / SVG / logo-mark** | `px` | Primitives visuelles fixes. |
| Breakpoints `@media` | `px` | Voir section Responsive. |

**Body** : ne jamais écrire `body { font-size: 16px }`. Soit on n'écrit rien (le défaut navigateur = 16px = 1rem), soit on écrit `font-size: 100%` pour expliciter qu'on respecte la préférence user.

**Spacing tokens** (`--space-*`) : exprimés en `rem` dans `clamp()`. Le `vw` reste tel quel (il est déjà fluide par viewport).

**Conversion rapide** : `valeur_px / 16 = valeur_rem`. Ex: `12px → 0.75rem`, `48px → 3rem`, `72px → 4.5rem`.

## Responsive & breakpoints

- **Mobile-first** systématique : styles par défaut pour mobile, puis `@media (min-width: ...)` pour amplifier vers desktop. Jamais l'inverse (`max-width:` sauf cas exceptionnel justifié).
- **Media queries co-localisées par composant**, pas regroupées en fin de fichier. Avec le nesting natif, on lit un composant et son responsive d'un coup. La duplication d'une même MQ dans plusieurs blocs est neutralisée par gzip.
- **Breakpoints unifiés** définis comme tokens dans `tokens.css` :
  - `--bp-sm: 600px` — petites tablettes / grands mobiles paysage
  - `--bp-md: 720px` — tablettes (transition footer accordéon → grille, section-head row)
  - `--bp-lg: 900px` — desktop (nav-links visibles, hamburger caché)
  - `--bp-xl: 1200px` — large desktop (rare, pour les grilles dense)
- **Limitation native** : les custom properties ne fonctionnent **pas** dans `@media` (`@media (min-width: var(--bp-lg))` ne marche pas, spec CSS). Donc on **duplique la valeur** dans le `@media` (`@media (min-width: 900px) { ... }`) et on commente si non évident. `tokens.css` reste la source de vérité — si un breakpoint bouge, find-replace projet (4-5 occurrences max).
- **Pas de breakpoint hors de cette liste** sans en discuter. Si un composant a besoin d'un palier intermédiaire, c'est probablement le signe qu'il devrait utiliser une **container query** (`@container`) plutôt qu'une viewport MQ.

## Composants Twig

**Stack** : `symfony/ux-twig-component` (déjà via `ux-live-component`) + `symfony/ux-toolkit` (CLI dev).

**Localisation** : `templates/components/<Name>.html.twig`. PascalCase pour le nom de fichier et l'import.

**Syntaxe** : préférer `<twig:Button variant="primary">Voir la recette</twig:Button>` à `{% component 'Button' %}`.

**Backing PHP** (optionnel) : `src/Twig/Components/<Name>.php` avec `#[AsTwigComponent]` quand on a besoin de logique, validation des props, ou propriétés calculées. Sinon, anonymous component (Twig pur).

**CSS associé** : pour chaque composant `<X>.html.twig`, créer `assets/styles/components/<x>.css` (kebab-case) et l'importer dans `app.css` :

```css
@import "./components/x.css" layer(components);
```

**Naming BEM strict** ([getbem.com](https://getbem.com/introduction/)) :
- **Block** : nom du composant en kebab-case (`.nav`, `.btn`, `.drawer`, `.recipe-card`).
- **Element** : `block__element` (`.nav__link`, `.drawer__head`, `.recipe-card__media`). Toujours double underscore.
- **Modifier** : `block--modifier` ou `block__element--modifier` (`.btn--primary`, `.nav__link--active`). Toujours double tiret.
- Les **utilitaires layout** (`.wrap`, `.section`, `.h-scroll`, `.kicker`, `.sep`) restent en kebab-case sans BEM — ce sont des helpers, pas des blocks.
- État côté JS (drawer ouvert, etc.) : préférer un attribut `data-*` sur l'élément contrôleur plutôt qu'une classe globale `is-*` quand possible.

**Style de nesting CSS** : on garde le **wrapping via `& .block__element`** à l'intérieur du bloc parent pour la **co-localisation visuelle** (un composant et tous ses éléments sont lisibles d'un bloc). On accepte le léger bump de spécificité (`(0,2,0)` au lieu de `(0,1,0)`) ; en pratique ça ne pose pas de problème vu la discipline BEM. Le concat SASS-style `&__head` n'est **pas** supporté par le CSS natif (`&` est une référence de sélecteur, pas une string) — donc pas tentable de toute façon.

**Jamais de styling de balise HTML brute dans un composant** (`& b`, `& a`, `& ul`, `& svg`...). Toujours ajouter une classe BEM (`.logo__brand`, `.recipe-card__link`, `.nav__items`...). Si tu te retrouves à écrire `& b { ... }` ou `.card a { ... }`, c'est un anti-pattern : ajoute une classe sur la balise concernée. **Seules exceptions** : `reset.css` et `base.css` qui posent les défauts globaux par balise (h1-h4, p, body, *...) — c'est leur rôle, et ça reste contenu à ces deux fichiers.

**Attributs** : toujours forwarder `{{ attributes }}` sur la racine pour permettre l'override depuis le call-site.

## Accessibilité (WCAG 2.2 AA — non négociable)

Le site doit être **parfaitement accessible**. Standard cible : **WCAG 2.2 niveau AA**. Chaque composant ajouté ou modifié passe la checklist ci-dessous avant d'être considéré fini. Si tu hésites, lève la main plutôt que de livrer un composant inaccessible.

### Règles structurelles

**Sémantique HTML d'abord** :
- `<button>` pour une action, `<a href>` pour une navigation. Jamais `<div onclick>` ni `<span>` cliquable.
- Un seul `<h1>` par page. Pas de saut de niveau (h2 → h4 ❌).
- Préférer les éléments natifs (`<nav>`, `<main>`, `<aside>`, `<header>`, `<footer>`, `<details>`, `<dialog>`) à un `role="..."` ARIA quand ils existent. ARIA est un dernier recours.

**Clavier** :
- Tout élément interactif est atteignable au Tab. L'ordre du focus suit l'ordre visuel.
- `:focus-visible` reste visible — le ring est posé dans `base.css`, ne jamais le supprimer (`outline: none` interdit sans remplacement équivalent).
- ESC ferme overlays, modales, drawers, dropdowns.

**Focus management des panneaux** (modales, drawers, sheets) :
- À l'ouverture : mémoriser `document.activeElement`, déplacer le focus sur le 1er élément interactif du panneau.
- Pendant l'ouverture : `inert` sur tous les frères du panneau pour bloquer focus + lecteur d'écran sur l'arrière-plan. Pattern de référence : `assets/controllers/drawer_controller.js`.
- À la fermeture : restaurer le focus sur l'élément déclencheur mémorisé.

**Contraste** :
- Texte courant ≥ 4.5:1, texte large (≥ 18pt ou 14pt bold) et composants UI ≥ 3:1.
- Vérifier sur **les 3 palettes** (`peach`, `sage`, `cream`) — un changement de palette peut casser un contraste qui passait sur la palette par défaut.
- Jamais transmettre une info uniquement par la couleur (ajouter icône, texte, ou pattern).

**Cibles tactiles** :
- ≥ 44×44px effectif (`min-height: 48` sur `.btn`, `40×40` sur `.icon-btn` est borderline — l'entourer d'espace pour que la zone effective dépasse 44).
- Inputs : `font-size: 16px` mini (anti-zoom iOS, déjà dans `reset.css`).

**Mouvement** :
- `prefers-reduced-motion` neutralise déjà toutes les transitions globalement via `reset.css`. Si tu ajoutes une animation custom, valide qu'elle se désactive en mode reduced-motion.
- Pas d'auto-play, pas d'animation infinie (sauf indicateur de chargement court).

**Images** :
- `alt` toujours présent. Décoratif → `alt=""` (vide, pas absent). Sinon texte descriptif court.
- Du texte important ne vit jamais uniquement dans une image.

**Formulaires** :
- `<label for="...">` associé à chaque champ (ou label wrappant).
- Erreurs reliées au champ via `aria-describedby` ou `aria-errormessage`. `aria-invalid="true"` quand en erreur.
- `required` natif plutôt que `aria-required` quand possible.

**Live regions** :
- Mises à jour non critiques (search live, filtre) → `aria-live="polite"`.
- Erreurs urgentes → `aria-live="assertive"` ou `role="alert"`.

**Langue** :
- `<html lang="...">` toujours posé (géré dans `base.html.twig`).
- Bloc en langue différente du document → `lang="..."` local.

**Internationalisation des textes a11y** (règle ferme) :
- **Tout texte rendu pour l'utilisateur doit être traduit**, y compris les textes a11y invisibles : `aria-label`, `alt`, `title`, contenus de `<span class="sr-only">`, skip links, libellés de boutons d'icône. Locales supportées : `fr` (défaut) + `en` (`config/services.yaml` → `app.supported_locales`).
- Les textes du chrome de l'app (header, footer, drawer, layout) vivent dans le **domaine `layout`** (`translations/layout.fr.yaml` + `layout.en.yaml`). Pas de hardcode FR dans les composants Twig réutilisés.
- Les textes métier (search, post, category) restent dans `messages` (domaine par défaut).
- Au call-site : `{{ 'header.menu.close'|trans({}, 'layout') }}`.
- Quand tu ajoutes une nouvelle clé, ajoute la traduction **dans tous les fichiers de locale** (fr ET en), jamais une seule.

### Definition of Done — checklist composant

Avant de considérer un composant fini :
- [ ] Markup sémantique (pas de div soup, ARIA seulement si HTML insuffisant)
- [ ] Navigable au clavier seul (Tab/Enter/ESC selon nature)
- [ ] Focus visible sur chaque élément interactif
- [ ] Contraste vérifié sur les 3 palettes
- [ ] Cibles tactiles ≥ 44×44 (zone effective)
- [ ] Aucune info uniquement portée par la couleur
- [ ] Animations neutralisées en `prefers-reduced-motion`
- [ ] Si overlay/modale : focus trap (ou `inert` sur le reste) + restoration + ESC
- [ ] Test lecteur d'écran (VoiceOver/NVDA) sur composants complexes
- [ ] Lighthouse Accessibility ≥ 95 (cible 100), pas de violation `axe`

### Outils de vérification

- **Lighthouse** (Chrome DevTools → Lighthouse → Accessibility seul). Cible 100, plancher 95.
- **axe DevTools** (extension) — détection plus fine que Lighthouse.
- **Test clavier** : naviguer la page entière sans souris.
- **Forced colors** (Windows High Contrast) : DevTools → Rendering → Emulate CSS media feature `forced-colors: active` pour valider que rien ne casse.

### Références externes (lookup à la demande)

Pour les détails fins (rôles ARIA précis, patterns clavier d'un widget, contraste avancé, etc.), ne pas inventer ni rester vague — `WebFetch` la page concernée :

- **MDN Accessibility** — `https://developer.mozilla.org/en-US/docs/Web/Accessibility` (racine). Sous-sections utiles : `/Guides/ARIA`, `/Guides/Keyboard-navigable_JavaScript_widgets`, `/Guides/Mobile_accessibility_checklist`. C'est la référence canonique sur ARIA, sémantique HTML, patterns de widgets accessibles.
- **WAI-ARIA Authoring Practices** — `https://www.w3.org/WAI/ARIA/apg/patterns/` pour le pattern exact d'un widget complexe (combobox, dialog, tabs, treeview, etc.) avec interactions clavier de référence.
- **moderncss.dev** — `https://moderncss.dev/topics/` pour les techniques CSS modernes accessibles (focus rings custom, skip links, screen-reader utilities, prefers-color-scheme, etc.).

Règle : si tu hésites sur un comportement clavier ou un attribut ARIA, **lis la spec avant de coder**. Mieux vaut 30 secondes de WebFetch qu'un widget partiellement accessible.

## Palettes & densités

Switch via attributs `data-palette` et `data-density` sur `<html>` (déjà posé dans `base.html.twig`). Le user peut switcher en runtime avec un panel "Tweaks" — ce panel n'est **pas** activé par défaut dans le projet (était dans `doc/design/assets/shared.js` à porter en Stimulus si désiré).

Valeurs supportées :
- `data-palette` : `peach` (défaut), `sage`, `cream`
- `data-density` : `aerated` (défaut), `compact`

## Workflow d'ajout d'un composant

1. Identifier le composant dans `doc/design/*.html` — repérer le markup et le CSS de référence.
2. Créer `templates/components/<Name>.html.twig` avec props sémantiques (pas de "div soup").
3. Si props complexes : créer `src/Twig/Components/<Name>.php` avec `#[AsTwigComponent]`.
4. Créer `assets/styles/components/<name>.css` dans le layer `components`.
5. Ajouter l'import dans `app.css`.
6. Tester en isolation puis dans une page.
7. Si interactif → `data-controller="..."` Stimulus ou `<twig:Component />` Live selon nature (voir skill `symfony-ux`).

## Playbook d'intégration (design → Symfony)

Quand on porte une page de `doc/design/` vers la stack Symfony, ce skill fait office de **chef d'orchestre** : il décrit *ce qu'il faut faire*, et délègue *comment le faire* aux skills UX spécialisés. Suivre l'ordre ci-dessous évite de re-décider à chaque page.

### Scénario A — Porter une nouvelle page complète

1. **Lire la source** : ouvrir le `*.html` dans `doc/design/` + `assets/styles.css` correspondant. Repérer les blocs récurrents (header, hero, card grid, footer) → ce sont des candidats composants.
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
