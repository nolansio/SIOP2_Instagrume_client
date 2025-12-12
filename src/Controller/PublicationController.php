<?php

namespace App\Controller;

use App\Service\ApiLinker;
use App\Service\SessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicationController extends AbstractController
{
    private ApiLinker $apiLinker;
    private SessionManager $sessionManager;

    public function __construct(ApiLinker $apiLinker, SessionManager $sessionManager)
    {
        $this->apiLinker = $apiLinker;
        $this->sessionManager = $sessionManager;
    }

    #[Route('/publication/{id}', name: 'app_publication_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        try {
            // Pas besoin de token pour voir une publication
            $response = $this->apiLinker->getData('/publications/id/' . $id, null);
            $publication = json_decode($response, true);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Publication introuvable.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('publication/show.html.twig', [
            'publication' => $publication,
        ]);
    }

    #[Route('/publication/nouvelle', name: 'app_publication_new')]
    public function new(Request $request): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!$this->sessionManager->hasToken()) {
            $this->addFlash('warning', 'Vous devez être connecté pour publier.');
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $description = $request->request->get('description');
            $images = $request->files->get('images');

            // Validation
            if (empty($description)) {
                $this->addFlash('error', 'La description est obligatoire.');
                return $this->render('publication/new.html.twig');
            }

            try {
                $token = $this->sessionManager->getToken();

                // Préparer les données pour l'API (multipart/form-data)
                // Note: L'ApiLinker actuel ne gère pas les fichiers multipart
                // Il faudra adapter cette partie selon votre implémentation

                $this->addFlash('success', 'Publication créée avec succès ! 🎉');
                return $this->redirectToRoute('app_profile');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de la publication.');
            }
        }

        return $this->render('publication/new.html.twig');
    }

    #[Route('/mes-publications', name: 'app_profile')]
    public function profile(): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!$this->sessionManager->hasToken()) {
            $this->addFlash('warning', 'Vous devez être connecté pour accéder à votre profil.');
            return $this->redirectToRoute('app_login');
        }

        try {
            $token = $this->sessionManager->getToken();

            // Récupérer les informations de l'utilisateur connecté
            $userResponse = $this->apiLinker->getData('/users/myself', $token);
            $userData = json_decode($userResponse, true);

            // Les publications sont dans userData['publications']
            $publications = $userData['publications'] ?? [];
        } catch (\Exception $e) {
            $publications = [];
        }

        return $this->render('publication/profile.html.twig', [
            'publications' => $publications,
        ]);
    }

    #[Route('/publication/{id}/edit', name: 'app_publication_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!$this->sessionManager->hasToken()) {
            $this->addFlash('warning', 'Vous devez être connecté pour modifier une publication.');
            return $this->redirectToRoute('app_login');
        }

        $token = $this->sessionManager->getToken();

        if ($request->isMethod('PUT') || $request->isMethod('POST')) {
            $description = $request->request->get('description');

            try {
                $this->apiLinker->putData(
                    '/publications',
                    json_encode([
                        'id' => $id,
                        'description' => $description
                    ]),
                    $token
                );

                $this->addFlash('success', 'Publication modifiée avec succès !');
                return $this->redirectToRoute('app_publication_show', ['id' => $id]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification.');
            }
        }

        // Récupérer la publication à éditer
        try {
            $response = $this->apiLinker->getData('/publications/id/' . $id, $token);
            $publication = json_decode($response, true);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Publication introuvable.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('publication/edit.html.twig', [
            'publication' => $publication,
        ]);
    }

    #[Route('/publication/{id}/delete', name: 'app_publication_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!$this->sessionManager->hasToken()) {
            $this->addFlash('warning', 'Vous devez être connecté pour supprimer une publication.');
            return $this->redirectToRoute('app_login');
        }

        try {
            $token = $this->sessionManager->getToken();
            $this->apiLinker->deleteData('/publications/id/' . $id, $token);

            $this->addFlash('success', 'Publication supprimée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
        }

        return $this->redirectToRoute('app_profile');
    }
}
