<?php

namespace App\Controller;

use App\Service\PublicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicationViewController extends AbstractController
{
    private PublicationService $publicationService;

    public function __construct(PublicationService $publicationService)
    {
        $this->publicationService = $publicationService;
    }

    #[Route('/publication/{id}', name: 'app_publication_view')]
    public function view(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');
        $user = $request->getSession()->get('user');

        try {
            $publication = $this->publicationService->getPublicationById($id, $token);
        } catch (\Exception $e) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('publication/view.html.twig', [
            'publication' => $publication,
            'currentUser' => $user
        ]);
    }
}
