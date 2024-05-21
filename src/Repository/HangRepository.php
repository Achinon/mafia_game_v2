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

    public function clearSessionHangs(Session $session): void
    {
        $qb = $this->createQueryBuilder('h');
        $qb->select('h.id')
           ->leftJoin('h.player_voting', 'p')
           ->where('p.game_session = :gs')
           ->setParameter('gs', $session);

        $ids = $qb->getQuery()->getResult();

        if (!empty($ids)) {
            $qb = $this->createQueryBuilder('h')
                       ->delete()
                       ->where('h.id IN (:ids)')
                       ->setParameter('ids', array_column($ids, 'id'));

            $qb->getQuery()->execute();
        }
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
