<?php

namespace App\Enumerations;

enum VoteType: int
{
    case SKIP_DAY = 0;
    case HANG = 1;
    case START_GAME = 2;
    case READY = 3;
    const REMATCH = 4;

    public function getAllowedStages(): Stage
    {
        return match($this) {
            self::SKIP_DAY, self::HANG => Stage::Running,
            self::START_GAME, self::READY => Stage::Created,
            self::REMATCH => Stage::Finished,
        };
    }
}