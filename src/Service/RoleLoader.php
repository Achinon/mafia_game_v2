<?php

namespace App\Service;

use Doctrine\Persistence\ObjectManager;
use App\Domain\RoleInterface;
use App\Factory\RoleFactory;
use App\Iterator\RoleClassDtoIterator;
use App\Dto\RoleClassDto;
use Achinon\ToolSet\Dumper;
use App\Utils\Utils;
use Exception;

readonly class RoleLoader
{
    const NamespaceLoaded = 'App\\Domain\\Roles';

    public function __construct(private ObjectManager $manager) {}

    /**
     * @throws Exception
     */
    public function load(): void
    {
        $manager = $this->manager;

        $classes = $this->getRoleNames();
        $iterator = new RoleClassDtoIterator($classes);

        /** @var RoleClassDto $roleDto */
        foreach ($iterator as $roleDto) {
            $role = RoleFactory::createFromDto($roleDto);

            $manager->persist($role);

            try {
                $manager->flush();
            } catch (Exception $e) {
                $manager->clear();
                throw $e;
            }
        }

        $manager->flush();
    }

    private function getRoleNames(): array
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