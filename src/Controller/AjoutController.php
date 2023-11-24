<?php

// AjoutController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AjoutController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/ajout', name: 'app_ajout')]
    public function index(SessionInterface $session): Response
    {
        // Charger les données des fichiers cours.json et users.json
        $cours = $this->loadCoursFromJson();
        $users = $this->loadUsersFromJson();

        // Récupérer l'année de l'utilisateur actuellement connecté
        $userEmail = $session->get('user_email');
        $userYear = $this->getUserYear($userEmail, $users);

        // Filtrer les modules en fonction de l'année de l'utilisateur
        $filteredModules = $this->filterModulesByYear($cours, $userYear);

        return $this->render('ajout/index.html.twig', [
            'controller_name' => 'AjoutController',
            'modules' => $filteredModules,
        ]);
    }

    private function loadCoursFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/cours.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function loadUsersFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function getUserYear($email, $users)
    {
        foreach ($users as $user) {
            if ($email === $user['email']) {
                // Récupérer l'année depuis le profil de l'utilisateur
                return $this->extractYearFromModule($user['year']);
            }
        }

        return null;
    }

    private function filterModulesByYear($cours, $userYear)
    {
        $filteredModules = [];

        foreach ($cours as $module) {
            $moduleYear = $this->extractYearFromModule($module['module']);

            // Comparer le chiffre des centaines avec celui de l'année de l'utilisateur
            if ($moduleYear === $userYear) {
                $filteredModules[] = $module;
            }
        }

        return $filteredModules;
    }

    private function extractYearFromModule($module)
    {
        // Exclure la chaîne "BUT Semestre" et récupérer le chiffre à la fin
        $cleanModule = preg_replace('/BUT Semestre (\d+)/', '$1', $module);

        // Exclure les caractères non numériques
        return (int) preg_replace('/\D/', '', $cleanModule);
    }
}
