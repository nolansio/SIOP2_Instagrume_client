<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ModerationController extends AbstractController
{
    #[Route('/moderation/dashboard', name: 'app_moderation')]
    public function index(): Response
    {
        return $this->render('moderation/dashboard.html.twig', [
            'controller_name' => 'ModerationController',
        ]);
    }
}
