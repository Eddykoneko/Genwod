<?php

namespace App\DataFixtures;

use App\Entity\Commentaire;
use App\Entity\Seance;
use App\Entity\User;
use App\Entity\Wod;
use App\Enum\Difficulte;
use App\Enum\Equipement;
use App\Enum\Mode;
use App\Enum\Niveau;
use App\Enum\WodType;
use App\Generator\CriteresGeneration;
use App\Generator\WodGenerator;
use App\Service\CatalogueImporter;
use App\Service\Interactions;
use App\Service\Points;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Jeu de données de démonstration : catalogue, un admin, une douzaine d'athlètes,
 * des WODs générés et quatre mois de séances.
 *
 * Comptes : admin@genwod.fr et lea@genwod.fr, mot de passe « genwod-demo-2026 ».
 */
final class AppFixtures extends Fixture
{
    public const MOT_DE_PASSE = 'genwod-demo-2026';

    private const ATHLETES = [
        ['lea', 'Léa_Rx', Niveau::Confirme, 'Coach à Lyon. Team thrusters.'],
        ['max', 'MaxPower', Niveau::Confirme, 'Ancien rugbyman, accro aux chippers.'],
        ['ines', 'InesRows', Niveau::Intermediaire, 'Rameur le matin, barre le soir.'],
        ['tom', 'TomBurpees', Niveau::Intermediaire, null],
        ['sarah', 'Sarah.KB', Niveau::Intermediaire, 'Kettlebell addict.'],
        ['yanis', 'Yanis69', Niveau::Debutant, 'Premier WOD en janvier !'],
        ['chloe', 'ChloeBurpees', Niveau::Debutant, null],
        ['hugo', 'HugoDU', Niveau::Confirme, 'Double unders > tout.'],
        ['emma', 'Emma_Fit', Niveau::Intermediaire, null],
        ['nathan', 'NathanFT', Niveau::Debutant, 'Je reprends le sport.'],
    ];

    private const COMMENTAIRES = [
        'Les burpees à la fin, c\'est criminel 😅',
        'Fait en Scaled avec des pompes sur les genoux, ça passe très bien.',
        'Gros cardio, j\'ai dû fractionner dès le 2e tour.',
        'Parfait pour un midi, 20 minutes chrono !',
        'Je le refais la semaine prochaine pour battre mon temps.',
        'Conseil : partez moins vite que vous ne le pensez.',
        'Version maison avec un sac de sable à la place de la kettlebell : top.',
        'Mon nouveau WOD préféré.',
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly CatalogueImporter $catalogue,
        private readonly WodGenerator $generateur,
        private readonly Interactions $interactions,
        private readonly Points $points,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        mt_srand(2026);
        $this->catalogue->importer();

        $admin = $this->athlete('admin@genwod.fr', 'Coach_Genwod', Niveau::Confirme, 'Compte d\'administration.')
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $athletes = [];
        foreach (self::ATHLETES as [$prenom, $pseudo, $niveau, $bio]) {
            $user = $this->athlete($prenom.'@genwod.fr', $pseudo, $niveau, $bio);
            $user->setCreatedAt(new \DateTimeImmutable(\sprintf('-%d days', mt_rand(60, 200))));
            $manager->persist($user);
            $athletes[] = $user;
        }
        $manager->flush();

        // Des WODs générés par la communauté.
        $types = WodType::generables();
        $materiels = Equipement::selectionnables();
        for ($i = 0; $i < 24; ++$i) {
            shuffle($materiels);
            $criteres = new CriteresGeneration(
                $types[$i % 4],
                [8, 10, 12, 15, 16, 20, 25][mt_rand(0, 6)],
                Difficulte::cases()[mt_rand(0, 2)],
                \array_slice($materiels, 0, mt_rand(0, 4)),
                \sprintf('%08x', mt_rand()),
            );
            $creeLe = new \DateTimeImmutable(\sprintf('-%d days -%d hours', mt_rand(0, 120), mt_rand(0, 23)));
            $wod = $this->generateur->generer($criteres)
                ->setTitre(WodGenerator::titrePour($creeLe))
                ->setAuteur($athletes[mt_rand(0, \count($athletes) - 1)])
                ->setCreatedAt($creeLe);
            $manager->persist($wod);
        }
        $manager->flush();

        /** @var list<Wod> $wods */
        $wods = $manager->getRepository(Wod::class)->findAll();

        // Quatre mois de séances : certains athlètes sont très réguliers, d'autres moins.
        foreach ($athletes as $index => $athlete) {
            $assiduite = [0.65, 0.55, 0.45, 0.4, 0.35, 0.3, 0.25, 0.5, 0.3, 0.2][$index];
            $meilleurs = [];
            for ($jour = 120; $jour >= 0; --$jour) {
                if (mt_rand() / mt_getrandmax() > $assiduite) {
                    continue;
                }
                $wod = $wods[mt_rand(0, \count($wods) - 1)];
                $seance = $this->seanceAleatoire($athlete, $wod, new \DateTimeImmutable(\sprintf('-%d days', $jour)));

                $id = (int) $wod->getId();
                $record = isset($meilleurs[$id]) && $this->meilleure($seance, $meilleurs[$id]);
                if (!isset($meilleurs[$id]) || $record) {
                    $meilleurs[$id] = $seance;
                }
                $seance->setPoints($this->points->calculer($seance, $record));
                $manager->persist($seance);
            }
        }
        $manager->flush();

        // Likes, favoris et commentaires.
        foreach ($athletes as $athlete) {
            foreach ((array) array_rand($wods, mt_rand(3, 9)) as $i) {
                $this->interactions->basculerLike($athlete, $wods[$i]);
            }
            foreach ((array) array_rand($wods, mt_rand(1, 4)) as $i) {
                $this->interactions->basculerFavori($athlete, $wods[$i]);
            }
        }
        for ($i = 0; $i < 40; ++$i) {
            $manager->persist((new Commentaire())
                ->setAuteur($athletes[mt_rand(0, \count($athletes) - 1)])
                ->setWod($wods[mt_rand(0, \count($wods) - 1)])
                ->setContenu(self::COMMENTAIRES[mt_rand(0, \count(self::COMMENTAIRES) - 1)])
                ->setCreatedAt(new \DateTimeImmutable(\sprintf('-%d hours', mt_rand(1, 2000)))));
        }
        $manager->flush();
    }

    private function athlete(string $email, string $pseudo, Niveau $niveau, ?string $bio): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setPseudo($pseudo)
            ->setNiveau($niveau)
            ->setBio($bio)
            ->setIsVerified(true);

        return $user->setPassword($this->hasher->hashPassword($user, self::MOT_DE_PASSE));
    }

    private function seanceAleatoire(User $athlete, Wod $wod, \DateTimeImmutable $date): Seance
    {
        $seance = (new Seance())
            ->setAthlete($athlete)
            ->setWod($wod)
            ->setRealiseeLe($date)
            ->setMode(mt_rand(0, 100) < (Niveau::Debutant === $athlete->getNiveau() ? 25 : 70) ? Mode::Rx : Mode::Scaled)
            ->setRessenti(mt_rand(2, 5));

        $cap = $wod->getDuree() * 60;
        match ($wod->getType()) {
            WodType::ForTime => $seance->setTempsSecondes(mt_rand((int) ($cap * 0.45), $cap)),
            WodType::Amrap => $seance->setTours(mt_rand(max(1, intdiv($wod->getDuree(), 3)), $wod->getDuree()))->setReps(mt_rand(0, 20)),
            WodType::ForLoad => $seance->setChargeKg(mt_rand(80, 240) / 2),
            WodType::DeathBy => $seance->setTours(mt_rand(intdiv($wod->getDuree(), 2), $wod->getDuree() - 1))->setReps(mt_rand(0, 10)),
            default => $seance->setReps(mt_rand(6, 14) * $wod->getDuree()),
        };

        return $seance;
    }

    private function meilleure(Seance $a, Seance $b): bool
    {
        if ($a->getMode() !== $b->getMode()) {
            return Mode::Rx === $a->getMode();
        }

        return $a->getWod()?->getType()->scoreAuTemps()
            ? $a->getValeurScore() < $b->getValeurScore()
            : $a->getValeurScore() > $b->getValeurScore();
    }
}
