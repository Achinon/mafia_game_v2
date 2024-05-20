<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Utils\Time;
use App\Enumerations\VoteType;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\Table(name: "votes")]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $ms_time;

    #[ORM\Column(enumType: VoteType::class)]
    private VoteType $vote_type;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'votes')]
    #[ORM\JoinColumn(name: "player_id", referencedColumnName: "player_id", nullable: false, onDelete: 'CASCADE')]
    private Player $player;

    public function __construct(Player $player, VoteType $voteType) {
        $this->player = $player;
        $this->vote_type = $voteType;
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

    public function getVoteType(): VoteType
    {
        return $this->vote_type;
    }
}
