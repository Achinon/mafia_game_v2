<?php

namespace App\Domain\Roles;

use App\Enumerations\ConflictSide;
use App\Domain\AbstractRole;

class Mafioso extends AbstractRole
{
    public static string $description = 'Main evil character that tries to kill all citizens.';

    public function __construct() {
        $this->name = 'Mafioso';
        $this->conflict_side = ConflictSide::Evil;
    }
}