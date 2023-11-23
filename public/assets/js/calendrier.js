document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth'
    });

    // Charger les données JSON depuis un fichier externe
    fetch('http://sae301.mmi-troyes.fr:8313/assets/json/data.json')
        .then(response => response.json())
        .then(jsonData => {
            // Fonction pour colorier les cases du calendrier
            function colorierCases() {
                console.log('Chargement des données JSON :', jsonData);

                jsonData.forEach(function (event) {
                    // Convertir la date du format JSON en objet Date
                    var eventDate = new Date(event.date);
                    console.log('Date de l\'événement :', eventDate);

                    // Ajouter l'événement au calendrier
                    calendar.addEvent({
                        title: event.cours,
                        start: event.date,
                        backgroundColor: 'blue',  // Couleur que vous souhaitez utiliser
                        borderColor: 'blue'  // Couleur de la bordure, si nécessaire
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
