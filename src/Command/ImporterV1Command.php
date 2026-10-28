<?php

namespace App\Command;

use App\Entity\Commentaire;
use App\Entity\Seance;
use App\Entity\User;
use App\Entity\Wod;
use App\Enum\Difficulte;
use App\Enum\Mode;
use App\Enum\WodType;
use App\Generator\WodGenerator;
use App\Repository\UserRepository;
use App\Service\Points;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Reprend les données de la base Genwod v1 (projet DWWM) : comptes (mots de passe conservés),
 * exercices (devenus des WODs « libres »), historiques (devenus des séances) et commentaires.
 *
 *   php bin/console app:import-v1 "mysql://user:pass@127.0.0.1:3306/gen_wod" --simulation
 */
#[AsCommand(name: 'app:import-v1', description: 'Importe les données de la base Genwod v1.')]
final class ImporterV1Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $users,
        private readonly Points $points,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('URL de connexion à la base v1 (format DATABASE_URL)')] string $source,
        #[Option('Affiche ce qui serait importé sans rien écrire')] bool $simulation = false,
    ): int {
        $dejaImporte = $this->em->getRepository(Wod::class)->createQueryBuilder('w')
            ->select('COUNT(w.id)')->andWhere('w.graine LIKE :prefixe')->setParameter('prefixe', 'v1-%')
            ->getQuery()->getSingleScalarResult();
        if ($dejaImporte > 0) {
            $io->error('Les données v1 ont déjà été importées dans cette base.');

            return Command::FAILURE;
        }

        $v1 = DriverManager::getConnection((new DsnParser(['mysql' => 'pdo_mysql', 'postgresql' => 'pdo_pgsql', 'sqlite' => 'pdo_sqlite']))->parse($source));

        $users = $this->importerComptes($v1, $io);
        $wods = $this->importerExercices($v1);
        $seances = $this->importerHistoriques($v1, $users, $wods);
        $commentaires = $this->importerCommentaires($v1, $users, $wods);

        $io->table(['Élément', 'Importés'], [
            ['Comptes', \count(array_filter($users, static fn (User $u) => null === $u->getId()))],
            ['WODs (anciens exercices)', \count($wods)],
            ['Séances (anciens historiques)', $seances],
            ['Commentaires', $commentaires],
        ]);

        if ($simulation) {
            $io->note('Simulation : rien n\'a été enregistré.');

            return Command::SUCCESS;
        }

        $this->em->flush();
        $io->success('Import terminé. Les anciens mots de passe restent valables (re-hachés automatiquement à la prochaine connexion).');

        return Command::SUCCESS;
    }

    /** @return array<int, User> anciens id → comptes (existants ou nouveaux) */
    private function importerComptes(Connection $v1, SymfonyStyle $io): array
    {
        $comptes = [];
        $pseudos = [];
        foreach ($v1->fetchAllAssociative('SELECT * FROM user') as $ligne) {
            $email = mb_strtolower((string) $ligne['email']);
            $existant = $this->users->findOneBy(['email' => $email]);
            if (null !== $existant) {
                $io->comment(\sprintf('%s existe déjà : conservé tel quel.', $email));
                $comptes[(int) $ligne['id']] = $existant;
                continue;
            }

            // Pseudo dérivé du prénom + initiale du nom, rendu unique si besoin.
            $base = $this->slugger->slug(trim(($ligne['prenom'] ?? '').' '.mb_substr((string) ($ligne['nom'] ?? ''), 0, 1)), '')->toString();
            $pseudo = mb_substr('' !== $base ? $base : 'Athlete'.$ligne['id'], 0, 26);
            while (isset($pseudos[mb_strtolower($pseudo)]) || null !== $this->users->findOneBy(['pseudo' => $pseudo])) {
                $pseudo = mb_substr($pseudo, 0, 24).random_int(10, 99);
            }
            $pseudos[mb_strtolower($pseudo)] = true;

            $roles = json_decode((string) $ligne['roles'], true);
            $user = (new User())
                ->setEmail($email)
                ->setPseudo($pseudo)
                ->setPassword((string) $ligne['password'])
                ->setRoles(\in_array('ROLE_ADMIN', \is_array($roles) ? $roles : [], true) ? ['ROLE_ADMIN'] : [])
                ->setIsVerified((bool) ($ligne['is_verified'] ?? false))
                ->setTaille(isset($ligne['taille']) ? max(100, min(230, (int) $ligne['taille'])) : null)
                ->setPoids(isset($ligne['poids']) ? max(30, min(250, (int) $ligne['poids'])) : null)
                ->setCreatedAt(new \DateTimeImmutable((string) $ligne['created_at']));
            $this->em->persist($user);
            $comptes[(int) $ligne['id']] = $user;
        }

        return $comptes;
    }

    /** @return array<int, Wod> */
    private function importerExercices(Connection $v1): array
    {
        $wods = [];
        foreach ($v1->fetchAllAssociative('SELECT * FROM exercice') as $ligne) {
            $type = match (mb_strtoupper(trim((string) $ligne['type']))) {
                // La v2 n'a plus de Tabata : comme un AMRAP, il se score en répétitions.
                'AMRAP', 'TABATA' => WodType::Amrap,
                'EMOM' => WodType::Emom,
                default => WodType::ForTime,
            };
            $difficulte = match (mb_strtolower(trim((string) $ligne['difficulte']))) {
                'facile' => Difficulte::Facile,
                'difficile' => Difficulte::Difficile,
                default => Difficulte::Moyen,
            };

            $creeLe = null !== $ligne['created_at'] ? new \DateTimeImmutable((string) $ligne['created_at']) : null;
            // Titré par sa date, comme un WOD généré : les noms propres restent réservés aux benchmarks.
            $wod = (new Wod())
                ->setTitre(null !== $creeLe ? WodGenerator::titrePour($creeLe) : \sprintf('WOD n°%d de la v1', $ligne['id']))
                ->setGraine('v1-'.$ligne['id'])
                ->setType($type)
                ->setDifficulte($difficulte)
                ->setDuree(max(1, min(90, (int) ($ligne['duree'] ?? 15) ?: 15)))
                ->setConsignes(trim((string) ($ligne['description'] ?? '')) ?: null)
                // Pas un benchmark reconnu : publié comme WOD communautaire, hors sélection officielle.
                ->setOfficiel(false)
                ->setCreatedAt($creeLe ?? new \DateTimeImmutable());
            $this->em->persist($wod);
            $wods[(int) $ligne['id']] = $wod;
        }

        return $wods;
    }

    /**
     * @param array<int, User> $users
     * @param array<int, Wod>  $wods
     */
    private function importerHistoriques(Connection $v1, array $users, array $wods): int
    {
        $nb = 0;
        foreach ($v1->fetchAllAssociative('SELECT * FROM historique_exercice') as $ligne) {
            $user = $users[(int) $ligne['user_id_id']] ?? null;
            $wod = $wods[(int) $ligne['exercice_id_id']] ?? null;
            if (null === $user || null === $wod) {
                continue;
            }

            // En v1, la durée était stockée dans un DATETIME (seule l'heure compte).
            $temps = new \DateTimeImmutable((string) $ligne['temps']);
            $secondes = (int) $temps->format('G') * 3600 + (int) $temps->format('i') * 60 + (int) $temps->format('s');

            // La v1 ne datait pas les séances : on prend la date la plus récente connue (compte ou exercice).
            $seance = (new Seance())
                ->setRealiseeLe(max($user->getCreatedAt(), $wod->getCreatedAt())->setTime(0, 0))
                ->setAthlete($user)
                ->setWod($wod)
                ->setMode('SCALED' === mb_strtoupper((string) $ligne['mode']) ? Mode::Scaled : Mode::Rx)
                ->setTempsSecondes($secondes > 0 ? $secondes : null)
                ->setTours(null !== $ligne['nombre_tours'] ? (int) $ligne['nombre_tours'] : null)
                ->setReps(null !== $ligne['nombre_repetition'] ? (int) $ligne['nombre_repetition'] : null)
                ->setNotes($ligne['commentaire'] ?? null);
            $seance->setPoints($this->points->calculer($seance, false));
            $this->em->persist($seance);
            ++$nb;
        }

        return $nb;
    }

    /**
     * @param array<int, User> $users
     * @param array<int, Wod>  $wods
     */
    private function importerCommentaires(Connection $v1, array $users, array $wods): int
    {
        $nb = 0;
        foreach ($v1->fetchAllAssociative('SELECT * FROM commentaire') as $ligne) {
            $user = $users[(int) $ligne['user_id_id']] ?? null;
            $wod = $wods[(int) $ligne['exercice_id_id']] ?? null;
            if (null === $user || null === $wod || '' === trim((string) $ligne['commentaire'])) {
                continue;
            }
            $this->em->persist((new Commentaire())
                ->setAuteur($user)
                ->setWod($wod)
                ->setContenu(mb_substr((string) $ligne['commentaire'], 0, 1000))
                ->setCreatedAt(new \DateTimeImmutable((string) $ligne['created_at'])));
            ++$nb;
        }

        return $nb;
    }
}
