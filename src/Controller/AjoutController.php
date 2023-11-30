<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AjoutController extends AbstractController {
    private $serializer;

    public function __construct(SerializerInterface $serializer) {
        $this->serializer = $serializer;
    }
    
    #[Route('/ajout', name: 'app_ajout')]
    public function index(SessionInterface $session, Request $request): Response {
        // Check if the user is authenticated
        if (!$session->has('user_email')) {
            // Redirect to the login page or handle the unauthenticated user scenario as needed
            return $this->redirectToRoute('app_connexion');
        }
        
        $userYear = $this->extractYearFromUser($session->get('user_email'));
        $modules = $this->loadModulesFromJson();
        $filteredModules = $this->filterModulesByYear($modules, $userYear);

        $selectedDateParam = $request->query->get('date');
        $selectedDate = null;

        if($selectedDateParam) {
            // Convertir la date passée en paramètre GET au format adapté pour datetime-local
            $selectedDate = new \DateTime($selectedDateParam, new \DateTimeZone('Europe/Paris')); // Utilisez le fuseau horaire Europe/Paris ici

            if($selectedDate === false) {
                // Gérer l'erreur de format de date ici
                throw new \Exception('Format de date invalide');
            }

            $selectedDate = $selectedDate->format('Y-m-d\TH:i:s');
        }


        $selectedTp = $request->request->get('_tp', $session->get('user_tp')); // Utiliser le TP de l'utilisateur par défaut

        if($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $description = $request->request->get('description');
            $date = $request->request->get('date');
            $module = $request->request->get('_module');
            $tp = $request->request->get('_tp');
            $typeRendu = $request->request->get('_typeRendu'); // Nouveau champ

            $newData = [
                'titre' => $titre,
                'description' => $description,
                'date' => $date,
                'module' => $module,
                'tp' => $tp,
                'typeRendu' => $typeRendu, // Ajout du type de rendu
            ];

            if($this->addDataToJson($newData)) {
                $this->addFlash('success', 'Date ajoutée avec succès !');
                return $this->redirectToRoute('app_ajout');
            } else {
                $this->addFlash('error', 'Une erreur s\'est produite. Veuillez réessayer.');
            }
        }

        return $this->render('ajout/index.html.twig', [
            'controller_name' => 'AjoutController',
            'modules' => $filteredModules,
            'selectedDate' => $selectedDate,
            'tpOptions' => $this->getTpOptions(),
            'selectedTp' => $selectedTp,
        ]);
    }

    private function extractYearFromUser($userEmail) {
        return preg_replace('/[^0-9]/', '', $this->findUserByEmail($userEmail)['year']);
    }

    private function findUserByEmail($email) {
        $users = $this->loadUsersFromJson();

        foreach($users as $user) {
            if($email === $user['email']) {
                return $user;
            }
        }

        return null;
    }

    private function loadModulesFromJson() {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir').'/public/assets/json/cours.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function loadUsersFromJson() {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir').'/public/assets/json/users.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function filterModulesByYear($modules, $userYear) {
        $filteredModules = [];

        foreach($modules as $module) {
            $moduleYear = preg_replace('/[^0-9]/', '', $module['module']);

            if($moduleYear >= 100 && floor($moduleYear / 100) == $userYear) {
                $filteredModules[] = $module;
            }
        }

        return $filteredModules;
    }

    private function addDataToJson($newData) {
        $jsonData = $this->loadDataFromJson();
        $jsonData[] = $newData;
        $this->saveDataToJson($jsonData);

        // Vous pouvez ajouter ici une logique de validation ou de traitement
        // et retourner true si tout est ok, sinon false.
        return true;
    }

    private function getTpOptions() {
        // Charger les données TP à partir du fichier JSON
        $jsonData = $this->loadDataFromJson();

        // Extraire les TP uniques du jeu de données
        $tpOptions = array_unique(array_column($jsonData, 'tp'));

        // Trier les TP par ordre alphabétique
        sort($tpOptions);

        return $tpOptions;
    }

    private function loadDataFromJson() {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir').'/public/assets/json/data.json');
        return $this->serializer->decode($jsonContent, 'json');
    }

    private function saveDataToJson($jsonData) {
        $jsonContent = $this->serializer->encode($jsonData, 'json');
        file_put_contents($this->getParameter('kernel.project_dir').'/public/assets/json/data.json', $jsonContent);
    }
}
