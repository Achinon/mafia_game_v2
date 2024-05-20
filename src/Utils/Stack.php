<?php

namespace App\Utils;

class Stack
{
    /** @var array */
    private $stack;
    /** @var int */
    private $pointer;

    public function __construct()
    {
        $this->stack = [];
        $this->pointer = -1;
    }

    public function pop()
    {
        if($this->empty())
            return false;

        $this->pointer--;

        return array_pop($this->stack);
    }

    public function empty(): bool
    {
        return $this->pointer < 0;
    }

    public function count(): int
    {
        return $this->pointer + 1;
    }

    public function push($element): self
    {
        $this->stack[++$this->pointer] = $element;
        return $this;
    }

    /** reverse = true -> beginning of array is on top of the stack */
    public static function createFromArray(array $a, bool $reverse = false): self
    {
        $stack = new static();
        $a = $reverse ?
          array_reverse($a):
          $a;
        foreach ($a as $v)
            $stack->push($v);
        return $stack;
    }

    public function flip()
    {

    }
}