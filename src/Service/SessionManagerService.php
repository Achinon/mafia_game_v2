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
        if($voteRepository->hasPlayerAlreadyVoted($this->player, $vote_type)) {
            throw new \Error('This player has already voted.');
        }

        $vote = new Vote($this->player, $vote_type);
        $this->entity_manager->persist($vote);
        $this->entity_manager->flush();

        return $this;
    }

    /**
     * @throws Exception
     */
    public function startGame(): static
    {
        $this->entity_manager->beginTransaction();
        try {
            $this->clearSessionVotes();
            $this->session->setStage(Stage::Running);
            $this->assignRoles();
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

    private function clearSessionVotes(): static
    {
        /** @var VoteRepository $vote_repository */
        $vote_repository = $this->entity_manager->getRepository(Vote::class);
        /** @var HangRepository $vote_repository */
        $hang_repository = $this->entity_manager->getRepository(Hang::class);

        $vote_repository->clearSessionVotes($this->session);
        $hang_repository->clearSessionHangs($this->session);

        return $this;
    }

    public function disconnect(): static
    {
        $this->session->removePlayer($this->player);
        $this->entity_manager->persist($this->session);
        $this->entity_manager->flush();
        return $this;
    }

    public function hang(string $player_name): ?Hang
    {
        /** @var VoteRepository $voteRepository */
        $playerRepository = $this->entity_manager->getRepository(Player::class);

        if($this->player->getName() === $player_name) {
            throw new \Error('Cannot hang yourself.');
        }

        $playerToHang = $playerRepository->findOneBy(['name' => $player_name, 'game_session' => $this->session]);
        if(!$playerToHang) {
            throw new \Error('Player with that name is not connected to the game.');
        }
        return new Hang($this->player, $playerToHang);
    }

    /**
     *  todo: remake the code to be something simpler
     *
     *  todo 2: $allowedRoles = $this->game_session->getAvailableRoles();
     *
     *  todo 3: could add randomness
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