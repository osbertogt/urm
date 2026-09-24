document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendario');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
    events: FORM_URL + 'cit/citas_calendario.php'
  });
  calendar.render();
});
