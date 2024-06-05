<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Domain\RoleInterface;
use App\Entity\Role;
use App\Factory\RoleFactory;
use App\Service\RoleLoader;

class RoleFixtures extends Fixture
{
    public function __construct(private readonly RoleLoader $loader)
    {
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->loader->load();
    }
}
