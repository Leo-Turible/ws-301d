var eventsUrl = 'date.json';

      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          events: eventsUrl,
          eventRender: function(info) {
            // Définir la couleur pour toutes les dates
            info.el.style.backgroundColor = 'red';
          },
        });
        calendar.render();
      });