<?php

namespace App\Enumerations;

enum VoteType: int
{
    case SKIP_DAY = 0;
    case HANG = 1;
    case READY = 3;
    case SPARE = 4;
    case NO_MERCY = 5;

    public function getAllowedStages(): Stage
    {
        return match($this) {
            self::SKIP_DAY, self::HANG => Stage::Day,
            self::READY => Stage::Lobby,
            self::SPARE, self::NO_MERCY => Stage::On_Stool,
        };
    }
}