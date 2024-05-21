<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Utils\Time;
use App\Enumerations\VoteType;
use App\Repository\HangRepository;

#[ORM\Entity(repositoryClass: HangRepository::class)]
class Hang
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $ms_time;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: "player_id_to_hang", referencedColumnName: "player_id", nullable: false, onDelete: 'CASCADE')]
    private Player $player_to_hang;

    #[ORM\OneToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: "player_id_voting", referencedColumnName: "player_id", nullable: false, onDelete: 'CASCADE')]
    private Player $player_voting;

    public function getPlayerToHang(): Player
    {
        return $this->player_to_hang;
    }

    public function getPlayerVoting(): Player
    {
        return $this->player_voting;
    }

    public function __construct(Player $voter, Player $victim) {
        $this->player_to_hang = $victim;
        $this->player_voting = $voter;
        $this->ms_time = Time::currentMs();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMsTime(): string
    {
        return $this->ms_time;
    }
}
