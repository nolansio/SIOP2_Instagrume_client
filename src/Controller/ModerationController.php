<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ModerationController extends AbstractController
{

    public function __construct(private ApiLinker $apiLinker) {
    }

    #[Route('/moderation/dashboard', name: 'app_moderation')]
    public function index(): Response
    {
        return $this->render('moderation/dashboard.html.twig', [
            'controller_name' => 'ModerationController',
        ]);
    }

    #[Route('/moderation/user/ban', name: 'app_moderation')]
    public function banUser(Request $request): Response
    {
        $idUser = $request->request->get('id');
        $data = $this->jsonConverter->encodeToJson(['id-user' => $idUser]);
        $session = $request->getSession();
        $token = $session->get('token-session');
        $this->apiLinker->putData('/api/user/ban', $data, $token);
        return $this->render('moderation/dashboard.html.twig', [
            'controller_name' => 'ModerationController',
        ]);
    }

    #[Route('/moderation/user/deban', name: 'app_moderation')]
    public function debanUser(): Response
    {
        $idUser = $request->request->get('id');
        $data = $this->jsonConverter->encodeToJson(['id-user' => $idUser]);
        $session = $request->getSession();
        $token = $session->get('token-session');
        $this->apiLinker->putData('/api/user/deban', $data, $token);
        return $this->render('moderation/dashboard.html.twig', [
            'controller_name' => 'ModerationController',
        ]);
    }

    #[Route('/moderation/publication/lock', name: 'app_moderation')]
    public function lockPublication(): Response
    {
        $idPublication = $request->request->get('id');
        $data = $this->jsonConverter->encodeToJson(['id-publi' => $idPublication]);
        $session = $request->getSession();
        $token = $session->get('token-session');
        $this->apiLinker->putData('/api/publication/lock', $data, $token);
        return $this->render('moderation/dashboard.html.twig', [
            'controller_name' => 'ModerationController',
        ]);
    }

    #[Route('/moderation/publication/delock', name: 'app_moderation')]
    public function delockPublication(): Response
    {
        $idPublication = $request->request->get('id');
        $data = $this->jsonConverter->encodeToJson(['id-publi' => $idPublication]);
        $session = $request->getSession();
        $token = $session->get('token-session');
        $this->apiLinker->putData('/api/publication/delock', $data, $token);
        return $this->render('moderation/dashboard.html.twig', [
            'controller_name' => 'ModerationController',
        ]);
    }

}
