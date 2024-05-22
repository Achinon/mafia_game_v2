<?php

namespace App\Controller;

use App\ArgumentResolver\JsonParam;
use App\Entity\Player;
use App\Repository\SessionRepository;
use App\Service\SessionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\ArgumentResolver\Authorise;

#[Route('/api/player')]
class PlayerController extends AbstractController
{
    #[Route('/hang/{player_name}', name: 'player_hang', methods: ['POST'])]
    public function hang(#[Authorise] Player     $player,
                         string                  $player_name,
                         SessionManagerInterface $session_manager,
                         EntityManagerInterface  $em): Response
    {
        $hang = $session_manager->setPlayer($player)
                                ->hang($player_name);

        $em->persist($hang);
        $em->flush();

        return $this->json(['message' => 'Player voted to hang.']);
    }

    #[Route('/disconnect', name: 'player_disconnect', methods: ['DELETE'])]
    public function disconnect(#[Authorise] Player     $player,
                               SessionManagerInterface $session_manager): Response
    {
        $session_manager->setPlayer($player)
                        ->disconnect();

        return $this->json(['message' => 'Player disconnected.']);
    }
}
