<?php

namespace App\Controller;

use App\Service\ApiService;
use App\Service\UserService;
use App\Service\PublicationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    private UserService $userService;
    private PublicationService $publicationService;
    private ApiService $apiService;

    public function __construct(UserService $userService, PublicationService $publicationService, ApiService $apiService)
    {
        $this->userService = $userService;
        $this->publicationService = $publicationService;
        $this->apiService = $apiService;
    }

    #[Route('/mon-profil', name: 'app_my_profile')]
    public function myProfile(Request $request): Response
    {
        $token = $request->getSession()->get('token');
        $currentUser = $request->getSession()->get('user');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            // Récupérer les infos de l'utilisateur et ses publications
            $user = $this->userService->getMyself($token);
            $publications = $user['publications'] ?? [];

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'publications' => $publications
            ]);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/mon-profil/modifier', name: 'app_profile_edit')]
    public function edit(Request $request): Response
    {
        $token = $request->getSession()->get('token');
        $currentUser = $request->getSession()->get('user');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            // Récupérer les infos actuelles de l'utilisateur
            $user = $this->userService->getMyself($token);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $currentPassword = $request->request->get('current_password');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $avatar = $request->files->get('avatar');

            // Validation du mot de passe actuel (obligatoire)
            if (empty($currentPassword)) {
                $error = "Le mot de passe actuel est requis pour confirmer les modifications.";
            }

            // Validation du nouveau mot de passe si fourni
            if (!$error && !empty($password)) {
                if ($password !== $confirmPassword) {
                    $error = "Les mots de passe ne correspondent pas.";
                } elseif (strlen($password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères.";
                }
            }

            // Si pas d'erreur, vérifier le mot de passe actuel et mettre à jour le profil
            if (!$error) {
                try {
                    // Valider le mot de passe actuel en essayant de se connecter
                    $this->apiService->post('/token', [
                        'username' => $user['username'], // Ancien username
                        'password' => $currentPassword
                    ]);

                    $updateData = [];

                    // Ajouter username si changé
                    if (!empty($username) && $username !== $user['username']) {
                        $updateData['username'] = $username;
                    }

                    // Ajouter password si fourni
                    if (!empty($password)) {
                        $updateData['password'] = $password;
                    }

                    // Ajouter avatar si fourni
                    if ($avatar && $avatar->isValid()) {
                        $updateData['avatar'] = $avatar;
                    }

                    // Vérifier qu'il y a au moins une modification
                    if (empty($updateData)) {
                        $error = "Aucune modification détectée.";
                    } else {
                        // Appeler l'API pour mettre à jour
                        $updatedUser = $this->userService->updateProfile($user['id'], $updateData, $token);

                        // Si le username a changé, régénérer un nouveau token
                        if (isset($updateData['username'])) {
                            // Générer un nouveau token avec le nouveau username
                            $newTokenResponse = $this->apiService->post('/token', [
                                'username' => $updateData['username'], // Nouveau username
                                'password' => !empty($password) ? $password : $currentPassword // Nouveau password ou actuel
                            ]);

                            // Récupérer les nouvelles infos utilisateur
                            $newUserInfo = $this->apiService->get('/users/myself', $newTokenResponse['token']);

                            // Mettre à jour la session avec le nouveau token et les nouvelles infos
                            $session = $request->getSession();
                            $session->set('token', $newTokenResponse['token']);
                            $session->set('user', $newUserInfo);

                            $this->addFlash('success', 'Profil mis à jour avec succès ! Votre nom d\'utilisateur a été changé.');
                        } else {
                            $this->addFlash('success', 'Profil mis à jour avec succès !');
                        }

                        return $this->redirectToRoute('app_my_profile');
                    }
                } catch (\Exception $e) {
                    // Si l'erreur vient de la validation du mot de passe actuel
                    if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), 'Incorrect password') !== false) {
                        $error = "Le mot de passe actuel est incorrect.";
                    } else {
                        $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                    }
                }
            }
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'error' => $error
        ]);
    }
}
