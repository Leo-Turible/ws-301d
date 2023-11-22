<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class InscriptionController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/inscription', name: 'app_inscription')]
    public function index(Request $request): Response
    {
        // Handle the form submission for user registration
        if ($request->isMethod('POST')) {
            $email = $request->request->get('_email');
            $password = $request->request->get('_password');

            // Validate and insert the user data into the JSON file
            $this->registerUser($email, $password);

            // You may redirect to a login page or any other page after successful registration
            return $this->redirectToRoute('app_connexion');
        }

        return $this->render('inscription/index.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }

    private function registerUser($email, $password)
    {
        // Load existing users from the JSON file
        $users = $this->loadUsersFromJson();

        // Add the new user to the array
        $newUser = [
            'email' => $email,
            'password' => $password,
        ];

        $users[] = $newUser;

        // Save the updated users array back to the JSON file
        $this->saveUsersToJson($users);
    }

    private function loadUsersFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function saveUsersToJson($users)
    {
        $jsonContent = $this->serializer->encode($users, 'json');
        file_put_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json', $jsonContent);
    }

}
