<?php

namespace App\Controller;

use App\Service\CommentInteractionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentInteractionController extends AbstractController
{
    private CommentInteractionService $commentInteractionService;

    public function __construct(CommentInteractionService $commentInteractionService)
    {
        $this->commentInteractionService = $commentInteractionService;
    }

    #[Route('/commentaire/{id}/like', name: 'app_comment_like', methods: ['POST'])]
    public function like(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->commentInteractionService->likeComment($id, $token);
            $this->addFlash('success', 'Commentaire liké !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/commentaire/{id}/dislike', name: 'app_comment_dislike', methods: ['POST'])]
    public function dislike(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->commentInteractionService->dislikeComment($id, $token);
            $this->addFlash('success', 'Commentaire disliké !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/commentaire/like/{id}/supprimer', name: 'app_comment_like_delete', methods: ['POST'])]
    public function deleteLike(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->commentInteractionService->deleteLike($id, $token);
            $this->addFlash('success', 'Like retiré !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/commentaire/dislike/{id}/supprimer', name: 'app_comment_dislike_delete', methods: ['POST'])]
    public function deleteDislike(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->commentInteractionService->deleteDislike($id, $token);
            $this->addFlash('success', 'Dislike retiré !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}
