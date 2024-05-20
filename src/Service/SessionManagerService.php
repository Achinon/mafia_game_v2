<?php

namespace App\Service;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Player;
use App\Entity\VoteToRemove;
use App\Entity\Vote;
use App\Enumerations\VoteType;
use App\Enumerations\Stage;
use App\Repository\VoteRepository;
use App\Entity\Hang;

class SessionManagerService implements SessionManagerInterface
{
    private ?Session $session = null;
    private ?Player $player = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SessionRepository $sessionRepository)
    {}

    public function setGameSession(Session|string $session): static
    {
        $this->session = $session;
        return $this;
    }

    public function setGameSessionByJoinCode(Session|string $session): static
    {
        $this->session = $session;
        return $this;
    }

    public function newPlayer(string $player_name): Player
    {
        $new_player = new Player($this->session);
        $playerRepository = $this->entityManager->getRepository(Player::class);
        $nameCounter = $playerRepository->nameDuplicateNumber(
          $this->session,
          $player_name);

        $this->setPlayer($new_player);
        return $new_player->setName($player_name.$nameCounter);
    }

    public function setPlayer(Player $player): static
    {
        $this->player = $player;
        $this->session = $player->getGameSession();

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function isPlayerJoined(string $playerName): bool
    {
        return false;
    }

    public function getGameSession(): ?Session
    {
        return $this->session;
    }

    public function isStage(Stage $stage): bool
    {
        return $this->session->getStage() === $stage;
    }

    /**
     * @throws \Exception
     */
    public function vote(VoteType $vote_type): ?Vote
    {
        /** @var VoteRepository $voteRepository */
        $voteRepository = $this->entityManager->getRepository(Vote::class);

        $neededStage = $vote_type->getAllowedStages();

        if($neededStage != $this->session->getStage() ||
          $voteRepository->hasPlayerAlreadyVoted($this->player, $vote_type)) {
            return null;
        }

        $vote = new Vote($this->player, $vote_type);;
        return $vote;
    }

    public function verifyIfEligibleToStart(): bool
    {
        $voteRepository = $this->entityManager->getRepository(Vote::class);
        $usersReady = $voteRepository->getPlayerVoteCountOn($this->session, VoteType::READY);
        $numberOfJoinedPlayers = $this->session->getPlayers()->count();

        return $usersReady === $numberOfJoinedPlayers;
    }

    public function start(): static
    {
        if(!$this->verifyIfEligibleToStart()){
            return $this;
        }
        $this->entityManager->beginTransaction();
        try{
            $this->session->setStage(Stage::Running);
            $this->clearVotes();
            $this->entityManager->commit();
        }
        catch(\Exception $e){
            $this->entityManager->rollback();
        }
    }

    public function disconnect(): static
    {
        $this->session->removePlayer($this->player);
        return $this;
    }

    public function clearVotes(): static
    {
        /** @var VoteRepository $voteRepository */
        $voteRepository = $this->entityManager->getRepository(Vote::class);
        $voteRepository->clearSessionVotes($this->session);
        return $this;
    }

    public function hang(string $player_name): ?Hang
    {
        /** @var VoteRepository $voteRepository */
        $playerRepository = $this->entityManager->getRepository(Player::class);

        if($this->player->getName() === $player_name){
            throw new \Error('Cannot hang yourself.');
        }

        $playerToHang = $playerRepository->findOneBy(['name' => $player_name, 'game_session' => $this->session]);
        if(!$playerToHang){
            throw new \Error('Player with that name is not connected to the game.');
        }
        return new Hang($this->player, $playerToHang);
    }
}