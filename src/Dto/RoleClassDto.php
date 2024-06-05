<?php

namespace App\Dto;

readonly class RoleClassDto
{
    public function __construct(public string $name,
                                public string $description)
    {
    }
}