<?php

namespace App\DataFixtures\Factory;

use App\Entity\Company;
use Zenstruck\Foundry\ModelFactory;

class CompanyFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    protected static function getClass(): string
    {
        return Company::class;
    }
    protected function initialize(): self
    {
        return $this;
    }
    protected function getDefaults(): array
    {
        return  [
            'name' => self::faker()->company(),
        ];
    }
}
