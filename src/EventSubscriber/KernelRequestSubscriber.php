<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class KernelRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $locale = $event->getRequest()->getLocale();

        $this->entityManager
            ->getFilters()
            ->getFilter('locale_filter')
            ->setParameter('locale', $locale)
        ;
    }
}
