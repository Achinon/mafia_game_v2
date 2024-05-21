<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enumerations\ConflictSide;
use App\Domain\RoleInterface;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'roles')]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $role_id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->roleId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDomainClass(): RoleInterface
    {
        /** @var RoleInterface $class */
        $class ="App\\Domain\\Role\\$this->name";
        return new $class();
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
