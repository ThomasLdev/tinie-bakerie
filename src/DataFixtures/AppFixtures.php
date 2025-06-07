<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = UserFactory::createOne(
            [
                'email' => 'john@example.com',
                'plainPassword' => 'password123',
            ]
        );

        $manager->persist($user);
        $manager->flush();
    }
}
