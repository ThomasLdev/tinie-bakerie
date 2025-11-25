<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\Cache\WarmableCacheInterface;
use App\Services\Locale\LocaleProvider;
use App\Services\Locale\Locales;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsCommand(
    name: 'app:cache:warm',
    description: 'Warm up entity caches (posts, categories, headers) for all locales',
)]
class WarmCacheCommand extends Command
{
    /**
     * @param iterable<WarmableCacheInterface> $warmableCaches
     */
    public function __construct(
        #[AutowireIterator('service.warmable_cache')]
        private readonly iterable $warmableCaches,
        private readonly Locales $locales,
        #[Autowire(service: 'cache.app.taggable')]
        private readonly AdapterInterface $cache,
        private readonly EntityManagerInterface $entityManager,
        private readonly LocaleProvider $localeProvider,
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
                'cache',
                null,
                InputOption::VALUE_OPTIONAL,
                'Warm specific cache only (post, category, header)',
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command warms up your application's entity caches.

                    This fills the cache with posts, categories, and header data for all locales,
                    ensuring the first users after deployment get instant page loads.

                    Basic usage:
                      <info>php %command.full_name%</info>

                    Clear cache before warming (recommended after deployment):
                      <info>php %command.full_name% --clear-first</info>

                    Warm specific cache only:
                      <info>php %command.full_name% --cache=post</info>
                      <info>php %command.full_name% --cache=category</info>
                      <info>php %command.full_name% --cache=header</info>
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $startTime = microtime(true);

        $clearFirst = $input->getOption('clear-first');
        $cacheFilter = $input->getOption('cache');

        // Get available cache names
        $availableCaches = [];

        foreach ($this->warmableCaches as $cache) {
            $availableCaches[] = $cache->getEntityName();
        }

        // Validate cache filter
        if (null !== $cacheFilter) {
            if (!\is_string($cacheFilter)) {
                $io->error('Cache filter must be a string');

                return Command::FAILURE;
            }

            if (!\in_array($cacheFilter, $availableCaches, true)) {
                $io->error(\sprintf(
                    'Invalid cache type "%s". Available caches: %s',
                    $cacheFilter,
                    implode(', ', $availableCaches),
                ));

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

        /** @var array<string, int> $stats */
        $stats = [];

        // Warm each cache
        foreach ($this->warmableCaches as $cache) {
            $entityName = $cache->getEntityName();

            // Skip if filtering for specific cache
            if (null !== $cacheFilter && $cacheFilter !== $entityName) {
                continue;
            }

            $io->write(ucfirst($entityName) . ': ');
            $stats[$entityName] = 0;

            foreach ($io->progressIterate($locales) as $locale) {
                // Enable locale filter for this locale in CLI context
                // This ensures translations are filtered correctly during warmup
                $filters = $this->entityManager->getFilters();

                if (!$filters->isEnabled('locale_filter')) {
                    $filters->enable('locale_filter');
                }

                $filters->getFilter('locale_filter')->setParameter('locale', $locale);

                // Also set the locale on the LocaleProvider so the TranslatableEntitySubscriber
                // can inject the correct locale into entities during postLoad
                $this->localeProvider->setLocale($locale);

                // Warm the cache with the correct locale filter active
                $count = $cache->warmUp($locale);
                $stats[$entityName] += $count;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        $io->newLine();
        $io->writeln('<info>Cache Statistics:</info>');

        foreach ($stats as $entityName => $count) {
            $io->writeln(\sprintf('  • %s: <comment>%d</comment> entities cached', ucfirst($entityName), $count));
        }

        $io->newLine();
        $io->success(\sprintf('Cache warmed successfully in %ss', $duration));

        return Command::SUCCESS;
    }
}
