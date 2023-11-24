document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var infoDiv = document.getElementById('event-info');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        eventClick: function (info) {
            // Convertir la date du format JSON en objet Date
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
                <div class="event-info__content">
                    <strong>Titre:</strong> ${info.event.extendedProps.titre}<br>
                    <strong>Description:</strong> ${info.event.extendedProps.description || 'Aucune description disponible'}<br>
                    <strong>Date et Heure:</strong> ${formattedDate}<br>
                    <strong>Module:</strong> ${info.event.extendedProps.module} - ${info.event.extendedProps.nomCours}<br>
                    <strong>TP:</strong> ${info.event.extendedProps.tp || 'Non spécifié'}<br>
                    <strong>Type de Rendu:</strong> ${info.event.extendedProps.typeRendu || 'Non spécifié'}<br>
                </div>
                <hr>
            `;

            // Ajouter la croix à la div d'information
            appendCloseButton();

            // Ajouter le bouton "Ajouter"
            var addButton = document.createElement('button');
            addButton.textContent = 'Ajouter';

            // Ajouter le bouton à la div d'information
            infoDiv.appendChild(addButton);

            // Ajouter la classe "show" à la div d'information
            infoDiv.classList.add('show');

            // Ajouter l'événement de clic au bouton "Ajouter"
            addButton.addEventListener('click', function () {
                // Rediriger vers app_ajout
                window.location.href = '/ajout';  // Remplacez par l'URL réelle si nécessaire
            });

            // Ajouter l'événement de clic à la croix pour fermer la div
            infoDiv.querySelector('.event-info__close').addEventListener('click', function () {
                infoDiv.classList.remove('show');
            });
        },
        dateClick: function (info) {
            // Filtrer les événements du jour
            var dayEvents = calendar.getEvents().filter(event => {
                return event.start.getDate() === info.date.getDate();
            });

            // Construire le HTML avec les informations des événements du jour
            var eventsHtml = dayEvents.map(event => {
                // Convertir la date du format JSON en objet Date
                var formattedDate = new Intl.DateTimeFormat('fr-FR', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: false
                }).format(event.start);

                return `
                    <div class="event-info__content">
                        <strong>Titre:</strong> ${event.extendedProps.titre}<br>
                        <strong>Description:</strong> ${event.extendedProps.description || 'Aucune description disponible'}<br>
                        <strong>Date et Heure:</strong> ${formattedDate}<br>
                        <strong>Module:</strong> ${event.extendedProps.module} - ${event.extendedProps.nomCours}<br>
                        <strong>TP:</strong> ${event.extendedProps.tp || 'Non spécifié'}<br>
                        <strong>Type de Rendu:</strong> ${event.extendedProps.typeRendu || 'Non spécifié'}<br>
                    </div>
                    <hr>
                `;
            }).join('');

            // Ajouter les informations à la div d'information (sans la croix)
            infoDiv.innerHTML = eventsHtml;

            // Ajouter la croix à la div d'information
            appendCloseButton();

            // Ajouter le bouton "Ajouter"
            var addButton = document.createElement('button');
            addButton.textContent = 'Ajouter';

            // Ajouter le bouton à la div d'information
            infoDiv.appendChild(addButton);

            // Ajouter la classe "show" à la div d'information
            infoDiv.classList.add('show');

            // Ajouter l'événement de clic au bouton "Ajouter"
            addButton.addEventListener('click', function () {
                // Rediriger vers app_ajout
                window.location.href = '/ajout';  // Remplacez par l'URL réelle si nécessaire
            });

            // Ajouter l'événement de clic à la croix pour fermer la div
            infoDiv.querySelector('.event-info__close').addEventListener('click', function () {
                infoDiv.classList.remove('show');
            });
        }
    });

    // Ajouter un gestionnaire d'événements délégué pour détecter les clics sur la croix
    infoDiv.addEventListener('click', function (event) {
        if (event.target.classList.contains('event-info__close')) {
            infoDiv.classList.remove('show');
        }
    });

    // Fonction pour ajouter la croix à la div d'information
    function appendCloseButton() {
        // Ajouter la croix pour fermer la div
        var closeButton = document.createElement('div');
        closeButton.innerHTML = '&times;';
        closeButton.classList.add('event-info__close');

        // Ajouter la croix à la div d'information (avant les informations)
        infoDiv.insertBefore(closeButton, infoDiv.firstChild);
    }

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

                // Ajouter l'événement au calendrier
                calendar.addEvent({
                    title: event.module,
                    start: event.date,
                    backgroundColor: 'blue',  // Couleur que vous souhaitez utiliser
                    borderColor: 'blue',  // Couleur de la bordure, si nécessaire
                    extendedProps: {
                        titre: event.titre,
                        description: event.description,
                        module: event.module,
                        nomCours: coursModule ? coursModule.nomCours : 'Cours inconnu',
                        tp: event.tp,
                        typeRendu: event.typeRendu  // Assurez-vous que cette propriété existe dans data.json
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
