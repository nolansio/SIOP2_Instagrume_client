<?php

namespace App\Controller;

use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentController extends AbstractController
{
    private CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    #[Route('/commentaire/{id}/modifier', name: 'app_comment_edit', methods: ['POST'])]
    public function edit(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        $content = $request->request->get('content');

        if (empty($content)) {
            $this->addFlash('danger', 'Le commentaire ne peut pas être vide.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
        }

        try {
            $this->commentService->updateComment($id, $content, $token);
            $this->addFlash('success', 'Commentaire modifié avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/commentaire/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->commentService->deleteComment($id, $token);
            $this->addFlash('success', 'Commentaire supprimé avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}
