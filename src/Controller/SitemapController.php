<?php

namespace App\Controller;

use App\Repository\WodRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', format: 'xml', methods: ['GET'])]
    public function __invoke(WodRepository $wods): Response
    {
        $response = $this->render('sitemap.xml.twig', [
            'wods' => $wods->findBy(['public' => true], ['createdAt' => 'DESC'], 1000),
        ]);
        $response->headers->set('Content-Type', 'application/xml; charset=utf-8');
        $response->setPublic()->setMaxAge(3600);

        return $response;
    }
}
