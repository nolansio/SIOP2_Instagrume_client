<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SecurityController extends AbstractController
{
    #[Route('/security/login', name: 'app_security_login')]
    public function login(): Response
    {
        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    #[Route('/security/register', name: 'app_security_register')]
    public function register(): Response
    {
        return $this->render('security/register.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }
}
