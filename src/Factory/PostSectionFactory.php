<?php

namespace App\Factory;

use App\Entity\PostSection;
use App\Services\Translations\TranslatableEntityPropertySetter;
use App\Services\PostSection\Enum\PostSectionType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostSection>
 */
final class PostSectionFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly TranslatableEntityPropertySetter $propertySetter,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return PostSection::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'position' => self::faker()->randomNumber(),
            'post' => null,
            'type' => self::faker()->randomElement(PostSectionType::cases()),
            'updatedAt' => self::faker()->dateTime(),
            'content' => self::faker()->text(150),
            'media' => [],
        ];
    }

    protected function initialize(): static
    {
        return $this
             ->afterInstantiate(function (PostSection $postSection): void {
                 $this->propertySetter->processTranslations(
                     $postSection,
                     [
                         'content' => fn ($locale) => $postSection->getContent().' '.$locale,
                     ]
                 );
             })
        ;
    }
}
