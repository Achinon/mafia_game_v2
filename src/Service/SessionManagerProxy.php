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

/** The main purpose of this proxy is to run certain checks before executing
 *  the standard Service class.
 */
class SessionManagerProxy implements SessionManagerInterface
{
    private ?Session $session = null;

    /** @param SessionManagerService $sessionManager */
    public function __construct(
        private readonly SessionManagerInterface $sessionManager,
        private readonly SessionRepository $sessionRepository,
        private readonly EntityManagerInterface $entity_manager)
    {
    }


    /** @throws \Exception */
    private function verifyIfSessionIsSet(){
        if(is_null($this->sessionManager->getGameSession())){
            throw new \Exception('Cannot use session manager without setting the session first.');
        }
    }

    /** @throws \Exception */
    private function verifyIfPlayerIsSet(){
        if(is_null($this->sessionManager->getPlayer())){
            throw new \Exception('Cannot use session manager without setting the session first.');
        }
    }

    /** @throws \Exception */
    private function checkIfPlayerIsHost(){
        $this->verifyIfPlayerIsSet();
        $player = $this->sessionManager->getPlayer();
        $host = $this->sessionManager->getGameSession()->getHost();

        return $player === $host;
    }

    public function setGameSession(Session|string $session): static
    {
        if(!$session instanceof Session) {
            $session = $this->sessionRepository->findOneBy(['game_session_id' => $session]);
            if(!$session){
                throw new \Error('Session not found');
            }
        }
        $this->sessionManager->setGameSession($session);
        return $this;
    }

    public function setGameSessionByJoinCode(string $code): static
    {
        $session = $this->sessionRepository->findOneBy(["join_code" => $code, 'stage' => Stage::Created]);
        if(!$session) {
            throw new \Error('Session not found');
        }

        $this->sessionManager->setGameSession($session);
        return $this;
    }

    public function getGameSession(): ?Session
    {
        return $this->sessionManager->getGameSession();
    }

    public function newPlayer(string $player_name): Player
    {
        $this->verifyIfSessionIsSet();
        return $this->sessionManager->newPlayer($player_name);
    }

    public function setPlayer(Player $player): static
    {
        $this->sessionManager->setPlayer($player);
        return $this;
    }

    public function getPlayer(): Player
    {
        $this->verifyIfPlayerIsSet();
        return $this->sessionManager->getPlayer();
    }

    public function isPlayerJoined(string $playerName): bool
    {
        // TODO: Implement isPlayerJoined() method.
    }

    public function vote(VoteType $vote_type): ?Vote
    {
        $this->verifyIfPlayerIsSet();
        $this->verifyIfSessionIsSet();
        return $this->sessionManager->vote($vote_type);
    }

    public function isStage(Stage $stage): bool
    {
        return $this->sessionManager->isStage($stage);
    }

    public function verifyIfEligibleToStart(): bool
    {
        $isHost = $this->checkIfPlayerIsHost();
        $session = $this->sessionManager->getGameSession();

        if($isHost && $session->getStage() === Stage::Created){
            return $this->sessionManager->verifyIfEligibleToStart();
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function disconnect(): static
    {
        $this->verifyIfPlayerIsSet();
        $this->sessionManager->disconnect();
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function hang(string $player_name): ?Hang
    {
        $this->verifyIfPlayerIsSet();
        if($this->getGameSession()->getStage() != Stage::HANGING){
            throw new \Error('Cannot hang in current stage.');
        }
        return $this->sessionManager->hang($player_name);
    }
}