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
        $request = $event->getRequest();
        $locale = $request->getLocale();

        // Set the locale filter parameter for Doctrine
        $this->setLocaleFilterParameter($locale);

        // Set the locale in the request attributes
        $request->attributes->set('_locale', $locale);
    }

    private function setLocaleFilterParameter(string $locale): void
    {
        $this->managerRegistry
            ->getManager()
            ->getFilters()
            ->enable('locale_filter')
            ->setParameter('currentLocale', $locale);
    }
}
