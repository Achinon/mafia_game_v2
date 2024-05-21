<?php

namespace App\Domain;

use App\Enumerations\ConflictSide;

abstract class AbstractRole implements RoleInterface
{
    protected ConflictSide $conflict_side;
    /** @var ActionInterface[] */
    protected static array $actions = [];
    public static string $description = 'Description not set.';

    public static function getName(): string
    {
        return basename(str_replace('\\', '/', static::class));
    }

    public function getActions(): array
    {
        return static::$actions;
    }

    public static function getDescription(): string
    {
        return static::$description;
    }
}