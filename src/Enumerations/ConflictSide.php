<?php

namespace App\Enumerations;

enum ConflictSide: int
{
    case Good = 0;
    case Evil = 1;
    case Neutral = 2;

    public function ratio(): float
    {
        return match ($this){
            ConflictSide::Evil => 0.15,
            ConflictSide::Neutral => 0.1,
            ConflictSide::Good => 0.75
        };
    }
}