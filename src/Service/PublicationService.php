<?php

namespace App\Service;

class PublicationService
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Récupérer toutes les publications
     */
    public function getAllPublications(?string $token = null): array
    {
        return $this->apiService->get('/publications', $token);
    }

    /**
     * Récupérer une publication par son ID
     */
    public function getPublicationById(int $id, ?string $token = null): array
    {
        return $this->apiService->get('/publications/id/' . $id, $token);
    }

    /**
     * Récupérer une sélection aléatoire de publications
     */
    public function getRandomPublications(int $limit = 10, ?string $token = null): array
    {
        $publications = $this->getAllPublications($token);

        // Mélanger et limiter
        shuffle($publications);
        return array_slice($publications, 0, $limit);
    }

    /**
     * Créer une publication
     */
    public function createPublication(array $data, string $token): array
    {
        return $this->apiService->post('/publications', $data, $token);
    }

    /**
     * Modifier une publication
     */
    public function updatePublication(array $data, string $token): array
    {
        return $this->apiService->put('/publications', $data, $token);
    }

    /**
     * Supprimer une publication
     */
    public function deletePublication(int $id, string $token): void
    {
        $this->apiService->delete('/publications/id/' . $id, $token);
    }
}
