<?php

declare(strict_types=1);

namespace App\Form\Trait;

trait LocalizedFormType
{
    /**
     * @param array<string> $supportedLocales
     *
     * @return array<string,string>
     */
    public function getLocales(array $supportedLocales): array
    {
        $locales = [];

        foreach ($supportedLocales as $locale) {
            $locales[$locale] = $locale;
        }

        return $locales;
    }
}
