<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\Cache\CategoryCache;
use App\Services\Cache\HeaderCache;
use App\Services\Cache\PostCache;
use App\Services\Locale\Locales;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:cache:warm',
    description: 'Warm up entity caches (posts, categories, headers) for all locales',
)]
class WarmCacheCommand extends Command
{
    public function __construct(
        private readonly PostCache $postCache,
        private readonly CategoryCache $categoryCache,
        private readonly HeaderCache $headerCache,
        private readonly Locales $locales,
        #[Autowire(service: 'cache.app.taggable')]
        private readonly AdapterInterface $cache,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'clear-first',
                'c',
                InputOption::VALUE_NONE,
                'Clear cache before warming (useful after code changes)',
            )
            ->addOption(
                'entity',
                null,
                InputOption::VALUE_OPTIONAL,
                'Warm specific entity type only (post, category, header)',
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command warms up your application's entity caches.

                    This fills the Redis cache with posts, categories, and header data for all locales,
                    ensuring the first users after deployment get instant page loads.

                    Basic usage:
                      <info>php %command.full_name%</info>

                    Clear cache before warming (recommended after deployment):
                      <info>php %command.full_name% --clear-first</info>

                    Warm specific entity type only:
                      <info>php %command.full_name% --entity=post</info>
                      <info>php %command.full_name% --entity=category</info>
                      <info>php %command.full_name% --entity=header</info>
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $startTime = microtime(true);

        $clearFirst = $input->getOption('clear-first');
        $entityFilter = $input->getOption('entity');

        // Validate entity filter
        if (null !== $entityFilter) {
            if (!\is_string($entityFilter)) {
                $io->error('Entity filter must be a string');

                return Command::FAILURE;
            }

            if (!\in_array($entityFilter, ['post', 'category', 'header'], true)) {
                $io->error(\sprintf('Invalid entity type "%s". Valid options: post, category, header', $entityFilter));

                return Command::FAILURE;
            }
        }

        /** @var array<string> $locales */
        $locales = $this->locales->get();

        if ([] === $locales) {
            $io->error('No locales configured in app.supported_locales');

            return Command::FAILURE;
        }

        $io->title('Cache Warming');
        $io->writeln(\sprintf('Locales: <info>%s</info>', implode(', ', $locales)));

        if ($clearFirst) {
            $io->section('Clearing cache pool');
            $this->cache->clear();
            $io->success('Cache cleared');
        }

        $io->section('Warming caches');

        $stats = [
            'posts' => 0,
            'categories' => 0,
            'headers' => 0,
        ];

        // Warm posts cache
        if (null === $entityFilter || 'post' === $entityFilter) {
            $io->write('Posts: ');

            foreach ($io->progressIterate($locales) as $locale) {
                $posts = $this->postCache->get($locale);
                $stats['posts'] += \count($posts);
            }
        }

        // Warm categories cache
        if (null === $entityFilter || 'category' === $entityFilter) {
            $io->write('Categories: ');

            foreach ($io->progressIterate($locales) as $locale) {
                $categories = $this->categoryCache->get($locale);
                $stats['categories'] += \count($categories);
            }
        }

        // Warm headers cache
        if (null === $entityFilter || 'header' === $entityFilter) {
            $io->write('Headers: ');

            foreach ($io->progressIterate($locales) as $locale) {
                $headers = $this->headerCache->getCategories($locale);
                $stats['headers'] += \count($headers);
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        $io->newLine();
        $io->writeln('<info>Cache Statistics:</info>');
        $io->writeln(\sprintf('  • Posts:      <comment>%d</comment> entities cached', $stats['posts']));
        $io->writeln(\sprintf('  • Categories: <comment>%d</comment> entities cached', $stats['categories']));
        $io->writeln(\sprintf('  • Headers:    <comment>%d</comment> entities cached', $stats['headers']));
        $io->newLine();

        $io->success(\sprintf('Cache warmed successfully in %ss', $duration));

        return Command::SUCCESS;
    }
}
