<?php

namespace App\Controller;

use App\ArgumentResolver\JsonParam;
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

#[Route('/api/player')]
class PlayerController extends AbstractController
{
    public function __construct(
      private readonly SessionRepository   $repository,
      private readonly SerializerInterface $serializer, private readonly EntityManagerInterface $entity_manager)
    {
    }

    #[Route('/hang', name: 'player_hang', methods: ['POST'])]
    public function hang(#[Authorise] Player|null $player,
                         #[JsonParam] string      $player_name,
                         SessionManagerInterface  $session_manager,
                         EntityManagerInterface   $em): Response
    {
        if(!$player){
            return $this->json(['message' => 'Could not authorise.'], 403);
        }

        $this->entity_manager->beginTransaction();
        try{
            $hang = $session_manager->setPlayer($player)
                                    ->hang($player_name);

            $this->entity_manager->persist($hang);
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        }
        catch(\Error $e) {
            $this->entity_manager->rollback();
            return $this->json(['message' => $e->getMessage()], 400);
        }

        return $this->json(['message' => 'Player disconnected.']);
    }
}
