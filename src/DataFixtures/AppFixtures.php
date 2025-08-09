<?php

namespace App\DataFixtures;

use App\Factory\CategoryFactory;
use App\Factory\PostFactory;
use App\Factory\PostMediaFactory;
use App\Factory\TagFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        CategoryFactory::createMany(10);
        TagFactory::createMany(5);

        PostFactory::createMany(20, static function () {
            return [
                'category' => CategoryFactory::random(),
                'tags' => TagFactory::randomRange(1, 3),
                'media' => PostMediaFactory::createRange(1, 3),
            ];
        });
    }
}
