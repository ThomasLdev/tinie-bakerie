<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:create-upload-dirs',
    description: 'Create required upload directories'
)]
class CreateUploadDirsCommand extends Command
{
    private const array UPLOAD_DIRS = [
        '/public/upload/post',
        '/public/upload/post_section',
        '/public/upload/category',
    ];

    private const int DEFAULT_PERMISSIONS = 0755;

    protected function configure(): void
    {
        $this->addOption('clear', 'c', InputOption::VALUE_NONE);
    }

    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clearDirectories = $input->getOption('clear');

        foreach ($io->progressIterate(self::UPLOAD_DIRS) as $dir) {
            $fullPath = $this->projectDir . $dir;

            if (!$this->filesystem->exists($fullPath)) {
                $this->filesystem->mkdir($fullPath, self::DEFAULT_PERMISSIONS);

                $io->info("Created directory: $dir");

                continue;
            }

            if ($clearDirectories && $this->filesystem->exists($fullPath)) {
                $this->filesystem->remove($fullPath);
                $this->filesystem->mkdir($fullPath, self::DEFAULT_PERMISSIONS);

                $io->info("Cleared and recreated directory: $dir");
            }
        }

        $io->success('Upload directories have been created successfully.');

        return Command::SUCCESS;
    }
}
