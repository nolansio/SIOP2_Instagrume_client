<?php

namespace App\Controller;

use App\Service\ApiLinker;
use App\Service\SessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private ApiLinker $apiLinker;
    private SessionManager $sessionManager;

    public function __construct(ApiLinker $apiLinker, SessionManager $sessionManager)
    {
        $this->apiLinker = $apiLinker;
        $this->sessionManager = $sessionManager;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Récupérer une sélection aléatoire de publications depuis l'API
        try {
            $response = $this->apiLinker->getData('/publications', null);
            $publications = json_decode($response, true);

            // Mélanger et limiter à 12 publications
            shuffle($publications);
            $publications = array_slice($publications, 0, 12);
        } catch (\Exception $e) {
            // En cas d'erreur, tableau vide
            $publications = [];
        }

        return $this->render('home/index.html.twig', [
            'publications' => $publications,
        ]);
    }

    #[Route('/publications', name: 'app_publications')]
    public function publications(): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->sessionManager->hasToken()) {
            $this->addFlash('warning', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        try {
            $token = $this->sessionManager->getToken();
            $response = $this->apiLinker->getData('/publications', $token);
            $publications = json_decode($response, true);
        } catch (\Exception $e) {
            $publications = [];
        }

        return $this->render('publications/index.html.twig', [
            'publications' => $publications,
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }
}
