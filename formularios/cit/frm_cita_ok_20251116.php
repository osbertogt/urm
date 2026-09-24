<?php
// citas.php
require_once '../../assets/dbc.php';
if(!isset($_SESSION)) 
{ 
    session_start(); 
} 
$_SESSION['paciente_activo']=0;
$usuario=$_SESSION['usuario'];	
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
	  <th>Estado</th>
      <th>Acciones</th>
    </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal Cita -->
<div class="modal fade" id="modalCita" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered"> 
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
					<input type="hidden" name="usuario" id="usuario" value=""<?php echo $usuario;?>>

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
					  <label>Paciente</label>
					  <select class="form-select" id="id_paciente" name="id_paciente" style="width: 100%;"></select>
					</div>

					<hr>
					<p class="text-muted text-center small mb-3">
					  O bien, ingrese los datos si es un paciente nuevo:
					</p>

					<div class="mb-3">
					  <label>Nombre del paciente</label>
					  <input type="text" class="form-control" name="paciente_nombre" id="paciente_nombre">
					</div>

					<div class="mb-3">
					  <label>Teléfono</label>
					  <input type="text" class="form-control" name="paciente_telefono" id="paciente_telefono">
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

<!-- Modal pequeño para asignar o crear paciente -->
<div class="modal fade" id="modalSeleccionpaciente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h6 class="modal-title mb-0"><i class="bi bi-person-plus"></i> Asignar o crear paciente</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAsignarpaciente">
          <input type="hidden" id="id_cita_asignar" name="id_cita">

          <div class="mb-3">
            <label class="form-label">Seleccione paciente</label>
            <div class="input-group">
              <select id="id_paciente_modal" name="id_cliente" class="form-select" style="width: 100%;" required></select>
              <button type="button" class="btn btn-outline-success" id="btnNuevopaciente">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>

          <!-- Formulario para crear nuevo paciente -->
          <div id="nuevopacienteContainer" class="border rounded p-2 bg-light d-none">
            <h6 class="text-primary mb-2">Nuevo paciente</h6>
            <div class="mb-2">
              <input type="text" id="nombre_nuevo" name="nombre_nuevo" class="form-control form-control-sm" placeholder="Nombre " >
            </div>
            <div class="mb-2">
              <input type="text" id="apellido_nuevo" name="apellido_nuevo" class="form-control form-control-sm" placeholder="Apellido " >
            </div>
            <div class="mb-2">
              <input type="text" id="telefono_nuevo" name="telefono_nuevo" class="form-control form-control-sm" placeholder="Teléfono">
            </div>
            <div class="mb-2">
              <input type="email" id="email_nuevo" name="email_nuevo" class="form-control form-control-sm" placeholder="Correo electrónico">
            </div>
            <div class="text-end">
              <button type="button" class="btn btn-success btn-sm" id="btnGuardarNuevopaciente">
                <i class="bi bi-check-circle"></i> Guardar paciente
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCancelarNuevopaciente">
                Cancelar
              </button>
            </div>
          </div>

          <hr>
          <div class="text-end">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="bi bi-save"></i> Asignar paciente
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>

$(document).ready(function() {
	
	if ($.fn.DataTable.isDataTable('#tablaCitas')) {
		$('#tablaCitas').DataTable().destroy();
	}

    var tabla = $('#tablaCitas').DataTable({
    ajax: FORM_URL +'cit/listar_citas.php',
    columns: [
      { data: 'medico' },
      { data: 'fecha' },
      { data: 'hora' },
      { data: 'paciente_nombre' },
      { data: 'paciente_telefono' },
      { data: 'paciente_email' },
      { data: 'estado' },
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
    });
  });

  $('#tablaCitas').on('click', '.btnEditar', function() {
    const id = $(this).data('id');
    $.getJSON(FORM_URL +'cit/obtener_cita.php', { id }, function(data) {
      $('#id').val(data.id);
      $('#paciente_nombre').val(data.paciente_nombre);
      $('#paciente_telefono').val(data.paciente_telefono);
      $('#paciente_email').val(data.paciente_email);
    const fp = $('#fecha')[0]._flatpickr;
    if (fp) {
        fp.setDate(data.fecha);
    } else {
        $('#fecha').val(data.fecha);
    }
	  $('#id_especialidad').val(data.id_especialidad);
	  $('#id_medico').val(data.id_medico);
      $('#hora').val(data.hora);
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
	  const id_cita = $(this).data('id');

	  $.ajax({
		url: FORM_URL + 'cit/citas_actions.php',
		type: 'POST',
		data: { action: 'verificar_cliente', id_cita },
		dataType: 'json',
		success: function(resp) {
		  if (resp.tiene_paciente) {
			// Ya tiene paciente → proceder a cambiar estado
			cambiarEstadoCita(id_cita);
		  } else {
			// No tiene paciente → abrir modal para asignarlo
			$('#id_cita_asignar').val(id_cita);
			$('#modalSeleccionpaciente').modal('show');
		  }
		},
		error: function() {
		  alert('Error al verificar paciente.');
		}
	  });
	});

	// Función auxiliar para cambiar estado
	function cambiarEstadoCita(id) {
	  $.post(FORM_URL + 'cit/cambiar_estado.php', { id, estado: 3 }, function() {
		tabla.ajax.reload();
	  });
	}

  // Cargar especialidades
  $.get(FORM_URL +'cit/listar_especialidades.php', function(html) {
    $('#id_especialidad').html(html);
  });


	// Calendario
	$('#modalCalendario').on('shown.bs.modal', function () {
		const calendarEl = document.getElementById('calendario');
		
		// Asegurar que el contenedor tenga tamaño
		$(calendarEl).css('min-height', '600px');
		
		// Destruir y recrear (más confiable)
		if (window.calendar) {
			window.calendar.destroy();
		}

		window.calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
 			initialView: 'dayGridMonth',
			locale: 'es',
			themeSystem: 'bootstrap5',
			events: FORM_URL + 'cit/citas_calendario.php',
			height: 600,
            views: {
                timeGridWeek: {
                    titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
                },
                timeGridDay: {
                    titleFormat: { year: 'numeric', month: 'long', day: 'numeric' }
                }
            },
			eventDidMount: function (info) {
				let tooltip = new bootstrap.Tooltip(info.el, {
					title: `Paciente: ${info.event.title}\nHora: ${info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`,
					placement: 'top',
					trigger: 'hover',
					container: 'body'
				});
			}
		});
		
		window.calendar.render();
		
		// Actualizar tamaño después de renderizar
		setTimeout(() => {
			window.calendar.updateSize();
		}, 100);
	});

	// Inicializar Select2 para paciente existente
	$('#id_paciente').select2({

	  placeholder: 'Buscar paciente existente...',
	  allowClear: true,
	  dropdownParent: $('#modalCita'),
	  ajax: {
		url: FORM_URL + 'cit/listar_clientes.php',
		dataType: 'json',
		delay: 250,
		data: function (params) {
		  return { search: params.term };
		},
		processResults: function (data) {
		  return {
			results: data.map(function (item) {
			  return {
				id: item.id,
				text: item.nombre_completo
			  };
			})
		  };
		},
		cache: true
	  },
	  minimumInputLength: 1
	});

	$('#id_paciente_modal').select2({
	  placeholder: 'Buscar paciente...',
	  allowClear: true,
	  dropdownParent: $('#modalSeleccionpaciente'),
	  ajax: {
		url: FORM_URL + 'cit/listar_clientes.php',
		dataType: 'json',
		delay: 250,
		data: function (params) {
		  return { search: params.term };
		},
		processResults: function (data) {
		  return {
			results: data.map(function (item) {
			  return {
				id: item.id,
				text: item.nombre_completo
			  };
			})
		  };
		},
		cache: true
	  },
	  minimumInputLength: 1
	});

	// Mostrar formulario de nuevo paciente
	$('#btnNuevopaciente').on('click', function() {
	  $('#nuevopacienteContainer').removeClass('d-none');
	  $('#id_paciente_modal').prop('disabled', true);
	  $(this).prop('disabled', true);
	});

	// Cancelar nuevo paciente
	$('#btnCancelarNuevopaciente').on('click', function() {
	  $('#nuevopacienteContainer').addClass('d-none');
	  $('#id_paciente_modal').prop('disabled', false);
	  $('#btnNuevopaciente').prop('disabled', false);
	  $('#formAsignarpaciente')[0].reset();
	  $('#id_paciente_modal').val(null).trigger('change');
	});

	// Guardar nuevo paciente
	$('#btnGuardarNuevopaciente').on('click', function() {
	  const nombre = $('#nombre_nuevo').val().trim();
	  if (!nombre) {
		alert('Debe ingresar al menos el nombre del paciente.');
		return;
	  }

	  $.ajax({
		url: FORM_URL + 'cit/guardar_cliente.php',
		type: 'POST',
		dataType: 'json',
		data: {
		  nombre: $('#nombre_nuevo').val(),
		  apellido: $('#apellido_nuevo').val(),
		  telefono: $('#telefono_nuevo').val(),
		  email: $('#email_nuevo').val()
		},
		success: function(resp) {
		  if (resp.success) {
			// Insertar el nuevo paciente al select2
			const nuevopaciente = new Option(resp.nombre, resp.id, true, true);
			$('#id_paciente_modal').append(nuevopaciente).trigger('change');

			// Ocultar el formulario de nuevo paciente
			$('#nuevopacienteContainer').addClass('d-none');
			$('#id_paciente_modal').prop('disabled', false);
			$('#btnNuevopaciente').prop('disabled', false);
			//alert('paciente creado correctamente.');
		  } else {
			alert(resp.message || 'Error al guardar paciente.');
		  }
		},
		error: function() {
		  alert('Error de conexión al crear paciente.');
		}
	  });
	});

	// Cuando se selecciona un paciente existente
	$('#id_paciente').on('select2:select', function (e) {
	  const idpaciente = e.params.data.id;

	  if (!idpaciente) return;

	  $.getJSON(FORM_URL + 'cit/obtener_cliente.php', { id: idpaciente }, function (data) {
		if (data && !data.error) {
		  $('#paciente_nombre').val(data.nombre_completo);
		  $('#paciente_telefono').val(data.telefono || '');
		  $('#paciente_email').val(data.email || '');
		} else {
		  console.warn('paciente no encontrado o sin datos válidos');
		}
	  }).fail(function () {
		console.error('Error al cargar los datos del paciente.');
	  });
	});
	$('#id_paciente').on('select2:clear', function () {
	  $('#paciente_nombre, #paciente_telefono, #paciente_email').val('');
	});

	$('#formAsignarpaciente').on('submit', function(e) {
	  e.preventDefault();
		const tabActivo = $('.tab-pane.active');

		// Solo validar campos visibles
		tabActivo.find('[data-required]').each(function() {
			if (!$(this).val().trim()) {
				e.preventDefault();
				alert('Complete el campo: ' + $(this).attr('placeholder'));
				return false;
			}
		});

	  $.ajax({
		url: FORM_URL + 'cit/citas_actions.php',
		type: 'POST',
		data: $(this).serialize() + '&action=asignar_cliente',
		dataType: 'json',
		success: function(resp) {
		  if (resp.success) {
			$('#modalSeleccionpaciente').modal('hide');
			cambiarEstadoCita($('#id_cita_asignar').val());
		  } else {
			alert(resp.message || 'Error al asignar el paciente.');
		  }
		},
		error: function() {
		  alert('Error al guardar el paciente.');
		}
	  });
	});

  
});  // fin document ready

// Inicializar flatpickr siempre al abrir el modal
//let fpFecha = null;

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

  // Configurar según el modelo
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
