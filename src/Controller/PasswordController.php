<?php

// PasswordController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PasswordController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/password', name: 'app_password')]
    public function index(Request $request, SessionInterface $session): Response
    {
        $email = $request->request->get('_email');

        if ($request->isMethod('POST') && $email) {
            $isValidEmail = $this->validateEmail($email);

            if ($isValidEmail) {
                // Envoyer un message de réussite
                $this->addFlash('success', 'Un e-mail de récupération a été envoyé à votre adresse.');
            } else {
                // Envoyer un message d'erreur
                $this->addFlash('error', 'Adresse e-mail non reconnue. Veuillez réessayer.');
            }
        }

        return $this->render('password/index.html.twig', [
            'controller_name' => 'PasswordController',
        ]);
    }

    private function validateEmail($email)
    {
        $users = $this->loadUsersFromJson();

        foreach ($users as $user) {
            if ($email === $user['email']) {
                // E-mail trouvé, peut être considéré comme valide
                return true;
            }
        }

        // Aucun utilisateur avec cet e-mail trouvé
        return false;
    }

    private function loadUsersFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json');
        return $this->serializer->decode($jsonContent, 'json');
    }
}
