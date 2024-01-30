<?php

namespace App\Repository;

use App\Entity\LikeDislike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LikeDislike>
 *
 * @method LikeDislike|null find($id, $lockMode = null, $lockVersion = null)
 * @method LikeDislike|null findOneBy(array $criteria, array $orderBy = null)
 * @method LikeDislike[]    findAll()
 * @method LikeDislike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikeDislikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LikeDislike::class);
    }

//    /**
//     * @return LikeDislike[] Returns an array of LikeDislike objects
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

//    public function findOneBySomeField($value): ?LikeDislike
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
