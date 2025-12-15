<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\PublicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    private UserService $userService;
    private PublicationService $publicationService;

    public function __construct(UserService $userService, PublicationService $publicationService)
    {
        $this->userService = $userService;
        $this->publicationService = $publicationService;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request): Response
    {
        $token = $request->getSession()->get('token');
        $currentUser = $request->getSession()->get('user');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur est mod ou admin
        if (!in_array('ROLE_MOD', $currentUser['roles']) && !in_array('ROLE_ADMIN', $currentUser['roles'])) {
            $this->addFlash('danger', 'Accès refusé. Vous devez être modérateur ou administrateur.');
            return $this->redirectToRoute('app_home');
        }

        try {
            // Récupérer tous les utilisateurs et toutes les publications
            $users = $this->userService->getAllUsers($token);
            $publications = $this->publicationService->getAllPublications($token);

            return $this->render('dashboard/index.html.twig', [
                'users' => $users,
                'publications' => $publications,
                'currentUser' => $currentUser
            ]);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
}
