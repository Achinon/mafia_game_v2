<?php

namespace App\Service;

use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Player;
use App\Enumerations\Stage;
use App\Enumerations\VoteType;
use App\Entity\Vote;
use App\Entity\Hang;
use App\Repository\HangRepository;
use App\Repository\PlayerRepository;
use Exception;

/** The main purpose of this proxy is to run certain checks before executing
 *  the standard Service class.
 */
class SessionManagerProxy implements SessionManagerInterface
{
    private ?Session $session = null;

    /** @param SessionManagerService $session_manager */
    public function __construct(
        private readonly SessionManagerInterface $session_manager,
        private readonly SessionRepository       $session_repository,
        private readonly EntityManagerInterface  $entity_manager)
    {
    }


    /** @throws Exception */
    private function verifyIfSessionIsSet(){
        if(is_null($this->session_manager->getGameSession())){
            throw new Exception('Cannot use session manager without setting the session first.');
        }
    }

    /** @throws Exception */
    private function verifyIfPlayerIsSet(){
        if(is_null($this->session_manager->getPlayer())){
            throw new Exception('Cannot use session manager without setting the session first.');
        }
    }

    /** @throws Exception */
    private function checkIfPlayerIsHost(){
        $this->verifyIfPlayerIsSet();
        $player = $this->session_manager->getPlayer();
        $host = $this->session_manager->getGameSession()->getHost();

        return $player === $host;
    }

    public function setGameSession(Session|string $session): static
    {
        if(!$session instanceof Session) {
            $session = $this->session_repository->findOneBy(['game_session_id' => $session]);
            if(!$session){
                throw new \Error('Session not found');
            }
        }
        $this->session_manager->setGameSession($session);
        return $this;
    }

    public function setGameSessionByJoinCode(string $join_code): static
    {
        $this->session_manager->setGameSessionByJoinCode($join_code);
        return $this;
    }

    public function getGameSession(): ?Session
    {
        return $this->session_manager->getGameSession();
    }

    public function newPlayer(string $player_name): static
    {
        $this->verifyIfSessionIsSet();
        $this->session_manager->newPlayer($player_name);
        return $this;
    }

    public function setPlayer(Player $player): static
    {
        $this->session_manager->setPlayer($player);
        return $this;
    }

    /**
     * @throws Exception
     */
    public function getPlayer(): Player
    {
        $this->verifyIfPlayerIsSet();
        return $this->session_manager->getPlayer();
    }

    /**
     * @throws Exception
     */
    public function isPlayerJoined(string $playerName): bool
    {
        $this->verifyIfSessionIsSet();
        return $this->session_manager->isPlayerJoined($playerName);
    }

    public function vote(VoteType $vote_type): static
    {
        $this->verifyIfPlayerIsSet();
        $this->verifyIfSessionIsSet();
        $this->session_manager->vote($vote_type);
        return $this;
    }

    public function isStage(Stage $stage): bool
    {
        return $this->session_manager->isStage($stage);
    }

    /**
     * @throws Exception
     */
    public function startGame(): static
    {
        if(!$this->checkIfPlayerIsHost()){
            throw new \Error('Player attempting to start the game must be a host.');
        }

        $session = $this->session_manager->getGameSession();

        if($session->getStage() != Stage::Lobby){
            throw new \Error('The game is already started.');
        }

        $voteRepository = $this->entity_manager->getRepository(Vote::class);

        $usersReady = $voteRepository->getPlayerVoteCountOn($session, VoteType::READY);
        $numberOfJoinedPlayers = $session->getPlayers()->count();

        if($usersReady != $numberOfJoinedPlayers){
            throw new \Error('All players must mark themselves as ready before starting.');
        }

        $this->session_manager->startGame();

        return $this;
    }

    /**
     * @throws Exception
     */
    public function disconnect(): static
    {
        $this->verifyIfPlayerIsSet();
        $this->session_manager->disconnect();
        return $this;
    }

    /**
     * @throws Exception
     */
    public function hang(string $player_name): ?Hang
    {
        $this->verifyIfPlayerIsSet();
        if($this->getGameSession()->getStage() != Stage::HANGING){
            throw new \Error('Cannot hang in current stage.');
        }
        return $this->session_manager->hang($player_name);
    }
}