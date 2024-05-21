<?php

namespace App\Domain\Roles;

use App\Enumerations\ConflictSide;
use App\Domain\AbstractRole;

class Citizen extends AbstractRole
{
    public static string $description = 'Main good character that needs to survive until the end of the game to win.';

    public function __construct() {
        $this->conflict_side = ConflictSide::Good;
    }
}