<?php

namespace App\Service;

class CommentService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Créer un commentaire
     */
    public function createComment(string $content, int $publicationId, ?int $originalCommentId, string $token): array
    {
        $data = [
            'content' => $content,
            'id' => $publicationId
        ];

        // N'ajouter original_comment que s'il est vraiment défini et non vide
        if ($originalCommentId && $originalCommentId > 0) {
            $data['original_comment'] = $originalCommentId;
        }

        return $this->apiService->post('/comments', $data, $token);
    }

    /**
     * Modifier un commentaire
     */
    public function updateComment(int $id, string $content, string $token): array
    {
        return $this->apiService->put('/comments', [
            'id' => $id,
            'content' => $content
        ], $token);
    }

    /**
     * Supprimer un commentaire
     */
    public function deleteComment(int $id, string $token): void
    {
        $this->apiService->delete('/comments/id/' . $id, $token);
    }

    /**
     * Récupérer tous les commentaires
     */
    public function getAllComments(string $token): array
    {
        return $this->apiService->get('/comments', $token);
    }
}
