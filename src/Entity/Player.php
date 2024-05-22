<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use App\Utils\Utils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\Table(name: 'players')]
class Player
{
    #[ORM\Id]
    #[ORM\Column(length: 8)]

    private ?string $player_id;
    #[ORM\Column(type: 'boolean')]
    private bool $is_dead = false;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'players')]
    #[ORM\JoinColumn(name: 'game_session_id', referencedColumnName: 'game_session_id', nullable: false, onDelete: 'CASCADE')]
    private Session $game_session;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\OneToOne(targetEntity: Vote::class, mappedBy: 'player', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Vote $vote;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'players')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'role_id')]
    private ?Role $role = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function kill(): self
    {
        $this->is_dead = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDead(): bool
    {
        return $this->is_dead;
    }

    public function __construct(Session $game_session)
    {
        $this->player_id = Utils::generateRandomString(8);
        $this->game_session = $game_session;
        $this->game_session->addPlayer($this);
    }

    public function getPlayerId(): ?string
    {
        return $this->player_id;
    }

    public function getGameSession(): Session
    {
        return $this->game_session;
    }

    public function getVote(): ?Vote
    {
        return $this->vote;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }
}
