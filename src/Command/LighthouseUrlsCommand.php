<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-lighthouse-urls',
    description: 'Generate URLs from database fixtures for Lighthouse CI testing',
)]
final class LighthouseUrlsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'output',
            'o',
            InputOption::VALUE_REQUIRED,
            'Output file path for URLs',
            'lighthouse-urls.json'
        );
        $this->addOption(
            'locale',
            'l',
            InputOption::VALUE_REQUIRED,
            'Locale for URL generation',
            'fr'
        );
        $this->addOption(
            'base-url',
            'b',
            InputOption::VALUE_REQUIRED,
            'Base URL for the application',
            'https://local.tinie-bakerie.com'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $outputFile */
        $outputFile = $input->getOption('output');
        /** @var string $locale */
        $locale = $input->getOption('locale');
        /** @var string $baseUrl */
        $baseUrl = $input->getOption('base-url');

        $this->displayHeader($io, $locale, $baseUrl);

        try {
            // Enable locale filter for CLI context
            $this->enableLocaleFilter($locale);

            $urls = $this->generateUrls($baseUrl, $locale, $io);
            $this->writeUrlsToFile($urls, $outputFile);
            $this->displayResults($io, $urls, $outputFile);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error(sprintf('Failed to generate Lighthouse URLs: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    private function displayHeader(SymfonyStyle $io, string $locale, string $baseUrl): void
    {
        $io->title('Generating Lighthouse CI URLs from Database');
        $io->text(sprintf('Locale: <info>%s</info>', $locale));
        $io->text(sprintf('Base URL: <info>%s</info>', $baseUrl));
    }

    /**
     * Generate all URLs for Lighthouse testing.
     *
     * @return array<int, string>
     */
    private function generateUrls(string $baseUrl, string $locale, SymfonyStyle $io): array
    {
        $urls = [];
        $baseUrl = rtrim($baseUrl, '/');

        // 1. Home page
        $urls[] = $baseUrl;

        // 2. Posts listing page
        $urls[] = $this->buildPostsListingUrl($baseUrl, $locale);

        // 3. Post detail page
        $postUrl = $this->generatePostDetailUrl($baseUrl, $locale, $io);
        if (null !== $postUrl) {
            $urls[] = $postUrl;
        }

        // 4. Category page
        $categoryUrl = $this->generateCategoryUrl($baseUrl, $locale, $io);
        if (null !== $categoryUrl) {
            $urls[] = $categoryUrl;
        }

        return $urls;
    }

    private function buildPostsListingUrl(string $baseUrl, string $locale): string
    {
        $postsPath = $this->getPostsPathForLocale($locale);

        return sprintf('%s/%s/%s', $baseUrl, $locale, $postsPath);
    }

    private function generatePostDetailUrl(string $baseUrl, string $locale, SymfonyStyle $io): ?string
    {
        // Use DQL to get first active post with full translations
        // This avoids issues with PARTIAL selects and uninitialized properties
        $query = $this->entityManager->createQuery(
            'SELECT p, pt, c, ct
            FROM App\Entity\Post p
            JOIN p.translations pt
            LEFT JOIN p.category c
            LEFT JOIN c.translations ct
            WHERE p.active = :active
            ORDER BY p.createdAt DESC'
        )
            ->setParameter('active', true)
            ->setMaxResults(1);

        $posts = $query->getResult();

        if ([] === $posts) {
            return null;
        }

        $firstPost = $posts[0];
        $postTranslation = $firstPost->getTranslationByLocale($locale);
        $categoryTranslation = $firstPost->getCategory()?->getTranslationByLocale($locale);

        if (!$postTranslation || !$categoryTranslation) {
            return null;
        }

        $postSlug = $postTranslation->getSlug();
        $categorySlug = $categoryTranslation->getSlug();

        $io->success(sprintf(
            'Found post: %s (%s/%s)',
            $postTranslation->getTitle(),
            $categorySlug,
            $postSlug
        ));

        return $this->buildPostDetailUrl($baseUrl, $locale, $categorySlug, $postSlug);
    }

    private function buildPostDetailUrl(
        string $baseUrl,
        string $locale,
        string $categorySlug,
        string $postSlug
    ): string {
        $postsPath = $this->getPostsPathForLocale($locale);

        return sprintf(
            '%s/%s/%s/%s/%s',
            $baseUrl,
            $locale,
            $postsPath,
            $categorySlug,
            $postSlug
        );
    }

    private function generateCategoryUrl(string $baseUrl, string $locale, SymfonyStyle $io): ?string
    {
        // Use DQL to get first category with full translations
        $query = $this->entityManager->createQuery(
            'SELECT c, ct
            FROM App\Entity\Category c
            JOIN c.translations ct
            ORDER BY c.createdAt DESC'
        )
            ->setMaxResults(1);

        $categories = $query->getResult();

        if ([] === $categories) {
            return null;
        }

        $firstCategory = $categories[0];
        $categoryTranslation = $firstCategory->getTranslationByLocale($locale);

        if (!$categoryTranslation) {
            return null;
        }

        $categorySlug = $categoryTranslation->getSlug();

        $io->success(sprintf(
            'Found category: %s (%s)',
            $categoryTranslation->getTitle(),
            $categorySlug
        ));

        return $this->buildCategoryUrl($baseUrl, $locale, $categorySlug);
    }

    private function buildCategoryUrl(string $baseUrl, string $locale, string $categorySlug): string
    {
        return sprintf(
            '%s/%s/categories/%s',
            $baseUrl,
            $locale,
            $categorySlug
        );
    }

    private function getPostsPathForLocale(string $locale): string
    {
        return 'fr' === $locale ? 'articles' : 'posts';
    }

    /**
     * Write URLs array to JSON file.
     *
     * @param array<int, string> $urls
     */
    private function writeUrlsToFile(array $urls, string $outputFile): void
    {
        $jsonContent = json_encode($urls, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);

        if (false === $jsonContent) {
            throw new \RuntimeException('Failed to encode URLs to JSON');
        }

        if (false === file_put_contents($outputFile, $jsonContent)) {
            throw new \RuntimeException(sprintf('Failed to write URLs to file: %s', $outputFile));
        }
    }

    /**
     * Display success results to console.
     *
     * @param array<int, string> $urls
     */
    private function displayResults(SymfonyStyle $io, array $urls, string $outputFile): void
    {
        $io->success(sprintf('Generated %d URLs and saved to: %s', \count($urls), $outputFile));

        $io->section('Generated URLs:');
        $io->listing($urls);
    }

    /**
     * Enable and configure the locale filter for CLI context.
     * This ensures translations are filtered correctly when querying entities.
     */
    private function enableLocaleFilter(string $locale): void
    {
        $filters = $this->entityManager->getFilters();

        if (!$filters->isEnabled('locale_filter')) {
            $filters->enable('locale_filter');
        }

        $filters->getFilter('locale_filter')->setParameter('locale', $locale);
    }
}
