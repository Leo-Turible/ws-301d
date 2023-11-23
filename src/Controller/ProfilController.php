<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProfilController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/profil', name: 'app_profil')]
    public function index(SessionInterface $session): Response
    {
        $userEmail = $session->get('user_email');

        if ($userEmail) {
            $user = $this->findUserByEmail($userEmail);

            if ($user) {
                return $this->render('profil/index.html.twig', [
                    'user' => $user,
                ]);
            } else {
                $this->addFlash('error', 'User not found!');
                return $this->redirectToRoute('app_connexion');
            }
        } else {
            $this->addFlash('error', 'User not authenticated!');
            return $this->redirectToRoute('app_connexion');
        }
    }

    private function findUserByEmail($email)
    {
        $users = $this->loadUsersFromJson();

        foreach ($users as &$user) {
            if ($email === $user['email']) {
                // Vérifiez si l'utilisateur a les clés nécessaires
                $requiredKeys = ['email'];
                $optionalKeys = ['firstName', 'lastName', 'tp', 'year'];
                $userKeys = array_keys($user);

                $missingRequiredKeys = array_diff($requiredKeys, $userKeys);
                $missingOptionalKeys = array_diff($optionalKeys, $userKeys);

                if (!empty($missingRequiredKeys)) {
                    $missingKeysStr = implode(', ', $missingRequiredKeys);
                    throw new \Exception("Missing required keys in user data: $missingKeysStr");
                }

                // Si des clés optionnelles manquent, ajoutez-les avec une valeur par défaut
                foreach ($missingOptionalKeys as $optionalKey) {
                    $user[$optionalKey] = '';
                }

                return $user;
            }
        }

        return null;
    }

    private function hasRequiredKeys($user, $requiredKeys)
    {
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $user)) {
                return false;
            }
        }

        return true;
    }

    private function loadUsersFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json');
        return $this->serializer->decode($jsonContent, 'json');
    }
}
