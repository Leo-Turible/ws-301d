document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var infoDiv = document.getElementById('event-info'); // Ajout de la référence à la div d'information
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        eventClick: function (info) {
            // Convertir la date en format local
            var formattedDate = new Intl.DateTimeFormat('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: false
            }).format(info.event.start);

            // Afficher les informations de l'événement dans une div
            infoDiv.innerHTML = `
                <strong>Titre:</strong> ${info.event.title}<br>
                <strong>Description:</strong> ${info.event.extendedProps.description || 'Aucune description disponible'}<br>
                <strong>Date et Heure:</strong> ${formattedDate}<br>
                <strong>Module:</strong> ${info.event.extendedProps.module} - ${info.event.extendedProps.nomCours}<br>
                <strong>TP:</strong> ${info.event.extendedProps.tp || 'Non spécifié'}
            `;
        }
    });

    // Charger les données JSON depuis un fichier externe
    Promise.all([
        fetch('http://sae301.mmi-troyes.fr:8313/assets/json/data.json').then(response => response.json()),
        fetch('http://sae301.mmi-troyes.fr:8313/assets/json/cours.json').then(response => response.json())
    ])
    .then(([jsonData, coursData]) => {
        // Fonction pour colorier les cases du calendrier
        function colorierCases() {
            console.log('Chargement des données JSON :', jsonData);

            jsonData.forEach(function (event) {
                // Trouver le cours correspondant dans cours.json
                var coursModule = coursData.find(cours => cours.module === event.module);

                // Convertir la date du format JSON en objet Date
                var eventDate = new Date(event.date);
                console.log('Date de l\'événement :', eventDate);

                // Ajouter l'événement au calendrier
                calendar.addEvent({
                    title: event.module,
                    start: event.date,
                    backgroundColor: 'blue',  // Couleur que vous souhaitez utiliser
                    borderColor: 'blue',  // Couleur de la bordure, si nécessaire
                    extendedProps: {
                        description: event.description,
                        module: event.module,
                        nomCours: coursModule ? coursModule.nomCours : 'Cours inconnu',
                        tp: event.tp
                    }
                });
            });
        }

        // Appeler la fonction pour colorier les cases du calendrier
        colorierCases();

        // Rendre le calendrier
        calendar.render();
    })
    .catch(error => console.error('Erreur lors du chargement du fichier JSON:', error));
});
