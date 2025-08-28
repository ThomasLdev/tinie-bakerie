<?php

namespace App\Factory;

use App\Entity\Tag;
use App\Services\Translations\TranslatableEntityPropertySetter;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Tag>
 */
final class TagFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly TranslatableEntityPropertySetter $propertySetter,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return Tag::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'color' => self::faker()->hexColor(),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'title' => self::faker()->word(),
            'activatedAt' => self::faker()->boolean(90) ? self::faker()->dateTime() : null,
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Tag $tag) {
                $this->propertySetter->processTranslations(
                    $tag,
                    [
                        'title' => fn ($locale) => $tag->getTitle().' '.$locale,
                    ]
                );
            })
        ;
    }
}
