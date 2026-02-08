<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Services\Filter\LocaleFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class LocaleFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $filters = $this->entityManager->getFilters();

        if (str_contains($request->getPathInfo(), '/admin')) {
            if ($filters->isEnabled(LocaleFilter::NAME)) {
                $filters->disable(LocaleFilter::NAME);
            }

            return;
        }

        $filters
            ->enable(LocaleFilter::NAME)
            ->setParameter(LocaleFilter::PARAMETER_NAME, $request->getLocale());
    }
}
