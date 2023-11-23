<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            // Get the submitted email and password
            $email = $request->request->get('_email');
            $password = $request->request->get('_password');

            // Validate the credentials against users from the JSON file
            $isValid = $this->validateCredentials($email, $password);

            if ($isValid) {
                // Save the email in the session
                $session->set('user_email', $email);

                $this->addFlash('success', 'Login successful!');
                sleep(1.5); // Add a 3-second delay
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
        // Load user data from the JSON file
        $users = $this->loadUsersFromJson();

        // Validate the entered credentials against users from the JSON file
        foreach ($users as &$user) {
            if ($enteredEmail === $user['email'] && $enteredPassword === $user['password']) {
                // $user['roles'] = ['ROLE_USER'];
                
                // $this->saveUsersToJson($users);
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

    // private function saveUsersToJson($users)
    // {
    //     // Encoder les utilisateurs au format JSON
    //     $jsonContent = $this->serializer->encode($users, 'json');

    //     // Sauvegarder le contenu JSON dans le fichier
    //     file_put_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json', $jsonContent);
    // }
}
