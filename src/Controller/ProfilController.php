<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(Security $security): Response
{
    if (!$security->isGranted('ROLE_USER')) {
        return $this->redirectToRoute('app_connexion');
    }

    $user = $security->getUser();

    return $this->render('profil/index.html.twig', [
        'user' => $user,
    ]);
}
}
