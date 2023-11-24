<?php

// AjoutController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(SessionInterface $session, Request $request): Response
    {
        $userYear = $this->extractYearFromUser($session->get('user_email'));
        $modules = $this->loadModulesFromJson();
        $filteredModules = $this->filterModulesByYear($modules, $userYear);

        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $description = $request->request->get('description');
            $date = $request->request->get('date');
            $module = $request->request->get('_module');
            $tp = $request->request->get('_tp');

            $newData = [
                'titre' => $titre,
                'description' => $description,
                'date' => $date,
                'module' => $module,
                'tp' => $tp,
            ];

            $this->addDataToJson($newData);
        }

        return $this->render('ajout/index.html.twig', [
            'controller_name' => 'AjoutController',
            'modules' => $filteredModules,
        ]);
    }

    private function extractYearFromUser($userEmail)
    {
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
            $moduleYear = preg_replace('/[^0-9]/', '', $module['module']);

            if ($moduleYear >= 100 && floor($moduleYear / 100) == $userYear) {
                $filteredModules[] = $module;
            }
        }

        return $filteredModules;
    }

    private function addDataToJson($newData)
    {
        $jsonData = $this->loadDataFromJson();
        $jsonData[] = $newData;
        $this->saveDataToJson($jsonData);
    }

    private function loadDataFromJson()
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/data.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function saveDataToJson($jsonData)
    {
        $jsonContent = $this->serializer->encode($jsonData, 'json');
        file_put_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/data.json', $jsonContent);
    }
}
