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

        // Récupérer le TP de l'utilisateur connecté
        $userTp = $session->get('user_tp');

        // Récupérer la semaine sélectionnée et le TP sélectionné
        $selectedWeek = $request->request->get('date');
        $selectedTp = $request->request->get('tp', $userTp); // Utiliser le TP de l'utilisateur par défaut

        // Filtrer les travaux en fonction de la semaine et du TP
        $filteredData = $this->filterData($jsonData, $selectedWeek, $selectedTp);

        // Récupérer la liste des TP disponibles pour le formulaire
        $tpOptions = $this->getTpOptions($jsonData);

        return $this->render('filtrer/index.html.twig', [
            'controller_name' => 'FiltrerController',
            'filteredData' => $filteredData,
            'tpOptions' => $tpOptions,
            'selectedTp' => $selectedTp,
        ]);
    }

    private function filterData($jsonData, $selectedWeek, $selectedTp) {
        // Filtrer les travaux en fonction de la semaine et du TP
        $filteredData = array_filter($jsonData, function ($work) use ($selectedWeek, $selectedTp) {
            $weekMatches = !$selectedWeek || $this->isInWeek($work['date'], $selectedWeek);
            $tpMatches = $work['tp'] == $selectedTp;

            return $weekMatches && $tpMatches;
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

    private function loadDataFromJson(SerializerInterface $serializer) {
        $jsonContent = file_get_contents($this->getParameter('kernel.project_dir').'/public/assets/json/data.json');
        return $serializer->decode($jsonContent, 'json');
    }

    // ... (les autres méthodes de votre contrôleur)
}
