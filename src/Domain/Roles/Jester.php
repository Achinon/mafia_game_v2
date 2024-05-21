<?php

namespace App\Domain\Roles;

use App\Enumerations\ConflictSide;
use App\Domain\AbstractRole;

class Jester extends AbstractRole
{
    public static string $description = 'Neutral character that wins when hanged.';

    public function __construct() {
        $this->conflict_side = ConflictSide::Neutral;
    }
}