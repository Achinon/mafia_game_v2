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

    public function clearSessionVotes(Session $session): void
    {
        $qb = $this->createQueryBuilder('h');
        $qb->select('h.id')
           ->leftJoin('h.player_voting', 'p')
           ->where('p.game_session = :gs')
           ->setParameter('gs', $session);

        $ids = $qb->getQuery()
                  ->getResult();

        if(!empty($ids)) {
            $qb = $this->createQueryBuilder('h')
                       ->delete()
                       ->where('h.id IN (:ids)')
                       ->setParameter('ids', array_column($ids, 'id'));

            $qb->getQuery()
               ->execute();
        }
    }

    public function hasPlayerAlreadyVoted(Player $player): bool
    {
        return $this->createQueryBuilder('h')
                    ->select('COUNT(h.id)')
                    ->where('h.player_voting = :player')
                    ->setParameter('player', $player)
                    ->getQuery()
                    ->getSingleScalarResult() > 0;
    }

    public function getPlayersToHang(): array
    {
        return $this->createQueryBuilder('h')
                    ->select('COUNT(h.id)')
                    ->groupBy('h.player_to_hang')
                    ->getQuery()
                    ->getArrayResult();
    }

    public function getHangHighestVoted(Session $session): array
    {
        return $this->createQueryBuilder('h')
                    ->select('IDENTITY(h.player_to_hang) AS player_id', 'count(h.id) AS hang_count')
                    ->leftJoin('h.player_to_hang', 'p')
                    ->where('p.game_session = :gs')
                    ->setParameter('gs', $session)
                    ->groupBy('p')
                    ->orderBy('count(h.id)', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
