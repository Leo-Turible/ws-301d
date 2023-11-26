document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var infoDiv = document.getElementById('event-info');
    var selectedDate;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        eventClick: function (info) {
            var formattedDate = new Intl.DateTimeFormat('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: false
            }).format(info.event.start);

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

            if (info.event.start) {
                appendCloseButton();

                var addButton = document.createElement('button');
                addButton.textContent = 'Ajouter';

                infoDiv.appendChild(addButton);

                infoDiv.classList.add('show');

                selectedDate = info.event.start;

                addButton.addEventListener('click', function () {
                    // Normaliser la date pour éviter les problèmes de fuseau horaire
                    var normalizedDate = new Date(selectedDate.getTime() - selectedDate.getTimezoneOffset() * 60000);
                    window.location.href = '/ajout?date=' + normalizedDate.toISOString();
                });
            }

            infoDiv.querySelector('.event-info__close').addEventListener('click', function () {
                // Supprimer tous les éléments de la div event-info
                infoDiv.innerHTML = '';
                infoDiv.classList.remove('show');
            });
        },
        dateClick: function (info) {
            var dayEvents = calendar.getEvents().filter(event => {
                return event.start.getDate() === info.date.getDate();
            });

            dayEvents = sortByDateAndTime(dayEvents);

            var eventsHtml = dayEvents.map(event => {
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
                    <hr style="margin: 1rem 0;">
                `;
            }).join('');

            infoDiv.innerHTML = eventsHtml;

            if (dayEvents.length === 0) {
                infoDiv.innerHTML += '<p>Aucun travail pour le moment</p>';
            }

            if (info.date) {
                appendCloseButton();

                var addButton = document.createElement('button');
                addButton.textContent = 'Ajouter';

                infoDiv.appendChild(addButton);

                infoDiv.classList.add('show');

                selectedDate = info.date;

                addButton.addEventListener('click', function () {
                    // Normaliser la date pour éviter les problèmes de fuseau horaire
                    var normalizedDate = new Date(selectedDate.getTime() - selectedDate.getTimezoneOffset() * 60000);
                    window.location.href = '/ajout?date=' + normalizedDate.toISOString();
                });
            }

            infoDiv.querySelector('.event-info__close').addEventListener('click', function () {
                // Supprimer tous les éléments de la div event-info
                infoDiv.innerHTML = '';
                infoDiv.classList.remove('show');
            });
        }

    });

    infoDiv.addEventListener('click', function (event) {
        if (event.target.classList.contains('event-info__close')) {
            // Supprimer tous les éléments de la div event-info
            infoDiv.innerHTML = '';
            infoDiv.classList.remove('show');
        }
    });

    function appendCloseButton() {
        var closeButton = document.createElement('div');
        closeButton.innerHTML = '&times;';
        closeButton.classList.add('event-info__close');

        infoDiv.insertBefore(closeButton, infoDiv.firstChild);
    }

    function sortByDateAndTime(events) {
        return events.sort((a, b) => a.start - b.start);
    }

    Promise.all([
        fetch('http://sae301.mmi-troyes.fr:8313/assets/json/data.json').then(response => response.json()),
        fetch('http://sae301.mmi-troyes.fr:8313/assets/json/cours.json').then(response => response.json())
    ])
        .then(async ([jsonData, coursData]) => {
            async function colorierCases() {
                console.log('Chargement des données JSON :', jsonData);

                const response = await fetch('/get-user-tp');
                const data = await response.json();
                const userTp = data.user_tp;

                jsonData.forEach(function (event) {
                    if (event.tp === userTp) {
                        var coursModule = coursData.find(cours => cours.module === event.module);

                        calendar.addEvent({
                            title: event.module,
                            start: event.date,
                            backgroundColor: 'blue',
                            borderColor: 'blue',
                            extendedProps: {
                                titre: event.titre,
                                description: event.description,
                                module: event.module,
                                nomCours: coursModule ? coursModule.nomCours : 'Cours inconnu',
                                tp: event.tp,
                                typeRendu: event.typeRendu
                            }
                        });
                    }
                });
            }

            colorierCases();

            calendar.render();
        })
        .catch(error => console.error('Erreur lors du chargement du fichier JSON:', error));
});
