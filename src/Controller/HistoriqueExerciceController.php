<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Entity\HistoriqueExercice;
use App\Form\HistoriqueExerciceType; // Assurez-vous d'importer la classe de formulaire
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request; // Importez Request
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HistoriqueExerciceController extends AbstractController
{
    #[Route('/enregistrer-exercice/{id}', name: 'enregistrer_exercice')]
    public function enregistrerExercice(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        $historiqueExercice = new HistoriqueExercice();
        $historiqueExercice->setExerciceId($exercice);
        $form = $this->createForm(HistoriqueExerciceType::class, $historiqueExercice);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            
            $historiqueExercice->setUserId($this->getUser());
            $entityManager->persist($historiqueExercice);
            $entityManager->flush();
    
            // Rediriger l'utilisateur vers une page appropriée après l'enregistrement
            return $this->redirectToRoute('app_home');
        }
    
        return $this->render('exercice/save.html.twig', [
            'form' => $form->createView(),
            'exercice' => $exercice,
        ]);
    }

    #[Route('/mon-historique', name: 'app_mon_historique', methods: ['GET'])]
    public function monHistorique(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            // Rediriger vers la page de connexion ou afficher un message
            return $this->redirectToRoute('app_login');
        }

        $historiqueExercices = $user->getHistoriqueExercices(); 

        return $this->render('historique_exercice/index.html.twig', [
            'historiqueExercices' => $historiqueExercices,
        ]);
    }
}
