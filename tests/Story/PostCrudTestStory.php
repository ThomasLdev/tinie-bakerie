<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Category;
use App\Entity\Tag;
use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\TagFactory;
use App\Factory\TagTranslationFactory;
use Zenstruck\Foundry\Story;

/**
 * Story for PostCrudController functional tests.
 * Provides predictable test data that tests can rely on.
 */
final class PostCrudTestStory extends Story
{
    public function build(): void
    {
        // Create a test category with translations
        $this->addState('category', CategoryFactory::createOne([
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Test Category FR',
                    'slug' => 'test-category-fr',
                    'metaDescription' => str_repeat('A', 120),
                    'excerpt' => str_repeat('B', 50),
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Category EN',
                    'slug' => 'test-category-en',
                    'metaDescription' => str_repeat('C', 120),
                    'excerpt' => str_repeat('D', 50),
                ]),
            ],
        ]));

        // Create test tags with translations
        $this->addState('tag1', TagFactory::createOne([
            'translations' => [
                TagTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Tag Test 1 FR',
                ]),
                TagTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Tag Test 1 EN',
                ]),
            ],
        ]));

        $this->addState('tag2', TagFactory::createOne([
            'translations' => [
                TagTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Tag Test 2 FR',
                ]),
                TagTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Tag Test 2 EN',
                ]),
            ],
        ]));

        $this->addState('tag3', TagFactory::createOne([
            'translations' => [
                TagTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Tag Test 3 FR',
                ]),
                TagTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Tag Test 3 EN',
                ]),
            ],
        ]));
    }

    public function getCategory(): Category
    {
        $category = self::get('category');
        \assert($category instanceof Category);

        return $category;
    }

    public function getTag1(): Tag
    {
        return $this->getTag('tag1');
    }

    public function getTag2(): Tag
    {
        return $this->getTag('tag2');
    }

    public function getTag3(): Tag
    {
        return $this->getTag('tag3');
    }

    /**
     * @return Tag[]
     */
    public function getAllTags(): array
    {
        return [
            $this->getTag1(),
            $this->getTag2(),
            $this->getTag3(),
        ];
    }

    private function getTag(string $key): Tag
    {
        $tag = self::get($key);
        \assert($tag instanceof Tag);

        return $tag;
    }
}
