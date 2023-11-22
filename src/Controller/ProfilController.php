<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(UserInterface $user): Response
    {
        dump($user->getRoles()); // Debugging line
        if (!$this->isGranted('ROLE_USER')) {
            // Redirect or handle unauthorized access
            throw $this->createAccessDeniedException('Access Denied.');
        }

        return $this->render('profil/index.html.twig', [
            'user' => $user,
        ]);
    }
}
