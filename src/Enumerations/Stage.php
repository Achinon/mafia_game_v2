<?php

namespace App\Enumerations;

enum Stage: int
{
    case Lobby = 0;
    case Day = 1;
    case Night = 4;
    case Hanging = 3;
    case Finished = 2;
    case On_Stool = 5;
}