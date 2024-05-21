<?php

namespace App\Domain;

interface RoleInterface
{
    public static function getDescription(): string;

    public static function getName(): string;

    public function getActions(): array;
}