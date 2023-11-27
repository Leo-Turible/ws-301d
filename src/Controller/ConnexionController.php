<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

            $isValid = $this->validateCredentials($email, $password, $session);

            if ($isValid) {
                $this->addFlash('success', 'Connexion réussite !');
                return $this->redirectToRoute('app_connexion');
                // sleep(2);
                // return $this->redirectToRoute('app_profil');
            }
        }

        return $this->render('connexion/index.html.twig', [
            'controller_name' => 'ConnexionController',
            'last_username' => $lastUsername,
        ]);
    }

    private function validateCredentials($enteredEmail, $enteredPassword, $session)
    {
        $users = $this->loadUsersFromJson();

        foreach ($users as $user) {
            // Vérifie si l'e-mail et le mot de passe correspondent
            if ($enteredEmail === $user['email'] && $enteredPassword === $user['password']) {

                // Vérifie si l'e-mail se termine par "@etudiant.univ-reims.fr"
                if (substr($enteredEmail, -23) === "@etudiant.univ-reims.fr") {
                    // Stocke les informations de l'utilisateur dans la session
                    $session->set('user_email', $user['email']);
                    $session->set('user_first_name', $user['firstName']);
                    $session->set('user_last_name', $user['lastName']);
                    $session->set('user_tp', $user['tp']);

                    return true;
                } else {
                    // Ajoute un message d'erreur si l'e-mail n'est pas valide
                    $this->addFlash('error', 'Veuillez utiliser votre adresse URCA qui se termine par @etudiant.univ-reims.fr.');
                }
            }
        }

        return false;
    }


    private function loadUsersFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        // La méthode n'a pas besoin de contenu ici.
    }

    #[Route('/get-user-tp', name: 'get_user_tp')]
    public function getUserTp(SessionInterface $session): JsonResponse
    {
        $userTp = $session->get('user_tp');

        return $this->json(['user_tp' => $userTp]);
    }
}
