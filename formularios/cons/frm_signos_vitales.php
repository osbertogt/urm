<?php
require_once '../../assets/dbc.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <style>
        .modal-dialog {
            max-width: 40%;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
</head>

<body>
	<div class="container mt-4">
	  <h4><i class="bi bi-heart-pulse"></i> Toma de signos vitales</h4>
	  <table id="tablaFichas" class="table table-bordered table-striped table-hover w-100">
		<thead class="table-light">
		  <tr>
			<th>ID</th>
			<th>Fecha</th>
			<th>Hora</th>
			<th>Cliente</th>
			<th>Médico</th>
			<th>Acciones</th>
		  </tr>
		</thead>
		<tbody></tbody>
	  </table>
	</div>

	<!-- Modal para agregar atencion -->
	<div class="modal fade" id="modalSignosV" tabindex="-1" aria-labelledby="modalSignosVLabel" aria-hidden="true" data-bs-backdrop="false">
	  <div class="modal-dialog modal-sm">
		<form id="formSignosV">
			<input type="hidden" id="id_cliente" name="id_cliente">
			<input type="hidden" id="id_cita" name="id_cita">
			<div class="modal-content p-1" style="border: 2px solid blue;">
				<div class="modal-header p-0.5" style="background-color: #97BBFE; color: white;">
				  <h5 class="modal-title" id="modalSignosVLabel">Toma de signos vitales</h5>
				  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body"  style="background-color: #F0F8FF;">
					<div class="col mb-2">
						<div class="row-md-4">
						  <label class="form-label">Paciente</label>
						  <input type="text" class="form-control" id="paciente" disabled>
						</div>
						<div class="row-md-4">
						  <label class="form-label">Fecha ingreso</label>
						  <input type="date" class="form-control" id="fconsulta" name="fconsulta" disabled>
						</div>
						<div class="row-md-4">
						  <label class="form-label">Médico</label>
						  <input type="text" class="form-control" id="medico" disabled>
						</div>
					</div>
					<div class="row">
					  <div class="col-md">
						<div class="card mb-sm-3 shadow-sm">
						  <div class="card-header text-center"  style="background-color: #CCCCFF; color: black;">
							Signos vitales
						  </div>
						  <div class="card-body">

							<div class="row mb-2 align-items-center">
							  <div class="mb-3">
								<label for="fecha_hora" class="form-label">Fecha  y hora</label>
									<input type="datetime-local" class="form-control" id="fecha_hora" name="fecha_hora" required>
							  </div>
							</div>

							<div class="row mb-2 align-items-center">
							  <label for="presion" class="col-5 col-form-label">Presión arterial (mmHg)</label>
							  <div class="col-7">
								<input type="text" class="form-control" id="presion" name="presion">
							  </div>
							</div>

							<div class="row mb-2 align-items-center">
							  <label for="pulso" class="col-5 col-form-label">Pulso (latidos/min)</label>
							  <div class="col-7">
								<input type="text" class="form-control" id="pulso" name="pulso">
							  </div>
							</div>

							<div class="row mb-2 align-items-center">
							  <label for="respiracion" class="col-5 col-form-label">Respiración (resp/min)</label>
							  <div class="col-7">
								<input type="text" class="form-control" id="respiracion" name="respiracion">
							  </div>
							</div>

							<div class="row mb-2 align-items-center">
							  <label for="temperatura" class="col-5 col-form-label">Temperatura (°C)</label>
							  <div class="col-7">
								<input type="text" class="form-control" id="temperatura" name="temperatura">
							  </div>
							</div>
						  </div>
						</div>
					  </div>
					</div>
				</div>
				<div class="modal-footer">
				  <button type="submit" class="btn btn-success">Guardar</button>
				  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</form>
	  </div>
	</div>

	<script>

	if (typeof tabla === 'undefined') {
		var tabla;
	}

	$(document).ready(function () {
		if ($.fn.DataTable.isDataTable('#tablaFichas')) {
			$('#tablafiFichas').DataTable().destroy();
		}
		
	    tabla = $('#tablaFichas').DataTable({
		ajax: {
		  url: FORM_URL+'cons/fichas_uro_actions.php',
		  type: 'POST',
		  data: { action: 'listar' },
		  dataSrc: ''
		},
		columns: [
		  { data: 'id' },
		  { data: 'fecha' },
		  { data: 'hora' },
		  { data: 'cliente' },
		  { data: 'medico' },
		  {
			data: null,
			orderable: false,
			render: function (data) {
			  return `
				<button class="btn btn-sm btn-success btnFicha" 
				  data-id_cita="${data.id}"
				  data-cliente="${data.cliente}"			  
				  data-fecha="${data.fecha}"			  
				  data-medico="${data.medico}"	
				  data-id_medico="${data.id_medico}"	
				  data-id_cliente="${data.id_cliente}"	
				  data-bs-toggle="tooltip" 
				  title="Toma signos vitales">
				  <i class="bi bi-heart-pulse"></i>
				</button>`;
			}
		  }
		],
		order: [[1, 'desc'], [2, 'asc']],
		responsive: true,
		language: {
		  url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
		}
	  });

	  // Evento para abrir ficha médica
	$('#tablaFichas').on('click', '.btnFicha', function () {
		const id_cita     = $(this).data('id_cita');
		const paciente    = $(this).data('cliente');
		const medico      = $(this).data('medico');
		const fecha_hora  = $(this).data('fecha_hora');
		const fecha       = $(this).data('fecha');
		const id_cliente  = $(this).data('id_cliente');

		// Primero limpiar campos
		$('#temperatura').val('');
		$('#pulso').val('');
		$('#respiracion').val('');
		$('#presion').val('');

		// Llenar datos básicos
		$('#id_cita').val(id_cita);
		$('#id_cliente').val(id_cliente);
		$('#medico').val(medico);
		$('#paciente').val(paciente);
		$('#fconsulta').val(fecha);

		// Abrir modal
		$('#modalSignosV').modal('show');

		// ➜ Cargar signos desde BD vía AJAX
		$.ajax({
			url: FORM_URL+'cons/obtener_signos.php',
			type: 'POST',
			dataType: 'json',
			data: { id_cita: id_cita },
			success: function (resp) {
				if (resp.success) {
					$('#temperatura').val(resp.data.sv_temperatura);
					$('#pulso').val(resp.data.sv_frecuencia_cardiaca);
					$('#respiracion').val(resp.data.sv_frecuencia_respiratoria);
					$('#presion').val(resp.data.sv_presion_arterial);
					$('#fecha_hora').val(resp.data.fecha_hora);
				}
			},
			error: function () {
				console.error('Error al obtener signos');
			}
		});
	});
		
		document.addEventListener('DOMContentLoaded', function () {
			const input = document.getElementById('fecha_hora');
			if (input) {
				// Obtener fecha local del cliente en formato compatible
				const now = new Date();
				now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); 
				input.value = now.toISOString().slice(0,16); // yyyy-MM-ddTHH:mm
			}
		});
		// funcion guardar toma de signos vitales
		$('#formSignosV').on('submit', function(e) {
			e.preventDefault();
			const formData = new FormData(this);
			$.ajax({
				url: FORM_URL + 'cons/guardar_signos.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				success: function(response) {
					console.log("Respuesta:", response);
					if(response.status === 'ok') {
						$('#modalSignosV').modal('hide');
						$('#formSignosV')[0].reset(); 
						tabla.ajax.reload();
					} else {
						alert(response.message);
					}
				},
				error: function(xhr) {
					alert("Error en la solicitud: " + xhr.responseText);
				}
			});
			$('#modalClienteLabel').text('Nueva toma de signos');
		});

	}); // fin document ready
	

	</script>
</body>
</html>
