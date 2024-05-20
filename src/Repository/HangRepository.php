<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Session;
use App\Enumerations\VoteType;
use App\Entity\Player;
use App\Entity\Hang;

/**
 * @extends ServiceEntityRepository<Hang>
 */
class HangRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hang::class);
    }

    public function clearSessionHangs(Session $session)
    {
        return $this->createQueryBuilder('h')
                    ->delete()
                    ->leftJoin('h.player_to_hang', 'p')
                    ->where('p.game_session = :gs')
                    ->setParameter('gs', $session)
                    ->getQuery()
                    ->execute();
    }

    public function hasPlayerAlreadyVoted(Player $player, VoteType $vote_type)
    {
        return $this->createQueryBuilder('v')
                    ->select('COUNT(v.id)')
                    ->leftJoin('v.player', 'p')
                    ->where('v.player = :player')
                    ->andWhere('v.vote_type = :vote_type')
                    ->setParameter('vote_type', $vote_type)
                    ->setParameter('player', $player)
                    ->getQuery()
                    ->getSingleScalarResult() > 0;
    }
}
