<?php

namespace App\Entity;

use App\Enumerations\Stage;
use App\Repository\SessionRepository;
use App\Utils\Time;
use App\Utils\Utils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enumerations\VoteType;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\HangRepository;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'game_sessions')]
class Session
{
    #[ORM\Id]
    #[ORM\Column(length: 12)]
    private string $game_session_id;

    #[ORM\Column(length: 6)]
    private string $join_code;

    #[ORM\Column]
    private int $day_count = 0;

    #[ORM\Column(length: 255)]
    private string $ms_time_scheduler_delay;

    #[ORM\Column(length: 255)]
    private string $ms_time_created;

    #[ORM\Column(length: 255)]
    private string $ms_time_last_updated;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ms_time_started = null;

    #[ORM\Column(name: "stage_id", enumType: Stage::class)]
    private Stage $stage;

    #[ORM\OneToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'host_id', referencedColumnName: 'player_id')]
    private ?Player $host = null;

    /** @var Collection<int, Role>  */
    #[ORM\JoinTable(name: 'session_enabled_roles')]
    #[ORM\JoinColumn(name: 'game_session_id', referencedColumnName: 'game_session_id')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'role_id')]
    #[ORM\ManyToMany(targetEntity: Role::class)]
    private Collection $available_roles;

    /** @var Collection<int, Player>  */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: "game_session", cascade: ['remove'], orphanRemoval: true)]
    private Collection $players;

    public function __construct()
    {
        $this->game_session_id = Utils::friendlyString();
        $this->join_code = Utils::generateRandomNumberString(4);
        $this->ms_time_created = $this->ms_time_last_updated = Time::currentMs();
        $this->ms_time_scheduler_delay = Time::msFromMinutes(1);
        $this->available_roles = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->setStage(Stage::Lobby);
    }

    public function getGameSessionId(): string
    {
        return $this->game_session_id;
    }

    public function getHost(): ?Player
    {
        return $this->host;
    }

    private function setHost(Player $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function getJoinCode(): ?string
    {
        return $this->join_code;
    }

    public function setJoinCode(string $join_code): static
    {
        $this->join_code = $join_code;

        return $this;
    }

    public function getDayCount(): ?int
    {
        return $this->day_count;
    }

    public function isNight(): ?bool
    {
        return $this->is_night;
    }

    public function setNight(): static
    {
        if($this->getStage() === Stage::Day){
            $this->is_night = true;
        }

        return $this;
    }

    public function setDay(): static
    {
        if($this->isNight()){
            $this->day_count++;
            $this->is_night = true;
        }

        return $this;
    }

    public function getMsTimeCreated(): ?string
    {
        return $this->ms_time_created;
    }

    public function getMsTimeStarted(): ?string
    {
        return $this->ms_time_started;
    }

    public function setMsTimeStarted(string $ms_time_started): static
    {
        $this->ms_time_started = $ms_time_started;

        return $this;
    }

    public function getStage(): Stage
    {
        return $this->stage;
    }

    public function setStage(Stage $stage): static
    {
        $this->stage = $stage;

        return $this;
    }

    /** @return Collection<Player> */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player ...$playersToAdd): self
    {
        if(is_null($this->host)){
            $this->setHost($playersToAdd[0]);
        }
        foreach ($playersToAdd as $player) {
            if (!$this->players->contains($player)) {
                $this->players->add($player);
            }
        }
        return $this;
    }

    public function removePlayer(Player $player): self
    {
        if($this->players->contains($player)){
            $this->players->removeElement($player);

            $playerCount = $this->players->count();
            if($this->host === $player && $playerCount > 0){
                $this->setHost($this->players->first());
            }
            if($playerCount === 0){
                $this->host = null;
            }
        }

        return $this;
    }

    public function getAvailableRoles(): Collection
    {
        return $this->available_roles;
    }

    public function setAvailableRoles(Collection $available_roles): void
    {
        $this->available_roles = $available_roles;
    }

    public function getMsTimeLastUpdated(): string
    {
        return $this->ms_time_last_updated;
    }

    public function setMsTimeLastUpdated(string $ms_time_last_updated): void
    {
        $this->ms_time_last_updated = $ms_time_last_updated;
    }

    public function getMsTimeSchedulerDelay(): string
    {
        return $this->ms_time_scheduler_delay;
    }

    public function setMsTimeSchedulerDelay(string $ms_time_scheduler_delay): void
    {
        $this->ms_time_scheduler_delay = $ms_time_scheduler_delay;
    }
}
