<?php

namespace App\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class RequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
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
        $this->managerRegistry
            ->getManager()
            ->getFilters()
            ->enable('locale_filter')
            ->setParameter(
                'currentLocale',
                $event->getRequest()->attributes->get('_locale')
            );
    }
}
