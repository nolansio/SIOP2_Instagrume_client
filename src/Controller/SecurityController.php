<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Service\JsonConverter;
use App\Service\ApiLinker;


final class SecurityController extends AbstractController
{

    public function __construct(private ApiLinker $apiLinker, private JsonConverter $jsonConverter) {
    }

    #[Route('/security/login', name: 'app_security_login')]
    public function login(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if (!empty($username) && !empty($password)) {
            $data = $this->jsonConverter->encodeToJson(['username' => $username, 'password' => $password]);
            $response = $this->apiLinker->postData('/token', $data, null);
            $responseObject = json_decode($response);

            $session = $request->getSession();
            $session->set('username', $username);
            $session->set('token-session', $responseObject->token);
            $response = $this->apiLinker->getData('/myself', $session->get('token-session'));
            $responseObject = json_decode($response);
            return $this->redirect('/home');
        }

        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    #[Route('/security/register', name: 'app_security_register')]
    public function register(Request $request): Response
    {
        return $this->render('security/register.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    #[Route('/security/logout', name: 'app_security_register')]
    public function logout(Request $request) {
        $session = $request->getSession();
        $session->remove('token-session');
        $session->clear();

        return $this->render('security/login.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }
}
