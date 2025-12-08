<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Post;
use App\Services\Locale\Locales;
use App\Services\Search\IndexNameResolver;
use App\Services\Search\MeilisearchIndexer;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\Services\SettingsUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsCommand(
    name: 'meilisearch:index:locale',
    description: 'Index posts for all locales in separate Meilisearch indexes',
)]
final class MeilisearchIndexLocaleCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NormalizerInterface $normalizer,
        private readonly SettingsUpdater $settingsUpdater,
        private readonly Locales $locales,
        private readonly IndexNameResolver $indexNameResolver,
        private readonly MeilisearchIndexer $indexer,
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

        if ($posts === []) {
            $io->warning('No posts found to index');

            return Command::SUCCESS;
        }

        $io->info(\sprintf('Found %d post(s) to process', \count($posts)));

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
                throw new \InvalidArgumentException(\sprintf(
                    'Invalid locale "%s". Available locales: %s',
                    \is_scalar($specifiedLocale) ? (string) $specifiedLocale : 'non-scalar',
                    implode(', ', $availableLocales),
                ));
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
        $indexName = $this->indexNameResolver->resolve('posts', $locale);
        $io->section(\sprintf('Indexing for locale: %s (index: %s)', $locale, $indexName));

        if ($shouldClear) {
            $io->text('Deleting index to ensure clean state...');
            $this->indexer->deleteIndex($indexName);
        }

        $documents = $this->prepareDocuments($posts, $locale);
        $indexedCount = \count($documents);
        $skippedCount = \count($posts) - $indexedCount;

        if ($indexedCount > 0) {
            $this->indexer->addDocuments($indexName, $documents);
        }

        if ($shouldClear) {
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
     * @param Post[] $posts
     *
     * @return array<int, array<string, mixed>>
     */
    private function prepareDocuments(array $posts, string $locale): array
    {
        $documents = [];

        foreach ($posts as $post) {
            $normalized = $this->normalizer->normalize($post, null, [
                'meilisearch_locale' => $locale,
            ]);

            if (\is_array($normalized) && $normalized !== []) {
                $documents[] = $normalized;
            }
        }

        return $documents;
    }

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
}
