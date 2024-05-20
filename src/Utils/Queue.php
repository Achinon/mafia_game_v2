<?php

namespace App\Utils;

class Queue
{
    /** @var array */
    private $stack;
    /** @var int */
    private $l;
    /** @var int */
    private $p;

    public function __construct()
    {
        $this->stack = [];
        $this->purge();
    }

    public function enqueue($element): self
    {
        $this->stack[$this->p++] = $element;
        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->p === $this->l;
    }

    public function dequeue()
    {
        if($this->isEmpty())
            return false;
        return $this->stack[$this->l++];
    }

    public function purge()
    {
        $this->l = $this->p = 0;
    }

    public function peek()
    {
        return $this->stack[$this->l];
    }
}