<?php

namespace App\DataFixtures\Factory;

use App\Entity\User;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use function Zenstruck\Foundry\lazy;

class UserFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected static function getClass(): string
    {
        return User::class;
    }

    public static function createOneSuperAdmin(array $attributes = []): User
    {
        return (self::createOne(['role' => 'ROLE_SUPER_ADMIN','company'=>null] + $attributes))->object();
    }

    public static function createOneUser(array $attributes = []): User
    {
        return (self::createOne(['role' => 'ROLE_USER'] + $attributes))->object();
    }

    protected function getDefaults(): array
    {
        $name = self::faker()->unique()->regexify('/^[A-Z][A-Za-z ]{2}[A-Za-z ]*$/');
        return  [
            'name' => $name,
            'company' => lazy(static fn () => CompanyFactory::new()),
            'role' => 'ROLE_USER',
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }

}
