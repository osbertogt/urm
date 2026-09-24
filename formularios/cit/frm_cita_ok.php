<?php
// citas.php
require_once '../../assets/dbc.php';

?>
<!DOCTYPE html>
<html lang="es">
<style>
	.modal-body {
		max-height: 70vh;
		overflow-y: auto;
	}
	.card-header-azul {
		background-color: #2c5aa0;
		color: white;
	}
	.card-header-verde {
		background-color: #28a745;
		color: white;
	}

	.flatpickr-calendar {
	z-index: 2000 !important;
	}
	
	.fp-disabled-sunday {
	  color: red !important;
	  font-weight: bold;
	}

	.fp-disabled-range {
	  color: darkred !important;
	  background-color: #ffe6e6 !important;
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
  <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- 👈 modal más ancho -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar nueva cita</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3"> 
          
          <!-- Card izquierda -->
          <div class="col-md-7">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-chat-dots"></i> Datos de la cita</h6>
              </div>
              <div class="card-body">
                <form id="formCita">
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
					<input type="text" class="form-control" id="fecha" name="fecha" placeholder="Selecciona una fecha">
                  </div>

                  <div class="mb-3">
                    <label>Hora</label>
					<input type="time" name="hora" id="hora" class="form-control" required>
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

                  <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-save"></i> Guardar
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Card derecha -->
          <div class="col-md-5">
            <div class="card shadow-sm h-100">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Citas agendadas</h6>
              </div>
              <div class="card-body">
			   <div id="listaCitas">
				<p class="text-muted">Seleccione un médico y una fecha para ver las citas.</p>
			  </div>
             </div>
            </div>
          </div>
        </div> <!-- /.row -->
      </div> <!-- /.modal-body -->
    </div> <!-- /.modal-content -->
  </div> <!-- /.modal-dialog -->
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
    $.getJSON(FORM_URL + 'cit/citas_registradas_fecha.php', { id_medico, fecha }, function (data) {
      const contenedor = $('#listaCitas'); 
      contenedor.empty(); 

      if (!data || !Array.isArray(data) || data.length === 0) {
        contenedor.html('<p class="text-muted">No hay citas registradas para esta fecha.</p>');
        return;
      }

      // Crear lista
      const lista = $('<ul class="list-group list-group-flush small"></ul>');

      data.forEach(item => {
        const hora = item.hora || '—';
        const nombre = item.paciente_nombre || 'Paciente sin nombre';
        const li = $(`
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock"></i> ${hora}</span>
            <strong>${nombre}</strong>
          </li>
        `);
        lista.append(li);
      });

      contenedor.append(lista);
    }).fail(function () {
      $('#listaCitas').html('<p class="text-danger">Error al cargar las citas.</p>');
    });
  }
});
$('#modalCita').on('show.bs.modal', function () {
  $('#listaCitas').html('<p class="text-muted">Seleccione un médico y una fecha para ver las citas.</p>');
});

  $('#formCita').submit(function(e) {
    e.preventDefault();
    $.post(FORM_URL +'cit/guardar_cita.php', $(this).serialize(), function(resp) {
      $('#modalCita').modal('hide');
      tabla.ajax.reload();
    if (calendar) {
      calendar.refetchEvents(); 
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
    calendar.render(); 
  }
});

  
});

// Inicializar flatpickr siempre al abrir el modal
let fpFecha = null;

$('#modalCita').on('shown.bs.modal', function () {
  // Si ya existe una instancia previa, destrúyela
  if (fpFecha) fpFecha.destroy();

  // Inicialización básica
  fpFecha = flatpickr("#fecha", {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d/m/Y",
    locale: "es",
    disableMobile: true,
    minDate: "today",
    onDayCreate: function(selectedDates, dateStr, instance, dayElem) {
      const date = dayElem.dateObj;
      if (date && date.getDay() === 0) {
        dayElem.classList.add('fp-disabled-sunday');
      }
    }
  });
});

// Cuando cambia el médico, cargar la configuración personalizada
$('#id_medico').change(function () {
  const id_medico = $(this).val();
  if (!id_medico) return;

$.getJSON(FORM_URL + 'cit/fechas_modelo.php', { id_medico }, function (res) {
  const modelo = parseInt(res.modelo);
  const rangos = res.rangos.map(r => ({
    from: r.fecha_inicio,
    to: r.fecha_final
  }));

  let config = {
    dateFormat: "Y-m-d",
    locale: "es",
    altInput: true,
    altFormat: "d/m/Y",
    disableMobile: true,
    minDate: "today",
    onDayCreate: function(selectedDates, dateStr, instance, dayElem) {
      const date = dayElem.dateObj;
      if (!date) return;

      if (date.getDay() === 0) {
        dayElem.classList.add('fp-disabled-sunday');
      }
    }
  };

  // 🔹 Configurar según el modelo
  if (modelo === 1) {
    config.disable = [
      ...rangos,
      function(date) {
        return date.getDay() === 0; // domingos
      }
    ];
  } else if (modelo === 2) {
    config.enable = rangos;
    config.disable = [
      function(date) {
        return date.getDay() === 0;
      }
    ];
  }

  // Inicializar flatpickr una sola vez con la config final
  flatpickr("#fecha", config);
});
  
});



</script>


</body>
</html>
