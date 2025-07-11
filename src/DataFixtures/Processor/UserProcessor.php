<?php

namespace App\DataFixtures\Processor;

use App\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
#[AutoconfigureTag('fidry_alice_data_fixtures.processor')]
readonly class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function preProcess(string $id, object $object): void
    {
        if (false === $object instanceof User) {
            return;
        }

        $object->setPassword(
            $this->passwordHasher->hashPassword($object, $object->getPlainPassword() ?? '')
        );
    }

    public function postProcess(string $id, object $object): void
    {
        // do nothing
    }
}
