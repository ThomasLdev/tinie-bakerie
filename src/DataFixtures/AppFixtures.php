<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Recipe;
use App\Factory\CategoryFactory;
use App\Factory\CategoryMediaFactory;
use App\Factory\CategoryMediaTranslationFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\IngredientFactory;
use App\Factory\IngredientGroupFactory;
use App\Factory\IngredientGroupTranslationFactory;
use App\Factory\IngredientTranslationFactory;
use App\Factory\PostMediaFactory;
use App\Factory\PostMediaTranslationFactory;
use App\Factory\PostSectionFactory;
use App\Factory\PostSectionMediaFactory;
use App\Factory\PostSectionMediaTranslationFactory;
use App\Factory\PostSectionTranslationFactory;
use App\Factory\RecipeFactory;
use App\Factory\RecipeStepFactory;
use App\Factory\RecipeStepTranslationFactory;
use App\Factory\RecipeTranslationFactory;
use App\Factory\TagFactory;
use App\Factory\TagTranslationFactory;
use App\Services\Locale\Locales;
use App\Tests\Fixtures\MediaLoader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

use function Zenstruck\Foundry\Persistence\flush_after;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly MediaLoader $mediaLoader,
        private readonly Locales $locales,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        flush_after(function (): void {
            $categoryIndex = 0;
            $categories = CategoryFactory::createMany(5, function () use (&$categoryIndex): array {
                $isFeatured = $categoryIndex++ < 3;

                return [
                    'translations' => $this->createTranslations(CategoryTranslationFactory::new()),
                    'isFeatured' => $isFeatured,
                ];
            });

            foreach ($categories as $category) {
                CategoryMediaFactory::createRange(1, 3, fn (): array => ['category' => $category, 'translations' => $this->createTranslations(CategoryMediaTranslationFactory::new()), 'media' => $this->mediaLoader->getRandomMedia()]);
            }

            $tagIndex = 0;
            $tags = TagFactory::createMany(15, function () use (&$tagIndex): array {
                $isFeatured = $tagIndex++ < 5;

                return [
                    'translations' => $this->createTranslations(TagTranslationFactory::new()),
                    'isFeatured' => $isFeatured,
                    'image' => $this->mediaLoader->getRandomMedia(),
                ];
            });

            $recipeIndex = 0;

            /** @var Recipe[] $recipes */
            $recipes = RecipeFactory::createMany(30, function () use ($categories, $tags, &$recipeIndex): array {
                $randomCategory = $categories[array_rand($categories)];
                $randomTagCount = random_int(1, 3);
                $randomTags = (array) array_rand(array_flip(array_keys($tags)), min($randomTagCount, \count($tags)));
                $selectedTags = array_map(static fn (int $index) => $tags[$index], $randomTags);

                $isFeatured = ($recipeIndex++ % 10) < 2;

                return [
                    'translations' => $this->createTranslations(RecipeTranslationFactory::new()),
                    'category' => $randomCategory,
                    'tags' => $selectedTags,
                    'media' => [],
                    'sections' => [],
                    'ingredientGroups' => [],
                    'isFeatured' => $isFeatured,
                ];
            });

            foreach ($recipes as $recipe) {
                PostMediaFactory::createRange(1, 3, fn (): array => ['post' => $recipe, 'translations' => $this->createTranslations(PostMediaTranslationFactory::new()), 'media' => $this->mediaLoader->getRandomMedia()]);
            }

            $narrativeSections = [];
            $recipeSteps = [];

            foreach ($recipes as $recipe) {
                $sections = PostSectionFactory::createRange(1, 2, fn (): array => [
                    'post' => $recipe,
                    'translations' => $this->createTranslations(PostSectionTranslationFactory::new()),
                    'media' => [],
                ]);
                $narrativeSections = array_merge($narrativeSections, $sections);

                $stepCount = random_int(4, 8);

                for ($position = 0; $position < $stepCount; ++$position) {
                    $step = RecipeStepFactory::createOne([
                        'post' => $recipe,
                        'position' => $position,
                        'translations' => $this->createTranslations(RecipeStepTranslationFactory::new()),
                        'media' => [],
                    ]);
                    $recipeSteps[] = $step;
                }
            }

            foreach ($narrativeSections as $section) {
                PostSectionMediaFactory::createRange(1, 2, fn (): array => ['postSection' => $section, 'translations' => $this->createTranslations(PostSectionMediaTranslationFactory::new()), 'media' => $this->mediaLoader->getRandomMedia()]);
            }

            foreach ($recipeSteps as $step) {
                if ($this->flipCoin()) {
                    PostSectionMediaFactory::createOne([
                        'postSection' => $step,
                        'translations' => $this->createTranslations(PostSectionMediaTranslationFactory::new()),
                        'media' => $this->mediaLoader->getRandomMedia(),
                    ]);
                }
            }

            foreach ($recipes as $recipe) {
                $groupCount = random_int(1, 3);

                for ($groupPosition = 0; $groupPosition < $groupCount; ++$groupPosition) {
                    $group = IngredientGroupFactory::createOne([
                        'recipe' => $recipe,
                        'position' => $groupPosition,
                        'translations' => $this->createTranslations(IngredientGroupTranslationFactory::new()),
                    ]);

                    $ingredientCount = random_int(2, 6);

                    for ($ingredientPosition = 0; $ingredientPosition < $ingredientCount; ++$ingredientPosition) {
                        IngredientFactory::createOne([
                            'group' => $group,
                            'position' => $ingredientPosition,
                            'translations' => $this->createTranslations(IngredientTranslationFactory::new()),
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Creates translations for all configured locales.
     *
     * @return array<array-key, mixed>
     *
     * @phpstan-ignore-next-line
     */
    private function createTranslations(PersistentObjectFactory $factory): array
    {
        $locales = $this->locales->get();

        $sequence = [];

        foreach ($locales as $locale) {
            $sequence[] = ['locale' => $locale];
        }

        return $factory::createSequence($sequence);
    }

    private function flipCoin(): bool
    {
        return random_int(0, 1) === 1;
    }
}
