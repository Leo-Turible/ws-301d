<?php

// AjoutController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $userYear = $this->extractYearFromUser($session->get('user_email'));

        // Vous devez ajuster cette partie en fonction de la structure réelle de vos données
        $modules = $this->loadModulesFromJson();
        $filteredModules = $this->filterModulesByYear($modules, $userYear);

        return $this->render('ajout/index.html.twig', [
            'controller_name' => 'AjoutController',
            'modules' => $filteredModules,
        ]);
    }

    private function extractYearFromUser($userEmail)
    {
        // Exemple : "BUT Semestre 3" deviendra 3
        return preg_replace('/[^0-9]/', '', $this->findUserByEmail($userEmail)['year']);
    }

    private function findUserByEmail($email)
    {
        $users = $this->loadUsersFromJson();

        foreach ($users as $user) {
            if ($email === $user['email']) {
                return $user;
            }
        }

        return null;
    }

    private function loadModulesFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/cours.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function loadUsersFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/users.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function filterModulesByYear($modules, $userYear)
    {
        $filteredModules = [];

        foreach ($modules as $module) {
            // Exemple : "WS303D" deviendra 3
            $moduleYear = preg_replace('/[^0-9]/', '', $module['module']);

            // Comparaison des centaines
            if ($moduleYear >= 100 && floor($moduleYear / 100) == $userYear) {
                $filteredModules[] = $module;
            }
        }

        return $filteredModules;
    }
}
