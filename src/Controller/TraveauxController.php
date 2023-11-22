<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TraveauxController extends AbstractController
{
    #[Route('/traveaux', name: 'app_traveaux')]
    public function index(): Response
    {
        return $this->render('traveaux/index.html.twig', [
            'controller_name' => 'TraveauxController',
        ]);
    }
}
