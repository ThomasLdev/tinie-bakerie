<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(name: 'app:default_category', description: 'Creates a default category if it does not exist')]
class DefaultCategoryCommand extends Command
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(param: 'app.supported_locales')] private string $supportedLocales,
        #[Autowire(param: 'default_locale')] private string $defaultLocale,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Creating default category if not exist, for Post without any.');

        $defaultCategory = $this->categoryRepository->findOneBy(['slug' => 'defaut']);

        if ($defaultCategory instanceof Category) {
            $io->success('Default category already exists.');

            return Command::SUCCESS;
        }

        $slugger = new AsciiSlugger();
        $defaultCategory = new Category();

        $defaultCategory
            ->setTitle('Défaut')
            ->setSlug($slugger->slug($defaultCategory->getTitle())->lower()->toString())
        ;

        $this->entityManager->persist($defaultCategory);
        $this->entityManager->flush();

        foreach (explode('|', $this->supportedLocales) as $supportedLocale) {
            if ($supportedLocale === $this->defaultLocale) {
                continue;
            }

            $defaultCategory
                ->setLocale($supportedLocale)
                ->setTitle('Default '.$supportedLocale)
                ->setSlug($slugger->slug($defaultCategory->getTitle())->lower()->toString())
            ;

            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
