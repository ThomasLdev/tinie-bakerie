<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig\Components;

use App\Entity\Category;
use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Repository\CategoryRepository;
use App\Twig\Components\Footer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
#[CoversClass(Footer::class)]
#[CoversClass(CategoryRepository::class)]
final class FooterTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private Footer $footer;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);

        $container = self::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $footer = $container->get(Footer::class);
        \assert($footer instanceof Footer);
        $this->footer = $footer;
    }

    #[TestDox('Default limit returns 4 most recently updated featured categories in DESC order')]
    public function testDefaultLimitReturnsFourMostRecentFeaturedCategoriesOrderedByUpdatedAtDesc(): void
    {
        // Reference time, all featured categories spaced 5 minutes apart.
        $base = new \DateTimeImmutable('2026-01-01 12:00:00');

        // 6 featured categories — id keyed by minutes offset for clarity.
        $featured = [];
        for ($i = 0; $i < 6; ++$i) {
            $featured[$i] = $this->createCategory(
                titlePrefix: "Featured {$i}",
                isFeatured: true,
            );
        }

        // 2 non-featured categories that should never appear in the result —
        // even though their updatedAt is the most recent of all.
        $nonFeaturedA = $this->createCategory(titlePrefix: 'NonFeatured A', isFeatured: false);
        $nonFeaturedB = $this->createCategory(titlePrefix: 'NonFeatured B', isFeatured: false);
        $this->forceUpdatedAt($nonFeaturedA, $base->modify('+999 minutes'));
        $this->forceUpdatedAt($nonFeaturedB, $base->modify('+998 minutes'));

        $excludedIds = [$nonFeaturedA->getId(), $nonFeaturedB->getId()];

        // Force controlled updatedAt values via SQL — Gedmo Timestampable
        // overwrites updatedAt on flush, so we patch after the fact.
        // featured[5] is the most recent, featured[0] the oldest.
        foreach ($featured as $i => $category) {
            $offsetMinutes = $i * 5;
            $updatedAt = $base->modify("+{$offsetMinutes} minutes");
            $this->forceUpdatedAt($category, $updatedAt);
        }

        $this->entityManager->clear();

        $this->footer->featuredLimit = 4;
        $result = $this->footer->getFeaturedCategories();

        self::assertCount(4, $result);

        $titles = array_map(static fn (Category $c): string => $c->getTitle(), $result);
        self::assertSame(
            ['Featured 5 en', 'Featured 4 en', 'Featured 3 en', 'Featured 2 en'],
            $titles,
            'Should return the 4 most recent featured categories in DESC order of updatedAt',
        );

        $resultIds = array_map(static fn (Category $c): ?int => $c->getId(), $result);
        foreach ($excludedIds as $excludedId) {
            self::assertNotContains(
                $excludedId,
                $resultIds,
                'Non-featured categories must be filtered out, even when their updatedAt is the most recent',
            );
        }
    }

    #[TestDox('Custom limit of 2 returns exactly the 2 most recent featured categories')]
    public function testCustomLimitReturnsExactlyTwoMostRecentFeatured(): void
    {
        $base = new \DateTimeImmutable('2026-01-01 12:00:00');

        $featured = [];
        for ($i = 0; $i < 6; ++$i) {
            $featured[$i] = $this->createCategory(
                titlePrefix: "Featured {$i}",
                isFeatured: true,
            );
        }

        foreach ($featured as $i => $category) {
            $offsetMinutes = $i * 5;
            $this->forceUpdatedAt($category, $base->modify("+{$offsetMinutes} minutes"));
        }

        $this->entityManager->clear();

        $this->footer->featuredLimit = 2;
        $result = $this->footer->getFeaturedCategories();

        self::assertCount(2, $result);

        $titles = array_map(static fn (Category $c): string => $c->getTitle(), $result);
        self::assertSame(
            ['Featured 5 en', 'Featured 4 en'],
            $titles,
        );
    }

    private function createCategory(string $titlePrefix, bool $isFeatured): Category
    {
        $category = CategoryFactory::createOne([
            'isFeatured' => $isFeatured,
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => "{$titlePrefix} fr",
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => "{$titlePrefix} en",
                ]),
            ],
        ]);

        \assert($category instanceof Category);

        return $category;
    }

    /**
     * Bypass Gedmo Timestampable by patching `updated_at` directly via SQL.
     */
    private function forceUpdatedAt(Category $category, \DateTimeImmutable $updatedAt): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'UPDATE category SET updated_at = :updated_at WHERE id = :id',
            [
                'updated_at' => $updatedAt->format('Y-m-d H:i:s'),
                'id' => $category->getId(),
            ],
        );
    }
}
