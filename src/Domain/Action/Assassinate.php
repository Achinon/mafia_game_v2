<?php

namespace Action;

use App\Domain\AbstractAction;

class Assassinate extends AbstractAction
{
    public function __construct()
    {
        $this->perform_on_other_player = true;
    }

    public function perform()
    {
        // TODO: Implement execute() method.
    }
}