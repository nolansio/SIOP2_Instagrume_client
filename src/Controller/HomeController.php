<?php

namespace App\Controller;

use App\Service\ApiLinker;
use App\Service\JsonConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private ApiLinker $apiLinker,
        private JsonConverter $jsonConverter
    ) {}

    #[Route('/home', name: 'app_home')]
    #[Route('/', name: 'app_root')]
    public function index(Request $request): Response
    {
        $publications = [];
        $error = null;
        $isEmpty = false;

        try {
            // Récupérer le token depuis la session
            $session = $request->getSession();
            $token = $session->get('token-session', null);

            // IMPORTANT : Si pas de token, on passe NULL explicitement
            // L'API doit accepter les requêtes sans token pour /api/publications

            // Appeler l'API pour récupérer toutes les publications
            $response = $this->apiLinker->getData('/api/publications', null);

            // Décoder la réponse JSON
            $publications = json_decode($response, true);

            // Vérifier si le tableau est vide
            if (empty($publications)) {
                $isEmpty = true;
            }
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            // Erreur 4xx (401, 404, etc.)
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 401) {
                // L'API demande un token alors qu'elle ne devrait pas
                $error = "🔒 L'API nécessite une authentification. Ton coéquipier doit rendre la route /api/publications publique (voir FIX_API_JWT_PUBLIC_ROUTES.md)";
            } else {
                $error = "Erreur client (code $statusCode) : " . $e->getMessage();
            }
        } catch (\Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface $e) {
            // Erreur 5xx
            $error = "Erreur serveur API : " . $e->getMessage();
        } catch (\Exception $e) {
            // Autres erreurs (connexion impossible, etc.)
            $error = "Impossible de contacter l'API : " . $e->getMessage();
        }

        return $this->render('home/index.html.twig', [
            'publications' => $publications,
            'error' => $error,
            'isEmpty' => $isEmpty,
            'isConnected' => $session->has('token-session')
        ]);
    }
}
