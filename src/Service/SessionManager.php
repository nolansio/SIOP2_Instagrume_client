<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class SessionManager
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Sauvegarde le token JWT dans la session
     */
    public function setToken(string $token): void
    {
        $session = $this->requestStack->getSession();
        $session->set('api_token', $token);
    }

    /**
     * Récupère le token JWT depuis la session
     */
    public function getToken(): ?string
    {
        $session = $this->requestStack->getSession();
        return $session->get('api_token');
    }

    /**
     * Vérifie si un token existe dans la session
     */
    public function hasToken(): bool
    {
        return $this->getToken() !== null;
    }

    /**
     * Supprime le token de la session (déconnexion)
     */
    public function clearToken(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('api_token');
    }

    /**
     * Sauvegarde les données utilisateur dans la session
     */
    public function setUserData(array $userData): void
    {
        $session = $this->requestStack->getSession();
        $session->set('user_data', $userData);
    }

    /**
     * Récupère les données utilisateur depuis la session
     */
    public function getUserData(): ?array
    {
        $session = $this->requestStack->getSession();
        return $session->get('user_data');
    }

    /**
     * Supprime les données utilisateur de la session
     */
    public function clearUserData(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('user_data');
    }

    /**
     * Déconnexion complète : supprime token et données utilisateur
     */
    public function logout(): void
    {
        $this->clearToken();
        $this->clearUserData();
    }
}
