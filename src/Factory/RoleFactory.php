<?php

namespace App\Factory;

use App\Entity\Role;
use App\Dto\RoleClassDto;

class RoleFactory {
    public static function create(string $role_name, string $role_description): Role
    {
        $role = new Role();
        $role->setName($role_name)
             ->setDescription($role_description);

        return $role;
    }

    public static function createFromDto(RoleClassDto $dto): Role
    {
        $role = new Role();
        $role->setName($dto->name)
             ->setDescription($dto->description);

        return $role;
    }
}