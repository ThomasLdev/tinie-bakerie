<?php

namespace App\Factory;

use App\Entity\PostSectionMedia;
use App\Entity\PostSectionMediaTranslation;
use App\Services\Fixtures\Translations\TranslatableEntityPropertySetter;
use App\Services\Media\Enum\MediaType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostSectionMedia>
 */
final class PostSectionMediaFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly TranslatableEntityPropertySetter $propertySetter,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return PostSectionMedia::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'alt' => self::faker()->text(),
            'createdAt' => self::faker()->dateTime(),
            'title' => self::faker()->text(),
            'type' => self::faker()->randomElement(MediaType::cases()),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }

    protected function initialize(): static
    {
        return $this
             ->afterInstantiate(function (PostSectionMedia $postSectionMedia): void {
                 $this->propertySetter->processTranslations(
                     $postSectionMedia,
                     PostSectionMediaTranslation::class,
                     [
                         'title' => fn ($locale) => sprintf('%s %s',$postSectionMedia->getTitle(), $locale),
                         'alt' => fn ($locale) => sprintf('%s %s',$postSectionMedia->getAlt(), $locale),
                     ]
                 );
             })
        ;
    }
}
