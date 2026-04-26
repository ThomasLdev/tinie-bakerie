<?php

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\UX\StimulusBundle\StimulusBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
use Symfony\UX\Turbo\TurboBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Zenstruck\Foundry\ZenstruckFoundryBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use JoliCode\MediaBundle\JoliMediaBundle;
use JoliCode\MediaBundle\Bridge\EasyAdmin\JoliMediaEasyAdminBundle;
use Symfony\UX\LiveComponent\LiveComponentBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\UX\Toolkit\UXToolkitBundle;
use Symfony\UX\Icons\UXIconsBundle;

return [
    FrameworkBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    StimulusBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],
    TurboBundle::class => ['all' => true],
    MakerBundle::class => ['dev' => true],
    SecurityBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    StofDoctrineExtensionsBundle::class => ['all' => true],
    ZenstruckFoundryBundle::class => ['dev' => true, 'test' => true],
    TwigComponentBundle::class => ['all' => true],
    EasyAdminBundle::class => ['all' => true],
    DAMADoctrineTestBundle::class => ['test' => true],
    JoliMediaBundle::class => ['all' => true],
    JoliMediaEasyAdminBundle::class => ['all' => true],
    LiveComponentBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    UXToolkitBundle::class => ['dev' => true, 'test' => true],
    UXIconsBundle::class => ['all' => true],
];
