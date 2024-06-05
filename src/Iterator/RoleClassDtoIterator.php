<?php

namespace App\Iterator;

use App\Domain\RoleInterface;
use App\Factory\RoleFactory;
use App\Dto\RoleClassDto;
use App\Service\RoleLoader;
use Iterator;
use Achinon\ToolSet\Dumper;

class RoleClassDtoIterator implements Iterator
{
    /** @var string[]  */
    private array $roles;
    private int $position = 0;
    private ?RoleClassDto $current = null;

    public function __construct(array $role_classes)
    {
        $this->roles = $role_classes;
    }

    /** @returns class-string<RoleInterface> */
    public function current(): RoleClassDto
    {
        if(is_null($this->current)){
            $class_name = $this->roles[$this->position];

            $namespace = RoleLoader::NamespaceLoaded;
            /** @var class-string<RoleInterface> $fqcn */
            $fqcn = "$namespace\\$class_name";

            if(!in_array(RoleInterface::class, class_implements($fqcn))){
                throw new \Exception('RoleFactoryIterator has been injected with wrong kind of class.');
            }

            $name = $fqcn::getName();
            $description = $fqcn::getDescription();
            $this->current = new RoleClassDto($name, $description);
        }
        return $this->current;
    }

    public function next(): void
    {
        $this->current = null;
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->roles[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}