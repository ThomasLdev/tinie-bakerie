<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:create-upload-dirs',
    description: 'Create required upload directories for vich upload bundle'
)]
class CreateUploadDirsCommand extends Command
{
    private const string CONFIGURATION_KEY = 'vich_uploader.mappings';

    private const int DEFAULT_PERMISSIONS = 0755;

    protected function configure(): void
    {
        $this->addOption('clear', 'c', InputOption::VALUE_NONE);
    }

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clearDirectories = $input->getOption('clear');

        $mappings = $this->parameterBag->get(self::CONFIGURATION_KEY);

        if (!is_array($mappings) || [] === $mappings) {
            $io->error('No upload mappings found in the configuration.');

            return Command::FAILURE;
        }

        /** @var array<array-key,string> $mapping */
        foreach ($io->progressIterate($mappings) as $mapping) {
            $fullPath = $mapping['upload_destination'] ?? '';

            if ('' === $fullPath) {
                $io->error('Upload destination is not defined in the mapping configuration.');

                return Command::FAILURE;
            }

            if (!$this->filesystem->exists($fullPath)) {
                $this->filesystem->mkdir($fullPath, self::DEFAULT_PERMISSIONS);

                $io->info("Created directory: $fullPath");

                continue;
            }

            if ($clearDirectories) {
                $this->filesystem->remove($fullPath);
                $this->filesystem->mkdir($fullPath, self::DEFAULT_PERMISSIONS);

                $io->info("Cleared directory: $fullPath");
            }
        }

        $io->success('Upload directories have been created successfully.');

        return Command::SUCCESS;
    }
}
