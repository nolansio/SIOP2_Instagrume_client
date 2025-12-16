<?php

namespace App\Service;

class CommentInteractionService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Liker un commentaire
     */
    public function likeComment(int $commentId, string $token): array
    {
        return $this->apiService->post('/likes/comment/id/' . $commentId, [], $token);
    }

    /**
     * Disliker un commentaire
     */
    public function dislikeComment(int $commentId, string $token): array
    {
        return $this->apiService->post('/dislikes/comment/id/' . $commentId, [], $token);
    }

    /**
     * Supprimer un like de commentaire
     */
    public function deleteLike(int $likeId, string $token): void
    {
        $this->apiService->delete('/likes/id/' . $likeId, $token);
    }

    /**
     * Supprimer un dislike de commentaire
     */
    public function deleteDislike(int $dislikeId, string $token): void
    {
        $this->apiService->delete('/dislikes/id/' . $dislikeId, $token);
    }
}
