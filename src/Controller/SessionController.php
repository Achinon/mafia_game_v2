<?php

namespace App\Controller;

use App\ArgumentResolver\JsonParam;
use App\Entity\Session;
use App\Entity\Player;
use App\Enumerations\Stage;
use App\Enumerations\VoteType;
use App\Repository\SessionRepository;
use App\Service\SessionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Symfony\Component\Serializer\SerializerInterface;
use App\ArgumentResolver\Authorise;
use App\ArgumentResolver\FetchEntity;
use App\Utils\Utils;

#[Route('/api/session')]
class SessionController extends AbstractController
{
    #[Route('/', name: 'session_create', methods: ['PUT'])]
    public function create(#[JsonParam] string    $player_name,
                           EntityManagerInterface $em): Response
    {
        $session = new Session();
        $player = new Player($session);
        $player->setName($player_name);;

        $em->persist($session);
        $em->persist($player);
        $em->flush();

        return $this->json([
          'player_id' => $player->getPlayerId(),
          'game_session_id' => $player->getGameSession()
                                      ->getGameSessionId()]);
    }

    #[Route('/{session_id}', name: 'session_get', methods: ['GET'])]
    public function get(#[FetchEntity(fetchBy: ["game_session_id" => "session_id"])] Session $session): Response
    {
        $f = function(Player $player) {
            return ['name' => $player->getName(), 'alive' => !$player->isDead()];
        };

        return $this->json([
          'join_code' => $session->getJoinCode(),
          'is_night' => $session->isNight(),
          'stage' => $session->getStage()->name,
          'host' => $f($session->getHost()),
          'day_number' => $session->getDayCount(),
          'players' => array_map($f, $session->getPlayers()->toArray())
        ]);
    }

    #[Route('/join/{join_code}', name: 'session_join', methods: ['PUT'])]
    public function join(SessionManagerInterface $session_manager,
                         #[JsonParam] string     $player_name,
                         string                  $join_code): Response
    {
        $player = $session_manager->setGameSessionByJoinCode($join_code)
                                  ->newPlayer($player_name)
                                  ->getPlayer();

        return $this->json([
          'player_id' => $player->getPlayerId(),
          'game_session_id' => $player->getGameSession()
                                      ->getGameSessionId()]);
    }

    #[Route('/vote/{vote_type}', name: 'session_vote', requirements: ['vote_type' => new EnumRequirement(VoteType::class)], methods: ['POST'])]
    public function vote(#[Authorise] Player     $player,
                         SessionManagerInterface $session_manager,
                         VoteType                $vote_type): Response
    {
        $session_manager->setPlayer($player)
                        ->vote($vote_type);

        return $this->json(['message' => sprintf("Voted on %s.", $vote_type->name)]);
    }

    #[Route('/start', name: 'session_start', methods: ['POST'])]
    public function start(#[Authorise] Player     $player,
                          SessionManagerInterface $session_manager): Response
    {
        $session_manager->setPlayer($player)
                        ->startGame();

        return $this->json(['message' => 'The game was started.']);
    }
}
