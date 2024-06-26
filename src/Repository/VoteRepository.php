<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Session;
use App\Enumerations\VoteType;
use App\Entity\Player;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function getPlayerVoteCountOn(Session $session, VoteType $vote_type)
    {
        return $this->createQueryBuilder('v')
             ->select('distinct COUNT(v.id)')
             ->leftJoin('v.player', 'p')
             ->leftJoin('p.game_session', 'gs')
             ->where('p.game_session = :gs')
             ->andWhere('v.vote_type = :voteType')
             ->setParameter('gs', $session)
             ->setParameter('voteType', $vote_type)
             ->getQuery()
             ->getSingleScalarResult();
    }

    public function clearSessionVotes(Session $session): void
    {
        $qb = $this->createQueryBuilder('v');
        $qb->select('v.id')
           ->leftJoin('v.player', 'p')
           ->where('p.game_session = :gs')
           ->setParameter('gs', $session);

        $ids = $qb->getQuery()->getResult();

        if (!empty($ids)) {
            $qb = $this->createQueryBuilder('v')
                       ->delete()
                       ->where('v.id IN (:ids)')
                       ->setParameter('ids', array_column($ids, 'id'));

            $qb->getQuery()->execute();
        }
    }

    public function hasPlayerAlreadyVoted(Player $player)
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->leftJoin('v.player', 'p')
            ->where('v.player = :player')
            ->setParameter('player', $player)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
