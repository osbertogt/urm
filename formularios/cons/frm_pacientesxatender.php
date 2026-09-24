<?php
require_once '../../assets/dbc.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <style>
        .modal-dialog {
            max-width: 90%;
        }
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
        .card-header-lila {
            background-color: #9b59b6;
            color: white;
        }
        .card-header-naranja {
            background-color: #fd7e14;
            color: white;
        }
        .card-header-rojo {
            background-color: #dc3545;
            color: white;
        }
        .anamnesis-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>

<body>
<div class="container mt-4">
  <h4><i class="bi bi-file-medical"></i> Citas programadas urología</h4>
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

<!-- Modal para ficha médica -->
    <div class="modal fade" id="modalAtencion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header card-header-azul">
                    <h5 class="modal-title">Registro de consulta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAtencion" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_atencion_consulta" id="id_atencion_consulta">
						<input type="hidden" id="id_cita" name="id_cita">
						<input type="hidden" id="id_cliente" name="id_cliente">
						<input type="hidden" id="id_medico" name="id_medico">
						<div class="card mb-2">
						  <div class="card-header" style="background-color: #CCCCFF; color: black;"><i class="bi bi-person"></i> Datos generales</div>
						  <div class="card-body">
							<div class="row g-2">
							  <div class="col-md-4">
								<label for="nombre_cliente" class="form-label">Paciente</label>
								<input type="text" name="nombre_cliente" id="nombre_cliente" class="form-control"  disabled>
							  </div>
							  <div class="col-md-2">
								<label for="sexo_cliente" class="form-label">Sexo</label>
								<input type="text" name="sexo_cliente" id="sexo_cliente" class="form-control" disabled>
							  </div>
							  <div class="col-md-1">
								<label for="edad_cliente" class="form-label">Edad</label>
								<input type="text" name="edad_cliente" id="edad_cliente" class="form-control"  disabled>
							  </div>
							  <div class="col-md-3">
								<label for="domicilio_cliente" class="form-label">Domicilio</label>
								<input type="text" name="domicilio_cliente" id="domicilio_cliente" class="form-control" disabled>
							  </div>
							</div>
						  </div>
						</div>
                        <div >
                            <!-- TAB 1: FICHA -->
                            <!-- Datos generales -->
                            <div >
								<!-- Tipo de admisión y fecha -->
							  <div class="row g-3 mb-3">
								<div class="col-md-6">
								  <label>Tipo de consulta</label>
								  <select class="form-select" name="id_motivo_admision" id="id_motivo_admision"></select>
								</div>
								<div class="col-md-6">
								  <label>Fecha de atención</label>
								  <input type="date" name="fecha" id="fecha" class="form-control" value="<?=date('Y-m-d')?>" required>
								</div>
							  </div>
                               
        						<!-- Motivo de Consulta -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-verde">
                                        <h6 class="mb-0"><i class="bi bi-chat-dots"></i> Motivo de Consulta</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="motivo_consulta" rows="3" placeholder="Describa el motivo de la consulta"></textarea>
                                    </div>
                                </div>
                                
                                <!-- Síntomas -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-lila">
                                        <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Historia de enfermedad actual</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="sintomas" id="sintomas" rows="3" placeholder="Describa los síntomas presentados"></textarea>
                                    </div>
                                </div>
                        <!-- Anamnesis Remota -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-naranja d-flex align-items-center">
                                        <h6 class="mb-0"><i class="bi bi-clipboard-check"></i> Antecedentes</h6>
										<button class="btn btn-sm btn-light ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCard" aria-expanded="false" aria-controls="collapseCard">
											<i class="bi bi-chevron-down"></i>
										</button>
                                    </div>
									<div id="collapseCard" class="collapse">
                                    <div class="card-body">
                                        <?php
                                        $campos_anamnesis = [
                                            'alergias' => 'Alergias',
                                            'ecronicas' => 'Enfermedades Crónicas',
                                            'aquirurgi' => 'Antecedentes Quirúrgicos',
                                            'lesiones' => 'Lesiones Previas',
                                            'embarazo' => 'Embarazo',
                                            'etsvih' => 'ETS/VIH',
                                            'farmacos' => 'Uso de Fármacos',
                                            'otros' => 'Otros Antecedentes'
                                        ];
                                        
                                        foreach ($campos_anamnesis as $key => $label) {
                                            echo '
                                            <div class="anamnesis-item">
                                                <div class="row">
                                                    <div class="col-md-3">
														<label class="form-label">' . $label . '</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="anam_r_' . $key . '_si" id="anam_r_' . $key . '_si">
                                                            <label class="form-check-label" for="anam_r_' . $key . '_si">Sí</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="anam_r_' . $key . '_no" id="anam_r_' . $key . '_no">
                                                            <label class="form-check-label" for="anam_r_' . $key . '_no">No</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="anam_r_' . $key . '_obs" placeholder="Observaciones">
                                                    </div>
                                                </div>
                                            </div>';
                                        }
                                        ?>
                                    </div>
									</div>
                                </div>
				

								<!-- Examen físico -->
								<div class="card mb-3">
								  <div class="card-header bg-info text-white">
									<i class="bi bi-person-lines-fill"></i> Examen físico
								  </div>
								  <div class="card-body">
									<textarea class="form-control" name="examen_fisico" rows="3"></textarea>
								  </div>
								</div>

								<!-- Signos vitales -->
								<div class="card mb-3">
								  <div class="card-header bg-warning text-dark">
									<i class="bi bi-activity"></i> Signos vitales
								  </div>
								  <div class="card-body row g-3">
									<div class="col-md-3">
									  <label>Temperatura (°C)</label>
									  <input type="number" step="0.1" class="form-control" name="sv_temperatura">
									</div>
									<div class="col-md-3">
									  <label>Frec. Cardiaca</label>
									  <input type="number" class="form-control" name="sv_frecuencia_cardiaca">
									</div>
									<div class="col-md-3">
									  <label>Frec. Respiratoria</label>
									  <input type="number" class="form-control" name="sv_frecuencia_respiratoria">
									</div>
									<div class="col-md-3">
									  <label>Presión arterial</label>
									  <input type="text" class="form-control" name="sv_presion_arterial">
									</div>
								  </div>
								</div>
                                                            
                                <!-- Diagnóstico -->
                                <div class="card mb-3">
                                    <div class="card-header" style="background-color: #6eaff1; color: white;">
                                        <h6 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Impresión clínica</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="diagnostico" rows="3" ></textarea>
                                    </div>
                                </div>
                                
                                <!-- Indicaciones Médicas -->
                                <div class="card mb-3">
                                    <div class="card-header" style="background-color: #5F9EA0; color: white;">
                                        <h6 class="mb-0"><i class="bi bi-prescription2"></i> Tratamiento</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="tratamiento" rows="3" placeholder="Plan de tratamiento"></textarea>
                                    </div>
                                </div>
                                <!-- Receta de medicamentos -->
								<div class="card mb-3">
								  <div class="card-header card-header-azul">
									<h6 class="mb-0"><i class="bi bi-prescription2"></i> Receta de medicamentos</h6>
								  </div>
								  <div class="card-body">
									<textarea class="form-control" id="receta" name="receta" rows="3" placeholder="Receta de medicamentos"></textarea>
									<button type="button" id="btnPdfReceta" class="btn btn-light mt-3">
									  <i class="bi bi-file-earmark-pdf"></i> Generar receta
									</button>
								  </div>
								</div>

								<script>
								$(document).ready(function() {
								  $('#btnPdfReceta').on('click', function() {
									const receta = $('#receta').val().trim();
									const paciente = $('#nombre_cliente').val().trim();
									const fecha = $('#fecha').val().trim();

									if (!receta) {
									  alert('El área de receta no puede estar vacía!');
									  return;
									}

									// Generar PDF
									const url = FORM_URL + 'cons/generar_receta_pdf.php?' + $.param({
									  receta: receta,
									  paciente: paciente,
									  fecha: fecha
									});

									window.open(url, '_blank');
								  });
								});
								</script>
								
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
						<button type="button" id="btnToggle" class="btn btn-light">
							<i class="bi bi-tag"></i>
						</button>
					    <input type="number" id="precioConsulta" name="precioConsulta" class="form-control d-none" style="width: 120px;" min="0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar consulta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<!-- Scripts -->

<script>
	if (typeof tabla === 'undefined') {
		var tabla;
		var ajaxUrl;
	}
	ajaxUrl = FORM_URL+'cons/atenciones_ajax.php';
	
$(document).ready(function () {
	if ($.fn.DataTable.isDataTable('#tablaFichas')) {
		$('#tablafiFichas').DataTable().destroy();
	}

    tabla = $('#tablaFichas').DataTable({
    ajax: {
      url: FORM_URL+'cons/fichas_medicas_actions.php',
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
            <button class="btn btn-sm btn-info btnFicha" 
              data-id="${data.id}"
			  data-id_cliente="${data.id_cliente}"			  
			  data-cliente="${data.cliente}"			  
			  data-edad="${data.edad}"			  
			  data-sexo="${data.sexo}"	
			  data-id_medico="${data.id_medico}"	
			  data-direccion="${data.direccion}"			  
              data-bs-toggle="tooltip" 
              title="Ver ficha médica">
              <i class="bi bi-file-earmark-medical"></i>
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
	  const id = $(this).data('id');
	  const id_cliente = $(this).data('id_cliente');
	  const id_medico = $(this).data('id_medico');
	  const edad = $(this).data('edad');
	  const direccion = $(this).data('direccion');
	  const paciente = $(this).data('cliente');
	  const sexo = $(this).data('sexo');

	  $('#modalAtencion').modal('show');

	  $('#modalAtencion').off('shown.bs.modal').on('shown.bs.modal', function () {
		$('#id_cita').val(id);
		$('#id_cliente').val(id_cliente);
		$('#id_medico').val(id_medico);
		$('#nombre_cliente').val(paciente);
		$('#sexo_cliente').val(sexo);
		$('#edad_cliente').val(edad);
		$('#domicilio_cliente').val(direccion);
	  });

	  $.getJSON(FORM_URL + 'cons/obtener_atencion_consulta.php', { id_cita: id })
		.done(function (res) {
				console.log('Respuesta completa:', res);
				console.log('Datos recibidos:', res.data);

		  if (res && res.success && res.data) {
			const d = res.data;
			const dec = decodificar;

			$('#datos_parto').val(dec(d.datos_parto));
			$('#datos_recien_nacido').val(dec(d.datos_recien_nacido));
			$('#alimentacion_1er_anio').val(dec(d.alimentacion_1er_anio));
			$('#desarrollo_psicomotor').val(dec(d.desarrollo_psicomotor));
			$('#motivo_consulta').val(dec(d.motivo_consulta));
			$('#historia_enfermedad_actual').val(dec(d.historia_enfermedad_actual));
			$('#examen_fisico').val(dec(d.examen_fisico));
			$('#diagnostico').val(dec(d.diagnostico));
			$('#tratamiento').val(dec(d.tratamiento));
			$('#medicamentos_administrados').val(dec(d.medicamentos_administrados));
			$('#receta').val(dec(d.receta));
			$('#laboratorios').val(dec(d.laboratorios));

			$('#sv_temperatura').val(d.sv_temperatura ?? '');
			$('#sv_frecuencia_cardiaca').val(d.sv_frecuencia_cardiaca ?? '');
			$('#sv_frecuencia_respiratoria').val(d.sv_frecuencia_respiratoria ?? '');
			$('#sv_presion_arterial').val(d.sv_presion_arterial ?? '');
			$('#talla').val(d.talla ?? '');
			$('#peso').val(d.peso ?? '');
			$('#circ_cefalica').val(d.circ_cefalica ?? '');

		  } else {
			// si no hay datos, opcionalmente limpiar (ya lo limpiamos antes)
			// console.log('No hay consulta registrada para esta cita');
		  }
		})
		.fail(function (jqXHR, textStatus, err) {
		  console.error('Error cargando datos de atencion_consulta:', textStatus, err);
		})
		.always(function () {
		  $('#modalAtencion').modal('show');
		});
	  
	});

	function cargarSelectMotivos(){
	$.getJSON(ajaxUrl, { action: 'get_motivos' }, function(data){
	  let html = '<option value="">Seleccionar...</option>';
	  data.forEach(m => html += `<option value="${m.id}">${m.nombre}</option>`);
	  $('#id_motivo_admision').html(html);
	});
	}
	cargarSelectMotivos();
	
	$('#formAtencion').submit(function(e) {
		e.preventDefault();
		$.ajax({
			url: FORM_URL+'cons/guardar_atencion.php',
			type: 'POST',
			data: $(this).serialize(),
			dataType: 'json', 
			success: function(result) {
				console.log("Respuesta del servidor:", result);

				if (result.success) {
					$('#modalAtencion').modal('hide');
					table.ajax.reload();
					alert('Atención guardada correctamente');
				} else {
					alert('Error: ' + result.message);
				}
			},
			error: function(xhr, status, error) {
				console.error("Ajax error:", status, error, xhr.responseText);
				alert('Error de conexión');
			}
		});
	});
	$('#btnToggle').on('click', function() {
	  $('#precioConsulta').toggleClass('d-none');
	});

});
</script>
</body>
</html>
