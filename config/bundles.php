<?php

declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\UX\StimulusBundle\StimulusBundle;
use Symfony\UX\Turbo\TurboBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use Symfonycasts\TailwindBundle\SymfonycastsTailwindBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
use Vich\UploaderBundle\VichUploaderBundle;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

return [
    FrameworkBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    StimulusBundle::class => ['all' => true],
    SymfonycastsTailwindBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],
    TurboBundle::class => ['all' => true],
    MakerBundle::class => ['dev' => true],
    SecurityBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    StofDoctrineExtensionsBundle::class => ['all' => true],
    VichUploaderBundle::class => ['all' => true],
    ZenstruckFoundryBundle::class => ['dev' => true, 'test' => true],
    TwigComponentBundle::class => ['all' => true],
    EasyAdminBundle::class => ['all' => true],
];
