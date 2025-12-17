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
     * Verrouiller une publication
     */
    public function lockPublication(int $publicationId, string $token): void
    {
        $this->apiService->put('/publications/lock/id/' . $publicationId, [], $token);
    }

    /**
     * Déverrouiller une publication
     */
    public function unlockPublication(int $publicationId, string $token): void
    {
        $this->apiService->put('/publications/delock/id/' . $publicationId, [], $token);
    }

    /**
     * Bannir un utilisateur
     */
    public function banUser(int $userId, string $token): void
    {
        $this->apiService->put('/users/ban/id/' . $userId, [], $token);
    }

    /**
     * Débannir un utilisateur
     */
    public function unbanUser(int $userId, string $token): void
    {
        $this->apiService->put('/users/deban/id/' . $userId, [], $token);
    }
}
