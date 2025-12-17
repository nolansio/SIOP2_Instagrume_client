<?php

namespace App\Service;

class LikeService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Ajouter un like à une publication
     */
    public function likePublication(int $publicationId, string $token): array
    {
        return $this->apiService->post('/likes/publication/id/' . $publicationId, [], $token);
    }

    /**
     * Ajouter un dislike à une publication
     */
    public function dislikePublication(int $publicationId, string $token): array
    {
        return $this->apiService->post('/dislikes/publication/id/' . $publicationId, [], $token);
    }

    /**
     * Supprimer un like
     */
    public function deleteLike(int $likeId, string $token): void
    {
        $this->apiService->delete('/likes/id/' . $likeId, $token);
    }

    /**
     * Supprimer un dislike
     */
    public function deleteDislike(int $dislikeId, string $token): void
    {
        $this->apiService->delete('/dislikes/id/' . $dislikeId, $token);
    }

    /**
     * Ajouter un like à un commentaire
     */
    public function likeComment(int $commentId, string $token): array
    {
        return $this->apiService->post('/likes/commentaire', [
            'commentaire_id' => $commentId
        ], $token);
    }

    /**
     * Ajouter un dislike à un commentaire
     */
    public function dislikeComment(int $commentId, string $token): array
    {
        return $this->apiService->post('/dislikes/commentaire', [
            'commentaire_id' => $commentId
        ], $token);
    }
}
