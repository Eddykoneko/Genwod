<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Wod;
use App\Model\Page;
use App\Model\WodFiltre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wod>
 */
class WodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wod::class);
    }

    /** @return Page<Wod> */
    public function rechercher(WodFiltre $filtre, int $parPage = 12): Page
    {
        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.auteur', 'a')->addSelect('a')
            ->andWhere('w.public = true');

        if (null !== $filtre->q && '' !== trim($filtre->q)) {
            // Recherche dans le titre, les consignes et le nom des mouvements.
            $qb->andWhere($qb->expr()->orX(
                'LOWER(w.titre) LIKE :q',
                'LOWER(w.consignes) LIKE :q',
                'EXISTS (SELECT 1 FROM App\Entity\WodMouvement l JOIN l.mouvement m WHERE l.wod = w AND LOWER(m.nom) LIKE :q)',
            ))->setParameter('q', '%'.mb_strtolower(trim($filtre->q)).'%');
        }
        if (null !== $filtre->type) {
            $qb->andWhere('w.type = :type')->setParameter('type', $filtre->type);
        }
        if (null !== $filtre->difficulte) {
            $qb->andWhere('w.difficulte = :difficulte')->setParameter('difficulte', $filtre->difficulte);
        }
        if (null !== $filtre->dureeMax) {
            $qb->andWhere('w.duree <= :dureeMax')->setParameter('dureeMax', $filtre->dureeMax);
        }

        match ($filtre->tri) {
            'populaires' => $qb->orderBy('w.likesCount', 'DESC')->addOrderBy('w.createdAt', 'DESC'),
            'courts' => $qb->orderBy('w.duree', 'ASC')->addOrderBy('w.createdAt', 'DESC'),
            default => $qb->orderBy('w.createdAt', 'DESC'),
        };
        $qb->addOrderBy('w.id', 'DESC');

        $page = new Page($qb, $filtre->page, $parPage);
        $this->prechargerLignes($page);

        return $page;
    }

    /**
     * Le WOD du jour : choisi de façon déterministe parmi les WODs officiels,
     * pour que tout le monde voie le même WOD à la même date.
     */
    public function wodDuJour(\DateTimeImmutable $date): ?Wod
    {
        $ids = $this->createQueryBuilder('w')
            ->select('w.id')
            ->andWhere('w.officiel = true')
            ->andWhere('w.public = true')
            ->orderBy('w.id', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        if ([] === $ids) {
            return null;
        }

        // Mélange stable : hash de la date → index.
        $index = hexdec(substr(hash('xxh3', $date->format('Y-m-d')), 0, 7)) % \count($ids);

        return $this->find($ids[$index]);
    }

    /** @return list<Wod> */
    public function populaires(int $limite = 6): array
    {
        /** @var list<Wod> $wods */
        $wods = $this->createQueryBuilder('w')
            ->leftJoin('w.auteur', 'a')->addSelect('a')
            ->andWhere('w.public = true')
            ->orderBy('w.likesCount', 'DESC')
            ->addOrderBy('w.createdAt', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
        $this->prechargerLignes($wods);

        return $wods;
    }

    /**
     * Charge en une seule requête les lignes et mouvements d'une liste de WODs
     * (évite le problème N+1 sur les cartes, sans casser la pagination).
     *
     * @param iterable<Wod> $wods
     */
    public function prechargerLignes(iterable $wods): void
    {
        $ids = [];
        foreach ($wods as $wod) {
            $ids[] = $wod->getId();
        }
        if ([] !== $ids) {
            $this->createQueryBuilder('w')
                ->select('w', 'l', 'm')
                ->leftJoin('w.lignes', 'l')
                ->leftJoin('l.mouvement', 'm')
                ->andWhere('w.id IN (:ids)')->setParameter('ids', $ids)
                ->getQuery()
                ->getResult();
        }
    }

    /** @return list<Wod> */
    public function crees(User $auteur): array
    {
        /** @var list<Wod> $wods */
        $wods = $this->createQueryBuilder('w')
            ->andWhere('w.auteur = :auteur')->setParameter('auteur', $auteur)
            ->orderBy('w.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        $this->prechargerLignes($wods);

        return $wods;
    }

    public function compterPublics(): int
    {
        return $this->count(['public' => true]);
    }
}
