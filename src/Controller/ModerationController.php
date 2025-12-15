<?php

namespace App\Controller;

use App\Service\ModerationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ModerationController extends AbstractController
{
    private ModerationService $moderationService;

    public function __construct(ModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    #[Route('/moderation/user/{id}/ban', name: 'app_user_ban', methods: ['POST'])]
    public function banUser(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->moderationService->banUser($id, $token);
            $this->addFlash('success', 'Utilisateur banni avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/moderation/user/{id}/deban', name: 'app_user_deban', methods: ['POST'])]
    public function debanUser(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->moderationService->debanUser($id, $token);
            $this->addFlash('success', 'Utilisateur débanni avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/moderation/publication/{id}/lock', name: 'app_publication_lock', methods: ['POST'])]
    public function lockPublication(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->moderationService->lockPublication($id, $token);
            $this->addFlash('success', 'Publication verrouillée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/moderation/publication/{id}/unlock', name: 'app_publication_unlock', methods: ['POST'])]
    public function unlockPublication(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->moderationService->delockPublication($id, $token);
            $this->addFlash('success', 'Publication déverrouillée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}
