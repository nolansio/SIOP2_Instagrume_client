<?php

namespace App\Service;

class ModerationService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Bannir un utilisateur
     */
    public function banUser(int $userId, string $token): array
    {
        return $this->apiService->put('/users/ban', [
            'user_id' => $userId
        ], $token);
    }

    /**
     * Débannir un utilisateur
     */
    public function debanUser(int $userId, string $token): array
    {
        return $this->apiService->put('/users/deban', [
            'user_id' => $userId
        ], $token);
    }

    /**
     * Verrouiller une publication (empêche les commentaires)
     */
    public function lockPublication(int $publicationId, string $token): array
    {
        return $this->apiService->put('/publications/lock', [
            'publication_id' => $publicationId
        ], $token);
    }

    /**
     * Déverrouiller une publication (autorise les commentaires)
     */
    public function delockPublication(int $publicationId, string $token): array
    {
        return $this->apiService->put('/publications/delock', [
            'publication_id' => $publicationId
        ], $token);
    }
}
