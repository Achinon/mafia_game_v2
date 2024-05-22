<?php

namespace App\Service;

use App\Entity\Session;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Player;
use App\Entity\Vote;
use App\Enumerations\VoteType;
use App\Enumerations\Stage;
use App\Repository\VoteRepository;
use App\Entity\Hang;
use Exception;
use App\Repository\PlayerRepository;
use App\Entity\Role;
use App\Enumerations\ConflictSide;
use App\Domain\RoleInterface;
use App\Domain\Roles\Citizen;
use App\Domain\Roles\Mafioso;
use App\Domain\Roles\Jester;
use App\Repository\HangRepository;
use App\Utils\Utils;
use App\Utils\Time;

class SessionManagerService implements SessionManagerInterface
{
    private ?Session $session = null;
    private ?Player $player = null;

    public function __construct(
      private readonly EntityManagerInterface $entity_manager,
      private readonly SessionRepository      $session_repository)
    {
    }

    public function setGameSession(Session|string $session): static
    {
        $this->session = $session;
        return $this;
    }

    public function setGameSessionByJoinCode(string $join_code): static
    {
        $session = $this->session_repository->findOneBy(["join_code" => $join_code, 'stage' => Stage::Lobby]);
        if(!$session) {
            throw new \Error('Session not found');
        }

        return $this->setGameSession($session);
    }

    public function newPlayer(string $player_name): static
    {
        $new_player = new Player($this->session);
        $playerRepository = $this->entity_manager->getRepository(Player::class);
        $nameCounter = $playerRepository->nameDuplicateNumber(
          $this->session,
          $player_name);

        $new_player->setName($player_name.$nameCounter);
        $this->setPlayer($new_player);

        $this->entity_manager->persist($new_player);
        $this->entity_manager->flush();
        return $this;
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
        /** @var PlayerRepository $player_repository */
        $player_repository = $this->entity_manager->getRepository(Player::class);
        return !!$player_repository->findOneBy(["name" => $playerName, "game_session" => $this->session]);
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
     * @throws Exception
     */
    public function vote(VoteType $vote_type): static
    {
        $neededStage = $vote_type->getAllowedStages();
        if($neededStage != $this->session->getStage()) {
            throw new \Error(sprintf('This vote (%s) is only allowed on %s stage.', $vote_type->name, $neededStage->name));
        }

        /** @var VoteRepository $voteRepository */
        $voteRepository = $this->entity_manager->getRepository(Vote::class);
        if($voteRepository->hasPlayerAlreadyVoted($this->player)) {
            throw new \Error('This player has already voted.');
        }
        $alreadyVoted = $voteRepository->getPlayerVoteCountOn($this->session, $vote_type) + 1;

        $this->entity_manager->beginTransaction();
        try {
            $this->entity_manager->persist(new Vote($this->player, $vote_type));
            if($alreadyVoted >= $this->session->getPlayers()
                                              ->count() / 2) {
                $a = [
                  VoteType::HANG->value => [Hang::class, Stage::Hanging],
                  VoteType::SKIP_DAY->value => [Vote::class, Stage::Night]
                ];

                switch($vote_type) {
                    case VoteType::HANG:
                    case VoteType::SKIP_DAY:
                        $this->clearSessionVotesOrHangs($a[$vote_type->value][0]);
                        $this->session->setStage($a[$vote_type->value][1]);
                        $this->entity_manager->persist($this->session);
                        break;
                    case VoteType::NO_MERCY:
                    case VoteType::SPARE:
                        $playerID = $this->getPlayerOnStool()['player_id'];
                        if($playerID === $this->player->getId()) {
                            throw new \Error('Cannot vote when being on the stool.');
                        }
                        $this->clearSessionVotesOrHangs(Vote::class);
                        $this->session->setStage(Stage::Night);
                        if($vote_type == VoteType::NO_MERCY) {
                            $player_repository = $this->entity_manager->getRepository(Player::class);
                            $playerToKill = $player_repository->find($playerID);
                            $playerToKill->kill();
                            $this->entity_manager->persist($playerToKill);
                        }
                        $this->entity_manager->persist($this->session);
                    case VoteType::READY: // no action required
                }
            }
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        }
        catch(Exception|\Error $e) {
            $this->entity_manager->rollback();
            if($e instanceof \Error) {
                throw $e;
            }
            //todo: change after added logging
            throw $e;
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function startGame(): static
    {
        $this->entity_manager->beginTransaction();
        try {
            $this->clearSessionVotesOrHangs(Vote::class);
            $this->session->setStage(Stage::Day);
            $this->assignRoles();
            $this->session->setMsTimeStarted(Time::currentMs());
            $this->entity_manager->persist($this->session);
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        }
        catch(Exception $e) {
            $this->entity_manager->rollback();
            throw $e;
        }

        return $this;
    }

    /** @throws Exception
     * @var class-string<Vote::class>|class-string<Hang::class> $entity
     */
    private function clearSessionVotesOrHangs(string $entity): static
    {
        /** @var VoteRepository|HangRepository $vote_repository */
        $repository = $this->entity_manager->getRepository($entity);
        if(!method_exists($repository, 'clearSessionVotes')) {
            throw new Exception('Can only provide classes that posses this method in their repository.');
        }
        $repository->clearSessionVotes($this->session);

        return $this;
    }

    public function disconnect(): static
    {
        $this->session->removePlayer($this->player);
        $this->entity_manager->persist($this->session);
        $this->entity_manager->flush();
        return $this;
    }

    public function getPlayerOnStool()
    {
        $hang_repository = $this->entity_manager->getRepository(Hang::class);

        return $hang_repository->getHangHighestVoted($this->getGameSession());
    }

    /**
     * @throws Exception
     */
    public function hang(string $player_name): static
    {
        /** @var VoteRepository $voteRepository */
        $playerRepository = $this->entity_manager->getRepository(Player::class);

        $playerToHang = $playerRepository->findOneBy(['name' => $player_name, 'game_session' => $this->session]);
        if(!$playerToHang) {
            throw new \Error('Player with that name is not connected to the game.');
        }

        $this->entity_manager->beginTransaction();
        try {
            $hang = new Hang($this->player, $playerToHang);
            $this->entity_manager->persist($hang);

            $votesCount = $this->getPlayerOnStool()['hang_count'];

            if($votesCount > $this->session->getPlayers()
                                           ->count() / 2) {
                $this->session->setStage(Stage::On_Stool);
                $this->clearSessionVotesOrHangs(Vote::class);
                $this->entity_manager->persist($this->getGameSession());
            }
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        }
        catch(Exception $e) {
            $this->entity_manager->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     *  todo: remake the code to be something simpler
     *
     *  todo 2: $allowedRoles = $this->game_session->getAvailableRoles();
     *
     *  todo 3: could add randomness
     *
     * @throws Exception
     */
    private function assignRoles(): static
    {
        $players = $this->session->getPlayers();
        $playerCount = $players->count();

        $numberOfEvils = ceil($playerCount * ConflictSide::Evil->ratio()) ?? 1;
        $numberOfNeutrals = ceil($playerCount * ConflictSide::Neutral->ratio());
        $numberOfGoods = $playerCount - $numberOfNeutrals - $numberOfEvils;

        if($numberOfGoods < $playerCount / 2) {
            throw new Exception('Incorrect conflict side ratio set. There must be a minimum of 50% of good roles.');
        }

        $role_repository = $this->entity_manager->getRepository(Role::class);

        $playerSaveDebug = null;
        /** @var class-string<RoleInterface> $role_domain */
        $assign = function(string $role_domain) use ($players, $role_repository, $playerSaveDebug) {
            /** @var Player $player */
            $player = $players->current();
            $players->next();

            $role_entity = $role_repository->findOneBy(["name" => $role_domain::getName()]);

            $player->setRole($role_entity);
            return $player;
        };

        for($i = $numberOfGoods; $i > 0; $i--) {
            $playerSaveDebug[] = $assign(Citizen::class);
            if($numberOfEvils > 0) {
                $playerSaveDebug[] = $assign(Mafioso::class);
                $numberOfEvils--;
            }
            if($numberOfNeutrals > 0) {
                $playerSaveDebug[] = $assign(Jester::class);
                $numberOfNeutrals--;
            }
        }
//        Utils::dump(array_map(function(Player $r){ return $r->getRole()->getName();}, $playerSaveDebug));
        return $this;
    }
}