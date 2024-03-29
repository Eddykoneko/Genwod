<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Leaderboard;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Leaderboard>
 *
 * @method Leaderboard|null find($id, $lockMode = null, $lockVersion = null)
 * @method Leaderboard|null findOneBy(array $criteria, array $orderBy = null)
 * @method Leaderboard[]    findAll()
 * @method Leaderboard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeaderboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Leaderboard::class);
    }

    public function removeUserEntries(User $user): void
{
    $this->createQueryBuilder('l')
        ->delete()
        ->where('l.user = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->execute();
}
//    /**
//     * @return Leaderboard[] Returns an array of Leaderboard objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Leaderboard
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
