<?php

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class RequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(param: 'app.supported_locales')] private string $supportedLocales,
        #[Autowire(param: 'default_locale')] private string $defaultLocale,
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
        $request = $event->getRequest();
        $supportedLocales = explode('|', $this->supportedLocales);
        $locale = $request->attributes->get('_locale');

        if (!is_string($locale)) {
            $locale = $request->getPreferredLanguage($supportedLocales) ?? $this->defaultLocale;
            $request->setLocale($locale);
        }

        $request->attributes->set('_locale', $locale);

        if (!str_contains('/admin', $request->getRequestUri())) {
            return;
        }

        $this->setLocaleFilterParameter($locale);
    }

    private function setLocaleFilterParameter(string $locale): void
    {
        $this->entityManager
            ->getFilters()
            ->enable('locale_filter')
            ->setParameter('currentLocale', $locale);
    }
}
