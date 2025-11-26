<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicationController extends AbstractController
{
    #[Route('/publication', name: 'app_publication')]
    public function allPubli(): Response
    {
        return $this->render('publication/index.html.twig', [
            'controller_name' => 'PublicationController',
        ]);
    }

    #[Route('/publication/edit/', name: 'app_publication_edit')]
    public function editPubli(): Response
    {
        return $this->render('publication/edit.html.twig', [
            'controller_name' => 'PublicationController',
        ]);
    }

    #[Route('/publication/new/', name: 'app_publication_new')]
    public function newPubli(): Response
    {
        return $this->render('publication/new.html.twig', [
            'controller_name' => 'PublicationController',
        ]);
    }

    #[Route('/publication/show', name: 'app_publication_show')]
    public function showPubli(): Response
    {
        return $this->render('publication/show.html.twig', [
            'controller_name' => 'PublicationController',
        ]);
    }
}
