<?php

namespace App\DataFixtures;

use App\Services\Fixtures\Media\MediaLoader;
use App\Factory\CategoryFactory;
use App\Factory\CategoryMediaFactory;
use App\Factory\PostFactory;
use App\Factory\PostMediaFactory;
use App\Factory\PostSectionFactory;
use App\Factory\PostSectionMediaFactory;
use App\Factory\TagFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly MediaLoader $mediaLoader,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        CategoryFactory::createMany(5, function () {
            return [
                'media' => CategoryMediaFactory::createRange(1, 3, function () {
                    return $this->mediaLoader->getRandomMedia();
                }),
            ];
        });

        TagFactory::createMany(15);

        PostFactory::createMany(30, function () {
            return [
                'category' => CategoryFactory::random(),
                'tags' => TagFactory::randomRange(1, 3),
                'media' => PostMediaFactory::createRange(1, 3, function () {
                    return $this->mediaLoader->getRandomMedia();
                }),
            ];
        });

        PostSectionFactory::createMany(40, function () {
            return [
                'media' => PostSectionMediaFactory::createRange(1, 3, function () {
                    return $this->mediaLoader->getRandomMedia();
                }),
                'post' => PostFactory::random(),
            ];
        });
    }
}
