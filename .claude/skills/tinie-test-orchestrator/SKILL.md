---
name: tinie-test-orchestrator
description: Orchestrateur de tests Tinie Bakerie — choisit la bonne granularité (unit/functional/e2e), délègue aux skills spécialisés, puis vérifie la couverture. À activer dès qu'on demande "écris les tests", "couvre cette feature", "ajoute des tests pour la branche", "test coverage", ou après avoir mergé du code non testé. Ne PAS activer pour exécuter ponctuellement la suite (`make test`) ni pour debugger un test rouge isolé.
---

# Tinie Bakerie — Test Orchestrator

Skill **chef d'orchestre** : décide *ce qu'il faut tester et à quel niveau*, délègue *comment l'écrire* aux skills spécialisés, puis valide la couverture obtenue. Lui-même n'écrit pas les tests.

## Stack & commandes

- **PHP** : PHPUnit 12, testsuites `UnitTests` (`tests/Unit`) et `FunctionalTests` (`tests/Functional`).
- **Fixtures** : Foundry (`Zenstruck\Foundry`) — factories dans `src/Factory/`, stories dans `tests/Story/`.
- **HTTP** : `BaseControllerTestCase` (`tests/Functional/Controller/BaseControllerTestCase.php`) avec `ResetDatabase` + `Factories` + `loadStory()`.
- **E2E** : Playwright (`tests/e2e/`), Page Objects sous `tests/e2e/pages/`.
- **Run** : `make test` (tout), `make test.unit`, `make test.functional`, `make test.e2e`, `make test.coverage` (HTML dans `coverage/`).
- **Conteneur** : commandes via `docker compose exec php …` (déjà encapsulé dans le `Makefile`).

Les classes exclues de la couverture sont définies dans `phpunit.xml.dist` (`<source><exclude>`) — `src/Entity` et `src/DataFixtures`. Ne pas chercher à couvrir ces dossiers.

## Workflow en 4 phases

### Phase 1 — Analyse du diff de branche

```
git diff --stat main...HEAD
git diff --name-only main...HEAD
```

Classer chaque fichier modifié par **catégorie de surface** :

| Surface | Pattern de chemin | Signal |
|---|---|---|
| Controller HTTP | `src/Controller/**` (hors Admin) | endpoint à tester via WebTestCase |
| Controller EasyAdmin | `src/Controller/Admin/**` | testé via `tests/Functional/Controller/Admin/*` (existant), souvent route smoke + permissions |
| Service métier | `src/Services/**` | logique pure → unit prioritaire |
| Repository custom | `src/Repository/**` (méthodes hors `find*` Doctrine) | functional avec DB réelle |
| Form Type | `src/Form/**` | unit (TypeTestCase) |
| Twig Component (PHP) | `src/Twig/Components/**` | unit léger sur la logique du composant |
| Twig Component (anonymous) | `templates/components/**` | couvert via le test du controller qui le rend |
| Stimulus controller | `assets/controllers/**` | E2E si comportement critique (le JS *est* le test), sinon non testé |
| Validator / EventSubscriber / EntityListener | `src/Validator/**`, `src/EventSubscriber/**`, `src/EntityListener/**` | unit + un cas d'intégration |
| Entité | `src/Entity/**` | **non testé directement** (exclu coverage) — couvert via les surfaces qui les manipulent |
| Migration | `migrations/**` | non testé (smoke `doctrine:migrations:migrate` en CI) |
| CSS / templates pure présentation | `assets/styles/**`, `templates/*.html.twig` (sans logique) | non testé |

**Ignorer** les diffs pure cosmétique (CSS, README, traductions seules, formatage). S'ils sont seuls sur la branche → pas de tests à écrire, le dire au user.

### Phase 2 — Choix de granularité

**Règle de pouce** (du moins coûteux au plus coûteux) : `unit > functional > e2e`. Choisir le **niveau le plus haut qui couvre le risque**, pas le plus bas par dogme.

Heuristiques :

1. **Si la modification touche un Controller** → **un test fonctionnel sur le controller** couvre par effet de bord les services qu'il appelle. Ne PAS dupliquer en unit chaque service du chemin.
2. **Exception unit malgré le controller** : un service possède une **logique combinatoire riche** (parser, slugger, validateur, calcul) avec beaucoup de cas → l'isoler en unit avec `DataProvider` (cas multiples, feedback rapide). Pattern de référence : `tests/Unit/Services/Slug/SluggerTest.php`.
3. **Service utilisé par plusieurs controllers** → unit (coût d'écriture amorti, évite de re-tester la même logique 3 fois en functional).
4. **Helper Twig / Component anonyme** sans branche conditionnelle → couvert via le test du controller hôte, pas de test dédié.
5. **Form Type** → unit `TypeTestCase` (rapide, pas besoin de booter Symfony complet sauf si Type composé avec services).
6. **EventSubscriber Doctrine / EntityListener** → unit avec mocks Doctrine **+** un test functional minimal qui vérifie que le subscriber est bien câblé sur un flux réel.
7. **E2E uniquement si du JavaScript est impliqué dans le parcours** — règle dure. Drawer Stimulus, search LiveComponent qui re-render côté client, filtres dynamiques, modal `<dialog>` piloté en JS, Turbo Frame/Stream, copy-to-clipboard, focus trap : **oui**, E2E. Lien `<a href>` qui amène sur une page rendue serveur, formulaire `<form method="POST">` natif, redirect, 404, contenu Twig statique : **non**, c'est du functional, même si le test "ressemble" à un parcours utilisateur. Avant de choisir E2E, faire le **test du JS** : *« sans JavaScript activé dans le navigateur, ce parcours change-t-il de comportement ? »*. Si la réponse est non → c'est un test functional Symfony (10× plus rapide à écrire et exécuter, plus stable). Hésiter entre E2E et functional sans JS dans le parcours = automatiquement functional.
8. **Refactor sans changement de comportement** → pas de nouveau test, vérifier que les tests existants couvrent le chemin. S'ils ne couvrent pas, le combler **avant** le refactor (mais c'est un autre skill — `sc:improve`).

**Test du retrait** (à faire mentalement avant chaque test proposé) : *« Si je n'écris pas ce test, quel bug ne sera pas attrapé ? »*. Si la réponse est « aucun, c'est déjà couvert par X » → ne pas l'écrire.

### Phase 3 — Délégation

Pour chaque test décidé en Phase 2, déléguer au skill correspondant. Si le skill spécialisé n'existe pas (cas actuel — ce skill orchestrateur est livré seul), **spawner un sous-agent `general-purpose`** avec un brief auto-suffisant qui contient :

- Fichier(s) source à couvrir + leur diff
- Niveau de test choisi + justification 1 ligne
- Convention de référence (pointer vers un test existant de même niveau dans `tests/`)
- Localisation attendue du fichier de test
- Commande pour le lancer

Routage cible (les noms `tinie-test-*` sont à créer si la spécialisation devient nécessaire — pour l'instant, agent générique avec contexte) :

| Niveau | Skill spécialisé (futur) | Référence existante à imiter | Localisation |
|---|---|---|---|
| Unit pur | `tinie-test-unit` | `tests/Unit/Services/Slug/SluggerTest.php` | `tests/Unit/<Namespace>/<Class>Test.php` |
| Form Type | `tinie-test-unit` | `tests/Unit/Form/Type/PostMediaTypeTest.php` | `tests/Unit/Form/Type/<Type>Test.php` |
| Twig Component | `tinie-test-unit` | `tests/Unit/Twig/Components/SearchTest.php` | `tests/Unit/Twig/Components/<Name>Test.php` |
| EventSubscriber / Validator | `tinie-test-unit` | `tests/Unit/EventSubscriber/TranslatableEntitySubscriberTest.php` | `tests/Unit/<Namespace>/...Test.php` |
| Functional Controller | `tinie-test-functional` | `tests/Functional/Controller/RecipeControllerTest.php` + `tests/Story/RecipeControllerTestStory.php` | `tests/Functional/Controller/<Name>Test.php` |
| Functional Service (booting kernel) | `tinie-test-functional` | `tests/Functional/Services/Search/PostSearchTest.php` | `tests/Functional/Services/<Namespace>/<Service>Test.php` |
| EasyAdmin CRUD | `tinie-test-functional` | `tests/Functional/Controller/Admin/CategoryCrudControllerTest.php` | `tests/Functional/Controller/Admin/<Crud>Test.php` |
| E2E navigateur | `tinie-test-e2e` | `tests/e2e/simple-validation.spec.ts` + Page Objects | `tests/e2e/<flow>.spec.ts` |

**Conventions transverses imposées aux sous-agents** :

- `declare(strict_types=1)` + `final class … Test extends …`
- `#[CoversClass(...)]` sur la classe de test (un par classe couverte) — sans ça, la coverage par-classe est imprécise.
- `#[DataProvider(...)]` pour multiplier les cas, **jamais** de `foreach` à l'intérieur d'un test.
- Functional : utiliser `loadStory()` + une `Story` Foundry dédiée par controller (un Story par controller, pas un Story global).
- Functional : assertions sur le DOM via `Crawler` + `data-test-id="..."` (cf. `RecipeControllerTest.php`) — préférer un sélecteur `data-test-id` à un sélecteur de classe CSS qui peut bouger avec le design.
- E2E : Page Object par page (`tests/e2e/pages/<Name>Page.ts`), tests qui décrivent un parcours utilisateur, pas une page isolée.
- Pas de mock de la base en functional ni en E2E (cf. mémoire projet — Foundry + DB réelle).

**Parallélisation** : les sous-agents sont indépendants. Les spawner en **un seul message** avec plusieurs blocs `Agent` quand on a 2+ tests à écrire dans des surfaces différentes. Les tests sur la même surface (ex: 3 controllers admin) → un seul agent batch (cohérence de style).

### Phase 4 — Couverture & analyse des manques

Une fois tous les sous-agents revenus :

1. **Lancer** : `make test.coverage` (HTML dans `coverage/`).
2. **Lire** : ouvrir `coverage/index.html` ou parser `coverage/index.xml` si présent. Pour chaque fichier modifié sur la branche, relever **lines covered / total** et **branch coverage** si dispo.
3. **Diagnostic** sur trois axes :
   - **Manques durs** : un fichier modifié à 0 % de couverture → un test manque, retourner Phase 2/3 pour le couvrir.
   - **Branches non couvertes** : si une condition (`if`, `match`, ternaire) sur le diff est rouge → le test passe le chemin happy mais pas l'edge case. Demander un cas DataProvider en plus.
   - **Couverture trop large** : un fichier non touché par la branche qui passe de 80 % à 95 % → souvent signe d'un test functional sur-spécifié qui assert sur des branches non liées au changement. Pas un blocage, mais à signaler comme bruit potentiel.
4. **Seuil de décision** :
   - Code de chemin critique (controller, service métier touché) : viser **≥ 90 % lines + 80 % branches**.
   - Code de support (subscribers, validators) : **≥ 80 % lines** suffit si les branches conditionnelles sont couvertes.
   - **Ne jamais** demander 100 % — chasser les `// @codeCoverageIgnore` ou les fallback `throw new \LogicException('unreachable')` est une perte de temps. Les marquer plutôt qu'écrire un test impossible.
5. **Reporter au user** : tableau `surface | niveau choisi | fichier de test | couverture obtenue | manques restants`. Phrases courtes.

## Sortie attendue à chaque invocation

Avant de spawner quoi que ce soit, produire un **plan court** (≤ 15 lignes) :

```
Surfaces touchées :
- src/Controller/RecipeController.php   → functional   (test du controller, couvre Repository + Filter)
- src/Services/Recipe/IngredientParser.php → unit      (logique combinatoire, DataProvider)
- assets/controllers/recipe_portions_controller.js → e2e (interaction DOM côté client)

Tests à écrire : 3
Tests existants à étendre : 0
Estimation coverage cible sur le diff : ~92 % lines
```

Le user peut interrompre ou rerouter avant le spawn.

## Anti-patterns

- ❌ Tester chaque service individuellement quand un controller-level fait le job en un seul fichier.
- ❌ Écrire un E2E pour valider une logique qui peut être testée en unit ou functional.
- ❌ Écrire un E2E pour un parcours **sans JavaScript** (page rendue serveur, lien `<a href>` natif, form classique, redirect, 404). Sans JS dans le chemin, c'est par définition un test functional Symfony. En cas d'hésitation E2E vs functional sans JS dans le parcours → **toujours functional**.
- ❌ Mocker la DB ou les entités dans un test functional (cf. mémoire — interdiction projet).
- ❌ Tester `src/Entity/**` directement — exclu de la couverture, sans valeur (getters/setters auto, validation testée via Form ou Validator).
- ❌ Lancer `make test.coverage` avant que tous les sous-agents soient revenus — la mesure est trompeuse.
- ❌ Pousser pour 100 % de couverture. Le seuil utile est 80–90 %, le reste c'est de la chasse aux dépouilles.
- ❌ Écrire des assertions sur du markup qui n'a pas de `data-test-id` (le design bouge, le test casse pour rien).
- ❌ Créer un nouveau Story Foundry quand un existant couvre déjà le besoin — étendre l'existant.

## Quand ce skill ne suffit pas

- **Test rouge isolé à debugger** → pas ce skill, lecture directe + `make test` ciblé.
- **Refactor de la suite de tests** (lenteur, fragilité, doublons) → `refactoring-expert` ou `sc:improve`.
- **Mise en place d'un nouveau type de test** (ex: tests de mutation, tests visuels) → décision d'archi, pas un workflow récurrent.
- **CI / GitHub Actions cassée** → `devops-architect`, pas ce skill.
