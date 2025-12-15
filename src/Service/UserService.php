<?php

namespace App\Service;

class UserService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Récupérer un utilisateur par son ID
     */
    public function getUserById(int $id, ?string $token = null): array
    {
        return $this->apiService->get('/users/id/' . $id, $token);
    }

    /**
     * Récupérer un utilisateur par son nom d'utilisateur
     */
    public function getUserByUsername(string $username, ?string $token = null): array
    {
        return $this->apiService->get('/users/username/' . $username, $token);
    }

    /**
     * Rechercher des utilisateurs par nom d'utilisateur
     */
    public function searchUsers(string $search, ?string $token = null): array
    {
        return $this->apiService->get('/users/search/' . $search, $token);
    }

    /**
     * Récupérer l'utilisateur connecté
     */
    public function getMyself(string $token): array
    {
        return $this->apiService->get('/users/myself', $token);
    }
}
