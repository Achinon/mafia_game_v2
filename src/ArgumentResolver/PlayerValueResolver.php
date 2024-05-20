<?php

namespace App\ArgumentResolver;

use App\Utils\Utils;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Error\Error;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PlayerRepository;
use App\Entity\Player;

class PlayerValueResolver implements ValueResolverInterface
{
    public function __construct(private readonly PlayerRepository $player_repository)
    {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($argument)) {
            return [];
        }

        $player_id = $request->headers->get("player_id");
        yield $this->player_repository->findOneBy(['player_id' => $player_id]);
    }

    public function supports(ArgumentMetadata $argument): bool
    {
        return count($argument->getAttributes(Authorise::class)) > 0;
    }
}