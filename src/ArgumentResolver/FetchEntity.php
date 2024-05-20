<?php

namespace App\ArgumentResolver;


use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class FetchEntity{
    public function __construct(public array $fetchBy) { }
}