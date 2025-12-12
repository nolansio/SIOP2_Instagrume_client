<?php

namespace App\Controller;

use App\Service\ApiLinker;
use App\Service\SessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur de test pour vérifier la connexion à l'API
 * À supprimer une fois les tests terminés
 */
class TestApiController extends AbstractController
{
    private ApiLinker $apiLinker;
    private SessionManager $sessionManager;

    public function __construct(ApiLinker $apiLinker, SessionManager $sessionManager)
    {
        $this->apiLinker = $apiLinker;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Test de connexion à l'API
     * Route: /test-api
     */
    #[Route('/test-api', name: 'app_test_api')]
    public function testApi(): Response
    {
        $results = [
            'api_url' => $_ENV['API_SERVER_URL'] ?? 'NON DÉFINIE',
            'session_active' => session_status() === PHP_SESSION_ACTIVE,
            'token_in_session' => $this->sessionManager->hasToken(),
            'api_tests' => []
        ];

        // Test 1: Récupérer les publications (public)
        try {
            $response = $this->apiLinker->getData('/publications', null);
            $publications = json_decode($response, true);
            $results['api_tests']['get_publications'] = [
                'status' => 'SUCCESS',
                'count' => count($publications),
                'sample' => isset($publications[0]) ? [
                    'id' => $publications[0]['id'] ?? null,
                    'description' => substr($publications[0]['description'] ?? '', 0, 50) . '...'
                ] : null
            ];
        } catch (\Exception $e) {
            $results['api_tests']['get_publications'] = [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }

        // Test 2: Vérifier les utilisateurs (public)
        try {
            $response = $this->apiLinker->getData('/users', null);
            $users = json_decode($response, true);
            $results['api_tests']['get_users'] = [
                'status' => 'SUCCESS',
                'count' => count($users)
            ];
        } catch (\Exception $e) {
            $results['api_tests']['get_users'] = [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }

        // Test 3: Si connecté, tester l'authentification
        if ($this->sessionManager->hasToken()) {
            try {
                $token = $this->sessionManager->getToken();
                $response = $this->apiLinker->getData('/users/myself', $token);
                $userData = json_decode($response, true);
                $results['api_tests']['get_myself'] = [
                    'status' => 'SUCCESS',
                    'username' => $userData['username'] ?? 'unknown'
                ];
                $results['user_data'] = $this->sessionManager->getUserData();
            } catch (\Exception $e) {
                $results['api_tests']['get_myself'] = [
                    'status' => 'ERROR',
                    'message' => $e->getMessage()
                ];
            }
        } else {
            $results['api_tests']['get_myself'] = [
                'status' => 'SKIPPED',
                'message' => 'Non connecté - Connectez-vous pour tester cette route'
            ];
        }

        return $this->render('test/api_test.html.twig', [
            'results' => $results
        ]);
    }

    /**
     * Version JSON de test
     * Route: /test-api/json
     */
    #[Route('/test-api/json', name: 'app_test_api_json')]
    public function testApiJson(): JsonResponse
    {
        $results = [
            'api_url' => $_ENV['API_SERVER_URL'] ?? 'NON DÉFINIE',
            'session_active' => session_status() === PHP_SESSION_ACTIVE,
            'token_in_session' => $this->sessionManager->hasToken(),
        ];

        try {
            $response = $this->apiLinker->getData('/publications', null);
            $publications = json_decode($response, true);
            $results['api_connection'] = 'SUCCESS';
            $results['publications_count'] = count($publications);
        } catch (\Exception $e) {
            $results['api_connection'] = 'ERROR';
            $results['error_message'] = $e->getMessage();
        }

        return new JsonResponse($results);
    }
}
