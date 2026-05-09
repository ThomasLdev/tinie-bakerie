<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ErrorLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(param: 'app.supported_locales')]
        private string $supportedLocales,
        #[Autowire(param: 'kernel.default_locale')]
        private string $defaultLocale,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Priority must be > 16 (Symfony LocaleListener) so _locale is in the attributes
        // when LocaleListener runs on the error sub-request and resets the request locale.
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 17],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->attributes->has('exception')) {
            return;
        }

        if ($request->attributes->get('_locale')) {
            return;
        }

        $locales = explode('|', $this->supportedLocales);
        $segment = explode('/', trim($request->getPathInfo(), '/'), 2)[0];

        if ($segment !== '' && \in_array($segment, $locales, true)) {
            $request->attributes->set('_locale', $segment);

            return;
        }

        $request->attributes->set('_locale', $request->getPreferredLanguage($locales) ?? $this->defaultLocale);
    }
}
