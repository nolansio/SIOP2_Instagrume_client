<?php

namespace App\Controller;

use App\Service\LikeService;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InteractionController extends AbstractController
{
    private LikeService $likeService;
    private CommentService $commentService;

    public function __construct(LikeService $likeService, CommentService $commentService)
    {
        $this->likeService = $likeService;
        $this->commentService = $commentService;
    }

    #[Route('/publication/{id}/like', name: 'app_publication_like', methods: ['POST'])]
    public function likePublication(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->likeService->likePublication($id, $token);
            $this->addFlash('success', 'Like ajouté !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/publication/{id}/dislike', name: 'app_publication_dislike', methods: ['POST'])]
    public function dislikePublication(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->likeService->dislikePublication($id, $token);
            $this->addFlash('success', 'Dislike ajouté !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/like/{id}/supprimer', name: 'app_like_delete', methods: ['POST'])]
    public function deleteLike(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->likeService->deleteLike($id, $token);
            $this->addFlash('success', 'Like retiré !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/dislike/{id}/supprimer', name: 'app_dislike_delete', methods: ['POST'])]
    public function deleteDislike(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->likeService->deleteDislike($id, $token);
            $this->addFlash('success', 'Dislike retiré !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/publication/{id}/commenter', name: 'app_publication_comment', methods: ['POST'])]
    public function commentPublication(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        $content = $request->request->get('content');
        $originalCommentId = $request->request->get('original_comment_id');

        // Convertir en int ou null
        $originalCommentId = $originalCommentId ? (int)$originalCommentId : null;

        if (empty($content)) {
            $this->addFlash('danger', 'Le commentaire ne peut pas être vide.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
        }

        try {
            $this->commentService->createComment($content, $id, $originalCommentId, $token);
            $this->addFlash('success', 'Commentaire ajouté !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}
