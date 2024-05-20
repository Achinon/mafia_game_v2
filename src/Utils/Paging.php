<?php

namespace App\Utils;

use App\Enumerations\PagingSide;

class Paging
{
    /** @var int */
    const pageSize = 10;
    
    public static function getOffset(PagingSide $side, int $page): int
    {
        if($page < 1){
            return 0;
        }
        return match($side){
            PagingSide::Left => (max(1, $page) - 1) * static::pageSize,
            PagingSide::Right => static::pageSize * $page - 1
        };
    }
}