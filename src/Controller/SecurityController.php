<?php

namespace App\Controller;

use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request): Response
    {
        // Si déjà connecté, rediriger vers l'accueil
        if ($request->getSession()->get('token')) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            // Validation basique
            if ($password !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères.";
            } else {
                try {
                    // Appel à l'API pour créer l'utilisateur (sans email)
                    $response = $this->apiService->post('/users', [
                        'username' => $username,
                        'password' => $password
                    ]);

                    // Rediriger vers la page de connexion
                    $this->addFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $error = "Erreur lors de l'inscription : " . $e->getMessage();
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'error' => $error
        ]);
    }

    #[Route('/connexion', name: 'app_login')]
    public function login(Request $request): Response
    {
        // Si déjà connecté, rediriger vers l'accueil
        if ($request->getSession()->get('token')) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');

            try {
                // Appel à l'API pour obtenir le token
                $response = $this->apiService->post('/token', [
                    'username' => $username,
                    'password' => $password
                ]);

                // IMPORTANT : Récupérer les infos utilisateur AVANT de stocker en session
                $token = $response['token'];
                $userInfo = $this->apiService->get('/users/myself', $token);

                // Stocker le token et les infos utilisateur en session SEULEMENT si tout a réussi
                $session = $request->getSession();
                $session->set('token', $token);
                $session->set('user', $userInfo);

                $this->addFlash('success', 'Connexion réussie !');
                return $this->redirectToRoute('app_home');
            } catch (\Exception $e) {
                // Nettoyer la session en cas d'erreur
                $session = $request->getSession();
                $session->remove('token');
                $session->remove('user');

                $error = "Identifiants incorrects.";
            }
        }

        return $this->render('security/login.html.twig', [
            'error' => $error
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        // Récupérer la session
        $session = $request->getSession();

        // Supprimer explicitement les données
        $session->remove('token');
        $session->remove('user');

        // Vider complètement la session
        $session->clear();

        // Invalider la session
        $session->invalidate();

        $this->addFlash('success', 'Vous êtes déconnecté.');
        return $this->redirectToRoute('app_home');
    }
}
