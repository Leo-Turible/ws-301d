<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ConnexionController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/connexion', name: 'app_connexion')]
    public function index(Request $request, AuthenticationUtils $authenticationUtils, SessionInterface $session): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($request->isMethod('POST')) {
            $email = $request->request->get('_email');
            $password = $request->request->get('_password');

            $isValid = $this->validateCredentials($email, $password);

            if ($isValid) {
                $session->set('user_email', $email);

                $this->addFlash('success', 'Login successful!');
                sleep(1.5);
                return $this->redirectToRoute('app_profil');
            } else {
                $this->addFlash('error', 'Invalid credentials!');
            }
        }

        return $this->render('connexion/index.html.twig', [
            'controller_name' => 'ConnexionController',
            'last_username' => $lastUsername,
        ]);
    }

    private function validateCredentials($enteredEmail, $enteredPassword)
    {
        $users = $this->loadUsersFromJson();

        foreach ($users as &$user) {
            if ($enteredEmail === $user['email'] && $enteredPassword === $user['password']) {
                
                return true;
            }
        }

        return false;
    }

    private function loadUsersFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json');
        return $this->serializer->decode($jsonContent, 'json');
    }
}
