<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user/profile', name: 'app_user_profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/edit_profile', name: 'app_user_edit_profile')]
    public function editProfile(): Response
    {
        return $this->render('user/profile.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/search_profile', name: 'app_user_search_profile')]
    public function searchProfile(): Response
    {
        return $this->render('user/search.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
