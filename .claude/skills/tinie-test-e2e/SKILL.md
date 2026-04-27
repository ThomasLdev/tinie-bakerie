---
name: tinie-test-e2e
description: Skill d'écriture de tests end-to-end Playwright pour Tinie Bakerie. Suite **mobile-only Android**, en TypeScript, assertions web-first sans timeout en dur, sélecteurs via `data-test-id` (helper Twig `test_id()`). Un test = un parcours utilisateur attendu, en allant au plus direct (pas de re-navigation gratuite). Vérifie l'absence de flakiness en relançant le test plusieurs fois après écriture, puis lance `tsc --noEmit` une seule fois en fin de session. À activer pour tout fichier sous `tests/e2e/**`, ou quand `tinie-test-orchestrator` délègue un parcours navigateur (drawer, search live, JS critique, multi-pages). Ne PAS activer pour des assertions DOM serveur sans JS — c'est `tinie-test-functional`.
---

# Tinie Bakerie — End-to-End Tests (Playwright)

Skill **spécialiste**. Écrit des tests Playwright qui valident un **parcours utilisateur réel** sur un vrai navigateur mobile, sans rien tester de visuel ni de stylistique.

## Stack & contraintes projet

- **Playwright** (`@playwright/test`). TypeScript imposé — Playwright traite TS comme citoyen de première classe, ne pas tenter du JS pur.
- **Mobile-only Android** : suite exécutée sur un device Android émulé (`devices['Pixel 7']` ou similaire). Pas de Desktop, pas d'iOS.
- **Localisation** : `tests/e2e/<flow>.spec.ts`. Page Objects sous `tests/e2e/pages/<Name>Page.ts` quand un même parcours réutilise une séquence d'interactions.
- **Run** : `make test.e2e` (= `npm run test:e2e`). Variantes : `:debug`, `:headed`, `:report`.
- **Base URL** : configurée dans `playwright.config.ts` (réseau Docker interne : `http://php:80`).

## En cas de doute → context7, ne pas inventer

Playwright évolue vite (locators, web-first assertions, fixtures, configuration). En cas d'hésitation : **interroger context7 avant de coder**.

**Résoudre la library ID dynamiquement** :

1. Lire la version dans `package.json` (`@playwright/test`) ou `package-lock.json`.
2. `mcp__context7__resolve-library-id` avec `libraryName: "Playwright"` et un `query` précis (ex: « Playwright getByTestId custom attribute config », « Playwright web-first assertions toBeVisible auto-retry », « Playwright mobile emulation Android Pixel »).
3. Utiliser l'ID résolu dans `mcp__context7__query-docs`.

**Quand interroger** :
- API d'un locator peu utilisé (`getByRole`, `getByLabel`, `frameLocator`, `filter`, `nth`).
- Web-first assertion qu'on n'a pas écrite récemment (`toHaveCount`, `toContainText`, `toHaveAttribute`, `toHaveURL`, `toHaveScreenshot` — qu'on n'utilise PAS).
- Configuration : `testIdAttribute`, `webServer`, `projects`, `expect.timeout`, `use.actionTimeout`.
- Fixtures customs, `test.beforeAll` vs `test.beforeEach`, `test.use({})`.
- Network interception (`route`, `waitForResponse`, `waitForRequest`).
- Authentification persistée (`storageState`).

**Quand ne pas interroger** : ce qu'on connaît déjà (`page.goto`, `getByTestId`, `click`, `fill`, `expect(locator).toBeVisible()`). Roue de secours, pas zèle.

Règle ferme : préférer 30 secondes de `query-docs` à un sélecteur fragile ou un timeout en dur posé "au cas où".

## Configuration Playwright — vérifier avant d'écrire

Avant le premier test du projet, ouvrir `playwright.config.ts` et **garantir** ces points. Si manquants, les ajouter (et le dire au user) :

1. **Projet Android unique** (mobile-only) :
   ```ts
   import { defineConfig, devices } from '@playwright/test';

   export default defineConfig({
     testDir: './tests/e2e',
     fullyParallel: true,
     projects: [
       {
         name: 'android',
         use: { ...devices['Pixel 7'] },
       },
     ],
     // …
   });
   ```
   Ne **pas** garder Desktop Chrome / Firefox / WebKit en parallèle. La suite est mobile-only par décision projet.

2. **`testIdAttribute: 'data-test-id'`** dans `use:` global. Sans ça, `getByTestId('foo')` cherche `data-testid` (default Playwright) et ne trouve rien — le projet pose `data-test-id` via le helper Twig `test_id()`.
   ```ts
   use: {
     baseURL: 'http://php:80',
     testIdAttribute: 'data-test-id',
     trace: 'on-first-retry',
     screenshot: 'only-on-failure',
   },
   ```

3. **Pas de timeout global gonflé**. Garder les défauts (`expect.timeout: 5000`, `actionTimeout: 0`) — augmenter ces valeurs masque la flakiness au lieu de la corriger.

Si l'un de ces points manque, le corriger **dans le même PR que le premier test**, pas après coup.

## Ce qu'on teste — et ce qu'on ne teste pas

### Cible légitime d'un E2E

Un parcours qui **ne peut pas** être validé en functional :
- Interaction JS critique : drawer qui s'ouvre/ferme avec focus trap, search live qui re-render, filtre dynamique, copy-to-clipboard, modal `<dialog>`.
- Comportement Turbo / Stream : navigation sans reload, fragment qui se substitue, flash messages.
- Parcours multi-pages où la transition compte (cookie, session, redirect chain).
- Validation que l'utilisateur peut effectivement aboutir à l'objectif (commander, s'inscrire, arriver à la recette).

### Hors périmètre — **interdit** dans ce skill

- ❌ **Style / effets visuels** : pas de `toHaveScreenshot`, pas d'assertion sur classes CSS, couleurs, ombres, animations, layout. Si le composant est rouge, rond, ou décalé, ça concerne le design system, pas un E2E.
- ❌ **Markup statique** : si la page rend du HTML serveur sans JS, c'est un test functional Symfony, pas un E2E (10× plus rapide).
- ❌ **Logique métier interne** : un calcul de prix, une règle de validation, ça vit en unit ou functional.
- ❌ **API endpoint sans UI** : si on veut tester un JSON, c'est functional.
- ❌ **Performance / accessibilité** : Lighthouse a sa propre place, pas dans la suite Playwright fonctionnelle.

Avant d'écrire un E2E, demander : *« est-ce qu'un test functional Symfony observerait la même chose ? »*. Si oui → c'est un functional, pas un E2E.

## 1 test = 1 parcours utilisateur

**Règle** : une fonction `test(...)` décrit **un parcours unique avec un objectif unique**. Si le test fait deux choses, c'est deux tests.

| Mauvais | Bon |
|---|---|
| `test('navigate site')` qui ouvre 5 pages et clique 12 fois | `test('user reaches a recipe from the recipe index')` ; un autre test pour l'ouverture du drawer |
| Un seul test qui valide ouverture drawer + lien + page recette + ingrédient masqué | 4 tests : `drawer opens`, `drawer link navigates`, `recipe page renders ingredients`, `ingredients toggle hides them` |
| `test('search')` qui assert 3 cas différents avec des `if` | 3 tests, un par cas |

Un parcours qui rougit doit dire ce qui a cassé en lisant juste son nom.

### Format du nom

`<sujet> <verbe> <objet> [when <condition>]` — anglais, présent, lisible :
- ✅ `recipe drawer opens when burger is tapped`
- ✅ `search returns matching recipes when query has accents`
- ✅ `tapping a recipe card navigates to its detail page`
- ❌ `test1`, `it works`, `recipe flow`

## Aller au plus direct

**Règle ferme** : tester le comportement, pas le chemin pour y arriver. Si le test cible la page **recette**, on `page.goto('/fr/recettes/<slug>')` directement. On ne navigue depuis la home, on n'ouvre pas le drawer, on ne clique pas dans le menu — sauf si **la navigation EST le test**.

| Cible du test | Démarrage |
|---|---|
| Page recette s'affiche correctement | `page.goto('/fr/recettes/tarte-au-chocolat')` |
| Drawer s'ouvre depuis le burger | `page.goto('/fr/')` puis tap burger (la home est requise pour avoir le header) |
| Search live filtre | `page.goto('/fr/')` ou `/fr/recettes` puis tap input search |
| Filtres recette dynamiques | `page.goto('/fr/recettes')` directement |
| Parcours `home → recette via menu` | `page.goto('/fr/')` puis interactions menu (la navigation est l'objet du test) |

L'antipattern à éliminer : « je vais toujours sur la home et je clique pour me déplacer ». **Coût** : tests lents, flaky, et qui rougissent à cause d'un changement de header alors que la page testée est intacte.

## Sélecteurs : `data-test-id` uniquement

Le projet pose `data-test-id="..."` via le helper Twig `{{ test_id('...') }}` (rendu **uniquement en env test**, cf. `App\Twig\TestExtension`). C'est le **pivot** de tous les sélecteurs E2E.

Avec `testIdAttribute: 'data-test-id'` dans la config, on utilise **`getByTestId`** :

```ts
await page.getByTestId('recipe-card-search-form').waitFor();
await page.getByTestId('recipe-search-input').fill('chocolat');
await page.getByTestId('recipe-search-submit').click();

const cards = page.getByTestId(/^recipe-card-/);
await expect(cards).toHaveCount(3);
```

**Si l'élément ciblé n'a pas de `data-test-id`** : éditer le template, poser `{{ test_id('...') }}` sur l'élément (cf. skill `tinie-test-functional` pour les conventions de nommage), **dans le même PR que le test**. Plus jamais de sélecteur CSS / structurel / textuel.

**Exceptions où `getByRole`/`getByLabel` est acceptable** :
- Champs de formulaire avec `<label>` : `getByLabel('Email')` est sémantiquement plus correct que `getByTestId`. Mais bascule en `getByTestId` dès que le label est traduit (FR/EN), sinon le test casse au switch de locale.
- Liens primaires : `getByRole('link', { name: '...' })` pour vérifier l'a11y du libellé — sinon préférer `getByTestId`.

Pas d'autre sélecteur. Pas de `page.locator('.recipe-card__title')`, pas de `page.locator('h2 >> text=...')`, pas de XPath.

## Web-first assertions — pas de timeout en dur

**Règle ABSOLUE** : pas de `page.waitForTimeout(N)`, pas de `setTimeout`, pas de `sleep`. Les assertions Playwright (`expect(locator).toXxx()`) **retentent automatiquement** jusqu'au `expect.timeout` (défaut 5s) — c'est **le** mécanisme d'attente.

| ❌ Anti-pattern | ✅ À utiliser |
|---|---|
| `await page.waitForTimeout(2000)` | `await expect(locator).toBeVisible()` |
| `await page.waitForLoadState('networkidle')` puis assertion | `await expect(locator).toBeVisible()` (auto-retry remplace l'attente) |
| `if (await locator.count() > 0) { … }` | `await expect(locator).toHaveCount(N)` |
| `await locator.click(); await page.waitForTimeout(500); …` | `await locator.click(); await expect(target).toBeVisible()` |
| `await page.waitForSelector(...)` | `await expect(page.getByTestId('foo')).toBeVisible()` |

Web-first assertions courantes :
- `toBeVisible()`, `toBeHidden()`, `toBeAttached()`, `toBeEnabled()`, `toBeDisabled()`
- `toHaveText('...')`, `toContainText('...')`, `toHaveValue('...')`
- `toHaveCount(N)`, `toHaveAttribute('href', '/fr/...')`
- `toHaveURL('/fr/recettes/...')`, `toHaveTitle(/.../)`

**Quand un timeout dur est légitime** (rare, à justifier en commentaire 1 ligne) :
- Animation contractuelle bloquante (CSS qui dure exactement 300ms et l'élément n'est pas observable autrement). Toujours préférer attendre l'état post-animation : `await expect(locator).toHaveCSS('opacity', '1')` plutôt qu'un `waitForTimeout(300)`.
- Debounce d'un input dont on connaît la valeur exacte. Mieux : `await expect(results).toHaveCount(N)` après `fill`, l'auto-retry absorbe le debounce.

Si tu ressens le besoin d'un `waitForTimeout`, c'est presque toujours le signal d'une assertion mal choisie. **Remonter au user** plutôt que de planquer la flakiness.

## Page Object Model — quand l'utiliser

POM (cf. `tests/e2e/pages/EasyAdminPage.ts`) **seulement** quand la même séquence d'interactions est utilisée par 2+ tests. Autrement c'est de la cérémonie.

Patron :
```ts
// tests/e2e/pages/RecipePage.ts
import type { Page, Locator } from '@playwright/test';

export class RecipePage {
  readonly page: Page;
  readonly title: Locator;
  readonly ingredientsToggle: Locator;

  constructor(page: Page) {
    this.page = page;
    this.title = page.getByTestId('recipe-show-title');
    this.ingredientsToggle = page.getByTestId('recipe-show-ingredients-toggle');
  }

  async goto(slug: string) {
    await this.page.goto(`/fr/recettes/${slug}`);
    await this.title.waitFor();
  }
}
```

Règles :
- Les Locators sont **initialisés dans le constructeur** (lazy, ils ne touchent pas le DOM avant utilisation).
- Pas de méthode `getXxx()` qui retourne un Locator construit à la volée — ça duplique.
- Une méthode = une action utilisateur (`openDrawer()`, `searchFor(query)`, `selectFirstResult()`). Pas de méthode trop fine (`clickOnButtonX`).
- Le POM ne contient **aucune assertion**. Les assertions vivent dans les tests.
- Si un POM dépasse ~80 lignes ou regroupe 3 surfaces différentes → splitter.

Pas de POM pour 1 test. C'est de l'over-engineering.

## Squelette de référence

```ts
import { test, expect } from '@playwright/test';

test.describe('Recipe show page', () => {
  test('renders title and ingredients for an active recipe', async ({ page }) => {
    await page.goto('/fr/recettes/tarte-au-chocolat');

    await expect(page.getByTestId('recipe-show-title')).toHaveText('Tarte au chocolat');
    await expect(page.getByTestId('recipe-show-ingredients')).toBeVisible();
    await expect(page.getByTestId(/^recipe-show-ingredient-/)).toHaveCount(8);
  });

  test('hides ingredients when user taps the toggle', async ({ page }) => {
    await page.goto('/fr/recettes/tarte-au-chocolat');

    const list = page.getByTestId('recipe-show-ingredients-list');
    await expect(list).toBeVisible();

    await page.getByTestId('recipe-show-ingredients-toggle').click();
    await expect(list).toBeHidden();
  });
});
```

Notes :
- Pas de `beforeEach` pour 2 tests qui font la même `goto` — ça marche mais ça obscurcit le start state. Si on l'utilise, le justifier.
- `test.describe` regroupe par **page** ou par **flow**, pas par "ce qu'on testait il y a 6 mois".
- Une seule assertion finale par test idéalement, ou une chaîne d'assertions qui décrit le **résultat attendu**, pas le chemin.

## Workflow d'écriture

1. **Lire la cible** : quelle page/composant, quelle interaction, quel résultat attendu. Si flou → demander au user, ne pas inventer.
2. **Vérifier la config Playwright** (Android-only, `testIdAttribute`). Corriger si besoin.
3. **Vérifier les `data-test-id`** sur les éléments qu'on va cibler. Si manquants, éditer les templates Twig.
4. **Écrire le ou les tests**. Un parcours = un test. Direct vers la page cible.
5. **Lancer en headless** une fois pour vérifier : `docker compose exec node npm run test:e2e -- tests/e2e/<file>.spec.ts`.
6. **Relancer 3-5 fois pour détecter la flakiness** : `docker compose exec node npm run test:e2e -- tests/e2e/<file>.spec.ts --repeat-each=5`. Si un seul échec sur 5 → c'est flaky, **ne pas accepter** : remonter à l'auto-retry des locators ou à un état non-déterministe (ordre, race condition, animation).
7. **Une fois tous les tests de la session écrits**, lancer **une seule fois** la vérification TypeScript du projet :
   ```bash
   docker compose exec node npx tsc --noEmit -p tsconfig.json
   ```
   Si pas de `tsconfig.json` à la racine, créer un minimal `tsconfig.json` dédié aux tests E2E (`tests/e2e/tsconfig.json`) avec `"strict": true`, `"target": "ES2022"`, `"module": "ESNext"`, `"moduleResolution": "Bundler"`, `"types": ["node"]`, `"include": ["**/*.ts"]`. Le faire **une fois pour le projet**, pas à chaque test.

**Ne pas relancer `tsc` à chaque test écrit** — c'est lent et la perte de feedback rapide n'apporte rien (Playwright signale déjà les erreurs TS au runtime). Une passe finale suffit pour attraper les types qui auraient échappé.

## Isolation & parallélisme — un user par worker

`fullyParallel: true` est activé : **chaque test doit pouvoir tourner dans un worker Playwright indépendant**, dans n'importe quel ordre, sans dépendance sur ce qu'un autre test a fait.

### Règles d'isolation (non négociables)

- **Aucun état partagé global** entre tests : pas de variable de module mutée pendant un test, pas d'objet réutilisé entre `test(...)`, pas de `test.serial` sauf justification écrite (race serveur incontournable).
- **Aucune dépendance d'ordre** : le test 2 ne peut pas supposer que le test 1 a créé une entité. Si le test 2 a besoin d'une donnée, il la crée lui-même (ou elle est créée par fixture worker-scoped, voir plus bas).
- **Données isolées entre tests** : si un test mute la DB (créer une recette via l'admin), les données qu'il crée doivent être uniques (slug avec suffixe `workerIndex` + `Date.now()`, ou cleanup à la fin).
- **Données isolées entre workers** : même principe à l'échelle du worker. Deux workers ne doivent jamais convoiter la même ressource (même user admin, même slug, même URL unique).
- **Une page par test** (`page` fixture default), jamais `page` réutilisée d'un test à l'autre — Playwright donne déjà une page neuve par test, ne pas la singletoner.

### Tests admin : un user par worker (pattern Playwright officiel)

Quand un test passe par `/admin` (EasyAdmin), il faut une **session authentifiée**. La règle est : **un user dédié par worker**, sa session est créée une seule fois au démarrage du worker, puis réutilisée pour tous les tests du worker via `storageState`. C'est le pattern canonique Playwright pour les suites avec auth.

Pourquoi un user **par worker** et pas un user global :
- Si tous les workers utilisent le même compte, ils écrasent leurs sessions et provoquent des invalidations croisées (logout côté serveur, rotation de token).
- Si un test mute l'état du user (préférences, onboarding, etc.), il pollue les autres workers.
- Avec un user par worker, chaque worker est son propre univers — vraie isolation.

**Avant d'implémenter**, interroger context7 pour le pattern exact à jour (`mcp__context7__query-docs` sur la lib Playwright résolue, query : « Playwright authentication one account per worker storageState fixture »). Le squelette ci-dessous est un point de départ, pas une vérité figée :

```ts
// tests/e2e/fixtures/admin.ts
import { test as base, expect } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';

type WorkerFixtures = {
  adminStorageState: string;
};

export const test = base.extend<{}, WorkerFixtures>({
  storageState: ({ adminStorageState }, use) => use(adminStorageState),

  adminStorageState: [async ({ browser }, use, testInfo) => {
    const id = testInfo.parallelIndex;
    const file = path.resolve(testInfo.project.outputDir, `.auth/admin-${id}.json`);

    if (fs.existsSync(file)) {
      await use(file);
      return;
    }

    fs.mkdirSync(path.dirname(file), { recursive: true });

    const page = await browser.newPage({ storageState: undefined });
    await page.goto('/admin/login');
    await page.getByTestId('admin-login-email').fill(`admin+w${id}@tinie.test`);
    await page.getByTestId('admin-login-password').fill('test-password');
    await page.getByTestId('admin-login-submit').click();
    await expect(page.getByTestId('admin-dashboard')).toBeVisible();
    await page.context().storageState({ path: file });
    await page.close();

    await use(file);
  }, { scope: 'worker' }],
});

export { expect } from '@playwright/test';
```

Côté projet :
- Provisionner les comptes `admin+w0@tinie.test` … `admin+wN@tinie.test` (N = nombre max de workers) **dans une fixture Symfony test-only** (équivalent Foundry Story dédiée). Ne **pas** créer ces comptes à chaud côté Playwright si les rôles/permissions doivent être stables.
- Le fichier `.auth/admin-<id>.json` est dans `testInfo.project.outputDir` → poubelle CI, regénéré par run. Pas commité.

Test admin type :
```ts
import { test, expect } from './fixtures/admin';

test('admin can publish a recipe draft', async ({ page }) => {
  await page.goto('/admin?crudController=Recipe&crudAction=index');
  await page.getByTestId('recipe-row-status-toggle').first().click();
  await expect(page.getByTestId('flash-success')).toBeVisible();
});
```

### Tests publics (front non authentifié)

Pas d'auth, pas de fixture worker spéciale. La fixture `page` par défaut suffit — chaque test reçoit un contexte vierge, isolation native.

### Vérifier l'isolation avant de commit

- `--workers=4 --repeat-each=3` doit passer aussi facilement que `--workers=1`. Si `--workers=4` flake et `--workers=1` non → c'est un défaut d'isolation.
- Si un test échoue uniquement avec un seuil de workers précis → état partagé (DB, fichier, cache, port). Diagnostiquer avant de baisser les workers.

## Détection de flakiness

Un test E2E qui rougit une fois sur 10 est **inutile** : il vole la confiance dans toute la suite.

Causes courantes (du plus fréquent au plus rare) :
1. **Sélecteur qui matche plusieurs éléments** intermittemment → strict mode failure. Réduire le scope (`page.getByTestId('foo').first()` est rarement bon, préférer un ID plus précis).
2. **Animation/transition non attendue** → laisser l'auto-retry de l'assertion absorber la durée. Si insuffisant, attendre un état post-animation (`toBeVisible`, `toHaveAttribute`) plutôt qu'un délai.
3. **Réseau / requête en vol** → utiliser `page.waitForResponse(/\/api\/.../)` autour de l'action, ou `page.route()` pour stub si la requête est secondaire au test.
4. **Ordre de DOM non stable** (tri SQL non déterministe, hash IDs) → corriger côté serveur (tri par `id ASC` en cas d'égalité), pas côté test.
5. **Effet de side-state entre tests parallèles** → `fullyParallel: true` est OK si les tests ne mutent pas la DB partagée. Pour un test qui crée/édite, isoler via fixtures dédiées ou `test.serial`.

Si après 3 passes de `--repeat-each=5` un test reste flaky : **stop**, remonter au user. Un test rouge intermittent merite analyse, pas un retry magique.

## Definition of Done — checklist E2E

Avant de considérer un test fini :
- [ ] Config Playwright validée : Android-only, `testIdAttribute: 'data-test-id'`
- [ ] Cible légitime E2E (interaction JS, multi-pages, parcours utilisateur)
- [ ] Un parcours par test, nom descriptif en anglais
- [ ] `goto()` direct vers la page cible, pas de navigation gratuite
- [ ] Sélecteurs via `getByTestId()` exclusivement (sauf `getByRole`/`getByLabel` justifié)
- [ ] Aucun `waitForTimeout`, aucun `sleep`, aucun timeout en dur
- [ ] Web-first assertions partout (`toBeVisible`, `toHaveCount`, `toHaveURL`, etc.)
- [ ] Aucune assertion de style/visuel
- [ ] Test passe 5 fois consécutives en local (`--repeat-each=5`)
- [ ] Test passe avec `--workers=4 --repeat-each=3` aussi facilement qu'avec `--workers=1` (preuve d'isolation)
- [ ] Aucun état partagé entre tests, aucune dépendance d'ordre
- [ ] Si admin : auth via fixture `storageState` worker-scoped, **un user dédié par worker**
- [ ] `tsc --noEmit` vert (lancé une fois en fin de session)
- [ ] POM utilisé seulement si la séquence est partagée par 2+ tests

## Anti-patterns

- ❌ `await page.waitForTimeout(N)` "au cas où". Toujours.
- ❌ Sélecteurs CSS, classes, structure (`h2 > a`), XPath, textes traduits.
- ❌ Tester la même chose qu'un test functional Symfony (gaspillage 10×).
- ❌ Test qui démarre depuis la home pour aller tester une page profonde.
- ❌ Une fonction `test(...)` qui valide 4 choses indépendantes.
- ❌ Augmenter `expect.timeout` ou `actionTimeout` pour faire passer un test flaky.
- ❌ Garder Desktop Chrome dans `projects` : la suite est mobile-only Android.
- ❌ Visual regression (`toHaveScreenshot`) — pas le rôle de cette suite.
- ❌ POM créé pour un seul test.
- ❌ Lancer `tsc` après chaque test écrit. Une passe en fin de session suffit.
- ❌ Accepter un test flaky parce que « ça passe sur la 2ème tentative ». Le retry CI est un filet de sécurité, pas une excuse.
- ❌ État partagé entre tests (variable de module mutée, fichier réutilisé, entité réutilisée par slug). Casse l'isolation worker.
- ❌ Tests admin qui partagent un seul compte global entre workers. Provoque des sessions concurrentes et des invalidations croisées.
- ❌ Désactiver `fullyParallel` ou `--workers` parce qu'un test rougit en parallèle — diagnostiquer l'isolation, pas masquer le symptôme.
- ❌ Hardcoder `parallelIndex` à 0 dans une fixture (cf. exemples copy-paste mal recopiés). Toujours lire `testInfo.parallelIndex`.

## Quand ce skill ne suffit pas

- **Markup serveur sans JS** : c'est un test functional Symfony → **`tinie-test-functional`**.
- **Logique pure** : unit → **`tinie-test-unit`**.
- **Décision de niveau** (E2E vs functional) → **`tinie-test-orchestrator`**.
- **Régression visuelle ou audit a11y/perf** : Lighthouse, axe, ou Percy — pas Playwright fonctionnel. Discuter avec le user avant de mettre en place.
- **Flakiness persistante après diagnostic** : remonter au user avec les hypothèses (race, sélecteur, ordre serveur), ne pas patcher avec des waits.
