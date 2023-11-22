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
            $firstName = $request->request->get('_firstName');
            $lastName = $request->request->get('_lastName');
            $email = $request->request->get('_email');
            $password = $request->request->get('_password');
            $tp = $request->request->get('_tp');
            $year = $request->request->get('_year');

            // Validate and insert the user data into the JSON file
            $this->registerUser($firstName, $lastName, $email, $password, $tp, $year);

            // You may redirect to a login page or any other page after successful registration
            return $this->redirectToRoute('app_connexion');
        }

        return $this->render('inscription/index.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }

    private function registerUser($firstName, $lastName, $email, $password, $tp, $year)
    {
        // Load existing users from the JSON file
        $users = $this->loadUsersFromJson();

        // Add the new user to the array
        $newUser = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
            'tp' => $tp,
            'year' => $year,
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
