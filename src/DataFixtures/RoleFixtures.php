<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Domain\RoleInterface;
use App\Entity\Role;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $classes = $this->getClassesInNamespace();
        $namespace = "App\\Domain\\Roles";
        foreach ($classes as $className) {
            /** @var RoleInterface|string $class */
            $class = "$namespace\\$className";
            $description = $class::getDescription();

            $role = new Role();
            $role->setName($className)
                 ->setDescription($description);

            $manager->persist($role);
            try {
                $manager->flush();
            } catch (\Exception $e) {
                $manager->clear();
                throw $e;
            }
        }

        $manager->flush();
    }

    private function getClassesInNamespace(): array
    {
        $classes = [];
        $path = __DIR__ . '/../Domain/Roles/';
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $classes[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return $classes;
    }
}
