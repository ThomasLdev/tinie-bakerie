<?php

declare(strict_types=1);

namespace App\Command;

use App\Tests\Story\E2EFrontStory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

/**
 * Resets the test database and loads the deterministic Playwright dataset.
 *
 * Drops `app_test`, recreates it, runs migrations, then loads `E2EFrontStory`.
 * Refuses to run outside `APP_ENV=test` so an accidental call against the dev
 * or prod connection cannot wipe real data.
 */
#[AsCommand(
    name: 'app:e2e:reset',
    description: 'Drop, migrate and seed the test database for the Playwright suite',
)]
final class E2EResetCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ('test' !== $this->environment) {
            $io->error(\sprintf(
                'app:e2e:reset must run with APP_ENV=test (got "%s"). Refusing to touch a non-test database.',
                $this->environment,
            ));

            return Command::FAILURE;
        }

        // Each sub-command runs in its own PHP process so the kernel boots fresh against
        // the post-create connection. Calling them via Application::find()->run() reuses
        // the booted kernel and trips Doctrine's cached metadata after a database drop.
        $io->section('Resetting app_test schema');
        $this->runConsole($io, ['doctrine:database:drop', '--if-exists', '--force']);
        $this->runConsole($io, ['doctrine:database:create']);
        $this->runConsole($io, ['doctrine:migrations:migrate', '--no-interaction', '--allow-no-migration']);

        $io->section('Loading E2EFrontStory');
        E2EFrontStory::load();

        $io->success('Test database ready for Playwright.');

        return Command::SUCCESS;
    }

    /**
     * @param list<string> $args
     */
    private function runConsole(SymfonyStyle $io, array $args): void
    {
        $process = new Process(
            ['bin/console', ...$args, '--env=test'],
            $this->projectDir,
            ['APP_ENV' => 'test'],
            null,
            120.0,
        );

        $process->mustRun(static function (string $type, string $buffer) use ($io): void {
            $io->write($buffer);
        });
    }
}
