<?php
// citas.php
require_once '../../assets/dbc.php';

?>
<!DOCTYPE html>
<html lang="es">
<style>
  .modal-header,
  .modal-body {
	background-color: #E0FFFF;
  }

  .modal-header {
	padding: 1rem;
	background-color: #97BBFE;
	color: white;
  }

  .modal-body {
	padding: 1rem; /* igual que el header */
  }

  .modal-content {
	border-radius: 12px;
	overflow: hidden;
  }
</style>

<head>
<!-- FullCalendar CSS y JS con Bootstrap 5 Theme -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/bootstrap5/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>

</head>
<body>
<div class="container mt-4">
  <h4>Gestión de Citas Médicas</h4>
  <button class="btn btn-primary mb-3" id="btnNuevaCita"><i class="bi bi-plus-circle"></i> Nueva Cita</button>
<!-- Botón para abrir el modal -->
<button class="btn btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#modalCalendario">
  <i class="bi bi-calendar4-week"></i>
</button>

  <table id="tablaCitas" class="table table-bordered table-striped">
    <thead>
    <tr>
      <th>Código</th>
      <th>Médico</th>
      <th>Fecha</th>
      <th>Hora</th>
      <th>Paciente</th>
      <th>Teléfono</th>
      <th>Email</th>
      <th>Acciones</th>
    </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal Cita -->
<div class="modal fade" id="modalCita" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formCita">
        <div class="modal-header">
          <h5 class="modal-title">Registrar Cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">

          <div class="mb-3">
            <label>Especialidad</label>
            <select class="form-select" id="id_especialidad" name="id_especialidad"></select>
          </div>
          <div class="mb-3">
            <label>Médico</label>
            <select class="form-select" id="id_medico" name="id_medico"></select>
          </div>
          <div class="mb-3">
            <label>Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha">
          </div>
          <div class="mb-3">
            <label>Horario disponible</label>
            <select class="form-select" id="id_horario" name="id_horario"></select>
          </div>
          <div class="mb-3">
            <label>Nombre del paciente</label>
            <input type="text" class="form-control" name="paciente_nombre" id="paciente_nombre" required>
          </div>
          <div class="mb-3">
            <label>Teléfono</label>
            <input type="text" class="form-control" name="paciente_telefono" id="paciente_telefono" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="paciente_email" id="paciente_email">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Modal con Card -->
<div class="modal fade" id="modalCalendario" tabindex="-1" aria-labelledby="modalCalendarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCalendarioLabel">Calendario de Citas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="card shadow-sm">
          <div class="card-body">
            <div id="calendario" style="min-height: 600px;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let calendar; 
</script>

<script>
$(document).ready(function() {
  const tabla = $('#tablaCitas').DataTable({
    ajax: FORM_URL +'cit/listar_citas.php',
    columns: [
      { data: 'codigo' },
      { data: 'medico' },
      { data: 'fecha' },
      { data: 'hora' },
      { data: 'paciente_nombre' },
      { data: 'paciente_telefono' },
      { data: 'paciente_email' },
      {
        data: null,
        render: function (data) {
          return `
            <button class="btn btn-sm btn-warning btnEditar" data-id="${data.id}"><i class='bi bi-pencil'></i></button>
            <button class="btn btn-sm btn-danger btnCancelar" data-id="${data.id}"><i class='bi bi-x-square'></i></button>
            <button class="btn btn-sm btn-success btnAtender" data-id="${data.id}"><i class='bi-check2-square'></i></button>`;
        },
        orderable: false
      }
    ]
  });

  $('#btnNuevaCita').click(function() {
    $('#formCita')[0].reset();
    $('#id').val('');
    $('#modalCita').modal('show');
  });
  
 $('#modalCita').on('shown.bs.modal', function () {
  $.getJSON(FORM_URL + 'cit/listar_especialidades.php', function (data) {
    const $select = $('#id_especialidad');
    $select.empty().append('<option value="">Seleccione una especialidad</option>');
    data.forEach(function (especialidad) {
      $select.append(`<option value="${especialidad.id}">${especialidad.nombre}</option>`);
    });
  });
});
$('#id_especialidad').change(function () {
  const id_especialidad = $('#id_especialidad').val();
  $.getJSON(FORM_URL + 'cit/medicos_por_especialidad.php', { id_especialidad }, function (data) {
    const $medicoSelect = $('#id_medico');
    $medicoSelect.empty().append('<option value="">Seleccione un médico</option>');
    data.forEach(function (medico) {
      $medicoSelect.append(`<option value="${medico.id}">${medico.nombre}</option>`);
    });
  });
});

$('#id_medico, #fecha').change(function () {
  const id_medico = $('#id_medico').val();
  const fecha = $('#fecha').val();
  if (id_medico && fecha) {
    $.getJSON(FORM_URL + 'cit/horarios_disponibles.php', { id_medico, fecha }, function (data) {
      const $horarioSelect = $('#id_horario');
      $horarioSelect.empty().append('<option value="">Seleccione un horario</option>');
      data.forEach(function (horario) {
        $horarioSelect.append(`<option value="${horario.id}">${horario.hora}</option>`);
      });
    });
  }
});

  $('#formCita').submit(function(e) {
    e.preventDefault();
    $.post(FORM_URL +'cit/guardar_cita.php', $(this).serialize(), function(resp) {
      $('#modalCita').modal('hide');
      tabla.ajax.reload();
    if (calendar) {
      calendar.refetchEvents(); // <--- aquí se fuerza la recarga
    }
    });
  });

  $('#tablaCitas').on('click', '.btnEditar', function() {
    const id = $(this).data('id');
    $.getJSON(FORM_URL +'cit/obtener_cita.php', { id }, function(data) {
      $('#id').val(data.id);
      $('#paciente_nombre').val(data.paciente_nombre);
      $('#paciente_telefono').val(data.paciente_telefono);
      $('#paciente_email').val(data.paciente_email);
      $('#fecha').val(data.fecha);
      $('#id_especialidad').val(data.id_especialidad).trigger('change');
      setTimeout(() => {
        $('#id_medico').val(data.id_medico).trigger('change');
        setTimeout(() => {
          $('#id_horario').val(data.id_horario);
        }, 300);
      }, 300);
      $('#modalCita').modal('show');
    });
  });

  $('#tablaCitas').on('click', '.btnCancelar', function() {
    const id = $(this).data('id');
    $.post(FORM_URL +'cit/cambiar_estado.php', { id, estado: 2 }, function() {
      tabla.ajax.reload();
    });
  });

  $('#tablaCitas').on('click', '.btnAtender', function() {
    const id = $(this).data('id');
    $.post(FORM_URL +'cit/cambiar_estado.php', { id, estado: 3 }, function() {
      tabla.ajax.reload();
    });
  });

  // Cargar especialidades
  $.get(FORM_URL +'cit/listar_especialidades.php', function(html) {
    $('#id_especialidad').html(html);
  });

  // Calendario
  
const modal = document.getElementById('modalCalendario');

modal.addEventListener('shown.bs.modal', function () {
  const calendarEl = document.getElementById('calendario');

  if (!calendar) {
calendar = new FullCalendar.Calendar(calendarEl, {
  initialView: 'dayGridMonth',
  locale: 'es',
  themeSystem: 'bootstrap5',
  events: FORM_URL + 'cit/citas_calendario.php',

  eventDidMount: function (info) {
    // Crear tooltip usando el atributo 'title' o contenido personalizado
    let tooltip = new bootstrap.Tooltip(info.el, {
      title: `Paciente: ${info.event.title}\nHora: ${info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`,
      placement: 'top',
      trigger: 'hover',
      container: 'body'
    });
  }
});
    calendar.render();
  } else {
    calendar.render(); // por si estaba oculto
  }
});

  
  
  
});
</script>


</body>
</html>
