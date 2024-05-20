<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Session;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function nameExistsInLobby(string $getName, string $getGameSessionId)
    {
    }

    public function nameDuplicateNumber(Session $session, string $player_name): string
    {
        $qb = $this->createQueryBuilder('p')
                   ->select('p.name')
                   ->where('p.game_session = :ses_id')
                   ->andWhere('p.name LIKE :name')
                   ->setParameter('ses_id', $session)
                   ->setParameter('name', $player_name . '%')
                   ->orderBy('p.name', 'DESC')
                   ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result) {
            $existingName = $result['name'];
            if ($existingName === $player_name) {
                return '1';
            } else if (preg_match('/^' . preg_quote($player_name, '/') . '(\d+)$/', $existingName, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }
}
