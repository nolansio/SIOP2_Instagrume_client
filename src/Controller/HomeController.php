<?php

namespace App\Controller;

use App\Service\PublicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private PublicationService $publicationService;

    public function __construct(PublicationService $publicationService)
    {
        $this->publicationService = $publicationService;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $token = $request->getSession()->get('token');

        try {
            // Récupérer une sélection aléatoire de publications
            $publications = $this->publicationService->getRandomPublications(12, $token);
        } catch (\Exception $e) {
            $publications = [];
            $this->addFlash('danger', 'Erreur lors du chargement des publications.');
        }

        return $this->render('home/index.html.twig', [
            'publications' => $publications
        ]);
    }
}
