<?php

namespace App\Controller;

use App\Service\ApiLinker;
use App\Service\SessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    private ApiLinker $apiLinker;
    private SessionManager $sessionManager;

    public function __construct(ApiLinker $apiLinker, SessionManager $sessionManager)
    {
        $this->apiLinker = $apiLinker;
        $this->sessionManager = $sessionManager;
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request): Response
    {
        // Démarrer explicitement la session
        $session = $request->getSession();
        $session->start();

        // Si déjà connecté (token en session), rediriger vers l'accueil
        if ($this->sessionManager->hasToken()) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;
        $lastUsername = '';

        if ($request->isMethod('POST')) {
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');
            $lastUsername = $username;

            try {
                // Appel à l'API pour obtenir le token
                $response = $this->apiLinker->postData(
                    '/token',
                    json_encode([
                        'username' => $username,
                        'password' => $password
                    ]),
                    null // Pas de token pour la connexion
                );

                $data = json_decode($response, true);

                if (isset($data['token'])) {
                    // Sauvegarder le token en session
                    $this->sessionManager->setToken($data['token']);

                    // Récupérer les infos de l'utilisateur connecté
                    try {
                        $userResponse = $this->apiLinker->getData('/users/myself', $data['token']);
                        $userData = json_decode($userResponse, true);
                        $this->sessionManager->setUserData($userData);
                    } catch (\Exception $e) {
                        // Si on ne peut pas récupérer les infos, ce n'est pas bloquant
                    }

                    $this->addFlash('success', 'Connexion réussie ! Bienvenue ' . $username . ' 👋');
                    return $this->redirectToRoute('app_home');
                } else {
                    $error = 'Identifiants invalides.';
                }
            } catch (\Exception $e) {
                $error = 'Identifiants invalides ou erreur de connexion.';
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        // Si déjà connecté, rediriger vers l'accueil
        if ($this->sessionManager->hasToken()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            // Validation simple
            if (empty($username) || empty($password)) {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
                return $this->render('security/register.html.twig');
            }

            if ($password !== $passwordConfirm) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->render('security/register.html.twig');
            }

            if (strlen($password) < 6) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                return $this->render('security/register.html.twig');
            }

            // Appel à l'API pour créer le compte
            try {
                $response = $this->apiLinker->postData(
                    '/users',
                    json_encode([
                        'username' => $username,
                        'password' => $password,
                    ]),
                    null // Pas de token pour l'inscription
                );

                $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du compte. Le nom d\'utilisateur existe peut-être déjà.');
            }
        }

        return $this->render('security/register.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        // Supprimer le token et les données utilisateur de la session
        $this->sessionManager->logout();

        $this->addFlash('info', 'Vous avez été déconnecté avec succès.');
        return $this->redirectToRoute('app_home');
    }
}
