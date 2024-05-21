<?php

namespace App\Enumerations;

enum ConflictSide: int
{
    case Good = 0;
    case Evil = 1;
    case Neutral = 2;
}