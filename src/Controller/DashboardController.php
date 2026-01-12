<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request): Response
    {
        $token = $request->getSession()->get('token');
        $currentUser = $request->getSession()->get('user');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que l'utilisateur est MOD ou ADMIN
        $roles = $currentUser['roles'] ?? [];
        if (!in_array('ROLE_MOD', $roles) && !in_array('ROLE_ADMIN', $roles)) {
            $this->addFlash('danger', 'Accès interdit. Vous devez être modérateur ou administrateur.');
            return $this->redirectToRoute('app_home');
        }

        try {
            // Récupérer tous les utilisateurs
            $users = $this->userService->getAllUsers($token);

            return $this->render('dashboard/index.html.twig', [
                'users' => $users
            ]);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
}
