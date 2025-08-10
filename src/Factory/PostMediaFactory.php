<?php

namespace App\Factory;

use App\Entity\PostMedia;
use App\Services\Fixtures\MediaFileProcessor;
use App\Services\Fixtures\TranslatableEntityPropertySetter;
use App\Services\Media\Enum\MediaType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostMedia>
 */
final class PostMediaFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly TranslatableEntityPropertySetter $propertySetter,
        private readonly MediaFileProcessor $mediaFileProcessor,
    )
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return PostMedia::class;
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
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(PostMedia $postMedia) {
                $this->mediaFileProcessor->process($postMedia, 'posts');
                $this->propertySetter->processTranslations(
                    $postMedia,
                    [
                        'title' => fn($locale) => $postMedia->getTitle() . ' ' . $locale,
                        'alt' => fn($locale) => $postMedia->getAlt() . ' ' . $locale,
                    ]
                );


            })
        ;
    }
}
