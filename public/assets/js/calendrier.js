document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth'
    });
    calendar.render();

    // Appelle la fonction pour charger et colorer les dates à partir du fichier JSON
    loadAndColorDates('../json/data.json', calendar);
});

function loadAndColorDates(jsonFilePath, calendar) {
    // Charge le fichier JSON
    fetch(jsonFilePath)
        .then(response => response.json())
        .then(data => {
            // Parcourt les événements dans le fichier JSON
            data.events.forEach(event => {
                // Ajoute un événement au calendrier avec la couleur spécifiée
                calendar.addEvent({
                    title: event.title,
                    start: event.date,
                    color: event.color // Assure-toi que la propriété color est présente dans ton fichier JSON
                });
            });
        })
        .catch(error => console.error('Erreur lors du chargement du fichier JSON', error));
}

  