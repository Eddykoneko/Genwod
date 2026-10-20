<?php

namespace App\Repository;

use App\Entity\Seance;
use App\Entity\User;
use App\Entity\Wod;
use App\Enum\Mode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seance>
 */
class SeanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seance::class);
    }

    /**
     * Classement d'un WOD : le meilleur résultat de chaque athlète, RX avant Scaled.
     *
     * @return list<Seance>
     */
    public function classementWod(Wod $wod, ?Mode $mode = null, int $limite = 20): array
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.athlete', 'a')->addSelect('a')
            ->andWhere('s.wod = :wod')->setParameter('wod', $wod);
        if (null !== $mode) {
            $qb->andWhere('s.mode = :mode')->setParameter('mode', $mode);
        }

        /** @var list<Seance> $seances */
        $seances = $qb->getQuery()->getResult();
        $auTemps = $wod->getType()->scoreAuTemps();

        usort($seances, static function (Seance $a, Seance $b) use ($auTemps): int {
            // RX passe toujours devant Scaled.
            $mode = (Mode::Scaled === $a->getMode()) <=> (Mode::Scaled === $b->getMode());
            if (0 !== $mode) {
                return $mode;
            }
            $score = $auTemps
                ? $a->getValeurScore() <=> $b->getValeurScore()
                : $b->getValeurScore() <=> $a->getValeurScore();

            return 0 !== $score ? $score : $a->getRealiseeLe() <=> $b->getRealiseeLe();
        });

        $meilleurs = [];
        foreach ($seances as $seance) {
            $id = $seance->getAthlete()?->getId();
            if (null !== $id) {
                $meilleurs[$id] ??= $seance;
            }
        }

        return \array_slice(array_values($meilleurs), 0, $limite);
    }

    /**
     * Classement global par points, éventuellement depuis une date.
     *
     * @return list<array{id: int, pseudo: string, teinte: int<0, 359>, points: int, seances: int}>
     */
    public function classementGlobal(?\DateTimeImmutable $depuis = null, int $limite = 50): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('a.id, a.pseudo, SUM(s.points) AS points, COUNT(s.id) AS seances')
            ->join('s.athlete', 'a')
            ->groupBy('a.id, a.pseudo')
            ->orderBy('points', 'DESC')
            ->addOrderBy('seances', 'DESC')
            ->setMaxResults($limite);
        if (null !== $depuis) {
            $qb->andWhere('s.realiseeLe >= :depuis')->setParameter('depuis', $depuis);
        }

        return array_values(array_map(static fn (array $l) => [
            'id' => (int) $l['id'],
            'pseudo' => (string) $l['pseudo'],
            'teinte' => abs(crc32((string) $l['pseudo'])) % 360,
            'points' => (int) $l['points'],
            'seances' => (int) $l['seances'],
        ], $qb->getQuery()->getArrayResult()));
    }

    /**
     * Nombre de séances par jour (format Y-m-d) depuis une date, pour la heatmap.
     *
     * @return array<string, int>
     */
    public function activiteParJour(User $athlete, \DateTimeImmutable $depuis): array
    {
        $lignes = $this->createQueryBuilder('s')
            ->select('s.realiseeLe AS jour, COUNT(s.id) AS nb')
            ->andWhere('s.athlete = :athlete')->setParameter('athlete', $athlete)
            ->andWhere('s.realiseeLe >= :depuis')->setParameter('depuis', $depuis)
            ->groupBy('s.realiseeLe')
            ->getQuery()
            ->getArrayResult();

        $activite = [];
        foreach ($lignes as $ligne) {
            $jour = $ligne['jour'] instanceof \DateTimeInterface ? $ligne['jour']->format('Y-m-d') : substr((string) $ligne['jour'], 0, 10);
            $activite[$jour] = (int) $ligne['nb'];
        }

        return $activite;
    }

    /**
     * Toutes les dates d'entraînement distinctes, de la plus récente à la plus ancienne.
     *
     * @return list<string>
     */
    public function datesEntrainement(User $athlete): array
    {
        $dates = $this->createQueryBuilder('s')
            ->select('DISTINCT s.realiseeLe AS jour')
            ->andWhere('s.athlete = :athlete')->setParameter('athlete', $athlete)
            ->orderBy('jour', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        return array_values(array_map(
            static fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : substr((string) $d, 0, 10),
            $dates,
        ));
    }

    /** @return array{seances: int, points: int, rx: int} */
    public function totaux(User $athlete, ?\DateTimeImmutable $depuis = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) AS seances, COALESCE(SUM(s.points), 0) AS points')
            ->addSelect('SUM(CASE WHEN s.mode = :rx THEN 1 ELSE 0 END) AS rx')
            ->setParameter('rx', Mode::Rx)
            ->andWhere('s.athlete = :athlete')->setParameter('athlete', $athlete);
        if (null !== $depuis) {
            $qb->andWhere('s.realiseeLe >= :depuis')->setParameter('depuis', $depuis);
        }
        $ligne = $qb->getQuery()->getSingleResult();

        return ['seances' => (int) $ligne['seances'], 'points' => (int) $ligne['points'], 'rx' => (int) $ligne['rx']];
    }

    /** @return list<Seance> */
    public function dernieres(User $athlete, int $limite = 5): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.wod', 'w')->addSelect('w')
            ->andWhere('s.athlete = :athlete')->setParameter('athlete', $athlete)
            ->orderBy('s.realiseeLe', 'DESC')
            ->addOrderBy('s.id', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    /**
     * Meilleure performance de l'athlète sur chaque WOD réalisé.
     *
     * @return list<Seance>
     */
    public function recordsPersonnels(User $athlete): array
    {
        /** @var list<Seance> $seances */
        $seances = $this->createQueryBuilder('s')
            ->join('s.wod', 'w')->addSelect('w')
            ->andWhere('s.athlete = :athlete')->setParameter('athlete', $athlete)
            ->getQuery()
            ->getResult();

        $records = [];
        foreach ($seances as $seance) {
            $id = $seance->getWod()?->getId();
            if (null === $id) {
                continue;
            }
            $actuel = $records[$id] ?? null;
            if (null === $actuel || $this->estMeilleure($seance, $actuel)) {
                $records[$id] = $seance;
            }
        }
        usort($records, static fn (Seance $a, Seance $b) => $b->getRealiseeLe() <=> $a->getRealiseeLe());

        return $records;
    }

    public function meilleurePerformance(User $athlete, Wod $wod): ?Seance
    {
        $meilleure = null;
        foreach ($this->findBy(['athlete' => $athlete, 'wod' => $wod]) as $seance) {
            if (null === $meilleure || $this->estMeilleure($seance, $meilleure)) {
                $meilleure = $seance;
            }
        }

        return $meilleure;
    }

    public function estMeilleure(Seance $candidate, Seance $reference): bool
    {
        if ($candidate->getMode() !== $reference->getMode()) {
            return Mode::Rx === $candidate->getMode();
        }

        return $candidate->getWod()?->getType()->scoreAuTemps()
            ? $candidate->getValeurScore() < $reference->getValeurScore()
            : $candidate->getValeurScore() > $reference->getValeurScore();
    }

    public function compterAthletesActifs(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(DISTINCT s.athlete)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
