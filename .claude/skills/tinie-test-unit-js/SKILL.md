---
name: tinie-test-unit-js
description: Skill d'écriture de tests unitaires JavaScript Vitest pour Tinie Bakerie. Cible la logique JS pure et les Stimulus controllers (montage en JSDOM, observation par effet de bord sur le DOM). Un test = un comportement, naming explicite, assertions qui cassent vraiment, mocks minimaux (préférer fixtures HTML + Stimulus réel), edge cases pour faire tomber le code. À activer dès qu'on écrit ou modifie un fichier sous `tests/Js/**` (Vitest) ou `assets/controllers/**` (Stimulus), ou quand `tinie-test-orchestrator` délègue un test unit JS (logique combinatoire dans un controller, helper JS pur). Ne PAS activer pour du PHP (`tinie-test-unit`), pour un parcours navigateur full (`tinie-test-e2e`), ni pour tester des effets visuels ou des animations.
---

# Tinie Bakerie — Unit Tests JavaScript (Vitest)

Skill **spécialiste**. Écrit des tests Vitest qui ciblent la **logique JS isolée** ou un **Stimulus controller** monté en JSDOM. Rapides, déterministes, observables par effet de bord sur le DOM ou par valeur de retour.

## Stack

- **Vitest** : version verrouillée dans `package.json` / `package-lock.json`. API `import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'`.
- **Environnement** : `jsdom` (configuré dans `vitest.config.ts`). Fournit `document`, `window`, `localStorage`, etc.
- **Stimulus** : `@hotwired/stimulus` est déjà dépendance projet (utilisée par AssetMapper côté front). On instancie une vraie `Application`, on register le controller sous test, on lui donne un fragment HTML — pas de mock de Stimulus.
- **AssetMapper** : pas de bundler côté prod, ESM natif. Vitest utilise Vite sous le capot, l'import direct des fichiers `assets/controllers/*.js` fonctionne sans transformation.
- **Tests sous** : `tests/Js/<dossier miroir>/<Name>.spec.ts` (TypeScript, cohérent avec Playwright). Ne **pas** mélanger avec `tests/Unit/**` qui est PHP.
- **Run** : `npm run test:js` (à exposer dans `package.json` → `"test:js": "vitest run"`), watch mode `npm run test:js:watch` → `vitest`. `make test.js` à proposer dans le `Makefile`.
- **Coverage** : `vitest run --coverage` (provider `v8` ou `istanbul`, à fixer dans `vitest.config.ts`).

## En cas de doute → context7, ne pas inventer

Vitest évolue vite (API mocks, fixtures, snapshots, configuration). En cas d'hésitation : **interroger context7 avant de coder**.

**Résoudre la library ID dynamiquement** :

1. Lire la version dans `package.json` ou `package-lock.json` (clé `vitest`).
2. `mcp__context7__resolve-library-id` avec `libraryName: "Vitest"` et un `query` précis (ex: « Vitest test.each typed array of cases », « Vitest vi.stubGlobal localStorage », « Vitest jsdom environment configuration »).
3. Pour les questions Stimulus côté tests : `libraryName: "Stimulus"` (Hotwired) — patterns de montage `Application.start()`, lifecycle `connect`/`disconnect`, accès aux targets/values en test.
4. Utiliser l'ID résolu dans `mcp__context7__query-docs`. Ne pas se rabattre sur une version voisine — l'API mocks/spies a bougé entre majeures.

**Quand interroger** :
- Helpers `vi.*` qu'on n'utilise pas tous les jours (`vi.useFakeTimers`, `vi.advanceTimersByTime`, `vi.stubGlobal`, `vi.spyOn`, `vi.mock` partiel).
- `expect` matcher peu courant (`toMatchObject`, `toStrictEqual` vs `toEqual`, `toHaveBeenCalledWith`, `toHaveBeenLastCalledWith`).
- Configuration JSDOM (URL fictive, cookies, `pretendToBeVisual`, etc.).
- Stimulus en test : moment où le `connect()` lifecycle fire, comment forcer un re-scan, attendre un microtask.
- Couverture : exclude patterns dans `vitest.config.ts`, format de report.

**Quand ne pas interroger** : ce qu'on connaît déjà (`describe`, `it`, `expect.toBe`, `vi.fn()`, `beforeEach`). Roue de secours, pas zèle.

Règle ferme : si la doc context7 contredit ce skill → remonter au user.

## Squelette de référence — Stimulus controller

Pattern canonique : monter une `Application`, registrer le controller, observer le DOM après l'action.

```ts
// tests/Js/controllers/recipe_portions_controller.spec.ts
import { Application } from '@hotwired/stimulus';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import RecipePortionsController from '../../../assets/controllers/recipe_portions_controller';

describe('recipe_portions_controller', () => {
  let app: Application;

  beforeEach(async () => {
    document.body.innerHTML = `
      <div data-controller="recipe-portions"
           data-recipe-portions-base-value="4"
           data-recipe-portions-min-value="1"
           data-recipe-portions-max-value="24">
        <button data-test-id="portions-decrease"
                data-recipe-portions-target="decreaseBtn"
                data-action="click->recipe-portions#decrease">−</button>
        <span data-test-id="portions-value"
              data-recipe-portions-target="value">4</span>
        <button data-test-id="portions-increase"
                data-recipe-portions-target="increaseBtn"
                data-action="click->recipe-portions#increase">+</button>
        <span data-test-id="ingredient-flour"
              data-recipe-portions-target="quantity"
              data-base-quantity="200">200</span>
      </div>
    `;
    app = Application.start();
    app.register('recipe-portions', RecipePortionsController);
    await Promise.resolve(); // laisse le scan + connect() se résoudre
  });

  afterEach(() => {
    app.stop();
    document.body.innerHTML = '';
  });

  it('renders base portions count on connect', () => {
    expect(byTestId('portions-value').textContent).toBe('4');
  });

  it('disables decrease button when current is at min', () => {
    decreaseUntil(1);
    expect(byTestId<HTMLButtonElement>('portions-decrease').disabled).toBe(true);
  });
});

function byTestId<T extends Element = HTMLElement>(id: string): T {
  const el = document.querySelector<T>(`[data-test-id="${id}"]`);
  if (!el) throw new Error(`No element with data-test-id="${id}"`);
  return el;
}

function decreaseUntil(target: number) {
  const btn = byTestId<HTMLButtonElement>('portions-decrease');
  while (Number(byTestId('portions-value').textContent) > target && !btn.disabled) {
    btn.click();
  }
}
```

Règles de squelette :
- Imports explicites depuis `vitest`. Ne **pas** s'appuyer sur des globals — toujours `import { it, expect } from 'vitest'`.
- `beforeEach` monte un fragment HTML neuf et démarre une `Application` Stimulus neuve. `afterEach` arrête tout — isolation totale entre tests.
- `await Promise.resolve()` (ou `await new Promise(r => queueMicrotask(r))`) après le `register()` pour laisser Stimulus scanner le DOM et appeler `connect()`. Sans ça, les targets ne sont pas encore wired.
- Helpers locaux (`byTestId`, `decreaseUntil`) au bas du fichier, pas de classe `PageObject` style — ces fichiers sont petits.
- Sélecteurs via `[data-test-id="..."]`, jamais via classes CSS ou structure DOM.

## Squelette de référence — module JS pur

Pour un helper JS sans Stimulus (ex: `assets/utils/format-quantity.js` hypothétique) :

```ts
// tests/Js/utils/format-quantity.spec.ts
import { describe, expect, it } from 'vitest';
import { formatQuantity } from '../../../assets/utils/format-quantity';

describe('formatQuantity', () => {
  it.each([
    [0, '0'],
    [1, '1'],
    [0.25, '0,3'],
    [9.5, '9,5'],
    [10.7, '11'],
    [100.1, '100'],
  ])('formats %s as %s', (input, expected) => {
    expect(formatQuantity(input)).toBe(expected);
  });
});
```

Pas de JSDOM nécessaire si la fonction ne touche pas le DOM — la config Vitest peut isoler ce fichier en `environment: 'node'` via un commentaire `// @vitest-environment node` en tête.

## 1 test = 1 comportement

Comme en PHP. Une fonction `it(...)` décrit **un seul** comportement observable. Si tu écris « *and* » dans la description, c'est deux tests.

| Mauvais | Bon |
|---|---|
| `it('works')` | `it('disables decrease button when current is at min')` |
| `it('handles all cases')` avec 5 `expect` indépendants | 5 `it()`, ou un `it.each([...])` avec clés descriptives |
| `it('init and increment and persist')` | 3 tests : `it('renders base count on connect')`, `it('increments on click')`, `it('persists state to localStorage on change')` |

Un test rouge doit dire ce qui est cassé, en lisant juste son nom.

## Naming explicite

Le nom doit décrire **le comportement attendu** :

- ✅ `it('disables increase button when current reaches max')`
- ✅ `it('formats fractional quantity with French comma')`
- ✅ `it('restores checked state from localStorage on connect')`
- ✅ `it('ignores corrupt JSON in localStorage entry')`
- ❌ `it('test1')`, `it('it works')`, `it('handles edge case')`

Format : `it('<verbe au présent> <complément> [when <condition>]')`. En `it.each`, **la chaîne de format porte la phrase**, pas un compteur :

```ts
it.each([
  { factor: 0.5, base: 200, expected: '100' },
  { factor: 0.25, base: 100, expected: '25' },
])('scales $base × $factor to $expected', ({ factor, base, expected }) => { /* … */ });
```

Quand un test casse, la description avec valeurs interpolées est lisible directement dans la sortie Vitest.

## Features Vitest à utiliser

### `it.each` / `describe.each` — multiplier les cas

```ts
it.each([
  [0, '0min'],
  [30, '30min'],
  [60, '1h'],
  [90, '1h30'],
  [1440, '1j'],
])('formats %d minutes as %s', (input, expected) => {
  expect(formatDuration(input)).toBe(expected);
});
```

- Préférer la **forme objet** (`{ factor, base, expected }`) dès qu'on a 3+ paramètres — l'interpolation `$base` reste lisible.
- Plusieurs `it.each` dans un même `describe` = OK, regroupent par catégorie.

### Mocks (`vi.fn`, `vi.spyOn`, `vi.stubGlobal`)

- `vi.fn()` — mock function pour observer les appels (`expect(fn).toHaveBeenCalledWith(...)`).
- `vi.spyOn(obj, 'method')` — espionner sans remplacer. Restaurer avec `mockRestore()` ou via `vi.restoreAllMocks()` en `afterEach`.
- `vi.stubGlobal('localStorage', mockStorage)` — remplacer un global (utile pour simuler `localStorage` indisponible / qui throw). Restaurer avec `vi.unstubAllGlobals()`.
- `vi.useFakeTimers()` — figer le temps. Restaurer avec `vi.useRealTimers()` en `afterEach`. Ne l'appeler que dans les tests qui en ont besoin, pas globalement.

### Helpers utiles

| Helper | Quand l'utiliser |
|---|---|
| `expect.soft(...)` | Asserter sans interrompre le test au premier échec — utile pour vérifier plusieurs invariants en un test (rare, justifie). |
| `expect.unreachable()` | Branche qu'on ne devrait jamais atteindre. |
| `expect.poll(() => …, { timeout })` | Attendre qu'une condition devienne vraie. **Quasi jamais** en unit JSDOM — un microtask suffit. |
| `vi.waitFor(() => …)` | Attendre qu'un side-effect arrive (ex: après un event dispatché). |

### Annotations à éviter

- `test.skip` / `it.skip` en commit — soit le test marche, soit on le supprime.
- `test.only` / `it.only` — bloquant pour la suite. CI doit refuser ces appels (`forbidOnly` Vitest config).
- `test.todo(...)` — accepté pour signaler une intention mais doit avoir un ticket associé.

## Assertions qui cassent vraiment

Une assertion utile pose une **contrainte sur le comportement** ; si quelqu'un change le code par erreur, le test rougit.

| ❌ Anti-pattern | ✅ Préférer |
|---|---|
| `expect(el).toBeTruthy()` quand `el` est forcément un Node | `expect(el.textContent).toBe('expected')` |
| `expect(arr.length).toBeGreaterThan(0)` | `expect(arr).toEqual(['a', 'b', 'c'])` |
| `expect(fn).toHaveBeenCalled()` (sans argument) | `expect(fn).toHaveBeenCalledWith('expected', 42)` |
| `expect(text).toContain('error')` | `expect(text).toBe('Locale "xx" is not supported')` |
| `toEqual` (équivalence structurelle) | `toBe` ou `toStrictEqual` (identité stricte) — sauf besoin explicite d'equals lâche |
| `expect(...).toBeDefined()` | Type le plus précis possible (`toBe(...)`, `toEqual({...})`) |

**Test du retrait** appliqué aux assertions : *« Si je retire cette ligne, est-ce qu'un bug réel pourrait passer ? »*. Non = inutile.

**Mutation mentale** : avant de valider un test, *« quel changement minimal du code de prod pourrait casser cette assertion ? »*. Si la réponse est « rien d'évident », l'assertion est trop lâche.

## Mocker le strict minimum

**Hiérarchie de préférence**, du plus réel au moins réel :

1. **Stimulus réel + fragment HTML JSDOM** — pour tout test de controller. C'est l'analogue de Foundry côté PHP : la fixture est une vraie `Application` qui boot.
2. **Modules JS importés directement** — pour les helpers purs. Pas de mock par défaut.
3. **`vi.fn()` callback** — quand on veut vérifier qu'un handler est appelé avec les bons arguments.
4. **`vi.spyOn`** — pour observer un appel sans remplacer (ex: `vi.spyOn(localStorage, 'setItem')` pour vérifier qu'on persiste).
5. **`vi.stubGlobal`** — pour simuler un global qui n'existe pas / throw (mode privé Safari, `localStorage` indisponible).
6. **`vi.mock(modulePath)`** — dernier recours, pour neutraliser un import lourd. Pas pour mocker un controller du projet — l'instancier réellement coûte moins.

**Ce qu'on ne mock pas** :
- Stimulus (`@hotwired/stimulus`). Toujours réel.
- DOM / `document` / `window`. JSDOM le fournit.
- Le controller sous test. Si tu mocks ses méthodes, tu ne testes plus rien.
- `localStorage` quand le test décrit le happy path — utiliser le vrai (réinitialisé entre tests via `localStorage.clear()` dans `beforeEach`).

### Mocker `localStorage` indisponible

Pour tester le `try/catch` autour de `localStorage` (mode privé, quota dépassé) :

```ts
beforeEach(() => {
  vi.stubGlobal('localStorage', {
    getItem: () => { throw new Error('SecurityError'); },
    setItem: () => { throw new Error('QuotaExceededError'); },
  });
});

afterEach(() => {
  vi.unstubAllGlobals();
});

it('does not throw when localStorage is unavailable', () => {
  // monter le controller, déclencher la persistance
  expect(() => byTestId<HTMLInputElement>('item-1').click()).not.toThrow();
});
```

## `#privates` natifs — tester par effet de bord

Convention projet : `#name` natifs (cf. mémoire — `feedback_js_private`). **Conséquence** : ces méthodes sont **inaccessibles depuis le test**, on ne peut pas faire `controller.#format(0.25)`.

**À tester via les méthodes publiques et l'effet observable** :
- `#format(n)` du portions controller → click sur `increase()`/`decrease()`, lire `quantityTarget.textContent`.
- `#persist()` / `#restore()` du checklist → click sur une case, recharger l'app (stop + start), vérifier que l'état revient.
- `#render()` du portions controller → click, vérifier `valueTarget.textContent` + `decreaseBtnTarget.disabled`.

C'est plus verbeux qu'un test direct, mais ça **prouve l'intégration** : le controller relie bien sa logique à son DOM. Si on a vraiment besoin de tester une fonction pure isolée → la sortir du controller dans un module utilitaire (`assets/utils/format-quantity.js`), tester le module directement.

## Edge cases — faire tomber le code

Lister mentalement les axes pertinents au type de surface :

**Pour un Stimulus controller** :
- Cible absente : `hasXxxTarget === false` (le controller doit gérer le cas).
- `Values` à leurs valeurs par défaut (defaut Stimulus) vs définies en data-attribute.
- `connect()` appelé deux fois (start → stop → start).
- DOM modifié pendant la vie du controller (target ajouté/supprimé) si le controller en dépend.
- Event sans payload, payload mal formé.

**Pour `localStorage`** :
- Entrée absente.
- JSON invalide.
- Payload non-Array / non-Object là où on l'attend.
- Mismatch de longueur (état sauvegardé pour 5 items, page rendue avec 7).
- localStorage indisponible / `setItem` qui throw.

**Pour les bornes numériques** :
- Min, min-1, min+1, max, max-1, max+1, 0, négatif si non attendu, `Infinity`, `NaN`.

**Pour le formatage** :
- Zéro, entier, fractionnaire, négatif, très grand, très petit (proche de zéro), `NaN`/`Infinity`.
- Locale (séparateur `,` vs `.`, milliers).

**Pour les listeners DOM** :
- Click répété rapide (debounce ?).
- Click sur un élément disabled (devrait être no-op).

**Règle pragmatique** : si un edge case ne peut pas atteindre le code (filtré en amont par le markup), ne pas le tester. Sinon, c'est exactement là qu'il faut un cas.

## Ne pas tester inutilement

Pas de test pour :

- **Configurations Stimulus triviales** : `static targets = ['x']` seul, sans logique. Couvert par tout test qui utilise le target.
- **Imports / re-exports**.
- **Effets purement visuels** : transitions CSS, opacité, z-index. Hors périmètre (c'est le rôle du design system, pas d'un test JS).
- **Comportements natifs du navigateur** : `<a href>` qui navigue, `<form>` qui soumet, `<input>` qui se coche. Tester la **réaction** du controller à ces events, pas l'event lui-même.
- **Dépendances tierces** : `@hotwired/stimulus`. On teste qu'on l'utilise correctement, pas qu'il fonctionne.

## Definition of Done — checklist test JS

Avant de considérer un test fini :
- [ ] Imports explicites depuis `vitest` (pas de globals).
- [ ] `describe` qui groupe par surface, `it` qui décrit un comportement.
- [ ] `beforeEach`/`afterEach` qui isolent l'état (DOM, Application, localStorage, mocks).
- [ ] Stimulus instancié réellement, jamais mocké.
- [ ] Sélecteurs via `[data-test-id="..."]` (cohérent avec Twig + Playwright).
- [ ] `it.each` dès qu'on a 3+ cas similaires, avec clés descriptives.
- [ ] Assertions sur valeur précise (pas `toBeTruthy` paresseux).
- [ ] Edge cases pertinents (fixture vide, target absent, localStorage indisponible, bornes numériques).
- [ ] Aucun `test.only`, aucun `test.skip` non justifié.
- [ ] Le test passe en `vitest run` (pas en watch seul).
- [ ] Biome lint vert sur le fichier.

## Anti-patterns

- ❌ Tester un controller sans le monter via `Application.register()`. Instancier `new MyController()` à la main = on ne teste pas Stimulus, on teste un objet décorrélé.
- ❌ Mocker `@hotwired/stimulus` ou ses helpers (`Application`, `Controller`).
- ❌ Lire `controller.#privateMethod` ou `controller._private` — la convention projet impose `#`, le test doit passer par les méthodes publiques.
- ❌ `expect(...).toBeTruthy()` quand on peut asserter une valeur précise.
- ❌ Sélecteurs CSS, IDs CSS, ou structurels (`.recipe-portions__value`, `div > span`). Toujours `[data-test-id="..."]`.
- ❌ `setTimeout` / `await new Promise(r => setTimeout(r, 100))` pour attendre Stimulus. Préférer `await Promise.resolve()` (microtask) ou `vi.waitFor(...)`.
- ❌ `test.each` avec compteur (`Test 1`, `Test 2`) — toujours une phrase descriptive.
- ❌ Tests partagent un état global (variable de module). `beforeEach` doit tout réinitialiser.
- ❌ Tester l'animation, le style calculé (`getComputedStyle`), ou un screenshot. Hors périmètre.
- ❌ Skip sans ticket associé.
- ❌ Réutiliser le même fragment HTML pour 5 tests qui mutent l'état — chaque test recompose son DOM via `beforeEach`.

## Quand ce skill ne suffit pas

- **Logique PHP** → **`tinie-test-unit`**.
- **Service / controller PHP qui passe par le container** → **`tinie-test-functional`**.
- **Parcours utilisateur multi-pages avec navigation, persistance entre pages, ou interactions cross-controllers** → **`tinie-test-e2e`** (Playwright Android).
- **Décision de niveau** (unit JS vs E2E) → **`tinie-test-orchestrator`**. Règle : si la logique est isolable (formatage, bornes, parsing) → ici. Si l'intégration DOM + Stimulus + persistance + navigation est testée comme un tout → E2E.
- **Tests visuels / régression visuelle** : pas le rôle de Vitest. Lighthouse, Percy ou équivalent.
- **Refacto profonde du controller pour le rendre testable** : signal d'archi (ex: extraire la logique pure dans un module utilitaire), pas un travail de test ; remonter au user.
