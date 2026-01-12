<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\PublicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    private UserService $userService;
    private PublicationService $publicationService;

    public function __construct(UserService $userService, PublicationService $publicationService)
    {
        $this->userService = $userService;
        $this->publicationService = $publicationService;
    }

    #[Route('/recherche', name: 'app_search')]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $token = $request->getSession()->get('token');
        $users = [];

        if ($query) {
            try {
                // Récupérer les usernames correspondants
                $usernames = $this->userService->searchUsers($query, $token);

                // Récupérer les détails complets de chaque utilisateur (avec avatar)
                foreach ($usernames as $username) {
                    try {
                        $userDetails = $this->userService->getUserByUsername($username, $token);
                        $users[] = $userDetails;
                    } catch (\Exception $e) {
                        // Ignorer les erreurs pour des utilisateurs individuels
                        continue;
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de la recherche.');
            }
        }

        return $this->render('search/index.html.twig', [
            'query' => $query,
            'users' => $users
        ]);
    }

    #[Route('/utilisateur/{username}', name: 'app_user_profile')]
    public function userProfile(string $username, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        try {
            // Récupérer l'utilisateur
            $user = $this->userService->getUserByUsername($username, $token);

            // Récupérer toutes les publications et filtrer celles de cet utilisateur
            $allPublications = $this->publicationService->getAllPublications($token);
            $userPublications = array_filter($allPublications, function ($pub) use ($user) {
                return $pub['user']['id'] === $user['id'];
            });
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_search');
        }

        return $this->render('search/profile.html.twig', [
            'user' => $user,
            'publications' => $userPublications
        ]);
    }
}
