<?php

namespace App\Factory;

use App\Entity\CategoryMedia;
use App\Services\Fixtures\TranslatableEntityPropertySetter;
use App\Services\Media\Enum\MediaType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<CategoryMedia>
 */
final class CategoryMediaFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly TranslatableEntityPropertySetter $propertySetter,
    )
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return CategoryMedia::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'alt' => self::faker()->text(10),
            'createdAt' => self::faker()->dateTime(),
            'mediaName' => '',
            'title' => self::faker()->text(10),
            'updatedAt' => self::faker()->dateTime(),
            'type' => MediaType::Image,
            'mediaFile' => null
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(CategoryMedia $media) {
                $this->propertySetter->processTranslations(
                    $media,
                    [
                        'title' => fn($locale) => $media->getTitle() . ' ' . $locale,
                        'alt' => fn($locale) => $media->getAlt() . ' ' . $locale,
                    ]
                );
            })
        ;
    }
}
