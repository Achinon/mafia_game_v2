<?php

namespace App\Controller;

use App\ArgumentResolver\Body;
use App\Dto\PlayerDto;
use App\Entity\Player;
use App\Entity\Session;
use App\Enumerations\Stage;
use App\Enumerations\VoteType;
use App\Repository\SessionRepository;
use App\Service\SessionManagerInterface;
use App\Service\SessionManagerService;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Symfony\Component\Serializer\SerializerInterface;
use App\ArgumentResolver\Authorise;

#[Route('/api/session')]
class SessionController extends AbstractController
{
    public function __construct(
      private readonly SessionRepository   $repository,
      private readonly SerializerInterface $serializer)
    {
    }

    #[Route('/', name: 'session_create', methods: ['POST'])]
    public function create(#[Body] PlayerDto $playerDto,
                           EntityManagerInterface $em): Response
    {
        $session = new Session();
        $playersJoined = $session->getPlayers();
        $newPlayerName = $playerDto->getName();

        foreach($playersJoined as $player) {
            if($player->getName() === $newPlayerName) {
                $newPlayerName .= "_2";
            }
        }

        $player = new Player($session);
        $player->setName($playerDto->getName());;

        $em->persist($session);
        $em->persist($player);
        $em->flush();

        return $this->json([
          'player_id' => $player->getPlayerId(),
          'game_session_id' => $player->getGameSession()
                                      ->getGameSessionId()]);
    }

    #[Route('/{id}', name: 'session_get', methods: ['GET'])]
    public function get(string $id): Response
    {
        $session = $this->repository->findOneBy(["game_session_id" => $id]);

        if(!$session) {
            return $this->json(['message' => 'Session not found.'], 404);
        }

        return $this->json([
          'join_code' => $session->getJoinCode(),
          'is_night' => $session->isNight(),
          'stage' => $session->getStage()->name,
          'host' => $session->getHost()->getName(),
          'day_number' => $session->getDayCount(),
            'players' => array_map(function(Player $player) {
                return $player->getName();
            }, $session->getPlayers()->toArray())
        ]);
    }

    #[Route('/join/{join_code}', name: 'session_join', methods: ['POST'])]
    public function join(SessionManagerInterface $sessionManager,
                         EntityManagerInterface $em,
                         #[Body] PlayerDto $playerDto,
                         string $join_code): Response
    {
        $player = $sessionManager
          ->setGameSessionByJoinCode($join_code)
          ->newPlayer($playerDto->getName());

        $em->persist($player);
        $em->flush();

        return $this->json([
          'player_id' => $player->getPlayerId(),
          'game_session_id' => $player->getGameSession()
                                      ->getGameSessionId()]);
    }

    #[Route('/vote/{vote_type}', name: 'session_vote', requirements: ['vote_type' => new EnumRequirement(VoteType::class)], methods: ['POST'])]
    public function vote(#[Authorise] Player|null $player,
                         SessionManagerInterface $session_manager,
                         EntityManagerInterface $em,
                         VoteType $vote_type): Response
    {
        if(!$player){
            return $this->json(['message' => 'Could not authorise.'], 403);
        }

        $vote = $session_manager->setPlayer($player)
            ->vote($vote_type);

        if($vote){
            $em->persist($vote);
            $em->flush();
            return $this->json(['message' => sprintf("Voted on %s.", $vote_type->name)]);
        }
        return $this->json(['message' => 'Did not vote.'], 400);
    }

    #[Route('/start', name: 'session_start', methods: ['POST'])]
    public function start(#[Authorise] Player|null $player,
                         SessionManagerInterface $session_manager,
                         EntityManagerInterface $em): Response
    {
        if(!$player){
            return $this->json(['message' => 'Could not authorise.'], 403);
        }

        $session = $session_manager->setPlayer($player)
                                   ->verifyIfEligibleToStart()
                                   ->getGameSession();

        if($session->getStage() === Stage::Running){
            $em->persist($session);
            $em->flush();
            return $this->json(['message' => 'The game has started.']);
        }
        return $this->json(['message' => 'The game could not start.'], 400);
    }

    #[Route('/disconnect', name: 'session_disconnect', methods: ['POST'])]
    public function disconnect(#[Authorise] Player|null $player,
                         SessionManagerInterface $session_manager,
                         EntityManagerInterface $em): Response
    {
        if(!$player){
            return $this->json(['message' => 'Could not authorise.'], 403);
        }

        $session = $session_manager->setPlayer($player)
                                   ->disconnect()
                                   ->getGameSession();

        $em->persist($session);
        $em->flush();

        return $this->json(['message' => 'Player disconnected.']);
    }
}
