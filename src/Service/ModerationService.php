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
    public function banUser(int $userId, int $banDurationDays, string $token): void
    {
        $this->apiService->put('/users/ban/id/' . $userId, [
            'user_id' => $userId,
            'banDurationDays' => $banDurationDays
        ], $token);
    }

    /**
     * Débannir un utilisateur
     */
    public function debanUser(int $userId, string $token): void
    {
        $this->apiService->put('/users/deban/id/' . $userId, [], $token);
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
    public function delockPublication(int $publicationId, string $token): void
    {
        $this->apiService->put('/publications/delock/id/' . $publicationId, [], $token);
    }
}
