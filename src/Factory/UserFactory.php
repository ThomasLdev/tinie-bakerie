<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<User>
     */
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'email' => self::faker()->email(),
            'plainPassword' => self::faker()->password(),
            'roles' => [],
            'updatedAt' => self::faker()->dateTime(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
        // ->afterInstantiate(function(User $user): void {})
    }
}
