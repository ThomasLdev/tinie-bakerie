---
name: tinie-test-functional
description: Skill d'écriture de tests fonctionnels Symfony pour Tinie Bakerie. Choisit entre `KernelTestCase` (logique côté serveur, services qui dépendent du container/DB) et `WebTestCase` (parcours HTTP avec rendu DOM), pilote les fixtures via Foundry + Stories, assertions DOM via `data-test-id` posés par `{{ test_id(...) }}`. À activer pour tout fichier sous `tests/Functional/**`, ou quand `tinie-test-orchestrator` délègue un test de niveau functional (controller HTTP, repository custom, service intégré, EasyAdmin CRUD). Ne PAS activer pour de la logique pure isolée (`tinie-test-unit`) ni pour un parcours navigateur multi-pages avec JS (`tinie-test-e2e`).
---

# Tinie Bakerie — Functional Tests

Skill **spécialiste**. Écrit des tests fonctionnels Symfony qui bootent le kernel, branchent une vraie DB (Foundry), et observent le **vrai comportement** d'une surface (controller, service, repository) — sans mocks de domaine.

## Stack

- **Symfony** : version verrouillée dans `composer.lock`. S'aligner sur la version courante, ne pas s'appuyer sur des souvenirs d'une LTS antérieure.
- **PHPUnit** : version verrouillée dans `composer.lock`. API attributs uniquement.
- **Fixtures** : Foundry (`Zenstruck\Foundry`) — factories sous `src/Factory/`, stories sous `tests/Story/`. Une story par surface (controller/service) qui pose un état déterministe.
- **Base controller test case** : `tests/Functional/Controller/BaseControllerTestCase.php` — encapsule `KernelBrowser`, `ResetDatabase`, `Factories`, `loadStory()` et le HTTPS host `local.tinie-bakerie.com`.
- **Helper DOM** : `App\Twig\TestExtension::renderTestId()` rend `data-test-id="..."` **uniquement en env test** via `{{ test_id('foo') }}`. C'est le **pivot** de toutes les assertions DOM.
- **Run** : `make test.functional`, ou `docker compose exec php vendor/bin/phpunit tests/Functional/<chemin>`.

## En cas de doute → context7, ne pas inventer

Les tests fonctionnels Symfony évoluent vite (constraints de testing, attributs, helpers `WebTestCase`/`KernelTestCase`, integration Foundry/Doctrine). En cas d'hésitation : **interroger context7 avant de coder**.

**Résoudre les library IDs dynamiquement** (le projet peut migrer à tout moment) :

1. Lire les versions exactes dans `composer.lock` → blocs `"name": "symfony/framework-bundle"` (ou `symfony/symfony`) et `"name": "phpunit/phpunit"`.
2. Appeler `mcp__context7__resolve-library-id` avec `libraryName: "Symfony"` (et un `query` qui mentionne le sujet : `WebTestCase`, `KernelTestCase`, `assertResponseRedirects`, `data fixtures testing`, `LoginUser`, `submitForm`, etc.) → récupérer l'ID Symfony pour la majeure courante.
3. Idem pour PHPUnit si la question concerne un attribut/assertion PHPUnit.
4. Pour Foundry / Zenstruck : `libraryName: "Zenstruck Foundry"` — pareil, version courante.
5. Utiliser ces IDs dans `mcp__context7__query-docs`. Ne **pas** se rabattre sur une version voisine.

**Quand interroger** :
- Helpers d'assertion HTTP qu'on n'a pas utilisé récemment (`assertResponseRedirects`, `assertResponseHeaderSame`, `assertSelectorTextContains`, `assertCheckboxChecked`…).
- API client (`loginUser`, `catchExceptions`, `disableReboot`, `enableProfiler`, request avec serveur params).
- Foundry — `_real()`, `withoutPersisting()`, `as()`, `pool()`, intégration avec `ResetDatabase`.
- Doctrine — `clear()` à propos après modification, gestion des filtres SQL pendant le test, transactions.
- Crawler — sélecteurs avancés, `filterXPath`, `selectButton`, `submitForm`.

**Quand ne pas interroger** : ce qu'on connaît déjà (`assertResponseIsSuccessful`, `assertSame`, `request('GET', ...)`, `loadStory()`, `RecipeFactory::createOne`). Roue de secours, pas zèle.

Règle ferme : si la doc context7 contredit ce skill → remonter au user.

## Décider : `KernelTestCase` vs `WebTestCase`

**Règle binaire** :
- Le test a besoin d'**inspecter du HTML rendu** (DOM, redirects, cookies, statut HTTP, headers, profiler) → `WebTestCase` (via `BaseControllerTestCase`).
- Le test n'a besoin que d'**appeler du PHP côté serveur** (un service, un repository, un event listener, un command handler, un message handler) → `KernelTestCase`.

`WebTestCase` boote un **HTTP kernel complet** + simule un client. Il est strictement plus lourd que `KernelTestCase`. Choisir le plus léger qui couvre le risque.

| Surface | Choix par défaut | Pourquoi |
|---|---|---|
| Controller HTTP front public (`src/Controller/*`) | `WebTestCase` | On veut le statut + le DOM + redirects + headers |
| Controller EasyAdmin (`src/Controller/Admin/*`) | `WebTestCase` | Idem + auth + flow CRUD |
| Service métier qui dépend du container ET de la DB (search, query, repository custom) | `KernelTestCase` | Pas de DOM à observer, on appelle directement le service |
| Repository custom avec QueryBuilder complexe / DQL | `KernelTestCase` | Test du SQL, pas du HTTP |
| EventSubscriber Doctrine / Lifecycle listener | `KernelTestCase` | On déclenche un flush, on observe le résultat |
| Validator avec dépendances container | `KernelTestCase` | Idem |
| Command Console (`bin/console foo:bar`) | `KernelTestCase` + `CommandTester` | Pas d'HTTP, juste l'application console |
| Message handler (Messenger) | `KernelTestCase` + `InMemoryTransport` | Boucle messenger sans bus HTTP |
| Form Type avec services (datatransformers, choices DB) | `KernelTestCase` (`TypeTestCase` ne suffit pas si dépendances container) | On a besoin du container |

Si l'auteur du test hésite entre les deux pour un même fichier → c'est probablement deux fichiers de test (un kernel-level pour la logique, un web-level pour l'HTTP smoke).

## Squelette : controller HTTP (`WebTestCase`)

Référence : `tests/Functional/Controller/RecipeControllerTest.php` + `tests/Story/RecipeControllerTestStory.php`.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\RecipeController;
use App\Repository\RecipeRepository;
use App\Tests\Story\RecipeControllerTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(RecipeController::class)]
#[CoversClass(RecipeRepository::class)]
final class RecipeControllerTest extends BaseControllerTestCase
{
    public function testIndexShowsActiveRecipesOrderedByCreatedAtDesc(): void
    {
        /** @var RecipeControllerTestStory $story */
        $story = $this->loadStory(static fn () => RecipeControllerTestStory::load());

        $crawler = $this->client->request(Request::METHOD_GET, '/fr/recettes');

        self::assertResponseIsSuccessful();

        $cards = $crawler->filter('[data-test-id^="recipe-card-"]');
        self::assertCount(\count($story->getActiveRecipes()), $cards);
    }
}
```

## Squelette : service intégré (`KernelTestCase`)

Référence : `tests/Functional/Services/Search/PostSearchTest.php`.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services\Search;

use App\Services\Search\PostSearch;
use App\Tests\Story\PostSearchTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
#[CoversClass(PostSearch::class)]
final class PostSearchTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private PostSearch $search;

    protected function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);

        $container = self::getContainer();
        $this->search = $container->get(PostSearch::class);
    }

    public function testItRanksTitleMatchesAboveBodyMatches(): void
    {
        PostSearchTestStory::load();

        $results = $this->search->search('chocolat', 'fr');

        self::assertSame(['Tarte au chocolat', 'Note sur le chocolat'], array_map(
            static fn ($r) => $r->title,
            $results,
        ));
    }
}
```

## 1 test = 1 comportement

Comme en unit. Une méthode = un comportement observable. Si tu écris « *and* » dans la description → deux tests.

| Mauvais | Bon |
|---|---|
| `testRecipeFlow()` qui crée, liste, modifie | `testIndexLists*`, `testShowDisplays*`, `testEditPersists*` |
| `testIndex()` qui asserte 8 choses sur le DOM | `testIndexShows*Recipes()`, `testIndexOrdersByCreatedAtDesc()`, `testIndexHidesInactiveRecipes()` |
| Une méthode qui boucle sur 5 URLs | `#[DataProvider]` qui yield les 5 URLs avec clé descriptive |

Le test doit pointer **un seul** point de défaillance. Quand il rougit, le nom suffit à diagnostiquer.

## Setup : dépendances réelles depuis le container

**Règle** : la grande majorité des dépendances vient du **container**, pas d'un mock. Le test prouve que la chaîne réelle marche, pas que des mocks bien arrangés se parlent.

`setUp` typique d'un test fonctionnel :

```php
protected function setUp(): void
{
    self::bootKernel(['environment' => 'test']);
    $container = self::getContainer();

    $this->subject = $container->get(MaSurface::class);
    $this->em = $container->get(EntityManagerInterface::class);
}
```

**Ce qu'on prend toujours du container** :
- Le service sous test (`MaSurface::class`).
- `EntityManagerInterface` quand on a besoin d'observer la DB.
- Repositories Doctrine (`$container->get(MyRepository::class)`).
- Services métier collaborateurs (translator, validator, slugger, services maison).
- `RequestStack` quand le service en dépend (et on push une `Request` réelle dedans, pas un mock).
- Bus Messenger, Cache, Filesystem, Logger via le container.

**Ce qu'on peut mocker dans un functional** (rare, et toujours justifié) :
- Adapters externes : HTTP client vers une API tierce (Stripe, Mailchimp, OpenAI). Utiliser `MockHttpClient` plutôt que `createMock`.
- Horloge : `ClockInterface` quand on a besoin d'un temps figé.
- Sources d'aléa : générateur d'UUID, randomizer.
- Services lourds non pertinents pour le test (envoi d'email réel → `MailerInterface` peut rester réel, l'env test capture les mails dans le profiler ; pas besoin de mocker).

**Ce qu'on ne mock pas en functional** :
- Doctrine (EM, repositories, connexion). Foundry + `ResetDatabase` gèrent l'isolation.
- Symfony Validator, Translator, Form, Security : tout vient du container.
- Les entités du domaine, jamais.
- Les autres services maison non-externes.

### Signal SRP : setUp qui dérape

Si le `setUp` dépasse **~15 lignes utiles** (hors boot kernel + prises de container évidentes), ou si tu dois construire 3+ collaborateurs avec leurs propres dépendances pour que le service réponde, **stop** : c'est un signal **fort** que la classe sous test viole le SRP.

Symptômes typiques :
- 5+ services pris du container, dont la moitié ne servent qu'à un cas de test sur deux.
- Une `Story` Foundry qui doit poser **plus de 4 entités** différentes pour qu'un seul cas passe (suggère que la surface couvre trop de chemins).
- Du code de "préparation d'état" copié-collé dans plusieurs `testXxx`.
- On hésite entre tester 6 cas distincts ou 1 cas qui assert 6 choses.

Action : **remonter au user**, ne pas masquer le problème en complexifiant le test.

> *« Ce test demande {N} dépendances pour boot. La classe `Foo` semble porter responsabilités A/B/C. Faut-il splitter avant de tester, ou écrire les tests sur la classe actuelle quand même ? »*

L'écriture du test n'est **pas** le moment où on cache une dette d'architecture sous une pile de mocks ; c'est le moment où on la révèle.

## Assertions sur l'état réel

### Côté service / repository (KernelTestCase)

Asserter sur **la valeur de retour** ou **l'état observable de la DB**. Pas de mock-d'observabilité.

| ❌ | ✅ |
|---|---|
| `assertNotNull($result)` | `assertSame(['Tarte au chocolat', 'Brioche au chocolat'], array_column($result, 'title'))` |
| `assertCount(3, $result)` (count seul) | `assertCount(3, ...)` **+** assertion sur l'identité des éléments |
| `assertTrue($repository->save($x))` (la méthode `save` ne renvoie probablement rien d'utile) | `$em->clear(); $reloaded = $repository->find($id); assertSame('expected', $reloaded->getTitle())` |
| « pas d'exception levée » comme seule assertion | Si c'est vraiment ça qu'on teste, `#[DoesNotPerformAssertions]` + commentaire ; sinon ajouter une assertion concrète |

**Toujours `$em->clear()` avant de reload depuis la DB**, sinon Doctrine rend l'objet en cache et masque les bugs de mapping/cascade. Pattern présent dans `BaseControllerTestCase::loadStory()`.

### Côté DOM (WebTestCase)

**Règle ferme** : on cible le DOM via `data-test-id`, **jamais** via classes CSS, IDs CSS, sélecteurs structurels, ou textes traduits.

Pourquoi : le design bouge (refonte en cours `DESIGN/recipe-page`), les classes BEM/utilitaires changent, les libellés sont traduits FR/EN. Un test qui filtre `.recipe-card__title` casse à chaque refonte sans qu'aucun comportement n'ait régressé. Un test qui filtre `[data-test-id="recipe-card-12"]` ne casse que si le contrat de testabilité change.

#### Poser le `data-test-id` côté Twig

Utiliser le helper Twig `test_id()` (porté par `App\Twig\TestExtension`). Il rend `data-test-id="..."` **uniquement en env test** — pas de pollution du DOM en prod/dev.

```twig
{# templates/recipe/index.html.twig #}
<article {{ test_id('recipe-card-' ~ recipe.id) }} class="recipe-card">
  <a {{ test_id('recipe-card-link-' ~ recipe.id) }} href="...">
    <h2 {{ test_id('recipe-card-title-' ~ recipe.id) }}>{{ recipe.title }}</h2>
  </a>
</article>

<form {{ test_id('recipe-search-form') }}>
  <input {{ test_id('recipe-search-input') }} name="q" />
  <button {{ test_id('recipe-search-submit') }} type="submit">Chercher</button>
</form>
```

Conventions de nommage (kebab-case) :
- `<surface>-<rôle>` : `recipe-card`, `recipe-search-input`, `pagination-next`.
- `<surface>-<rôle>-<id>` quand l'élément est répété : `recipe-card-12`, `comment-author-3`.
- `<page>-<section>` pour les zones de page : `recipe-show-ingredients`, `header-nav`, `footer-newsletter`.
- Pas de hiérarchie en CSS-style (`.foo .bar`) — chaque ID est plat et global au DOM rendu.

#### Sélectionner depuis le test

```php
// Élément unique
self::assertSame(1, $crawler->filter('[data-test-id="recipe-search-form"]')->count());

// Liste
$cards = $crawler->filter('[data-test-id^="recipe-card-"]'); // préfixe
self::assertCount(3, $cards);

// Texte de l'élément
self::assertSame(
    'Tarte au chocolat',
    trim($crawler->filter('[data-test-id="recipe-card-title-12"]')->text()),
);

// Attribut
self::assertSame(
    '/fr/recettes/tarte-au-chocolat',
    $crawler->filter('[data-test-id="recipe-card-link-12"]')->attr('href'),
);

// Submit form ciblé
$client->submitForm(/* button selector */, [
    'q' => 'chocolat',
]);
// Pour cibler un form précis, préférer :
$form = $crawler->filter('[data-test-id="recipe-search-form"]')->form([
    'q' => 'chocolat',
]);
$client->submit($form);
```

#### Ce qu'on n'asserte PAS

- ❌ `assertSelectorExists('.recipe-card__title')` (classe CSS qui peut bouger).
- ❌ `assertSelectorTextContains('h2', 'Tarte')` (sélecteur structurel).
- ❌ `assertStringContainsString('<h2>Tarte', $crawler->html())` (encore plus fragile).
- ❌ Asserter sur des textes localisés sans `data-test-id` (`assertSelectorTextContains(..., 'Voir la recette')`). Soit asserter sur l'identifiant cible, soit sur l'attribut `href`/`action`.

#### Ce qu'on asserte sans `data-test-id`

Quelques choses qu'on garde via les helpers Symfony natifs (pas de `data-test-id` requis) :
- `assertResponseIsSuccessful()`, `assertResponseStatusCodeSame(404)`.
- `assertResponseRedirects('/fr/recettes')`.
- `assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8')`.
- `assertResponseFormatSame('html')`.

Ces assertions ne dépendent pas du markup, donc pas de pivot `data-test-id` nécessaire.

#### Si le markup à tester n'a pas de `data-test-id`

Avant d'écrire un test sur un sélecteur CSS ou structurel : **éditer le template pour poser un `{{ test_id(...) }}`**. C'est plus rapide que de maintenir un sélecteur fragile, et ça documente l'API de testabilité du composant. Dans la même PR que le test.

## Mocks autorisés en functional — checklist

Avant de mocker, demander : *« est-ce vraiment hors du périmètre du test ? »*

| Mockable raisonnablement | Garde-fou |
|---|---|
| Client HTTP externe (`HttpClientInterface`) | Utiliser `MockHttpClient` du container, pas `createMock` |
| `ClockInterface` | Si on a besoin d'un temps figé, sinon laisser réel |
| Générateur d'UUID, randomizer | Idem |
| Mailer pour vérifier qu'un mail part | Pas la peine — `assertEmailCount`, `assertEmailHtmlBodyContains` sur le profiler de mailer suffisent |

Quand tu dois mocker un service du domaine pour faire passer un test functional, c'est un signal SRP — voir section précédente.

## Edge cases pour functional

- **Locale** : tester les routes localisées (`/fr/...` ET `/en/...`) sur les controllers multilingues.
- **404** : ID/slug inexistant, entité inactive (`active = false` filtrée par `LocaleFilter` etc.), entité dans une autre locale.
- **Auth** : route protégée appelée sans login → redirect/401, login user via `loginUser()` puis re-test.
- **Method not allowed** : POST sur une route GET-only.
- **CSRF / form** : token absent, token invalide.
- **Pagination** : page 0, page négative, page > max, page sans résultats.
- **Tri / filtre** : ordre par défaut, ordre explicite, filtre vide vs filtre absent.
- **Doublons / unicité** : insertion qui viole une contrainte unique → 422 / message d'erreur sur `data-test-id` du champ.
- **Caractères spéciaux dans l'URL** : slug avec `é`, espaces encodés, etc.

Côté service (KernelTestCase) : appliquer aussi les edge cases unit (vide, null, unicode, bornes) sur les **vrais** chemins métier que la DB peut renvoyer.

## Definition of Done — checklist functional

Avant de considérer un test fini :
- [ ] Bonne classe de base : `WebTestCase` (DOM/HTTP) ou `KernelTestCase` (logique serveur)
- [ ] `#[CoversClass]` sur la cible primaire **+** classes traversées (Repository, Filter, Subscriber qui participent au chemin)
- [ ] Une méthode = un comportement, nom explicite
- [ ] Story Foundry dédiée à la surface, posée via `loadStory()` (controller) ou `MaStory::load()` (kernel)
- [ ] Aucun mock du domaine, dépendances réelles depuis le container
- [ ] `setUp` < ~15 lignes utiles ; sinon SRP signalé au user
- [ ] DOM ciblé via `data-test-id` posés par `{{ test_id(...) }}` — jamais de classe CSS
- [ ] Helpers Symfony pour HTTP (`assertResponseIsSuccessful`, `assertResponseRedirects`)
- [ ] Edge cases : 404, autre locale, état inactif, auth si pertinent
- [ ] `$em->clear()` avant de relire la DB pour vérifier un side-effect

## Anti-patterns

- ❌ Démarrer un test functional pour ce qui peut être testé en unit (logique pure, pas de container, pas de DB).
- ❌ Mocker `EntityManager`, un repository Doctrine, ou une entité du domaine.
- ❌ Asserter sur du markup via classes CSS (`.recipe-card__title`) ou structure (`h2 > a`).
- ❌ Asserter sur des textes traduits (`assertSelectorTextContains(..., 'Voir la recette')`) — ça casse quand FR ↔ EN.
- ❌ Setup à 30 lignes qui prépare 6 entités pour 1 test. Splitter la classe ou la story.
- ❌ Story Foundry global réutilisée entre tests qui ne partagent pas le même état → tests intermittents.
- ❌ Réutiliser `$client` d'un test à l'autre via une variable de classe sans recréer entre cas → fuites d'état.
- ❌ Oublier `$em->clear()` après une mutation, et asserter sur l'objet en cache identité.
- ❌ Tester la même chose en functional ET en unit (gaspillage). Choisir le niveau qui couvre, pas les deux.
- ❌ Ajouter un `data-test-id` qui inclut une classe CSS ou un texte traduit (`data-test-id="recipe-card recipe-card--featured"`) — un seul ID par élément, stable et sémantique.

## Quand ce skill ne suffit pas

- Logique pure isolée (Slugger, parser, validator pur sans container) → **`tinie-test-unit`**.
- Parcours navigateur multi-pages avec JS (drawer, search live qui re-render côté client) → **`tinie-test-e2e`**.
- Décision de niveau (functional vs unit vs e2e) → remonter à **`tinie-test-orchestrator`**.
- Le `setUp` ne peut pas tenir sous 15 lignes utiles malgré l'effort → remonter au user (problème SRP côté code, pas côté test).
