<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->text(180),
            'plainPassword' => self::faker()->password(),
            'roles' => [],
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (User $user): void {
                $user
                    ->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()))
                    ->eraseCredentials();
            });
    }
}
