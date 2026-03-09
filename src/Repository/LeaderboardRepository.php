<?php
namespace App\Repository;

use App\Entity\Leaderboard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LeaderboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Leaderboard::class);
    }

    public function findGlobalLeaderboard()
    {
        return $this->createQueryBuilder('l')
            ->select('u.email, SUM(l.score) as totalScore') // Vérifiez que la propriété "score" existe dans l'entité Leaderboard
            ->join('l.user_id', 'u') // Utilisation correcte de "user_id" comme nom de la propriété dans l'entité Leaderboard
            ->groupBy('u.id')
            ->orderBy('totalScore', 'DESC')
            ->getQuery()
            ->getResult();
    }
}