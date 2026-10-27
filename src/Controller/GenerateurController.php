<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Difficulte;
use App\Enum\Equipement;
use App\Enum\WodType;
use App\Generator\CriteresGeneration;
use App\Generator\WodGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/generateur')]
final class GenerateurController extends AbstractController
{
    public function __construct(private readonly WodGenerator $generateur)
    {
    }

    #[Route('', name: 'app_generateur', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $query = $request->query->all();

        // Premier passage : on pré-règle la difficulté selon le niveau de l'athlète.
        if (!isset($query['type'])) {
            $user = $this->getUser();
            $criteres = new CriteresGeneration(
                difficulte: $user instanceof User ? ($user->getNiveau()?->difficulteConseillee() ?? Difficulte::Moyen) : Difficulte::Moyen,
            );

            return $this->render('generateur/index.html.twig', ['criteres' => $criteres, 'wod' => null] + $this->choix());
        }

        $criteres = CriteresGeneration::depuisQuery($query);
        // Sans graine, on en tire une et on redirige : l'URL obtenue est partageable.
        if (!isset($query['graine']) || $query['graine'] !== $criteres->graine) {
            return $this->redirectToRoute('app_generateur', $criteres->versQuery());
        }

        return $this->render('generateur/index.html.twig', [
            'criteres' => $criteres,
            'wod' => $this->generateur->generer($criteres),
        ] + $this->choix());
    }

    #[Route('/enregistrer', name: 'app_generateur_enregistrer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsCsrfTokenValid('generateur-enregistrer')]
    public function enregistrer(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        // On régénère côté serveur à partir des critères : impossible d'injecter un WOD arbitraire.
        $criteres = CriteresGeneration::depuisQuery($request->request->all());
        $wod = $this->generateur->generer($criteres)
            ->setAuteur($user)
            ->setPublic($request->request->getBoolean('public', true));

        $titre = trim((string) $request->request->get('titre'));
        if ('' !== $titre) {
            $wod->setTitre(mb_substr($titre, 0, 80));
        }

        $em->persist($wod);
        $em->flush();
        $this->addFlash('success', 'WOD enregistré ! Lancez le chrono quand vous êtes prêt.');

        return $this->redirectToRoute('app_wod_show', ['slug' => $wod->getSlug()]);
    }

    /** @return array<string, mixed> */
    private function choix(): array
    {
        return [
            'types' => WodType::generables(),
            'difficultes' => Difficulte::cases(),
            'equipements' => Equipement::selectionnables(),
        ];
    }
}
