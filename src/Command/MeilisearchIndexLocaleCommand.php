<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Post;
use App\Services\Locale\Locales;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\Services\SettingsUpdater;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsCommand(
    name: 'meilisearch:index:locale',
    description: 'Index posts for all locales in separate Meilisearch indexes',
)]
final class MeilisearchIndexLocaleCommand extends Command
{
    private const PRIMARY_KEY = 'id';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Client $client,
        private readonly NormalizerInterface $normalizer,
        private readonly SettingsUpdater $settingsUpdater,
        private readonly Locales $locales,
        #[Autowire(param: 'meilisearch.prefix')]
        private readonly string $prefix,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Delete and recreate the indexes before indexing')
            ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'Index only a specific locale (e.g., fr, en)')
            ->setHelp('This command indexes all active posts to locale-specific Meilisearch indexes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Indexing posts for locales');

        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        $totalPosts = \count($posts);

        if ($totalPosts === 0) {
            $io->warning('No posts found to index');

            return Command::SUCCESS;
        }

        $io->info(\sprintf('Found %d post(s) to process', $totalPosts));

        $localesToIndex = $this->getLocalesToIndex($input);
        $shouldClear = (bool) $input->getOption('clear');

        foreach ($localesToIndex as $locale) {
            $this->indexPostsForLocale($io, $posts, $locale, $shouldClear);
        }

        $io->success('All locales indexed successfully!');

        return Command::SUCCESS;
    }

    /**
     * @return array<string>
     */
    private function getLocalesToIndex(InputInterface $input): array
    {
        $specifiedLocale = $input->getOption('locale');

        if ($specifiedLocale !== null) {
            $availableLocales = $this->locales->get();

            if (!\is_string($specifiedLocale) || !\in_array($specifiedLocale, $availableLocales, true)) {
                throw new \InvalidArgumentException(\sprintf('Invalid locale "%s". Available locales: %s', \is_scalar($specifiedLocale) ? (string) $specifiedLocale : 'non-scalar', implode(', ', $availableLocales)));
            }

            return [$specifiedLocale];
        }

        return $this->locales->get();
    }

    /**
     * @param Post[] $posts
     */
    private function indexPostsForLocale(SymfonyStyle $io, array $posts, string $locale, bool $shouldClear): void
    {
        $indexName = \sprintf('%sposts_%s', $this->prefix, $locale);
        $io->section(\sprintf('Indexing for locale: %s (index: %s)', $locale, $indexName));

        $indexRecreated = false;

        if ($shouldClear) {
            $this->deleteIndex($io, $indexName);
            $indexRecreated = true;
        }

        $documents = $this->prepareDocuments($posts, $locale);
        $indexedCount = \count($documents);
        $skippedCount = \count($posts) - $indexedCount;

        if ($indexedCount > 0) {
            $wasRecreated = $this->addDocumentsWithRetry($io, $indexName, $documents);
            $indexRecreated = $indexRecreated || $wasRecreated;
        }

        // Apply settings from meilisearch.yaml if index was recreated
        if ($indexRecreated) {
            $this->updateIndexSettings($io, $indexName);
        }

        $io->success(\sprintf(
            'Indexed %d post(s) for locale "%s" (skipped %d without translation)',
            $indexedCount,
            $locale,
            $skippedCount,
        ));
    }

    /**
     * Delete an index entirely to ensure it's recreated with the correct primary key.
     */
    private function deleteIndex(SymfonyStyle $io, string $indexName): void
    {
        $io->text('Deleting index to ensure clean state...');

        try {
            $this->client->deleteIndex($indexName);
            $io->text('Index deleted');
        } catch (ApiException $e) {
            if ($e->httpStatus !== 404) {
                throw $e;
            }
            $io->text('Index did not exist, will be created fresh');
        }
    }

    /**
     * @param Post[] $posts
     *
     * @return array<int, array<string, mixed>>
     */
    private function prepareDocuments(array $posts, string $locale): array
    {
        $documents = [];

        foreach ($posts as $post) {
            if (!$this->hasTranslationForLocale($post, $locale)) {
                continue;
            }

            $normalized = $this->normalizer->normalize($post, null, [
                'meilisearch_locale' => $locale,
            ]);

            if (!empty($normalized) && \is_array($normalized)) {
                $documents[] = $normalized;
            }
        }

        return $documents;
    }

    /**
     * Add documents to the index, with retry on primary key mismatch.
     *
     * @param array<int, array<string, mixed>> $documents
     *
     * @return bool True if the index was recreated due to primary key mismatch
     */
    private function addDocumentsWithRetry(SymfonyStyle $io, string $indexName, array $documents): bool
    {
        try {
            $this->client->index($indexName)->addDocuments($documents, self::PRIMARY_KEY);

            return false;
        } catch (ApiException $e) {
            // Handle primary key mismatch - delete index and retry
            if ($e->httpStatus === 400 && str_contains($e->getMessage(), 'primary key')) {
                $io->warning('Index has incompatible primary key, recreating index...');
                $this->deleteIndex($io, $indexName);
                $this->client->index($indexName)->addDocuments($documents, self::PRIMARY_KEY);
                $io->text('Index recreated and documents added successfully');

                return true;
            }

            throw $e;
        }
    }

    /**
     * Apply settings from meilisearch.yaml to the index.
     *
     * @param non-empty-string $indexName
     */
    private function updateIndexSettings(SymfonyStyle $io, string $indexName): void
    {
        $io->text('Applying index settings from configuration...');

        try {
            $this->settingsUpdater->update($indexName);
            $io->text('Index settings applied successfully');
        } catch (\Exception $e) {
            $io->warning(\sprintf('Could not apply index settings: %s', $e->getMessage()));
        }
    }

    private function hasTranslationForLocale(Post $post, string $locale): bool
    {
        foreach ($post->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }
}
