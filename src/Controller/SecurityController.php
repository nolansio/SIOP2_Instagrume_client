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
                    // Appel à l'API pour créer l'utilisateur
                    $response = $this->apiService->post('/users', [
                        'username' => $username,
                        'password' => $password
                    ]);

                    // Rediriger vers la page de connexion
                    $this->addFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    // Gérer spécifiquement l'erreur 409 (nom d'utilisateur déjà utilisé)
                    if (strpos($e->getMessage(), '409') !== false || strpos($e->getMessage(), 'Username already exists') !== false) {
                        $error = "Ce nom d'utilisateur est déjà utilisé. Veuillez en choisir un autre.";
                    } else {
                        $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                    }
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

                // Récupérer les infos utilisateur
                $token = $response['token'];
                $userInfo = $this->apiService->get('/users/myself', $token);

                // Stocker le token et les infos utilisateur en session
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

                // Détecter le type d'erreur
                $errorMessage = $e->getMessage();

                // Cas 1 : Utilisateur banni (403)
                if (strpos($errorMessage, '403') !== false || strpos($errorMessage, 'User is banned') !== false) {
                    // Récupérer les informations de l'utilisateur pour avoir la date de bannissement
                    try {
                        // On ne peut pas récupérer les infos sans token, donc on affiche un message générique
                        // Vous pouvez créer un endpoint spécial dans l'API qui renvoie la date de fin du ban
                        $error = "Votre compte est temporairement banni.";
                    } catch (\Exception $e2) {
                        $error = "Votre compte est temporairement banni.";
                    }
                }
                // Cas 2 : Utilisateur introuvable (404)
                elseif (strpos($errorMessage, '404') !== false || strpos($errorMessage, 'User not found') !== false) {
                    $error = "Utilisateur introuvable. Vérifiez votre nom d'utilisateur.";
                }
                // Cas 3 : Mot de passe incorrect (401)
                elseif (strpos($errorMessage, '401') !== false || strpos($errorMessage, 'Incorrect password') !== false) {
                    $error = "Mot de passe incorrect.";
                }
                // Cas 4 : Erreur générique
                else {
                    $error = "Identifiants incorrects.";
                }
            }
        }

        return $this->render('security/login.html.twig', [
            'error' => $error
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->remove('token');
        $session->remove('user');
        $session->clear();
        $session->invalidate();

        $this->addFlash('success', 'Vous êtes déconnecté.');
        return $this->redirectToRoute('app_home');
    }
}
