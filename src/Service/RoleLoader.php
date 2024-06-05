<?php

namespace App\Service;

use Doctrine\Persistence\ObjectManager;
use App\Domain\RoleInterface;
use App\Factory\RoleFactory;

readonly class RoleLoader
{
    const NamespaceLoaded = 'App\\Domain\\Roles';

    public function __construct(private ObjectManager $manager) {}

    public function load(): void
    {
        $manager = $this->manager;

        $classes = $this->getClassesInNamespace();
        $namespace = static::NamespaceLoaded;
        foreach ($classes as $className) {
            /** @var RoleInterface|string $class */
            $class = "$namespace\\$className";

            $role = RoleFactory::create($className, $class::getDescription());

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