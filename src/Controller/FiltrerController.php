<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FiltrerController extends AbstractController
{
    #[Route('/filtrer', name: 'app_filtrer')]
    public function index(Request $request, SessionInterface $session, SerializerInterface $serializer): Response
    {
        // Récupérer les données de data.json
        $jsonData = $this->loadDataFromJson($serializer);

        // Récupérer les données de cours.json
        $coursesJsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/cours.json');
        $coursesData = $serializer->decode($coursesJsonContent, 'json');

        // Récupérer le TP de l'utilisateur connecté
        $userTp = $session->get('user_tp');

        // Récupérer la semaine sélectionnée, le TP sélectionné, le module sélectionné et le type de rendu sélectionné
        $selectedWeek = $request->request->get('date');
        $selectedTp = $request->request->get('tp', $userTp); // Utiliser le TP de l'utilisateur par défaut
        $selectedModule = $request->request->get('module');
        $selectedTypeRendu = $request->request->get('typeRendu');

        // Filtrer les travaux en fonction de la semaine, du TP, du module et du type de rendu
        $filteredData = $this->filterData($jsonData, $selectedWeek, $selectedTp, $selectedModule, $selectedTypeRendu);

        // Récupérer la liste des TP disponibles pour le formulaire
        $tpOptions = $this->getTpOptions($jsonData);

        // Récupérer la liste des modules disponibles pour le formulaire
        $moduleOptions = $this->getModuleOptions($jsonData, $coursesData);

        // Récupérer la liste des types de rendu disponibles pour le formulaire
        $typeRenduOptions = $this->getTypeRenduOptions($jsonData);

        // Obtenir la date stockée dans localStorage ou utiliser la date actuelle
        $storedDate = $request->getSession()->get('stored_date');
        $currentWeek = $storedDate ?: (new \DateTime())->format('Y-\WW');

        return $this->render('filtrer/index.html.twig', [
            'controller_name' => 'FiltrerController',
            'filteredData' => $filteredData,
            'tpOptions' => $tpOptions,
            'selectedTp' => $selectedTp,
            'currentWeek' => $currentWeek,
            'moduleOptions' => $moduleOptions,
            'selectedModule' => $selectedModule,
            'typeRenduOptions' => $typeRenduOptions,
            'selectedTypeRendu' => $selectedTypeRendu,
        ]);
    }

    private function filterData($jsonData, $selectedWeek, $selectedTp, $selectedModule, $selectedTypeRendu)
    {
        // Filtrer les travaux en fonction de la semaine, du TP, du module et du type de rendu
        $filteredData = array_filter($jsonData, function ($work) use ($selectedWeek, $selectedTp, $selectedModule, $selectedTypeRendu) {
            $weekMatches = !$selectedWeek || $this->isInWeek($work['date'], $selectedWeek);
            $tpMatches = $work['tp'] == $selectedTp;
            $moduleMatches = !$selectedModule || $work['module'] == $selectedModule;
        
            // Vérifier si la clé 'typeRendu' existe avant de l'utiliser
            $typeRenduMatches = !$selectedTypeRendu || (isset($work['typeRendu']) && $work['typeRendu'] == $selectedTypeRendu);
        
            return $weekMatches && $tpMatches && $moduleMatches && $typeRenduMatches;
        });
        

        // Trier les données par date
        usort($filteredData, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $filteredData;
    }

    private function isInWeek($date, $selectedWeek)
    {
        $dateTime = new \DateTime($date);
        $week = $dateTime->format('W');
        $year = $dateTime->format('Y');

        return $selectedWeek == "$year-W$week";
    }

    private function getTpOptions($jsonData)
    {
        // Extraire les TP uniques du jeu de données
        $tpOptions = array_unique(array_column($jsonData, 'tp'));

        // Trier les TP par ordre alphabétique
        sort($tpOptions);

        return $tpOptions;
    }

    private function getModuleOptions($jsonData, $coursesData)
    {
        // Extraire les modules uniques du jeu de données
        $moduleOptions = array_unique(array_column($jsonData, 'module'));

        // Trier les modules par ordre alphabétique
        sort($moduleOptions);

        // Associer le nom du cours à chaque module
        $moduleOptionsWithCourse = [];
        foreach ($moduleOptions as $module) {
            $courseInfo = $this->findCourseInfo($coursesData, $module);
            $nomCours = $courseInfo ? $courseInfo['nomCours'] : 'Nom de cours non trouvé';
            $moduleOptionsWithCourse[$module] = ['nomCours' => $nomCours];
        }

        return $moduleOptionsWithCourse;
    }

    private function getTypeRenduOptions($jsonData)
    {
        // Extraire les types de rendu uniques du jeu de données
        $typeRenduOptions = array_unique(array_column($jsonData, 'typeRendu'));

        // Trier les types de rendu par ordre alphabétique
        sort($typeRenduOptions);

        return $typeRenduOptions;
    }

    private function loadDataFromJson(SerializerInterface $serializer)
    {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/data.json');
        $jsonData = $serializer->decode($jsonContent, 'json');

        // Charger les informations supplémentaires du cours.json
        $coursesJsonContent = file_get_contents($this->getParameter('kernel.project_dir') . '/public/assets/json/cours.json');
        $coursesData = $serializer->decode($coursesJsonContent, 'json');

        // Associer les informations du cours au tableau principal
        foreach ($jsonData as &$work) {
            $module = $work['module'] ?? null;
            $courseInfo = $this->findCourseInfo($coursesData, $module);

            if ($courseInfo) {
                $work['nomCours'] = $courseInfo['nomCours'];
            } else {
                $work['nomCours'] = 'Nom de cours non trouvé';
            }
        }

        return $jsonData;
    }

    private function findCourseInfo($coursesData, $module)
    {
        foreach ($coursesData as $course) {
            if ($course['module'] === $module) {
                return $course;
            }
        }

        return null;
    }
}
