<?php

namespace App\Enumerations;

enum Actions: int
{
    case BARRICADE = 0;
    case ASSASSINATE = 1;
    case INVESTIGATE = 2;
}