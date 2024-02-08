<?php

namespace App\Repository;

use App\Form\RechercheExerciceType;
use App\Entity\Exercice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exercice>
 *
 * @method Exercice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exercice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exercice[]    findAll()
 * @method Exercice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExerciceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercice::class);
    }

    public function filterExercices($data)
    {
        $queryBuilder = $this->createQueryBuilder('e');
    
        if ($data['type']) {
            $queryBuilder->andWhere('e.type = :type')
                ->setParameter('type', $data['type']);
        }
    
    // Vérification et traitement pour 'type' et 'difficulte'...

        if ($data->duree) {
            $duree = explode('-', $data->duree);
        if (count($duree) == 2) {
            $queryBuilder->andWhere('e.duree >= :minDuree')
                ->andWhere('e.duree <= :maxDuree')
                ->setParameter('minDuree', (int) $duree[0])
                ->setParameter('maxDuree', (int) $duree[1]);
        } else {
            $queryBuilder->andWhere('e.duree >= :minDuree')
                ->setParameter('minDuree', (int) $duree[0]);
            }
        }   
    
        if ($data['difficulte']) {
            $queryBuilder->andWhere('e.difficulte = :difficulte')
                ->setParameter('difficulte', $data['difficulte']);
        }
    
        return $queryBuilder->getQuery()->getResult();
    }

    public function findRandom()
    {
        // Obtenez le nombre total d'enregistrements
        $count = $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();

        // Générez un nombre aléatoire entre 0 et le nombre total d'enregistrements
        $rand = rand(0, $count - 1);

        // Utilisez ce nombre aléatoire pour sauter un certain nombre d'enregistrements et obtenir le suivant
        return $this->createQueryBuilder('e')
            ->setFirstResult($rand)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

//     * @return Exercice[] Returns an array of Exercice objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Exercice
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}