<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\Player;
use App\Enumerations\VoteType;
use App\Entity\Vote;
use App\Enumerations\Stage;
use App\Entity\Hang;

interface SessionManagerInterface
{
    public function newPlayer(string $player_name): Player;
    public function isPlayerJoined(string $playerName): bool;
    public function getGameSession(): ?Session;
    public function setGameSession(Session|string $session): static;
    public function setGameSessionByJoinCode(string $join_code): static;
    public function setPlayer(Player $player): static;
    public function getPlayer(): Player;
    public function isStage(Stage $stage): bool;
    public function vote(VoteType $vote_type): ?Vote;

    public function verifyIfEligibleToStart(): bool;

    public function disconnect(): static;

    public function hang(string $player_name): ?Hang;
}