<?php

namespace App\Domain;

abstract class AbstractAction implements ActionInterface
{
    protected bool $perform_on_other_player;
}