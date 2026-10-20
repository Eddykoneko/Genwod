<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Seance;
use App\Entity\User;
use App\Entity\Wod;
use App\Enum\Mode;
use App\Form\CommentaireType;
use App\Form\SeanceType;
use App\Repository\SeanceRepository;
use App\Security\Voter\CommentaireVoter;
use App\Security\Voter\WodVoter;
use App\Service\EnregistreurSeance;
use App\Service\Interactions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/wods')]
final class WodController extends AbstractController
{
    public function __construct(
        private readonly SeanceRepository $seances,
        private readonly Interactions $interactions,
    ) {
    }

    /** La bibliothèque : la recherche est gérée par le Live Component « WodRecherche ». */
    #[Route('', name: 'app_wod_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('wod/index.html.twig');
    }

    #[Route('/{slug}', name: 'app_wod_show', methods: ['GET'])]
    #[IsGranted(WodVoter::VOIR, subject: 'wod')]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])] Wod $wod,
        #[MapQueryParameter] ?string $classement = null,
    ): Response {
        return $this->renderShow($wod, Mode::tryFrom((string) $classement));
    }

    #[Route('/{slug}/seance', name: 'app_wod_seance', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted(WodVoter::VOIR, subject: 'wod')]
    public function seance(
        #[MapEntity(mapping: ['slug' => 'slug'])] Wod $wod,
        #[CurrentUser] User $user,
        Request $request,
        EnregistreurSeance $enregistreur,
    ): Response {
        $seance = (new Seance())->setWod($wod);
        $form = $this->createForm(SeanceType::class, $seance, ['wod_type' => $wod->getType()]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->renderShow($wod, null, seanceForm: $form, status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $record = $enregistreur->enregistrer($seance, $user, $wod);
        $this->addFlash('success', $record
            ? \sprintf('Nouveau record personnel : %s ! +%d points', $seance->getScoreLisible(), $seance->getPoints())
            : \sprintf('Séance enregistrée : %s. +%d points', $seance->getScoreLisible(), $seance->getPoints()));

        return $this->redirectToRoute('app_wod_show', ['slug' => $wod->getSlug(), '_fragment' => 'classement'], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{slug}/commentaires', name: 'app_wod_commenter', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted(WodVoter::VOIR, subject: 'wod')]
    public function commenter(
        #[MapEntity(mapping: ['slug' => 'slug'])] Wod $wod,
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $commentaire = (new Commentaire())->setWod($wod)->setAuteur($user);
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->renderShow($wod, null, commentaireForm: $form, status: Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($commentaire);
        $em->flush();

        // Avec Turbo, on insère le commentaire et on vide le formulaire sans recharger la page.
        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('wod/_commentaire.stream.html.twig', [
                'commentaire' => $commentaire,
                'wod' => $wod,
                'form' => $this->createForm(CommentaireType::class, new Commentaire(), [
                    'action' => $this->generateUrl('app_wod_commenter', ['slug' => $wod->getSlug()]),
                ]),
            ]);
        }

        return $this->redirectToRoute('app_wod_show', ['slug' => $wod->getSlug(), '_fragment' => 'commentaires'], Response::HTTP_SEE_OTHER);
    }

    #[Route('/commentaires/{id}/supprimer', name: 'app_commentaire_supprimer', methods: ['POST'])]
    #[IsGranted(CommentaireVoter::SUPPRIMER, subject: 'commentaire')]
    public function supprimerCommentaire(Commentaire $commentaire, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('supprimer-commentaire-'.$commentaire->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $id = $commentaire->getId();
        $slug = $commentaire->getWod()?->getSlug();
        $em->remove($commentaire);
        $em->flush();

        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return new Response(\sprintf('<turbo-stream action="remove" target="commentaire-%d"></turbo-stream>', $id));
        }

        return $this->redirectToRoute('app_wod_show', ['slug' => $slug, '_fragment' => 'commentaires'], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{slug}/like', name: 'app_wod_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsCsrfTokenValid(new Expression('"interaction-" ~ args["wod"].getId()'))]
    public function like(#[MapEntity(mapping: ['slug' => 'slug'])] Wod $wod, #[CurrentUser] User $user, Request $request): Response
    {
        $this->interactions->basculerLike($user, $wod);

        return $this->repondreInteraction($wod, $user, $request);
    }

    #[Route('/{slug}/favori', name: 'app_wod_favori', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsCsrfTokenValid(new Expression('"interaction-" ~ args["wod"].getId()'))]
    public function favori(#[MapEntity(mapping: ['slug' => 'slug'])] Wod $wod, #[CurrentUser] User $user, Request $request): Response
    {
        $etat = $this->interactions->basculerFavori($user, $wod);
        if (TurboBundle::STREAM_FORMAT !== $request->getPreferredFormat()) {
            $this->addFlash('success', $etat ? 'Ajouté à vos favoris.' : 'Retiré de vos favoris.');
        }

        return $this->repondreInteraction($wod, $user, $request);
    }

    #[Route('/{slug}/supprimer', name: 'app_wod_supprimer', methods: ['POST'])]
    #[IsGranted(WodVoter::GERER, subject: 'wod')]
    #[IsCsrfTokenValid(new Expression('"supprimer-wod-" ~ args["wod"].getId()'))]
    public function supprimer(#[MapEntity(mapping: ['slug' => 'slug'])] Wod $wod, EntityManagerInterface $em): Response
    {
        $em->remove($wod);
        $em->flush();
        $this->addFlash('success', 'WOD supprimé.');

        return $this->redirectToRoute('app_espace_wods', [], Response::HTTP_SEE_OTHER);
    }

    private function repondreInteraction(Wod $wod, User $user, Request $request): Response
    {
        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('wod/_interactions.stream.html.twig', [
                'wod' => $wod,
                'aime' => $this->interactions->aime($user, $wod),
                'favori' => $this->interactions->estFavori($user, $wod),
            ]);
        }

        return $this->redirectToRoute('app_wod_show', ['slug' => $wod->getSlug()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param FormInterface<Seance>|null      $seanceForm
     * @param FormInterface<Commentaire>|null $commentaireForm
     */
    private function renderShow(Wod $wod, ?Mode $mode, ?FormInterface $seanceForm = null, ?FormInterface $commentaireForm = null, int $status = 200): Response
    {
        $user = $this->getUser();
        $connecte = $user instanceof User;

        $seanceForm ??= $this->createForm(SeanceType::class, (new Seance())->setWod($wod), [
            'wod_type' => $wod->getType(),
            'action' => $this->generateUrl('app_wod_seance', ['slug' => $wod->getSlug()]),
        ]);
        $commentaireForm ??= $this->createForm(CommentaireType::class, new Commentaire(), [
            'action' => $this->generateUrl('app_wod_commenter', ['slug' => $wod->getSlug()]),
        ]);

        return $this->render('wod/show.html.twig', [
            'wod' => $wod,
            'classement' => $this->seances->classementWod($wod, $mode),
            'modeClassement' => $mode,
            'record' => $connecte ? $this->seances->meilleurePerformance($user, $wod) : null,
            'aime' => $connecte && $this->interactions->aime($user, $wod),
            'favori' => $connecte && $this->interactions->estFavori($user, $wod),
            'seanceForm' => $seanceForm,
            'commentaireForm' => $commentaireForm,
        ], new Response(status: $status));
    }
}
