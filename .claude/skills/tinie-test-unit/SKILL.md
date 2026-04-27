---
name: tinie-test-unit
description: Skill d'écriture de tests unitaires PHPUnit pour Tinie Bakerie. Couvre l'API attributs PHPUnit, un test = un comportement, naming explicite, assertions qui cassent vraiment, mocking minimal au profit de Builders/Foundry, edge cases pour faire tomber le code. À activer dès qu'on écrit ou modifie un fichier sous `tests/Unit/**`, ou quand l'orchestrateur (`tinie-test-orchestrator`) délègue un test de niveau unit (service pur, Form Type, Validator, EventSubscriber, Twig Component, Slugger, etc.). Ne PAS activer pour des tests qui bootent le kernel Symfony ou une DB (c'est `tinie-test-functional`).
---

# Tinie Bakerie — Unit Tests

Skill **spécialiste**. Écrit des tests unitaires PHPUnit isolés, rapides, qui cassent uniquement quand le comportement change.

## Stack

- **PHPUnit** : version verrouillée dans `composer.lock` (clé `phpunit/phpunit`). Toujours s'aligner sur cette version, ne pas s'appuyer sur des souvenirs d'une autre majeure.
- API **attributs uniquement** — aucune `@dataProvider` ou `@covers` en docblock.
- Tests sous `tests/Unit/<Namespace miroir de src/>/<Classe>Test.php`.
- Run ciblé : `make test.unit`, ou `docker compose exec php vendor/bin/phpunit tests/Unit/<chemin>`.
- Lancer fréquemment, c'est rapide — feedback < 2s par fichier.

## En cas de doute → context7, ne pas inventer

Si tu hésites sur un attribut PHPUnit, le nom exact d'une assertion, l'API d'un mock, le comportement d'un fixture, ou la migration entre versions : **interroger context7 avant de coder**, jamais après. Le coût d'un `query-docs` est de quelques secondes, le coût d'un test qui ment ou d'un attribut qui n'existe pas est bien plus élevé.

**Résoudre la library ID dynamiquement** (le projet peut migrer de version à tout moment) :

1. Lire la version exacte dans `composer.lock` → bloc `"name": "phpunit/phpunit"`, champ `"version"`.
2. Appeler `mcp__context7__resolve-library-id` avec `libraryName: "PHPUnit"` et un `query` qui mentionne la majeure/mineure trouvée → récupérer l'ID de la forme `/websites/phpunit_de_en_<major>_<minor>`.
3. Utiliser cet ID dans `mcp__context7__query-docs`. Ne **pas** se rabattre sur une version voisine — les attributs et la matrice d'API évoluent à chaque majeure.

**Quand interroger** :
- On va écrire un attribut qu'on n'a pas utilisé récemment (`#[TestWithJson]`, `#[DataProviderExternal]`, `#[Depends]`, `#[BackupGlobals]`…).
- On hésite entre deux assertions proches (`assertSame` vs `assertEquals`, `assertCount` vs `assertSameSize`, `assertEqualsCanonicalizing`…).
- On tape une API mock peu courante (matcher d'arguments custom, `willReturnCallback`, méthodes dont la signature a bougé entre majeures).
- On vérifie qu'un attribut docblock historique a bien un équivalent attribut PHP dans la version courante.
- Le test passe trop facilement et on suspecte une assertion silencieuse (typique : `expectException` mal configuré, `assertThat` avec contrainte mal nommée).

**Quand ne pas interroger** : ce qu'on connaît déjà (`assertSame`, `setUp`, `expectException`, `createMock`/`createStub`, `#[CoversClass]`, `#[DataProvider]`, `#[Test]`). Pas de zèle, c'est une roue de secours.

Règle ferme : préférer 30 secondes de `query-docs` à un test fragile basé sur une mémoire approximative. Si la réponse de context7 contredit ce que ce skill affirme → remonter au user, ne pas trancher seul.

## Squelette de référence

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Slug;

use App\Services\Slug\Slugger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Slugger::class)]
final class SluggerTest extends TestCase
{
    private Slugger $slugger;

    protected function setUp(): void
    {
        $this->slugger = new Slugger();
    }

    #[Test]
    #[TestDox('lowercases mixed-case input')]
    public function itLowercasesMixedCaseInput(): void
    {
        self::assertSame('hello-world', $this->slugger->slugify('Hello World'));
    }
}
```

Règles de squelette :
- `declare(strict_types=1)` toujours.
- `final class … Test extends TestCase` — empêche les sous-classes de tests, qui sont une dette à coup sûr.
- `@internal` en docblock (le projet l'utilise partout, garde la cohérence).
- `#[CoversClass(...)]` au niveau classe pour **chaque** classe sous test (un test peut en couvrir plusieurs si elles vivent ensemble — ex: une exception métier liée à un service).
- `#[UsesClass(...)]` pour les classes qu'on instancie réellement mais qu'on ne couvre pas (évite les warnings "indirect coverage" en mode strict).
- `#[Test]` pour pouvoir nommer en anglais lisible (`itLowercasesMixedCaseInput`) au lieu du préfixe `test`. Garder une convention par fichier — soit toutes les méthodes en `#[Test] itXxx()`, soit toutes en `testXxx()`. Pas de mélange.
- `#[TestDox('phrase descriptive')]` quand le nom de méthode reste cryptique malgré l'effort.

## 1 test = 1 comportement

**Règle** : une méthode de test décrit **un seul** comportement observable. Si on doit écrire « *and* » dans la phrase de description, c'est deux tests.

| Mauvais | Bon |
|---|---|
| `testSlugifyWorks()` | `itLowercasesMixedCaseInput()`, `itTrimsLeadingSpaces()`, `itCollapsesMultipleSpacesIntoSingleHyphen()` |
| Une méthode avec 5 `assertSame` sur 5 inputs différents | Un `#[DataProvider]` ou `#[TestWith]` qui fait varier l'input |
| Asserter l'état avant *et* la valeur de retour *et* les side-effects | Trois tests, ou regrouper en un test « happy path » + tests dédiés pour chaque side-effect spécifique |

**Conséquence** : un test rouge pointe instantanément vers la fonction cassée, sans avoir à lire 30 lignes de scénario.

## Naming explicite

Le nom doit décrire **le comportement attendu**, pas la méthode appelée :

- ✅ `itReturnsEmptyStringWhenInputIsOnlyWhitespace`
- ✅ `itThrowsWhenLocaleIsNotSupported`
- ✅ `itAttachesViolationToTranslationsWhenLocaleIsDuplicated`
- ❌ `testSlugify()` (quoi exactement ?)
- ❌ `testSlugifyReturnsString` (forcément)
- ❌ `testEdgeCase1` (lequel, et pourquoi ?)

Format : `it<VerbAuPrésent><Complé="ment><WhenCondition>?`. En `#[TestWith]` / `#[DataProvider]`, **la clé du yield porte la phrase** :

```php
yield 'collapses multiple spaces into single hyphen' => ['hello    world', 'hello-world'];
yield 'trims trailing whitespace' => ['hello   ', 'hello'];
```

Quand un test casse, le nom de la clé apparaît dans la sortie — ça raconte directement le bug.

## Features PHPUnit à utiliser

### DataProvider — pour multiplier les cas

```php
#[Test]
#[DataProvider('provideSlugificationCases')]
#[DataProvider('provideAccentCases')]      // empilable
public function itSlugifies(string $input, string $expected): void
{
    self::assertSame($expected, $this->slugger->slugify($input));
}

/** @return iterable<string, array{0: string, 1: string}> */
public static function provideSlugificationCases(): iterable
{
    yield 'simple lowercase passes through' => ['hello', 'hello'];
    yield 'space becomes hyphen' => ['a b', 'a-b'];
}
```

- Provider **`public static`** (les versions modernes de PHPUnit le forcent).
- `yield` avec **clé descriptive**, jamais en array indexé.
- Plusieurs `#[DataProvider]` empilés sur une même méthode = OK, regroupent par catégorie. Pattern utilisé dans `tests/Unit/Services/Slug/SluggerTest.php`.

### TestWith — pour 1-3 cas inline

Quand on a 1-3 cas et que créer un provider est trop verbeux :

```php
#[Test]
#[TestWith(['', ''])]
#[TestWith(['   ', ''])]
#[TestWith(["\t\n", ''])]
public function itReturnsEmptyOnBlankInput(string $input, string $expected): void
{
    self::assertSame($expected, $this->slugger->slugify($input));
}
```

Au-delà de 3-4 → bascule en `DataProvider` pour la lisibilité.

### Autres attributs utiles

| Attribut | Quand l'utiliser |
|---|---|
| `#[CoversClass(X::class)]` | Classe principale sous test. Sans ça, la coverage est attribuée par fichier source touché → imprécis. |
| `#[UsesClass(X::class)]` | VO ou DTO instanciés dans le test mais pas la cible. Évite les warnings de PHPUnit en `--strict-coverage`. |
| `#[CoversNothing]` | Test d'intégration de plusieurs classes où aucune n'est la cible primaire (rare en unit). |
| `#[Group('slow')]` | Marquer un test lent pour pouvoir l'exclure ; `--group=slow`/`--exclude-group=slow`. À utiliser parcimonieusement. |
| `#[TestDox(...)]` | Descriptif lisible au lieu du nom de méthode. Surtout utile en data-driven. |
| `#[DoesNotPerformAssertions]` | Test qui vérifie « ça ne lève pas d'exception ». Évite l'avertissement « risky test ». |
| `#[Depends('itLoads')]` | À éviter — couple les tests. Préférer rendre chaque test autonome. |

Annotations docblock (`@dataProvider`, `@covers`, `@test`, `@expectedException`) → **interdites**, les versions modernes de PHPUnit ne les lisent plus.

### Exceptions

Pas d'attribut `#[ExpectedException]`. Dans le corps :

```php
$this->expectException(UnexpectedTypeException::class);
$this->expectExceptionMessage('Expected ArrayCollection');
$this->expectExceptionCode(42);

$validator->validate('wrong', $constraint);
```

Toujours **après** le setup et **avant** l'appel qui doit lever.

## Assertions qui cassent vraiment

Une assertion utile pose une **contrainte sur le comportement** ; si quelqu'un change le code par erreur, le test rougit. Une assertion creuse passe quoi qu'il arrive.

| ❌ Anti-pattern | ✅ Préférer |
|---|---|
| `assertNotNull($result)` sur une méthode qui retourne `string` (impossible à null) | `assertSame('expected', $result)` |
| `assertIsArray($result)` sans vérifier le contenu | `assertSame(['a', 'b'], $result)` |
| `assertCount(3, $result)` sans vérifier l'identité des éléments | `assertSame(['a', 'b', 'c'], array_map(fn ($x) => $x->name, $result))` |
| `assertTrue($x->isValid())` quand `$x` n'a aucune raison d'être invalide | Tester aussi un cas invalide pour prouver que le check existe |
| `assertStringContainsString('error', $message)` | `assertSame('Locale "xx" is not supported', $message)` |
| `$this->expectException(\Throwable::class)` | Type le plus précis possible (`UnexpectedTypeException::class`) + `expectExceptionMessage(...)` |
| `assertEquals` (compare lâche) | `assertSame` (compare strict, identité d'instance pour les objets) — sauf besoin explicite d'equals |

**Test du retrait** appliqué aux assertions : *« Si je retire cette ligne, est-ce qu'un bug réel pourrait passer ? »*. Non = inutile. Un seul `assertSame` ciblé bat 5 assertions vagues.

**Mutation mentale** : avant de valider un test, se demander *« quel changement minimal du code de prod pourrait casser cette assertion ?»*. Si la réponse est « rien d'évident » → l'assertion est trop lâche.

## Mocker le strict minimum — Builder Pattern par défaut

**Hiérarchie de préférence**, du plus réel au moins réel :

1. **Objet réel construit avec `new`** — value object, DTO, exception, simple data class. Toujours préférer.
2. **Foundry Factory** (`src/Factory/*Factory.php`) — pour toute entité Doctrine. Foundry **est** le builder du projet, ne pas le doubler. En unit, utiliser `RecipeFactory::createOne()` avec `withoutPersisting()` si on n'a pas de DB :
   ```php
   $recipe = RecipeFactory::new()->withoutPersisting()->create(['cookingTime' => 30])->_real();
   ```
   Si la factory n'existe pas pour une entité touchée → la créer dans `src/Factory/` (cf. les voisines pour le style).
3. **Builder maison** (`tests/Builder/<Name>Builder.php`) — pour les **non-entités** : DTOs, value objects, payloads de requête, agrégats de service complexes. Créer le builder s'il n'existe pas.
4. **Stub** (`createStub(Interface::class)`) — collaborateur dont on ne vérifie que le retour, pas l'appel.
5. **Mock** (`createMock(...)` + `expects(...)`) — **uniquement** quand on doit prouver qu'une interaction a eu lieu (ex: `ExecutionContextInterface::buildViolation` doit être appelé exactement 1 fois). Mocker un objet sans `expects` = code smell, utiliser un stub.

**Anti-pattern majeur** : mocker un objet de domaine qu'on possède (`$post = $this->createMock(Post::class)`). Construire un vrai `Post` ou créer la factory foundry associée. Mocker des objets externes (Doctrine `EntityManager`, Symfony `ExecutionContextInterface`, services tiers) = OK, c'est légitime.

### Convention du Builder maison

Si la factory foundry ne matche pas le pattern, passer sur un Builder Pattern maison.

Localisation : `tests/Builder/<Name>Builder.php`. Squelette :

```php
<?php

declare(strict_types=1);

namespace App\Tests\Builder;

use App\Services\Search\SearchQuery;

final class SearchQueryBuilder
{
    private string $term = 'chocolat';
    private string $locale = 'fr';
    private int $limit = 10;

    public static function new(): self
    {
        return new self();
    }

    public function withTerm(string $term): self
    {
        $clone = clone $this;
        $clone->term = $term;

        return $clone;
    }

    public function withLocale(string $locale): self
    {
        $clone = clone $this;
        $clone->locale = $locale;

        return $clone;
    }

    public function build(): SearchQuery
    {
        return new SearchQuery($this->term, $this->locale, $this->limit);
    }
}
```

Règles :
- **Defaults sains** : un `Builder::new()->build()` retourne un objet **valide** dans le cas le plus courant. Le test n'écrit que ce qui s'écarte du défaut.
- **Immutable fluent** : `with*()` retourne un **clone**, pas `$this`. Évite que deux tests qui partagent un builder se contaminent.
- **Pas de `setX()`** : les builders ne sont pas des entités.
- **Un builder par classe non-entité non-triviale**. Pas de builder pour un objet à 1 propriété, on instancie directement.
- **Pas de logique métier** dans le builder — c'est une fabrique de fixtures, pas une implémentation alternative.
- Quand un builder n'existe pas et qu'un test en aurait besoin → **le créer dans le même PR**. C'est un investissement qui rentabilise dès le 2ᵉ test.

## Edge cases — faire tomber le code

Un fichier de test qui ne contient que le happy path **est inutile**. Pour chaque méthode publique non triviale, lister mentalement les axes d'edge cases puis écrire un cas par axe pertinent :

- **Vide** : chaîne `''`, array `[]`, collection vide, `null` si nullable.
- **Whitespace seul** : `'   '`, `"\t\n\r"`.
- **Unicode** : accents (`café`), emojis (`🥐`), CJK, RTL (`مرحبا`), normalisation (NFC vs NFD).
- **Bornes numériques** : 0, 1, -1, `PHP_INT_MAX`, `PHP_INT_MIN`, négatifs si non attendus.
- **Bornes temporelles** : passé / futur, fuseaux différents, DST, dates invalides (`2025-02-30`).
- **Doublons** : même clé, même slug, même locale (cf. `ValidTranslationsValidatorTest::testValidateWithDuplicateLocales`).
- **Casse** : `Hello`, `HELLO`, `hello`, mélanges. Surtout sur slug, recherche, comparaison de locale.
- **Caractères spéciaux** : `&`, `<`, `>`, `"`, `'`, `\`, `/`, `..`, `.`, ` ` final, séparateurs (`-`, `_`).
- **Très long input** : 10⁴ caractères pour vérifier la perf et la troncature.
- **Type inattendu** : float au lieu d'int, array au lieu de string (si signature accepte `mixed` ou si désérialisé).
- **État pré-existant** : appel deux fois de suite, idempotence, contention.
- **Ordre** : entrée non triée, doublons en désordre.
- **Locale absente / mal formée** : `'xx'`, `''`, `'EN'` (majuscule), `'en_US'` vs `'en-US'`.

**Règle pragmatique** : si un edge case ne peut **pas** atteindre la fonction (input filtré en amont par le framework, contrainte typée en signature), ne pas le tester ici. Mais s'il peut atteindre la fonction et que le code n'a aucune protection, c'est exactement là qu'il faut un test — soit il devra durcir le code, soit prouver que le comportement actuel est intentionnel.

## Ne pas tester inutilement

Pas de test pour :

- **Getters / setters** triviaux (renvoient une propriété telle quelle). Le mainteneur est plus utile ailleurs.
- **Constructeurs** qui ne font qu'assigner — couvert par tout autre test qui instancie la classe.
- **Délégation pure** d'une méthode à un autre service sans transformation. Tester le service en aval, pas le wrapper.
- **Configuration Symfony** (services.yaml, Doctrine mapping). Couvert par le boot du kernel en functional.
- **Code généré** (Doctrine proxies, Foundry factories, EasyAdmin CRUD).
- **Classes `final readonly` value objects** sans logique. L'assignation est correcte par typage, c'est tout.
- **`__toString()` qui concatène** — sauf format public documenté (slug, identifiant lisible).
- **Une dépendance externe** (Doctrine, Symfony Validator). Tester *qu'on l'utilise correctement*, pas qu'elle fonctionne.

Si tu ne sais plus pourquoi un test existe en le relisant 6 mois plus tard → c'était un test à ne pas écrire.

## Definition of Done — checklist test unit

Avant de considérer un test fini :
- [ ] Une seule classe couverte (ou groupe cohérent justifié), `#[CoversClass]` posé
- [ ] Un test = un comportement nommé en clair
- [ ] Happy path **+** au moins 2 edge cases pertinents
- [ ] Mocks limités aux frontières externes ; objets de domaine = vrais ou via Builder/Foundry
- [ ] Assertions qui pointent une valeur précise (pas `assertNotNull` paresseux)
- [ ] DataProvider/TestWith dès qu'on a 3+ cas similaires, clés descriptives
- [ ] `setUp` factorise uniquement ce qui est partagé par **tous** les tests du fichier
- [ ] Aucun `sleep`, aucun appel réseau, aucune écriture disque
- [ ] Run < 100 ms par test (sinon c'est probablement du functional déguisé)
- [ ] PHPStan reste vert (`make phpstan`)

## Anti-patterns

- ❌ Tests qui appellent la méthode sous test mais ne vérifient rien (ou seulement `assertNotNull`).
- ❌ `setUp` énorme qui prépare l'état de 12 tests dont 11 n'en ont pas besoin.
- ❌ Mock de l'objet sous test, ou partial mock (`getMockBuilder()->onlyMethods()`). Si tu ressens le besoin, la classe a une responsabilité de trop — refacto.
- ❌ Assertions sur des messages d'erreur localisés ou formatés (`'Le slug ne peut être vide'`). Asserter sur le **type** d'exception et un identifiant stable, pas sur la traduction.
- ❌ Tests qui dépendent de l'ordre d'exécution (état partagé statique, `#[Depends]`).
- ❌ Tests d'entité Doctrine en isolation pure (les entités sont exclues de la coverage par `phpunit.xml.dist` — tester ailleurs).
- ❌ Réutiliser une factory Foundry qui persiste sans `withoutPersisting()` dans un test unit (ralentit + nécessite la DB).
- ❌ `assertEquals` quand `assertSame` suffit (et il suffit presque toujours).
- ❌ Tests qui copient l'implémentation en miroir (`if input is empty return empty` côté prod, et `if input is empty assert empty` côté test). Tester le **contrat**, pas le code.

## Quand ce skill ne suffit pas

- Test qui doit booter le kernel Symfony, taper la DB, ou rendre un controller → **`tinie-test-functional`**.
- Test de parcours navigateur → **`tinie-test-e2e`**.
- Décision de niveau (unit vs functional) → remonter à **`tinie-test-orchestrator`**.
- Refacto profonde de la classe sous test pour la rendre testable → c'est un signal d'archi, pas un travail de test ; remonter au user.
