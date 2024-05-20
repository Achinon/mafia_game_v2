<?php

namespace App\Enumerations;

enum Stage: int
{
    case Created = 0;
    case Running = 1;
    case Finished = 2;
    case HANGING = 3;
}