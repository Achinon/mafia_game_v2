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
    public function __construct(
      private readonly SessionRepository      $repository,
      private readonly SerializerInterface    $serializer,
      private readonly EntityManagerInterface $entity_manager)
    {
    }

    #[Route('/hang', name: 'player_hang', methods: ['POST'])]
    public function hang(#[Authorise] Player $player,
                         #[JsonParam] string      $player_name,
                         SessionManagerInterface  $session_manager,
                         EntityManagerInterface   $em): Response
    {
        $hang = $session_manager->setPlayer($player)
                                ->hang($player_name);

        $em->persist($hang);
        $em->flush();

        return $this->json(['message' => 'Player disconnected.']);
    }
}
