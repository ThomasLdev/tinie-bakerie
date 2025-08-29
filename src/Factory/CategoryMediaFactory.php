<?php

namespace App\Factory;

use App\Entity\CategoryMedia;
use App\Entity\CategoryMediaTranslation;
use App\Services\Fixtures\Translations\TranslatableEntityPropertySetter;
use App\Services\Media\Enum\MediaType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<CategoryMedia>
 */
final class CategoryMediaFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly TranslatableEntityPropertySetter $propertySetter,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return CategoryMedia::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'alt' => self::faker()->text(10),
            'createdAt' => self::faker()->dateTime(),
            'mediaName' => '',
            'title' => self::faker()->text(10),
            'updatedAt' => self::faker()->dateTime(),
            'type' => MediaType::Image,
            'mediaFile' => null,
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (CategoryMedia $media) {
                $this->propertySetter->processTranslations(
                    $media,
                    CategoryMediaTranslation::class,
                    [
                        'title' => fn ($locale) => sprintf('%s %s', $media->getTitle(), $locale),
                        'alt' => fn ($locale) => sprintf('%s %s', $media->getAlt(), $locale),
                    ]
                );
            })
        ;
    }
}
