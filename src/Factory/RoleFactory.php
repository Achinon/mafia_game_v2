<?php

namespace App\Factory;

use App\Entity\Role;

class RoleFactory {
    public static function create(string $role_name, string $role_description): Role
    {
        $role = new Role();
        $role->setName($role_name)
             ->setDescription($role_description);

        return $role;
    }
}