<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\PublicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    private UserService $userService;
    private PublicationService $publicationService;

    public function __construct(UserService $userService, PublicationService $publicationService)
    {
        $this->userService = $userService;
        $this->publicationService = $publicationService;
    }

    #[Route('/mon-profil', name: 'app_my_profile')]
    public function myProfile(Request $request): Response
    {
        $token = $request->getSession()->get('token');
        $currentUser = $request->getSession()->get('user');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            // Récupérer les infos de l'utilisateur et ses publications
            $user = $this->userService->getMyself($token);
            $publications = $user['publications'] ?? [];

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'publications' => $publications
            ]);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
}
