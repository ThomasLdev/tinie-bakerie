<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

abstract class LocalizedCrudController extends AbstractCrudController
{
    public function __construct(
        #[Autowire(param: 'app.supported_locales')] private readonly string $supportedLocales
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return '';
    }

    protected function getSupportedLocales(): array
    {
        return explode('|', $this->supportedLocales);
    }
}
