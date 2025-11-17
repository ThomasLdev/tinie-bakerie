<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:meilisearch:index',
    description: 'Index all posts to Meilisearch',
)]
final class MeilisearchIndexCommand extends Command
{
    public function __construct(
        private readonly SearchService $searchService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear the index before indexing')
            ->setHelp('This command indexes all active posts to Meilisearch for search functionality.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Meilisearch Indexing');

        try {
            // Clear index if requested
            if ($input->getOption('clear')) {
                $io->section('Clearing index...');
                $this->searchService->clear('posts');
                $io->success('Index cleared successfully');
            }

            // Get all posts
            $io->section('Fetching posts...');
            $posts = $this->entityManager->getRepository(Post::class)->findAll();
            $totalPosts = count($posts);

            if ($totalPosts === 0) {
                $io->warning('No posts found to index');
                return Command::SUCCESS;
            }

            $io->info(sprintf('Found %d active post(s) to index', $totalPosts));

            // Index posts
            $io->section('Indexing posts...');
            $io->progressStart($totalPosts);

            $indexed = 0;
            foreach ($posts as $post) {
                try {
                    $this->searchService->index($post, 'posts');
                    $indexed++;
                    $io->progressAdvance();
                } catch (\Exception $e) {
                    $io->error(sprintf('Failed to index post ID %d: %s', $post->getId(), $e->getMessage()));
                }
            }

            $io->progressFinish();

            $io->success(sprintf('Successfully indexed %d out of %d post(s)', $indexed, $totalPosts));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('An error occurred: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
