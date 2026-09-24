<?php
require_once '../../../assets/dbc.php';
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
		/* Estilos para el modal de servicios */
		/* Asegurar que select2 se muestre correctamente en modal */
		.modal .select2-container--open {
			z-index: 9999 !important;
		}

		.modal .select2-dropdown {
			z-index: 9999 !important;
		}

		/* Responsividad para DataTable en modal */
		@media (max-width: 768px) {
			#tablaServiciosLab_wrapper .dataTables_wrapper .dataTables_length,
			#tablaServiciosLab_wrapper .dataTables_wrapper .dataTables_filter {
				float: none;
				text-align: center;
				margin-bottom: 10px;
			}
		}
		.timeline-container {
			position: relative;
			margin-left: 15px;
			padding-left: 20px;
			border-left: 3px solid #0d6efd;
		}

		.timeline-item {
			position: relative;
			margin-bottom: 20px;
		}

		.timeline-dot {
			position: absolute;
			left: -11px;
			top: 6px;
			width: 14px;
			height: 14px;
			background-color: #0d6efd;
			border-radius: 50%;
		}

		.timeline-content {
			background: #f8f9fa;
			padding: 10px 12px;
			border-radius: 6px;
		}

		.timeline-header {
			margin-bottom: 5px;
		}

		.timeline-body {
			font-size: 0.95rem;
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
                    <h5 class="modal-title">
					<i class="bi bi-clipboard-plus"></i>Nueva consulta urología</h5>
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
						  <!-- Tabs -->
						  <ul class="nav nav-tabs" id="tabsAtencion" role="tablist">
							<li class="nav-item">
							  <button class="nav-link active" id="tabFicha-tab" data-bs-toggle="tab" data-bs-target="#tabFicha" type="button" role="tab">
								<i class="bi bi-journal-text"></i> Ficha
							  </button>
							</li>
							<li class="nav-item">
							  <button class="nav-link" id="tabResultados-tab" data-bs-toggle="tab" data-bs-target="#tabResults" type="button" role="tab">
								<i class="bi bi-list-check"></i> Resultados de laboratorio
							  </button>
							</li>
							<li class="nav-item">
							  <button class="nav-link" id="tabHistorial-tab" data-bs-toggle="tab" data-bs-target="#tabHistorial" type="button" role="tab">
								<i class="bi bi-calendar2-week"></i> Historial
							  </button>
							</li>
						  </ul>
						<div class="tab-content mt-3">
							<!-- TAB 1: FICHA -->
							<div class="tab-pane fade show active" id="tabFicha" role="tabpanel">
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
										<div class="col-12">
											<div class="card-body">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="motivo_consulta" 
															  name="motivo_consulta" 
															  rows="2" 
															  placeholder="Describa el motivo de la consulta"
															  aria-label="Motivo de Consulta"></textarea>
													<button type="button" 
															id="btnHMotivo" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-calendar-week-fill"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Síntomas -->
									<div class="card mb-3">
										<div class="card-header card-header-lila">
											<h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Historia de enfermedad actual</h6>
										</div>
										<div class="col-12">
											<div class="card-body">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="historia_enfermedad" 
															  name="historia_enfermedad" 
															  rows="2" 
															  placeholder="Describa los síntomas presentados"
															  aria-label="Historia de enfermedad actual"></textarea>
													<button type="button" 
															id="btnHHistoria" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-calendar-week-fill"></i>
													</button>
												</div>
											</div>
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
											<div class="card-body" id="card_antecedentes">
											   <div class="table-responsive">
													<table class="table table-bordered table-hover table-sm">
														<thead class="table-light">
															<tr>
																<th style="width: 30%;">Antecedente</th>
																<th style="width: 20%;"></th>
																<th style="width: 20%;"></th>
																<th style="width: 30%;">Observaciones</th>
															</tr>
														</thead>
														<tbody id="tablaAntecedentes">
															<!-- Se generarán filas dinámicamente -->
														</tbody>
													</table>
												</div>
												<div class="mt-3 text-muted small">
													<i class="bi bi-info-circle"></i> 
													Escriba en observaciones para marcar automáticamente o marque/desmarque manualmente.
												</div>
											
											</div>
										</div>
									</div>
									<script>
									$(document).ready(function() {
										 const antecedentesConfig = [
											{ nombre: "Alergias" },
											{ nombre: "Enfermedad crónica" },
											{ nombre: "Antecedentes quirúrgicos" },
											{ nombre: "Lesiones previas" },
											{ nombre: "Embarazo" },
											{ nombre: "ETS/VIH" },
											{ nombre: "Uso de fármacos" },
											{ nombre: "Otros antecedentes" }
										];
										const id_especialidad=1;

										// Función para inicializar la tabla con datos existentes
										function inicializarTablaAntecedentes(datosExistentes = null) {
											const tbody = $('#tablaAntecedentes');
											tbody.empty();
											
											antecedentesConfig.forEach((antecedente, index) => {
												const numero = index + 1;
												
												// Buscar si hay datos existentes para este antecedente
												const datoExistente = datosExistentes ? 
													datosExistentes.find(d => d.id_antecedente === numero) : null;
												
												const presente = datoExistente ? datoExistente.presente : 0;
												const notas = datoExistente ? datoExistente.notas : '';
												
												const row = `
													<tr id="fila_antecedente_${numero}" class="fila-antecedente ${presente ? 'table-info' : ''}">
														<td>
															<input type="hidden" name="antecedente_${numero}_nombre" value="${antecedente.nombre}">
															<input type="hidden" name="antecedente_${numero}_id" value="${numero}">
															${antecedente.nombre}
														</td>
														<td class="text-center">
															<div class="form-check d-inline-block">
																<input type="checkbox" 
																	   class="form-check-input checkbox-antecedente" 
																	   id="checkboxno_${numero}" 
																	   name="checkboxno_${numero}"
																	   value="1"
																	   ${presente ? '' : 'checked'}>
																<label class="form-check-label" for="checkboxno_${numero}">No</label>
															</div>
														</td>
														<td class="text-center">
															<div class="form-check d-inline-block">
																<input type="checkbox" 
																	   class="form-check-input checkbox-antecedente" 
																	   id="checkbox_${numero}" 
																	   name="checkbox_${numero}"
																	   value="1"
																	   ${presente ? 'checked' : ''}>
																<label class="form-check-label" for="checkbox_${numero}">Sí</label>
															</div>
														</td>
														<td>
															<input type="text" 
																   class="form-control notas-antecedente" 
																   id="notas_${numero}" 
																   name="notas_${numero}"
																   placeholder="Detalles o especificaciones"
																   value="${notas}">
														</td>
													</tr>
												`;
												tbody.append(row);
											});
											
											// Inicializar eventos
											inicializarEventosAntecedentes();
										}
											// Función para cargar antecedentes existentes del paciente
											function cargarAntecedentesExistentes(id_cliente, id_especialidad) {
												if (!id_cliente || !id_especialidad) {
													console.warn('Faltan datos para cargar antecedentes:', {id_cliente, id_especialidad});
													inicializarTablaAntecedentes(); // Inicializar vacía
													return;
												}
												
												// Mostrar indicador de carga
												$('#tablaAntecedentes').html(`
													<tr>
														<td colspan="3" class="text-center">
															<div class="spinner-border spinner-border-sm text-primary" role="status">
																<span class="visually-hidden">Cargando antecedentes...</span>
															</div>
															Cargando antecedentes...
														</td>
													</tr>
												`);
												
												$.ajax({
													url: FORM_URL + 'cons/pdt/obtener_antecedentes.php',
													type: 'POST',
													data: { 
														id_cliente: id_cliente,
														id_especialidad: id_especialidad,
														action: 'get_antecedentes'
													},
													dataType: 'json',
													success: function(response) {
														if (response.success && response.data && response.data.length > 0) {
															console.log('Antecedentes cargados:', response.data.length, 'registros');
															
															// Inicializar tabla con datos existentes
															inicializarTablaAntecedentes(response.data);
															
															// Mostrar mensaje si hay datos
															mostrarMensajeAntecedentes(response.data.length);
														} else {
															// No hay datos, inicializar tabla vacía
															console.log('No hay antecedentes registrados');
															inicializarTablaAntecedentes();
															
															// Mostrar mensaje informativo
															$('#tablaAntecedentes').before(`
															`);
														}
													},
													error: function(xhr, status, error) {
														console.error('Error cargando antecedentes:', error);
														
														// Inicializar tabla vacía en caso de error
														inicializarTablaAntecedentes();
														
														// Mostrar mensaje de error
														$('#tablaAntecedentes').before(`
														`);
													}
												});
											}

											// Función para mostrar mensaje de antecedentes cargados
											function mostrarMensajeAntecedentes(cantidad) {
												$('#tablaAntecedentes').before(`
												`);
												
												// Auto-ocultar después de 5 segundos
												setTimeout(() => {
													$('#mensajeAntecedentes').alert('close');
												}, 5000);
											}

										// Función para inicializar eventos
										function inicializarEventosAntecedentes() {
											// Evento: Al escribir en notas, marcar checkbox
											$(document).on('input', '.notas-antecedente', function() {
												const id = this.id.replace('notas_', '');
												const checkbox = $('#checkbox_' + id);
												
												if ($(this).val().trim() !== '') {
													checkbox.prop('checked', true);
													$(this).closest('tr').addClass('table-info');
												} else {
													$(this).closest('tr').removeClass('table-info');
												}
											});

											// Evento: Al desmarcar checkbox, limpiar notas
											$(document).on('change', '.checkbox-antecedente', function() {
												const id = this.id.replace('checkbox_', '');
												const notasInput = $('#notas_' + id);
												const fila = $(this).closest('tr');
												
												if (!$(this).is(':checked')) {
													notasInput.val('');
													fila.removeClass('table-info');
												} else {
													fila.addClass('table-info');
												}
											});

											// Evento: Focus en notas
											$(document).on('focus', '.notas-antecedente', function() {
												$(this).closest('tr').addClass('table-warning');
											});

											// Evento: Blur en notas
											$(document).on('blur', '.notas-antecedente', function() {
												$(this).closest('tr').removeClass('table-warning');
											});
										}

										// Función para cargar antecedentes precargados (si existen)
										function cargarAntecedentesPrecargados() {
											// Esta función se llamará desde el modal cuando se carguen datos
											// Por ahora está vacía, se completará cuando se abra el modal
										}

										// Inicializar tabla cuando el modal se muestre
										$(document).on('shown.bs.modal', '#modalAtencion', function() {
											setTimeout(() => {
												// Obtener datos del paciente
												const id_cliente = $('#id_cliente').val();
												const id_especialidad = 1;
												
												console.log('Cargando antecedentes para:', {
													id_cliente: id_cliente,
													id_especialidad: id_especialidad
												});
												
												// Inicializar tabla (vacía primero para mostrar estructura)
												if ($('#tablaAntecedentes').children().length === 0) {
													inicializarTablaAntecedentes();
												}
												
												// Cargar datos existentes si hay paciente
												if (id_cliente && id_cliente > 0) {
													cargarAntecedentesExistentes(id_cliente, id_especialidad);
												} else {
													console.warn('No hay ID de cliente para cargar antecedentes');
												}
											}, 100);
										});

										// Función para obtener todos los datos de antecedentes
										function obtenerDatosAntecedentes() {
											const datos = [];
											
											for (let i = 1; i <= 8; i++) {
												const nombre = $('input[name="antecedente_' + i + '_nombre"]').val();
												const presente = $('#checkbox_' + i).is(':checked') ? 1 : 0;
												const notas = $('#notas_' + i).val().trim();
												
												datos.push({
													numero: i,
													nombre: nombre,
													presente: presente,
													notas: notas
												});
											}
											
											return datos;
										}

										// Antes de enviar el formulario, podemos procesar los datos
										$('#formAtencion').submit(function(e) {
											// Aquí puedes procesar o validar los antecedentes si es necesario
											const antecedentes = obtenerDatosAntecedentes();
											//console.log('Antecedentes a guardar:', antecedentes);
											
											// También puedes agregar un campo hidden con todos los datos en JSON
											$('#datosAntecedentesJson').remove(); // Eliminar si existe
											
											const jsonData = JSON.stringify(antecedentes);
											$('<input>').attr({
												type: 'hidden',
												id: 'datosAntecedentesJson',
												name: 'datos_antecedentes_json',
												value: jsonData
											}).appendTo('#formAtencion');
										});

										// Función para limpiar la tabla (cuando se cierre el modal)
										$(document).on('hidden.bs.modal', '#modalAtencion', function() {
											// Opcional: limpiar los campos
											$('.checkbox-antecedente').prop('checked', false);
											$('.notas-antecedente').val('');
											$('.fila-antecedente').removeClass('table-info table-warning');
										});
									});
									</script>
					

									<!-- Examen físico -->
									<div class="card mb-3">
									  <div class="card-header bg-info text-white">
										<i class="bi bi-person-lines-fill"></i> Examen físico
									  </div>
										<div class="col-12">
											<div class="card-body">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="examen_fisico" 
															  name="examen_fisico" 
															  rows="2" 
															  placeholder="Describa los resultados del examen físico"
															  aria-label="Examen físico"></textarea>
													<button type="button" 
															id="btnHExamen" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-calendar-week-fill"></i>
													</button>
												</div>
											</div>
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
										  <input type="number" step="0.1" class="form-control" name="sv_temperatura" id="sv_temperatura">
										</div>
										<div class="col-md-3">
										  <label>Frec. Cardiaca</label>
										  <input type="number" class="form-control" name="sv_frecuencia_cardiaca" id="sv_frecuencia_cardiaca">
										</div>
										<div class="col-md-3">
										  <label>Frec. Respiratoria</label>
										  <input type="number" class="form-control" name="sv_frecuencia_respiratoria" id="sv_frecuencia_respiratoria">
										</div>
										<div class="col-md-3">
										  <label>Presión arterial</label>
										  <input type="text" class="form-control" name="sv_presion_arterial" id="sv_presion_arterial">
										</div>
									  </div>
									</div>
																
									<!-- Medicamentos administrados -->
									<div class="card mb-3">
										<div class="card-header" style="background-color: #0000CD; color: white;">
											<h6 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Medicamentos administrados</h6>
										</div>
										<div class="col-12">
											<div class="card-body">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="medicamentos_administrados" 
															  name="medicamentos_administrados" 
															  rows="2" 
															  placeholder="Describa los medicamentos administrados al paciente"
															  aria-label="Medicamentos Administrados físico"></textarea>
													<button type="button" 
															id="btnHMedica" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-calendar-week-fill"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
									<!-- Diagnóstico -->
									<div class="card mb-3">
										<div class="card-header" style="background-color: #6eaff1; color: white;">
											<h6 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Impresión clínica</h6>
										</div>
										<div class="col-12">
											<div class="card-body">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="diagnostico" 
															  name="diagnostico" 
															  rows="2" 
															  placeholder="Describa la impresión clínica"
															  aria-label="Impresión clínica"></textarea>
													<button type="button" 
															id="btnHDiagno" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-calendar-week-fill"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
									
									<!-- Indicaciones Médicas -->
									<div class="card mb-3">
										<div class="card-header" style="background-color: #5F9EA0; color: white;">
											<h6 class="mb-0"><i class="bi bi-prescription2"></i> Indicaciones médicas</h6>
										</div>
										<div class="col-12">
											<div class="card-body">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="indicaciones_medicas" 
															  name="indicaciones_medicas" 
															  rows="2" 
															  placeholder="Describa las indicaciones médicas"
															  aria-label="Indicaciones médicas"></textarea>
													<button type="button" 
															id="btnHIndica" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-calendar-week-fill"></i>
													</button>
												</div>
												<div class="input-group mb-3">
												<div class="col-md-4">
												  <label>Fecha de próxima cita</label>
												  <input type="date" name="proxima_cita" id="proxima_cita" class="form-control">
												</div>
												</div>
											</div>
										</div>
									</div>
									<!-- Receta de medicamentos -->
									<div class="card mb-3">
									  <div class="card-header card-header-azul">
										<h6 class="mb-0"><i class="bi bi-prescription2"></i> Receta de medicamentos</h6>
									  </div>
									  <div class="card-body">
										<div class="row">
											<div class="col-12">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="receta" 
															  name="receta" 
															  rows="3" 
															  placeholder="Receta de medicamentos"
															  aria-label="Receta"></textarea>
													<button type="button" 
															id="btnPdfReceta" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-prescription"></i> Generar receta
													</button>
													<button type="button" 
															id="btnHReceta" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-calendar-week-fill"></i>
													</button>
												</div>
											</div>
										</div>
									  </div>
									</div>
									<script>
									$(document).ready(function() {
									  $('#btnPdfReceta').on('click', function() {
										const tratamiento = $('#tratamiento').val();
										const receta = $('#receta').val().trim();
										const paciente = $('#nombre_cliente').val().trim();
										const fecha = $('#fecha').val();
										const proxima_cita = $('#proxima_cita').val();

										if (!receta) {
										  alert('El área de receta no puede estar vacía!');
										  return;
										}

										// Generar PDF
										const url = FORM_URL + 'cons/uro/generar_receta_pdf.php?' + $.param({
										  receta: receta,
										  paciente: paciente,
										  fecha: fecha,
										  tratamiento: tratamiento,
								  		  proxima_cita: proxima_cita
										});

										window.open(url, '_blank');
									  });
									});
									</script>
									<!-- Orden de estudios -->
									<div class="card mb-3">
									  <div class="card-header card-header-verde">
										<h6 class="mb-0"><i class="bi bi-prescription2"></i> Estudios indicados</h6>
									  </div>
									  <div class="card-body">
										<div class="row">
											<div class="col-12">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="laboratorios" 
															  name="laboratorios" 
															  rows="2" 
															  placeholder="Describa los estudios indicados (1)"
															  aria-label="Estudios indicados"></textarea>
													<button type="button" 
															id="btnPdfOtros" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-file-earmark-medical"></i> Generar orden
													</button>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-12">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="laboratorios2" 
															  name="laboratorios2" 
															  rows="2" 
															  placeholder="Describa los estudios indicados (2)"
															  aria-label="Estudios indicados"></textarea>
													<button type="button" 
															id="btnPdfOtros2" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-file-earmark-medical"></i> Generar orden
													</button>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-12">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="laboratorios3" 
															  name="laboratorios3" 
															  rows="2" 
															  placeholder="Describa los estudios indicados (3)"
															  aria-label="Estudios indicados"></textarea>
													<button type="button" 
															id="btnPdfOtros3" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-file-earmark-medical"></i> Generar orden
													</button>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-12">
												<div class="input-group mb-3">
													<textarea class="form-control" 
															  id="laboratorios4" 
															  name="laboratorios4" 
															  rows="2" 
															  placeholder="Describa los estudios indicados (4)"
															  aria-label="Estudios indicados"></textarea>
													<button type="button" 
															id="btnPdfOtros4" 
															class="btn btn-outline-secondary input-group-text"
															style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
														<i class="bi bi-file-earmark-medical"></i> Generar orden
													</button>
												</div>
											</div>
										</div>
									  </div>
									</div>
									<script>
									</script>
									<br>
								</div>
							</div> <!-- fin del div tab ficha-->
							<!-- TAB resultados -->
							<div class="tab-pane fade" id="tabResults" role="tabpanel">
								<table id="tablaInformes" class="table table-striped table-bordered table-sm nowrap" style="width:100%">
									<thead class="table-warning">
										<tr>
											<th>Fecha</th>
											<th>Nombre del Archivo</th>
											<th>Visualizar</th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
							<!-- TAB historial -->
							<div class="tab-pane fade" id="tabHistorial" role="tabpanel">
							  <table id="tblHistorial" class="table table-striped table-bordered display nowrap" style="width:100%">
								<thead>
								  <tr>
									<th>Fecha</th>
									<th>Número</th>
									<th>Especialidad</th>
									<th>Motivo admisión</th>
									<th>Paciente</th>
									<th>Médico</th>
									<th>Acciones</th>
								  </tr>
								</thead>
								<tbody></tbody>
							  </table>
							</div>
							
                        </div>
                    </div>
                    <div class="modal-footer">
						<button type="button" id="btnToggle" class="btn btn-light">
							<i class="bi bi-tag"></i>
						</button>
					    <input type="number" id="precioConsulta" name="precioConsulta" class="form-control d-none" style="width: 120px;" min="0.00" step="0.01" placeholder="0.00">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar consulta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<!-- Modal para servicios de laboratorio -->
<div class="modal fade" id="modalServiciosLab" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header card-header-azul">
                <h5 class="modal-title">
                    <i class="bi bi-flask"></i> Servicios de Laboratorio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Datos ocultos -->
                <input type="hidden" id="serv_id_atencion">
                <input type="hidden" id="serv_id_cita">
                <input type="hidden" id="serv_cliente">
                <input type="hidden" id="serv_edad">
                <input type="hidden" id="serv_sexo">
                
                <!-- Botones para PDF -->
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <button type="button" id="btnGenerarPrescripcion" class="btn btn-success">
                            <i class="bi bi-file-earmark-pdf"></i> Generar Prescripción PDF
                        </button>
                        <button type="button" id="btnGenerarOrdenInterna" class="btn btn-primary">
                            <i class="bi bi-clipboard-check"></i> Orden Interna
                        </button>
                    </div>
                    <div>
                        <span class="badge bg-info fs-6" id="lblPacienteServicios"></span>
                    </div>
                </div>
                
                <!-- DataTable de servicios -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-list-check"></i> Servicios Registrados</h6>
                    </div>
                    <div class="card-body">
                        <table id="tablaServiciosLab" class="table table-striped table-bordered table-hover w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Servicio</th>
                                    <th>Notas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Formulario para agregar/editar -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Agregar / Editar Servicio</h6>
                    </div>
                    <div class="card-body">
                        <form id="formServicioLab">
                            <input type="hidden" id="servicio_id" name="servicio_id" value="">
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="servicio_fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="servicio_fecha" name="servicio_fecha" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label for="servicio_select" class="form-label">Servicio</label>
                                    <select class="form-select" id="servicio_select" name="servicio_select" style="width: 100%;"></select>
                                </div>
                                <div class="col-md-12">
                                    <label for="servicio_notas" class="form-label">Notas</label>
                                    <textarea class="form-control" id="servicio_notas" name="servicio_notas" rows="3" placeholder="Observaciones o indicaciones especiales"></textarea>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-12 d-flex justify-content-end">
                                    <button type="button" id="btnCancelarServicio" class="btn btn-secondary me-2">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </button>
                                    <button type="submit" id="btnGuardarServicio" class="btn btn-success">
                                        <i class="bi bi-save"></i> Guardar Servicio
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->

<script>	//document.ready

	if (typeof tabla === 'undefined') {
		var tabla;
		var ajaxUrl;
	}
	ajaxUrl = FORM_URL+'cons/uro/atenciones_ajax.php';
	
$(document).ready(function () {
	if ($.fn.DataTable.isDataTable('#tablaFichas')) {
		$('#tablafiFichas').DataTable().destroy();
	}

    tabla = $('#tablaFichas').DataTable({
    ajax: {
      url: FORM_URL+'cons/uro/fichas_medicas_actions.php',
      type: 'POST',
      data: { action: 'listar', estado: '3' },
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
			;
        }
      }
    ],
    order: [[1, 'desc'], [2, 'asc']],
    responsive: true,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
    }
  });

function decodificar(texto) {
    if (!texto) return '';
    
    // Manejar escape múltiple recursivamente
    let resultado = texto;
    let anterior;
    
    do {
        anterior = resultado;
        resultado = resultado
            .replace(/&#38;/g, '&')
            .replace(/&amp;/g, '&')
            .replace(/&#13;&#10;/g, '\n')
            .replace(/\\n/g, '\n')
            .replace(/\\r/g, '\r');
    } while (resultado !== anterior); 
    
    return resultado;
}


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
		$('#tabFicha-tab').tab('show');
	  });

	  $.getJSON(FORM_URL + 'cons/uro/obtener_atencion_consulta.php', { id_cita: id })
		.done(function (res) {

		  if (res && res.success && res.data) {
			const d = res.data;
			const dec = decodificar;

			$('#datos_parto').val(dec(d.datos_parto));
			$('#datos_recien_nacido').val(dec(d.datos_recien_nacido));
			$('#alimentacion_1er_anio').val(dec(d.alimentacion_1er_anio));
			$('#desarrollo_psicomotor').val(dec(d.desarrollo_psicomotor));
			$('#motivo_consulta').val(dec(d.motivo_consulta));
			$('#motivo_consulta').val(dec(d.motivo_consulta));
			$('#historia_enfermedad').val(dec(d.historia_enfermedad_actual));
			$('#indicaciones_medicas').val(dec(d.indicaciones_medicas));
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
			console.log('No hay consulta registrada para esta cita');
		  }
		})
		.fail(function (jqXHR, textStatus, err) {
		  console.error('Error cargando datos de atencion_consulta:', textStatus, err);
		})
		.always(function () {
		  $('#modalAtencion').modal('show');
		});
		// Inicializar/Recargar DataTable de informes
		inicializarDataTableInformes(id_cliente);
	  
	});
	// evento cuando se muestra el tab de Resultados
	$('#tabResultados-tab').on('shown.bs.tab', function() {
		if ($.fn.DataTable.isDataTable('#tablaInformes')) {
			$('#tablaInformes').DataTable().destroy();
		}
		const idCliente = $("#id_cliente").val();
		if (idCliente) {
			inicializarDataTableInformes(idCliente);
		} else {
			console.warn('No hay ID cliente para cargar informes');
		}
	});

	function inicializarDataTableInformes(idCliente) {
		if (typeof tablainformes === 'undefined') {
			var tablainformes;
		}
		
		// Destruir si ya existe
		if ($.fn.DataTable.isDataTable('#tablaInformes')) {
			$('#tablaInformes').DataTable().destroy();
			$('#tablaInformes').empty(); // Limpiar
		}
		
		// Primero hacer una prueba directa
		$.ajax({
			url: FORM_URL + "cons/uro/listar_informes.php",
			type: "POST",
			data: { id_cliente: idCliente, FORM_URL: FORM_URL },
			success: function(response) {
				
				// Si la respuesta es buena, inicializar DataTable
				if (!$.fn.DataTable.isDataTable('#tablaInformes')) {
					tablainformes=$('#tablaInformes').DataTable({
						serverSide: false,
						destroy: true,
						data: response.data, // Usar datos directamente
						columns: [
							{ data: "fecha" },
							{ data: "nombre_original" },
							{ 
								data: "ver",
								orderable: false,
								searchable: false
							}
						],
						responsive: true,
						language: {
							url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
						},
						initComplete: function() {
						}
					});
				}
			},
			error: function(xhr, status, error) {
				if (!$.fn.DataTable.isDataTable('#tablaInformes')) {
					// Inicializar DataTable vacío
					tablainformes=$('#tablaInformes').DataTable({
						serverSide: false,
						destroy: true,
						data: [],
						columns: [
							{ data: "fecha" },
							{ data: "nombre_original" },
							{ data: "ver" }
						],
						language: {
							url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
						}
					});
				};
			}
		});
	}

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
			url: FORM_URL+'cons/uro/guardar_atencion.php',
			type: 'POST',
			data: $(this).serialize(),
			dataType: 'json', 
			success: function(result) {

				if (result.success) {
					$('#modalAtencion').modal('hide');
					tabla.ajax.reload();
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

// Eventos del modal de servicios

    
    // Botón para abrir modal en la tabla principal
    $(document).on('click', '.btnServiciosLab', function() {
        const data = {
            id: $(this).data('id'),
            cliente: $(this).data('cliente'),
            edad: $(this).data('edad'),
            sexo: $(this).data('sexo')
        };
        abrirModalServiciosLab(data);
    });
    
    // Guardar servicio
    $('#formServicioLab').submit(function(e) {
        e.preventDefault();
        
        const idCita = $('#serv_id_cita').val();
        const idServicio = $('#servicio_select').val();
        const notas = $('#servicio_notas').val();
        const idRegistro = $('#servicio_id').val();
        
        if (!idServicio) {
            alert('Por favor seleccione un servicio');
            return;
        }
        
        $.ajax({
            url: 'ajax_servicios.php',
            type: 'POST',
            data: {
                action: 'guardar_servicio',
                id: idRegistro,
                id_cita: idCita,
                id_servicio: idServicio,
                notas: notas
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Recargar tabla
                    cargarServiciosLab(idCita);
                    
                    // Limpiar formulario
                    $('#formServicioLab')[0].reset();
                    $('#servicio_select').val(null).trigger('change');
                    $('#servicio_id').val('');
                    
                    // Restaurar fecha actual
                    const hoy = new Date().toISOString().split('T')[0];
                    $('#servicio_fecha').val(hoy);
                    
                    alert(response.message || 'Servicio guardado correctamente');
                } else {
                    alert('Error: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function() {
                alert('Error de conexión');
            }
        });
    });
    
    // Editar servicio
    $(document).on('click', '.btn-editar-servicio', function() {
        const id = $(this).data('id');
        const idServicio = $(this).data('id_servicio');
        const notas = $(this).data('notas');
        
        // Establecer valores en el formulario
        $('#servicio_id').val(id);
        $('#servicio_notas').val(notas);
        
        // Seleccionar servicio en select2
        if (idServicio) {
            // Obtener nombre del servicio para mostrarlo
            $.ajax({
                url: 'ajax_servicios.php',
                type: 'POST',
                data: {
                    action: 'get_servicio',
                    id: idServicio
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const option = new Option(response.nombre, idServicio, true, true);
                        $('#servicio_select').append(option).trigger('change');
                    }
                }
            });
        }
    });

	  $('#btnPdfOtros').on('click', function() {
		const laboratorios = $('#laboratorios').val();
		const paciente = $('#nombre_cliente').val().trim();
		const fecha = $('#fecha').val();
		const proxima_cita = $('#proxima_cita').val();

		if (!laboratorios) {
		  alert('El área de estudios indicados no puede estar vacía!');
		  return;
		}

		// Generar PDF
		const url = FORM_URL + 'cons/uro/generar_orden_pdf.php?' + $.param({
		  paciente: paciente,
		  fecha: fecha,
		  laboratorios: laboratorios,
		  proxima_cita: proxima_cita
		});

		window.open(url, '_blank');
	  });
	  $('#btnPdfOtros2').on('click', function() {
		const laboratorios = $('#laboratorios2').val();
		const paciente = $('#nombre_cliente').val().trim();
		const fecha = $('#fecha').val();
		const proxima_cita = $('#proxima_cita').val();

		if (!laboratorios) {
		  alert('El área de estudios indicados no puede estar vacía!');
		  return;
		}

		// Generar PDF
		const url = FORM_URL + 'cons/uro/generar_orden_pdf.php?' + $.param({
		  paciente: paciente,
		  fecha: fecha,
		  laboratorios: laboratorios,
		  proxima_cita: proxima_cita
		});

		window.open(url, '_blank');
	  });
	  $('#btnPdfOtros3').on('click', function() {
		const laboratorios = $('#laboratorios3').val();
		const paciente = $('#nombre_cliente').val().trim();
		const fecha = $('#fecha').val();
		const proxima_cita = $('#proxima_cita').val();

		if (!laboratorios) {
		  alert('El área de estudios indicados no puede estar vacía!');
		  return;
		}

		// Generar PDF
		const url = FORM_URL + 'cons/uro/generar_orden_pdf.php?' + $.param({
		  paciente: paciente,
		  fecha: fecha,
		  laboratorios: laboratorios,
		  proxima_cita: proxima_cita
		});

		window.open(url, '_blank');
	  });
	  $('#btnPdfOtros4').on('click', function() {
		const laboratorios = $('#laboratorios4').val();
		const paciente = $('#nombre_cliente').val().trim();
		const fecha = $('#fecha').val();
		const proxima_cita = $('#proxima_cita').val();

		if (!laboratorios) {
		  alert('El área de estudios indicados no puede estar vacía!');
		  return;
		}

		// Generar PDF
		const url = FORM_URL + 'cons/uro/generar_orden_pdf.php?' + $.param({
		  paciente: paciente,
		  fecha: fecha,
		  laboratorios: laboratorios,
		  proxima_cita: proxima_cita
		});

		window.open(url, '_blank');
	  });

	$('#btnHMotivo').on('click', function () {
	  const idCliente = $('#id_cliente').val();

	  if (!idCliente) {
		Swal.fire({
		  icon: 'warning',
		  title: 'Cliente no seleccionado',
		  text: 'Debe seleccionar un cliente primero'
		});
		return;
	  }
	  mostrarHistorialCampo(idCliente, 'motivo_consulta', 'Historial de motivo de consulta');
	});
	$('#btnHHistoria').on('click', function () {
	  const idCliente = $('#id_cliente').val();

	  if (!idCliente) {
		Swal.fire({
		  icon: 'warning',
		  title: 'Cliente no seleccionado',
		  text: 'Debe seleccionar un cliente primero'
		});
		return;
	  }
	  mostrarHistorialCampo(idCliente, 'historia_enfermedad_actual', 'Historial de historia de enfermedad actual');
	});
	$('#btnHExamen').on('click', function () {
	  const idCliente = $('#id_cliente').val();

	  if (!idCliente) {
		Swal.fire({
		  icon: 'warning',
		  title: 'Cliente no seleccionado',
		  text: 'Debe seleccionar un cliente primero'
		});
		return;
	  }
	  mostrarHistorialCampo(idCliente, 'examen_fisico', 'Historial de examen_fisico');
	});
	$('#btnHMedica').on('click', function () {
	  const idCliente = $('#id_cliente').val();

	  if (!idCliente) {
		Swal.fire({
		  icon: 'warning',
		  title: 'Cliente no seleccionado',
		  text: 'Debe seleccionar un cliente primero'
		});
		return;
	  }
	  mostrarHistorialCampo(idCliente, 'medicamentos_administrados', 'Historial de medicamentos administrados');
	});
	$('#btnHDiagno').on('click', function () {
	  const idCliente = $('#id_cliente').val();

	  if (!idCliente) {
		Swal.fire({
		  icon: 'warning',
		  title: 'Cliente no seleccionado',
		  text: 'Debe seleccionar un cliente primero'
		});
		return;
	  }
	  mostrarHistorialCampo(idCliente, 'diagnostico', 'Historial de impresión clínica');
	});
	$('#btnHIndica').on('click', function () {
	  const idCliente = $('#id_cliente').val();

	  if (!idCliente) {
		Swal.fire({
		  icon: 'warning',
		  title: 'Cliente no seleccionado',
		  text: 'Debe seleccionar un cliente primero'
		});
		return;
	  }
	  mostrarHistorialCampo(idCliente, 'indicaciones_medicas', 'Historial de indicaciones médicas');
	});
	$('#btnHReceta').on('click', function () {
	  const idCliente = $('#id_cliente').val();

	  if (!idCliente) {
		Swal.fire({
		  icon: 'warning',
		  title: 'Cliente no seleccionado',
		  text: 'Debe seleccionar un cliente primero'
		});
		return;
	  }
	  mostrarHistorialCampo(idCliente, 'receta', 'Historial de recetas');
	});

	/**
	 * Función reutilizable
	 */
	function mostrarHistorialCampo(idCliente, campo, titulo) {
   if (!idCliente) {
        Swal.fire('Atención', 'No hay cliente seleccionado', 'warning');
        return;
    }

    $.ajax({
        url: FORM_URL + 'cons/uro/historial_campo.php',
        type: 'POST',
        dataType: 'json',
        data: {
            id_cliente: idCliente,
            campo: campo
        },
        success: function (data) {

            if (!data.length) {
                Swal.fire(titulo, 'No hay registros', 'info');
                return;
            }

            let timeline = `
                <div class="timeline-container">
            `;

            data.forEach(item => {
                timeline += `
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="badge bg-primary">
                                    ${item.fecha}
                                </span>
                                <span class="badge bg-secondary ms-1">
                                    Consulta #${item.numero_ticket}
                                </span>
                            </div>
                            <div class="timeline-body">
                                ${item.valor}
                            </div>
                        </div>
                    </div>
                `;
            });

            timeline += `</div>`;

            Swal.fire({
                title: titulo,
                html: `
                    <div style="max-height: 350px; overflow-y: auto;">
                        ${timeline}
                    </div>
                `,
                width: 700,
                showCloseButton: true,
                confirmButtonText: 'Cerrar'
            });
        },
        error: function () {
            Swal.fire('Error', 'No se pudo cargar el historial', 'error');
        }
    });
	}


    
}); // fin document.ready

function inicializarDataTableHistorial() {
  const idCliente = $("#id_cliente").val();
  
  if (!idCliente) {
    $('#tblHistorial').html(`
      <tbody><tr><td colspan="6">Seleccione un paciente</td></tr></tbody>
    `);
    return;
  }
  
  if ($.fn.DataTable.isDataTable('#tblHistorial')) {
    $('#tblHistorial').DataTable().destroy();
    $('#tblHistorial').empty();
  }
  
  // Crear estructura básica
  $('#tblHistorial').html(`
    <thead>
      <tr>
        <th>Fecha</th><th>Ticket</th><th>Especialidad</th>
        <th>Motivo</th><th>Cliente</th><th>Médico</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="6" class="text-center">
          <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          Cargando historial...
        </td>
      </tr>
    </tbody>
  `);
  
  // Inicializar DataTable
  $('#tblHistorial').DataTable({
    ajax: {
      url: FORM_URL+'cons/uro/listar_consultas_historial.php',
      type: 'POST',
      data: { id_cliente: idCliente },
      dataSrc: ''
    },
    columns: [
      { data: 'fecha' }, { data: 'numero_ticket' },
      { data: 'especialidad' }, { data: 'motivo' },
      { data: 'cliente' }, { data: 'medico' },
      {
        data: null,
        orderable: false,
        className: 'text-center',
        render: function (d) {
          return `<a class="btn btn-sm btn-outline-primary pdf-btn" target="_blank" 
                    href="formularios/cons/uro/generar_pdf_consulta.php?id_consulta=${encodeURIComponent(d.id)}" title="Ver datos consulta">
                    <i class="bi bi-file-medical"></i> 
                  </a>`;
        }
      }
   ],
    order: [[0, 'desc']],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
    }
  });
}

// Elimina las variables globales y solo usa esta función
$('#tabHistorial-tab').on('shown.bs.tab', inicializarDataTableHistorial);

</script>

<script>	

// Función para abrir modal de servicios
function abrirModalServiciosLab(data) {
    // Guardar datos del paciente
    $('#serv_id_cita').val(data.id);
    $('#serv_cliente').val(data.cliente);
    $('#serv_edad').val(data.edad);
    $('#serv_sexo').val(data.sexo);
    
    // Mostrar información del paciente
    $('#lblPacienteServicios').html(
        `<i class="bi bi-person"></i> ${data.cliente} | ` +
        `<i class="bi bi-calendar"></i> ${data.edad} años | ` +
        `<i class="bi bi-gender-${data.sexo === 'M' ? 'male' : 'female'}"></i> ${data.sexo === 'M' ? 'Masculino' : 'Femenino'}`
    );
    
    // Establecer fecha actual
    const hoy = new Date().toISOString().split('T')[0];
    $('#servicio_fecha').val(hoy);
    
    // Cargar select2 de servicios
    inicializarSelect2Servicios();
    
    // Cargar servicios existentes
    cargarServiciosLab(data.id);
    
    // Mostrar modal
    const modalEl = document.getElementById('modalServiciosLab');
    modalServiciosInstance = new bootstrap.Modal(modalEl);
    modalServiciosInstance.show();
}

// Inicializar select2 para servicios
function inicializarSelect2Servicios() {
    $('#servicio_select').select2({
        dropdownParent: $('#modalServiciosLab'),
        ajax: {
            url: 'ajax_servicios.php',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    term: params.term || '',
                    action: 'buscar_servicios'
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(servicio => ({
                        id: servicio.id,
                        text: servicio.nombre
                    }))
                };
            },
            cache: true
        },
        placeholder: 'Seleccione un servicio',
        minimumInputLength: 0,
        width: '100%',
        theme: 'bootstrap-5'
    }).on('select2:open', function() {
        // Asegurar que dropdown se muestre correctamente
        $('.select2-dropdown').css('z-index', 9999);
    });
}

// Cargar servicios de laboratorio
function cargarServiciosLab(idCita) {
    // Destruir DataTable si ya existe
    if (tablaServiciosLab !== null && $.fn.DataTable.isDataTable('#tablaServiciosLab')) {
        tablaServiciosLab.destroy();
        tablaServiciosLab = null;
    }
    
    // Inicializar DataTable
    tablaServiciosLab = $('#tablaServiciosLab').DataTable({
        ajax: {
            url: 'ajax_servicios.php',
            type: 'POST',
            data: {
                action: 'listar_servicios',
                id_cita: idCita
            },
            dataSrc: ''
        },
        columns: [
            { 
                data: 'id',
                className: 'text-center'
            },
            { 
                data: 'fecha',
                className: 'text-center',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString('es-ES') : '-';
                }
            },
            { 
                data: 'nombre_servicio',
                className: 'text-left'
            },
            { 
                data: 'notas',
                className: 'text-left',
                render: function(data) {
                    return data || '-';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-warning btn-editar-servicio" 
                                data-id="${data.id}"
                                data-id_servicio="${data.id_servicio}"
                                data-notas="${data.notas || ''}"
                                title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-eliminar-servicio ms-1" 
                                data-id="${data.id}"
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[1, 'desc'], [0, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        responsive: true,
        destroy: true, // Importante para evitar conflictos
        stateSave: false
    });
}

</script>
</body>
</html>
