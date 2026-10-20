<?php

namespace App\Controller\Admin;

use App\Repository\CommentaireRepository;
use App\Repository\SeanceRepository;
use App\Repository\UserRepository;
use App\Repository\WodRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly WodRepository $wods,
        private readonly SeanceRepository $seances,
        private readonly CommentaireRepository $commentaires,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'chiffres' => [
                ['Athlètes', $this->users->count([]), 'lucide:users'],
                ['WODs publics', $this->wods->compterPublics(), 'lucide:dumbbell'],
                ['Séances enregistrées', $this->seances->count([]), 'lucide:activity'],
                ['Commentaires', $this->commentaires->count([]), 'lucide:message-circle'],
            ],
            'derniersCommentaires' => $this->commentaires->findBy([], ['createdAt' => 'DESC'], 8),
            'classement' => $this->seances->classementGlobal(null, 5),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/img/logo.png" alt="" width="28" height="28" style="vertical-align:middle;border-radius:6px"> Genwod · Admin')
            ->setFaviconPath('img/logo.png')
            ->setLocales(['fr']);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-gauge');
        yield MenuItem::section('Contenu');
        yield MenuItem::linkTo(WodCrudController::class, 'WODs', 'fa fa-dumbbell');
        yield MenuItem::linkTo(MouvementCrudController::class, 'Mouvements', 'fa fa-list');
        yield MenuItem::section('Communauté');
        yield MenuItem::linkTo(UserCrudController::class, 'Athlètes', 'fa fa-users');
        yield MenuItem::linkTo(SeanceCrudController::class, 'Séances', 'fa fa-stopwatch');
        yield MenuItem::linkTo(CommentaireCrudController::class, 'Commentaires', 'fa fa-comments');
        yield MenuItem::section();
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_accueil');
    }
}
