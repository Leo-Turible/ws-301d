<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FiltrerController extends AbstractController {
    #[Route('/filtrer', name: 'app_filtrer')]
    public function index(Request $request, SessionInterface $session, SerializerInterface $serializer): Response {
        // Récupérer les données de data.json
        $jsonData = $this->loadDataFromJson($serializer);

        // Récupérer les données de cours.json
        $coursesJsonContent = file_get_contents($this->getParameter('kernel.project_dir').'/public/assets/json/cours.json');
        $coursesData = $serializer->decode($coursesJsonContent, 'json');

        // Récupérer le TP de l'utilisateur connecté
        $userTp = $session->get('user_tp');

        // Récupérer la semaine sélectionnée, le TP sélectionné et le module sélectionné
        $selectedWeek = $request->request->get('date');
        $selectedTp = $request->request->get('tp', $userTp); // Utiliser le TP de l'utilisateur par défaut
        $selectedModule = $request->request->get('module');

        // Filtrer les travaux en fonction de la semaine, du TP et du module
        $filteredData = $this->filterData($jsonData, $selectedWeek, $selectedTp, $selectedModule);

        // Récupérer la liste des TP disponibles pour le formulaire
        $tpOptions = $this->getTpOptions($jsonData);

        // Récupérer la liste des modules disponibles pour le formulaire
        // $moduleOptions = $this->getModuleOptions($jsonData);

        // Obtenir la date stockée dans localStorage ou utiliser la date actuelle
        $storedDate = $request->getSession()->get('stored_date');
        $currentWeek = $storedDate ?: (new \DateTime())->format('Y-\WW');

        // Récupérer la liste des modules disponibles pour le formulaire
        $moduleOptions = $this->getModuleOptions($jsonData, $coursesData);

        return $this->render('filtrer/index.html.twig', [
            'controller_name' => 'FiltrerController',
            'filteredData' => $filteredData,
            'tpOptions' => $tpOptions,
            'selectedTp' => $selectedTp,
            'currentWeek' => $currentWeek,
            'moduleOptions' => $moduleOptions,
            'selectedModule' => $selectedModule,
        ]);
    }

    private function filterData($jsonData, $selectedWeek, $selectedTp, $selectedModule) {
        // Filtrer les travaux en fonction de la semaine, du TP et du module
        $filteredData = array_filter($jsonData, function ($work) use ($selectedWeek, $selectedTp, $selectedModule) {
            $weekMatches = !$selectedWeek || $this->isInWeek($work['date'], $selectedWeek);
            $tpMatches = $work['tp'] == $selectedTp;
            $moduleMatches = !$selectedModule || $work['module'] == $selectedModule;

            return $weekMatches && $tpMatches && $moduleMatches;
        });

        // Trier les données par date
        usort($filteredData, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $filteredData;
    }

    private function isInWeek($date, $selectedWeek) {
        $dateTime = new \DateTime($date);
        $week = $dateTime->format('W');
        $year = $dateTime->format('Y');

        return $selectedWeek == "$year-W$week";
    }

    private function getTpOptions($jsonData) {
        // Extraire les TP uniques du jeu de données
        $tpOptions = array_unique(array_column($jsonData, 'tp'));

        // Trier les TP par ordre alphabétique
        sort($tpOptions);

        return $tpOptions;
    }

    private function getModuleOptions($jsonData, $coursesData) {
        // Extraire les modules uniques du jeu de données
        $moduleOptions = array_unique(array_column($jsonData, 'module'));

        // Trier les modules par ordre alphabétique
        sort($moduleOptions);

        // Associer le nom du cours à chaque module
        $moduleOptionsWithCourse = [];
        foreach($moduleOptions as $module) {
            $courseInfo = $this->findCourseInfo($coursesData, $module);
            $nomCours = $courseInfo ? $courseInfo['nomCours'] : 'Nom de cours non trouvé';
            $moduleOptionsWithCourse[$module] = ['nomCours' => $nomCours];
        }

        return $moduleOptionsWithCourse;
    }


    private function loadDataFromJson(SerializerInterface $serializer) {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir').'/public/assets/json/data.json');
        $jsonData = $serializer->decode($jsonContent, 'json');

        // Charger les informations supplémentaires du cours.json
        $coursesJsonContent = file_get_contents($this->getParameter('kernel.project_dir').'/public/assets/json/cours.json');
        $coursesData = $serializer->decode($coursesJsonContent, 'json');

        // Associer les informations du cours au tableau principal
        foreach($jsonData as &$work) {
            $module = $work['module'] ?? null;
            $courseInfo = $this->findCourseInfo($coursesData, $module);

            if($courseInfo) {
                $work['nomCours'] = $courseInfo['nomCours'];
            } else {
                $work['nomCours'] = 'Nom de cours non trouvé';
            }
        }

        return $jsonData;
    }

    private function findCourseInfo($coursesData, $module) {
        foreach($coursesData as $course) {
            if($course['module'] === $module) {
                return $course;
            }
        }

        return null;
    }

}
