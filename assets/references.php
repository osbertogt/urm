<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1/dist/cleave.min.js"></script>
<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/bootstrap5/main.min.css" rel="stylesheet">
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Configuración global para todos los DataTables
$.extend(true, $.fn.dataTable.defaults, {
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
    },
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel-fill"></i>',
            titleAttr: 'Exportar a Excel',
            className: 'btn btn-success btn-sm'
        },
        {
            extend: 'pdfHtml5',
            text: '<i class="bi bi-file-earmark-pdf-fill"></i>',
            titleAttr: 'Exportar a PDF',
            className: 'btn btn-danger btn-sm'
        },
        {
            extend: 'print',
            text: '<i class="bi bi-printer-fill"></i>',
            titleAttr: 'Imprimir',
            className: 'btn btn-secondary btn-sm'
        }
    ]
});
</script>

<script>
	  // Detectar origen (http/https + dominio + puerto si aplica)
	  const ORIGIN = window.location.origin;

	  // Detectar ruta base del proyecto (carpeta raíz del sistema)
	  const BASE_PATH = "<?= dirname($_SERVER['SCRIPT_NAME']) !== '/' ? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') : '' ?>";

	  // Ruta absoluta completa del sistema
	  const BASE_URL = ORIGIN + BASE_PATH + "/";

	  // Ruta a formularios
	  const FORM_URL = BASE_URL + "formularios/";

	  // Opcional: otras rutas útiles
	  const API_URL = BASE_URL + "api/";
	  const IMG_URL = BASE_URL + "assets/img/";
	  
	let calendarioGlobal = null;

	function mostrarCalendarioCitas(opciones = {}) {
	  const { idMedico = null, idCliente = null } = opciones;
	  const $modal = $('#modalCalendarioGlobal');
	  const $contenedor = $('#contenedorCalendarioGlobal');
	  const $filtroMedico = $('#filtroMedico');
	  const $filtroPaciente = $('#filtroPaciente');

	  $modal.modal('show');

	  // Inicializar Select2 global (si no existe)
	  function initSelect2($el, url, placeholder) {
		if (!$el.hasClass('select2-hidden-accessible')) {
		  $el.select2({
			placeholder,
			allowClear: true,
			ajax: {
			  url,
			  dataType: 'json',
			  delay: 250,
			  data: params => ({ search: params.term }),
			  processResults: data => ({
				results: data.map(item => ({ id: item.id, text: item.nombre_completo }))
			  })
			},
			dropdownParent: $modal
		  });
		}
	  }

	  initSelect2($filtroMedico, FORM_URL + 'cit/listar_medicos.php', 'Filtrar por médico...');
	  initSelect2($filtroPaciente, FORM_URL + 'cit/listar_clientes.php', 'Filtrar por paciente...');

	  // Inicializar calendario
	  $modal.on('shown.bs.modal', function () {
		if (!calendarioGlobal) {
		  calendarioGlobal = new FullCalendar.Calendar($contenedor[0], {
			initialView: 'dayGridMonth',
			themeSystem: 'bootstrap5',
			locale: 'es',
			height: 'auto',
			headerToolbar: {
			  left: 'prev,next today',
			  center: 'title',
			  right: 'dayGridMonth,timeGridWeek,timeGridDay'
			},
			events: function (fetchInfo, successCallback, failureCallback) {
			  $.ajax({
				url: FORM_URL + 'cit/citas_calendario_global.php',
				type: 'GET',
				data: {
				  id_medico: $filtroMedico.val() || idMedico,
				  id_cliente: $filtroPaciente.val() || idCliente
				},
				dataType: 'json',
				success: successCallback,
				error: failureCallback
			  });
			},
			eventDidMount: function (info) {
			  new bootstrap.Tooltip(info.el, {
				html: true,
				title: `
				  <b>Paciente:</b> ${info.event.extendedProps.paciente}<br>
				  <b>Médico:</b> ${info.event.extendedProps.medico}<br>
				  <b>Hora:</b> ${info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
				`,
				placement: 'top',
				container: 'body'
			  });
			},
			dateClick: function (info) {
			  abrirModalCita({ fecha: info.dateStr });
			},
			eventClick: function (info) {
			  abrirModalCita({ id_cita: info.event.id });
			}
		  });

		  calendarioGlobal.render();
		} else {
		  calendarioGlobal.refetchEvents();
		}
	  });

	  // Filtrar
	  $('#btnFiltrarCalendario').off('click').on('click', () => calendarioGlobal.refetchEvents());
	}

	/** Abre modal de cita (nueva o existente) */
	function abrirModalCita({ id_cita = null, fecha = null }) {
	  const $modal = $('#modalCita');
	  const $form = $('#formCita')[0];
	  $form.reset();
	  $('#id_cita').val('');

	  // Inicializar selects
	  ['#id_cliente', '#id_medico'].forEach(selector => {
		if (!$(selector).hasClass('select2-hidden-accessible')) {
		  $(selector).select2({
			dropdownParent: $modal,
			placeholder: 'Seleccionar...',
			ajax: {
			  url: selector === '#id_cliente'
				? FORM_URL + 'cit/listar_clientes.php'
				: FORM_URL + 'cit/listar_medicos.php',
			  dataType: 'json',
			  delay: 250,
			  data: params => ({ search: params.term }),
			  processResults: data => ({
				results: data.map(item => ({ id: item.id, text: item.nombre_completo }))
			  })
			}
		  });
		}
	  });

	  if (fecha) $('#fecha').val(fecha);

	  if (id_cita) {
		// Cargar datos existentes
		$.getJSON(FORM_URL + 'cit/obtener_cita.php', { id: id_cita }, data => {
		  $('#id_cita').val(data.id);
		  $('#fecha').val(data.fecha);
		  $('#hora').val(data.hora);
		  $('#notas').val(data.notas);

		  // Preseleccionar paciente y médico
		  if (data.id_cliente) {
			const option = new Option(data.paciente, data.id_cliente, true, true);
			$('#id_cliente').append(option).trigger('change');
		  }
		  if (data.id_medico) {
			const option = new Option(data.medico, data.id_medico, true, true);
			$('#id_medico').append(option).trigger('change');
		  }
		});
	  }

	  $modal.modal('show');

	  $('#btnGuardarCita').off('click').on('click', function () {
		const formData = $('#formCita').serialize();

		$.post(FORM_URL + 'cit/guardar_cita.php', formData, function (resp) {
		  if (resp.success) {
			Swal.fire('Éxito', resp.message, 'success');
			$('#modalCita').modal('hide');
			if (calendarioGlobal) calendarioGlobal.refetchEvents();
		  } else {
			Swal.fire('Error', resp.message, 'error');
		  }
		}, 'json');
	  });
	}

</script>
