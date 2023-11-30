<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CalendrierController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendrier')]
    public function index(SessionInterface $session): Response
    {
        // Check if the user is authenticated
        if (!$session->has('user_email')) {
            // Redirect to the login page or handle the unauthenticated user scenario as needed
            return $this->redirectToRoute('app_connexion');
        }
        return $this->render('calendrier/index.html.twig', [
            'controller_name' => 'CalendrierController',
        ]);
    }
}
