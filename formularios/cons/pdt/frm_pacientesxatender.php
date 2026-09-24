<?php
require_once '../../../assets/dbc.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>

    <style>
    .modal-dialog-scrollable .modal-body { max-height: calc(100vh - 180px); overflow-y:auto; }
    .card-header-azul { background:#2c5aa0; color:#fff; }
    .card-header-verde { background:#28a745; color:#fff; }
    .card-header-lila { background:#9b59b6; color:#fff; }
    .card-header-naranja { background:#fd7e14; color:#fff; }
    .card-header-rojo { background:#dc3545; color:#fff; }
    .card-header-azulclaro { background:#6495ED; color:#fff; }
    .card-header-aguamarina { background:#66CDAA; color:#fff; }
	.card-header-lima { background:#98FB98; color:#270F57; }
	/* Agregar a tu CSS */
	.modal .select2-container--open {
		z-index: 9999;
	}

	.modal .select2-dropdown {
		z-index: 9999;
	}

	/* Si usas modal-backdrop */
	.modal-backdrop ~ .select2-container {
		z-index: 9999;
	}

	.modal-backdrop ~ .select2-dropdown {
		z-index: 9999;
	}	
    </style>
</head>

<body>
<div class="container mt-4">
  <h4><i class="bi bi-file-medical"></i> Citas programadas pediatría</h4>
  <table id="tablaFichas" class="table table-bordered table-striped table-hover w-100">
    <thead class="table-light">
      <tr>
        <th>ID Cita</th>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Paciente</th>
        <th>Médico</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal para ficha médica -->
	<div class="modal fade" id="modalAtencion" tabindex="-1" aria-labelledby="modalAtencionLabel" aria-hidden="true">
	  <div class="modal-dialog modal-xl ">
		<div class="modal-content">
		  
		  <!-- Header -->
		  <div class="modal-header card-header-azul">
			<h5 class="modal-title" id="modalAtencionLabel">
			  <i class="bi bi-person-plus"></i> Nueva consulta pediátrica
			</h5>
			<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
		  </div>
			<form id="formAtencion" method='POST'>

		  <!-- Body -->
		  <div class="modal-body modal-dialog-scrollable" style="background-color: #F5FFFA;" >
				<input type="hidden" name="id_atencion_consulta" id="id_atencion_consulta">
				<input type="hidden" id="numerocliente" name="numerocliente">
				<input type="hidden" id="numeromedico" name="numeromedico">
				<input type="hidden" id="numerocita" name="numerocita" >
			  <!-- Datos generales del paciente -->
			  <div class="card mb-3">
				<div class="card-header card-header-lima d-flex align-items-center">
				  <i class="bi bi-person-badge"></i> Datos del paciente
				</div>
				<div class="card-body row g-3">
				  <div class="col-md-6">
					<label>Nombre completo</label>
					<input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" disabled>
				  </div>
				  <div class="col-md-2">
					<label>Sexo</label>
					<input type="text" class="form-control" id="sexo_cliente" disabled>
				  </div>
				  <div class="col-md-2">
					<label>Fecha nacimiento</label>
					<input type="date" class="form-control" id="fnac_cliente" disabled>
				  </div>
				  <div class="col-md-2">
					<label>Edad</label>
					<input type="text" class="form-control" id="edad_cliente" disabled>
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
				  <button class="nav-link" id="tabVacunas-tab" data-bs-toggle="tab" data-bs-target="#tabVacunas" type="button" role="tab">
					<i class="bi bi-syringe"></i> Vacunas
				  </button>
				</li>
				<li class="nav-item">
				  <button class="nav-link" id="tabResultados-tab" data-bs-toggle="tab" data-bs-target="#tabResults" type="button" role="tab">
					<i class="bi bi-list-check"></i> Resultados de laboratorio
				  </button>
				</li>
				<li class="nav-item">
					<button class="nav-link" id="tabCurvasOMS-tab" data-bs-toggle="tab" 
							data-bs-target="#tabCurvasOMS" type="button" role="tab">
						<i class="bi bi-graph-up-arrow"></i> Curvas crecimiento
					</button>
				</li>
				<li class="nav-item">
				  <button class="nav-link" id="tabHistorial-tab" data-bs-toggle="tab" data-bs-target="#tabHistorial" type="button" role="tab">
					<i class="bi bi-calendar2-week"></i> Historial
				  </button>
				</li>
			  </ul>

			  <div class="tab-content mt-3">

				<!-- TAB FICHA -->
				<div class="tab-pane fade show active" id="tabFicha" role="tabpanel">

				  <!-- Tipo de admisión y fecha -->
				  <div class="row g-3 mb-3">
					<div class="col-md-6">
					  <label>Tipo de consulta</label>
					  <select class="form-select" name="id_motivo_admision" id="id_motivo_admision"></select>
					</div>
					<div class="col-md-6">
					  <label>Fecha de atención</label>
					  <input type="date" class="form-control" name="fecha_atencion" id="fecha_atencion" value="<?=date('Y-m-d')?>">
					</div>
				  </div>

				  <!-- Datos primera consulta -->
				  <div class="card mb-3">
					<div class="card-header bg-info text-white">
					  <i class="bi bi-baby"></i> Datos de primera consulta
					</div>
					<div class="card-body row g-3">
					  <div class="col-md-6">
						<label>Datos del parto</label>
						<textarea class="form-control" id="datos_parto" name="datos_parto" rows="2"></textarea>
					  </div>
					  <div class="col-md-6">
						<label>Datos del recién nacido</label>
						<textarea class="form-control" id="datos_recien_nacido" name="datos_recien_nacido" rows="2"></textarea>
					  </div>
					  <div class="col-md-6">
						<label>Alimentación 1er año</label>
						<textarea class="form-control" id="alimentacion_1er_anio" name="alimentacion_1er_anio" rows="2"></textarea>
					  </div>
					  <div class="col-md-6">
						<label>Desarrollo psicomotor</label>
						<textarea class="form-control" id="desarrollo_psicomotor" name="desarrollo_psicomotor" rows="2"></textarea>
					  </div>
					</div>
				  </div>
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
										<th style="width: 40%;">Antecedente</th>
										<th style="width: 20%;"></th>
										<th style="width: 40%;">Observaciones</th>
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
					// Definir los 8 antecedentes 
					const antecedentesConfig = [
						{ nombre: "Enfermedades familiares" },
						{ nombre: "Enfermedades de la infancia" },
						{ nombre: "Hospitalizaciones" },
						{ nombre: "Alergias" },
						{ nombre: "Traumas" },
						{ nombre: "Cirugías" },
						{ nombre: "Perfil social" },
						{ nombre: "Otros" }
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
											<div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
												<i class="bi bi-info-circle"></i> 
												No se encontraron antecedentes previos registrados.
												<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
											</div>
										`);
									}
								},
								error: function(xhr, status, error) {
									console.error('Error cargando antecedentes:', error);
									
									// Inicializar tabla vacía en caso de error
									inicializarTablaAntecedentes();
									
									// Mostrar mensaje de error
									$('#tablaAntecedentes').before(`
										<div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
											<i class="bi bi-exclamation-triangle"></i> 
											No se pudieron cargar antecedentes previos. Puede continuar.
											<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
										</div>
									`);
								}
							});
						}

						// Función para mostrar mensaje de antecedentes cargados
						function mostrarMensajeAntecedentes(cantidad) {
							$('#tablaAntecedentes').before(`
								<div class="alert alert-success alert-dismissible fade show mb-3" role="alert" id="mensajeAntecedentes">
									<i class="bi bi-check-circle"></i> 
									Se cargaron ${cantidad} antecedente(s) previos registrados.
									<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
								</div>
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
							const id_cliente = $('#numerocliente').val();
							const id_especialidad = 1; // Ajusta esto según tu sistema (Pediatría = 3?)
							
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
						console.log('Antecedentes a guardar:', antecedentes);
						
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

				  <!-- Cards de atención -->
				  <div id="cardsAtencion">
					
					<!-- Motivo de consulta -->
					<div class="card mb-3">
					  <div class="card-header bg-primary text-white">
						<i class="bi bi-chat-dots"></i> Motivo de consulta
					  </div>
					  <div class="card-body">
						<textarea class="form-control" id="motivo_consulta" name="motivo_consulta" rows="3"></textarea>
					  </div>
					</div>

					<!-- Historia de la enfermedad actual -->
					<div class="card mb-3">
					  <div class="card-header bg-success text-white">
						<i class="bi bi-journal-medical"></i> Historia de la enfermedad actual
					  </div>
					  <div class="card-body">
						<textarea class="form-control" id="historia_enfermedad_actual" name="historia_enfermedad_actual"  rows="3"></textarea>
					  </div>
					</div>

					<!-- Examen físico -->
					<div class="card mb-3">
					  <div class="card-header bg-info text-white">
						<i class="bi bi-person-lines-fill"></i> Examen físico
					  </div>
					  <div class="card-body">
						<textarea class="form-control" id="examen_fisico" name="examen_fisico" rows="3"></textarea>
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
						  <input type="number" step="0.1" class="form-control" id="sv_temperatura" name="sv_temperatura">
						</div>
						<div class="col-md-3">
						  <label>Frec. Cardiaca</label>
						  <input type="number" class="form-control" id="sv_frecuencia_cardiaca" name="sv_frecuencia_cardiaca">
						</div>
						<div class="col-md-3">
						  <label>Frec. Respiratoria</label>
						  <input type="number" class="form-control" id="sv_frecuencia_respiratoria" name="sv_frecuencia_respiratoria">
						</div>
						<div class="col-md-3">
						  <label>Presión arterial</label>
						  <input type="text" class="form-control" id="sv_presion_arterial" name="sv_presion_arterial">
						</div>
					  </div>
					</div>

					<!-- Antropometría -->
					<div class="card mb-3">
					  <div class="card-header bg-secondary text-white">
						<i class="bi bi-rulers"></i> Antropometría
					  </div>
					  <div class="card-body row g-3">
						<div class="col-md-4">
						  <label>Talla (cm)</label>
						  <input type="number" step="0.1" class="form-control" name="talla" id="talla">
						</div>
						<div class="col-md-4">
						  <label>Peso (kg)</label>
						  <input type="number" step="0.1" class="form-control" name="peso" id="peso">
						</div>
						<div class="col-md-4">
						  <label>Circ. Cefálica (cm)</label>
						  <input type="number" step="0.1" class="form-control" name="circ_cefalica" id="circ_cefalica">
						</div>
					  </div>
					</div>

					<!-- Diagnóstico -->
					<div class="card mb-3">
					  <div class="card-header card-header-azulclaro d-flex align-items-center">
						<i class="bi bi-file-medical"></i>  Diagnóstico
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="diagnostico" id="diagnostico" rows="3"></textarea>
					  </div>
					</div>


					<!-- Medicamentos administrados -->
					<div class="card mb-3">
					  <div class="card-header bg-light text-dark">
						<i class="bi bi-bandaid"></i> Medicamentos administrados
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="medicamentos_administrados" id="medicamentos_administrados" rows="3"></textarea>
					  </div>
					</div>
					<!-- Tratamiento -->
					<div class="card mb-3">
					  <div class="card-header card-header-aguamarina d-flex align-items-center">
						<i class="bi bi-capsule"></i>  Plan de tratamiento
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="tratamiento" id="tratamiento" rows="3"></textarea>
					  </div>
					</div>

					<!-- Receta -->
					<div class="card mb-3">
					  <div class="card-header bg-primary text-white">
						<i class="bi bi-clipboard-check"></i> Indicaciones médicas
					  </div>
					  <div class="card-body">
						<textarea class="form-control" id="receta" name="receta" rows="3"></textarea>
						<button type="button" id="btnPdfReceta" class="btn btn-light mt-3">
						  <i class="bi bi-prescription"></i> Generar receta
						</button>
					  </div>
					</div>
					<script>
					$(document).ready(function() {
					  $('#btnPdfReceta').on('click', function() {
						const receta = $('#receta').val().trim();
						const paciente = $('#nombre_cliente').val().trim();
						const fecha = $('#fecha_atencion').val();
						const proxima_cita = $('#proxima_cita').val();

						if (!receta) {
						  alert('El área de receta no puede estar vacía!');
						  return;
						}

						// Generar PDF
						const url = FORM_URL + 'cons/pdt/generar_receta_pdf.php?' + $.param({
						  receta: receta,
						  paciente: paciente,
						  fecha: fecha,
						  proxima_cita: proxima_cita
						});

						window.open(url, '_blank');
					  });
					});
					</script>
					<br>
				  </div>
				</div>

				<!-- TAB VACUNAS -->
				<div class="tab-pane fade" id="tabVacunas" role="tabpanel">
				  <div class="card mb-3">
					<div class="card-header bg-info text-white">
					  <i class="bi bi-syringe"></i> Historial de Vacunas del paciente
					</div>
					<div class="card-body">
					  
					  <!-- DataTable -->
					  <table id="tblVacunas" class="table table-striped table-bordered w-100 mb-3">
						<thead>
						  <tr>
							<th>ID</th>
							<th>Fecha</th>
							<th>Vacuna</th>
							<th>Dosis</th>
							<th>Observaciones</th>
							<th>Acciones</th>
						  </tr>
						</thead>
					  </table>

					  <!-- Formulario para agregar/editar -->
					  <div class="card mt-3">
						<div class="card-header bg-success text-white">
						  <i class="bi bi-plus-circle"></i> Agregar / Editar Vacuna
						</div>
						<div class="card-body">
						  <div id="formVacuna">
							<input type="hidden" id="vac_id" name="id" value="">
							<div class="row g-2 align-items-end">
							  <div class="col-md-3">
								<label class="form-label">Fecha</label>
								<input type="date" id="vac_fecha" name="vac_fecha" class="form-control" readonly>
							  </div>
								<script>
								document.getElementById('vac_fecha').valueAsDate = new Date();
								</script>
							  <div class="col-md-4">
								<label class="form-label">Vacuna</label>
								<select id="id_vacuna" name="id_vacuna" class="form-select" ></select>
							  </div>
							  <div class="col-md-3">
								<label class="form-label">Dosis</label>
								<select id="id_dosis" name="id_dosis" class="form-select" ></select>
							  </div>
							  <div class="col-md-2 text-end">
								<button type="button" class="btn btn-success" id="btnGuardarVacuna">
								  <i class="bi bi-save"></i> Guardar
								</button>
							  </div>
							</div>

							<div class="row mt-3">
							  <div class="col-12">
								<label class="form-label">Observaciones</label>
								<textarea id="vac_observaciones" name="vac_observaciones" class="form-control" rows="2"></textarea>
							  </div>
							</div>
						  </div>
						</div>
					  </div>

					</div>
				  </div>
				</div>

				<!-- TAB CURVAS -->
				<div class="tab-pane fade" id="tabCurvasOMS" role="tabpanel">
					<div class="container-fluid">
						<!-- Card 1: Tabla de datos -->
						<div class="card mb-4">
							<div class="card-header bg-primary text-white">
								<i class="bi bi-table"></i> Datos Antropométricos del Paciente
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="tblDatosAntro" class="table table-striped table-bordered table-hover">
										<thead class="table-light">
											<tr>
												<th>Fecha</th>
												<th>Edad (meses)</th>
												<th>Talla (cm)</th>
												<th>Peso (kg)</th>
												<th>Circ. Cefálica (cm)</th>
												<th>Percentil Talla</th>
												<th>Z-Score</th>
											</tr>
										</thead>
										<tbody>
											<!-- Datos cargados por AJAX -->
										</tbody>
									</table>
								</div>
								<div id="loadingTable" class="text-center py-3">
									<div class="spinner-border text-primary" role="status">
										<span class="visually-hidden">Cargando...</span>
									</div>
									<p class="mt-2">Cargando datos antropométricos...</p>
								</div>
							</div>
						</div>

						<!-- Card 2: Gráfico Talla para la Edad -->
						<div class="card mb-4">
							<div class="card-header bg-success text-white">
								<i class="bi bi-graph-up"></i> Talla para la Edad - Percentiles OMS
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-8">
										<canvas id="chartTallaOMS" height="250"></canvas>
									</div>
									<div class="col-md-4">
										<div class="alert alert-info">
											<h6><i class="bi bi-info-circle"></i> Interpretación</h6>
											<ul class="mb-0 small">
												<li><span class="badge bg-danger">P3</span> - Bajo peso/estatura</li>
												<li><span class="badge bg-warning">P15</span> - Riesgo bajo</li>
												<li><span class="badge bg-success">P50</span> - Mediana</li>
												<li><span class="badge bg-warning">P85</span> - Riesgo alto</li>
												<li><span class="badge bg-danger">P97</span> - Sobrepeso/estatura</li>
											</ul>
										</div>
										<div id="infoTalla" class="mt-3">
											<!-- Información cargada dinámicamente -->
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Card 3: Gráfico Peso para la Edad -->
						<div class="card mb-4">
							<div class="card-header bg-warning text-dark">
								<i class="bi bi-speedometer2"></i> Peso para la Edad - Percentiles OMS
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-8">
										<canvas id="chartPesoOMS" height="250"></canvas>
									</div>
									<div class="col-md-4">
										<div id="infoPeso">
											<!-- Información cargada dinámicamente -->
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Card 4: Gráfico Circunferencia Cefálica -->
						<div class="card mb-4">
							<div class="card-header bg-info text-white">
								<i class="bi bi-record-circle"></i> Circunferencia Cefálica para la Edad - Percentiles OMS
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-8">
										<canvas id="chartCircOMS" height="250"></canvas>
									</div>
									<div class="col-md-4">
										<div id="infoCirc">
											<!-- Información cargada dinámicamente -->
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				
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
          </form>
		  <!-- Footer -->
		  <div class="modal-footer d-flex flex-wrap">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
			  <i class="bi bi-x-circle"></i> Cancelar
			</button>
			<button type="submit" form="formAtencion" class="btn btn-success">
			  <i class="bi bi-save"></i> Guardar
			</button>
		  </div>

		</div>
	  </div>
	</div>


<!-- Scripts -->

<script>
if (typeof tabla === 'undefined') {
	var tabla;
	var ajaxUrl;
}
ajaxUrl = FORM_URL+'cons/pdt/atenciones_ajax.php';
$(document).ready(function () {
	inicializarDatatable();

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
  const fnac = $(this).data('cliente_fnac');

  // campos básicos
  $('#numerocita').val(id);
  $('#numerocliente').val(id_cliente);
  $('#numeromedico').val(id_medico);
  $('#nombre_cliente').val(paciente);
  $('#sexo_cliente').val(sexo);
  $('#edad_cliente').val(edad);
  $('#domicilio_cliente').val(direccion);
  $('#fnac_cliente').val(fnac);
  
  inicializarDataTableVacunas(id_cliente);

  // limpiar campos de consulta mientras cargamos (opcional)
  const camposConsulta = [
    '#datos_parto', '#datos_recien_nacido', '#alimentacion_1er_anio',
    '#desarrollo_psicomotor', '#motivo_consulta', '#historia_enfermedad_actual',
    '#examen_fisico', '#sv_temperatura', '#sv_frecuencia_cardiaca',
    '#sv_frecuencia_respiratoria', '#sv_presion_arterial', '#talla',
    '#peso', '#circ_cefalica', 
    '#diagnostico', '#tratamiento', '#medicamentos_administrados',
    '#receta', '#laboratorios'
  ];
  camposConsulta.forEach(s => $(s).val(''));

  $.getJSON(FORM_URL + 'cons/pdt/obtener_atencion_consulta.php', { id_cita: id })
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
			url: FORM_URL+'cons/pdt/guardar_atencion.php',
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

//--
 
	$("#id_vacuna").select2({
		ajax: {
			dropdownParent: $('#modalAtencion'),
			url: FORM_URL+"cons/pdt/get_vacunas.php",
			dataType: "json",
			processResults: function (data) {
				return {
					results: data.map(v => {
						return {id: v.id, text: v.nombre};
					})
				};
			}
		},
		placeholder: "Seleccione una vacuna",
		width: "100%"
	}).on('select2:open', function() {
	}).on('select2:closing', function() {
	});
	//--
	$("#id_dosis").select2({
		ajax: {
			dropdownParent: $('#modalAtencion'),
			url: FORM_URL+"cons/pdt/get_dosis.php",
			dataType: "json",
			processResults: function (data) {
				return {
					results: data.map(v => {
						return {id: v.id, text: v.nombre};
					})
				};
			}
		},
		placeholder: "Seleccione una dosis",
		width: "100%"
	}).on('select2:open', function() {
	}).on('select2:closing', function() {
	});

   // Inicializar DataTable
function inicializarDataTableVacunas(id_cliente) {
    // Verificar que el elemento existe
    if ($('#tblVacunas').length === 0) {
        console.error('Elemento #tblVacunas no encontrado');
        return;
    }

    // Destruir DataTable si ya existe
    if ($.fn.DataTable.isDataTable('#tblVacunas')) {
        $('#tblVacunas').DataTable().destroy();
    }

    // Inicializar DataTable
		$('#tblVacunas').DataTable({
			ajax: {
				url: FORM_URL + "cons/pdt/get_vacunas_cliente.php",
				type: "POST",
				data: function() {
					return {
						numerocliente: id_cliente
					};
				},
				dataSrc: ""
			},
			columns: [
				{ data: "id" },
				{ data: "fecha" },
				{ data: "vacuna" },
				{ data: "dosis" }, 
				{ data: "observaciones" },
				{
					data: null,
					render: function(data) {
						return `
							<button type="button" class="btn btn-sm btn-warning editar" data-id='${JSON.stringify(data)}'>
								<i class="bi bi-pencil"></i>
							</button>
							<button type="button" class="btn btn-sm btn-danger eliminar" data-id="${data.id}">
								<i class="bi bi-trash"></i>
							</button>
						`;
					},
					orderable: false
				}
			],
			language: {
				url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
			}
		});
	}

    // Guardar registro
	$("#btnGuardarVacuna").on("click", function(){
		// Obtener el id_cliente directamente del formulario
		const id_cliente = $("#numerocliente").val();
		
		if (!id_cliente) {
			alert("No se ha seleccionado un cliente");
			return;
		}
		
		const formData = {
			numerocliente: id_cliente,
			id_vacuna: $("#id_vacuna").val(),
			id_dosis: $("#id_dosis").val(),
			vac_fecha: $("#vac_fecha").val(),
			observaciones: $("#vac_observaciones").val()
		};
		
		console.log('Enviando datos:', formData);
		
		$.post(FORM_URL+"cons/pdt/save_vacuna.php", formData, function (resp) {
			if (resp.status === "success") {
				alert("Guardado correctamente");
				// Resetear formulario
				$("#vac_id").val('');
				$("#vac_observaciones").val('');
				$("#id_vacuna").val(null).trigger("change");
				$("#id_dosis").val(null).trigger("change");
				
				// Recargar DataTable
				$('#tblVacunas').DataTable().ajax.reload();
			} else {
				alert("Error: " + resp.message);
			}
		}, "json");
	});

function recargarDataTableVacunas(id_cliente) {
    
    if ($.fn.DataTable.isDataTable('#tblVacunas')) {
        const dataTable = $('#tblVacunas').DataTable();
        
        // Intentar recargar
        dataTable.ajax.reload();
        console.log('Recarga solicitada');
    } else {
        inicializarDataTableVacunas(id_cliente);
    }
}

    // Cancelar edición
    $("#btnCancelar").on("click", function(){
        $("#formVacuna")[0].reset();
        $("#id").val("");
        $("#id_vacuna").val(null).trigger("change");
    });

	// Editar vacuna
	$(document).on("click", "#tblVacunas .editar", function(e){
		e.preventDefault();
		e.stopPropagation();
 		let v = $(this).data("id");
		$("#vac_id").val(v.id);
		$("#id_dosis").val(v.id_dosis);
		$("#vac_fecha").val(v.fecha);
		$("#vac_observaciones").val(v.observaciones);
		
		// Seleccionar vacuna y dosis
		if (v.id_vacuna) {
			let optionVacuna = new Option(v.vacuna, v.id_vacuna, true, true);
			$("#id_vacuna").append(optionVacuna).trigger("change");
		}
		if (v.id_dosis) {
			let optionDosis = new Option(v.dosis, v.id_dosis, true, true);
			$("#id_dosis").append(optionDosis).trigger("change");
		}
	});

	// Eliminar vacuna
	$(document).on("click", "#tblVacunas .eliminar", function(e){
		e.preventDefault();
		e.stopPropagation();
		if (!confirm("¿Seguro que desea eliminar este registro?")) return;
		let id = $(this).data("id");
		$.post(FORM_URL+"cons/pdt/delete_vacuna.php", {id}, function(resp){
			if (resp.status === "success") {
				if ($.fn.DataTable.isDataTable('#tblVacunas')) {
					$('#tblVacunas').DataTable().ajax.reload();
				}
			} else {
				alert("Error: " + resp.message);
			}
		}, "json");
	});


$(document).on('click', '.btnFicha', function() {
  const id = $(this).data('id');
  const id_cliente = $(this).data('id_cliente');
  const id_medico = $(this).data('id_medico');
  const edad = $(this).data('edad');
  const direccion = $(this).data('direccion');
  const paciente = $(this).data('cliente');
  const sexo = $(this).data('sexo');
  const fnac = $(this).data('cliente_fnac');

  // campos básicos
  $('#numerocita').val(id);
  $('#numerocliente').val(id_cliente);
  $('#numeromedico').val(id_medico);
  $('#nombre_cliente').val(paciente);
  $('#sexo_cliente').val(sexo);
  $('#edad_cliente').val(edad);
  $('#domicilio_cliente').val(direccion);
  $('#fnac_cliente').val(fnac);
  
  inicializarDataTableVacunas(id_cliente);

  // limpiar campos de consulta mientras cargamos (opcional)
  const camposConsulta = [
    '#datos_parto', '#datos_recien_nacido', '#alimentacion_1er_anio',
    '#desarrollo_psicomotor', '#motivo_consulta', '#historia_enfermedad_actual',
    '#examen_fisico', '#sv_temperatura', '#sv_frecuencia_cardiaca',
    '#sv_frecuencia_respiratoria', '#sv_presion_arterial', '#talla',
    '#peso', '#circ_cefalica', 
    '#diagnostico', '#tratamiento', '#medicamentos_administrados',
    '#receta', '#laboratorios'
  ];
  camposConsulta.forEach(s => $(s).val(''));

  $.getJSON(FORM_URL + 'cons/pdt/obtener_atencion_consulta.php', { id_cita: id })
    .done(function (res) {

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
    
    // Inicializar/Recargar DataTable de informes
    inicializarDataTableInformes(id_cliente);
});

// evento cuando se muestra el tab de Resultados
$('#tabResultados-tab').on('shown.bs.tab', function() {
	if ($.fn.DataTable.isDataTable('#tablaInformes')) {
		$('#tablaInformes').DataTable().destroy();
	}
    const idCliente = $("#numerocliente").val();
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
        url: FORM_URL + "cons/pdt/listar_informes.php",
        type: "POST",
        data: { id_cliente: idCliente, FORM_URL: FORM_URL },
        success: function(response) {
            
            // Si la respuesta es buena, inicializar DataTable
            tablainformes=$('#tablaInformes').DataTable({
                serverSide: false,
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
        },
        error: function(xhr, status, error) {
            
            // Inicializar DataTable vacío
            tablainformes=$('#tablaInformes').DataTable({
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
        }
    });
}


		// Instanciar CurvasCrecimientoOMS
		window.curvasOMS = new CurvasCrecimientoOMS();


}); // fin del document.ready

function inicializarDatatable(){
	
	tabla = $(tablaFichas);
    
    // Si ya existe, destruirlo
    if ($.fn.DataTable.isDataTable(tabla)) {
        tabla.DataTable().destroy();
        tabla.empty(); 
    }

    tabla.DataTable({
    ajax: {
      url: FORM_URL+'cons/pdt/fichas_medicas_actions.php',
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
              data-id_medico="${data.id_medico}"
              data-edad="${data.edad}"
              data-direccion="${data.direccion}"
              data-cliente="${data.cliente}"
              data-sexo="${data.sexo}"
              data-cliente_fnac="${data.fecha_nacimiento}"
              data-bs-toggle="tooltip"
              title="Ver ficha médica">
              <i class="bi bi-file-earmark-medical"></i>
            </button>`;
        }      
	  }
    ],
    order: [[1, 'desc'], [2, 'asc']],
    responsive: true,
    destroy: true, 
    stateSave: false 
  });
} // fin inicializardatatable



</script>

<script>

if (typeof historialDataTable === 'undefined') {
	var historialDataTable;
}

function inicializarDataTableHistorial() {
  const idCliente = $("#numerocliente").val();
  
  if (!idCliente) {
    console.warn('No hay ID cliente para cargar historial');
    return;
  }
  
  // Si ya existe, solo recargar los datos
  if (historialDataTable) {
    historialDataTable.ajax.reload();
    return;
  }
  
  // Inicializar por primera vez
  historialDataTable = $('#tblHistorial').DataTable({
    ajax: {
      url: FORM_URL+'cons/pdt/listar_consultas_historial.php',
      type: 'POST',
      data: { id_cliente: idCliente },
      dataSrc: ''
    },
    columns: [
      { data: 'fecha' },
      { data: 'numero_ticket' },
      { data: 'especialidad' },
      { data: 'motivo' },
      { data: 'cliente' },
      { data: 'medico' },
      {
        data: null,
        orderable: false,
        className: 'text-center',
        render: function (d) {
          return `<a class="btn btn-sm btn-outline-primary pdf-btn" target="_blank" 
                    href="formularios/cons/pdt/generar_pdf_consulta.php?id_consulta=${encodeURIComponent(d.id)}" title="Ver datos consulta">
                    <i class="bi bi-file-medical"></i> 
                  </a>`;
        }
      }
    ],
    responsive: true,
    order: [[0, 'desc']],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
    }
  });
}

// Evento cuando se muestra el tab de Historial
$('#tabHistorial-tab').on('shown.bs.tab', function() {
  inicializarDataTableHistorial();
});

// Limpiar referencia cuando se cierra el modal
$(document).on('hidden.bs.modal', '#modalAtencion', function() {
  historialDataTable = null;
});

//scripts CURVAS
// curvas_oms.js
class CurvasCrecimientoOMS {
    constructor() {
        this.chartTalla = null;
        this.chartPeso = null;
        this.chartCirc = null;
        this.datosPaciente = null;
        this.sexoPaciente = 'F';
        this.init();
    }

    init() {
        // Cargar datos cuando se muestra el tab
        $('#tabCurvasOMS-tab').on('shown.bs.tab', () => {
            this.cargarDatosPaciente();
        });
    }

    cargarDatosPaciente() {
        const idCliente = $("#numerocliente").val();
        
        if (!idCliente || idCliente == 0) {
            this.mostrarError("No hay paciente seleccionado");
            return;
        }

        // Mostrar loading
        $('#loadingTable').show();
        $('.card-body tbody').empty();

        $.ajax({
            url: FORM_URL + 'cons/pdt/curvas/cuvas_oms_ajax.php',
            type: 'POST',
            data: { id_cliente: idCliente },
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    this.datosPaciente = response.datos;
                    this.sexoPaciente = response.paciente.sexo;
                    
                    // Actualizar tabla
                    this.actualizarTabla(response);
                    
                    // Generar gráficas
                    this.generarGraficas();
                    
                    // Ocultar loading
                    $('#loadingTable').hide();
                } else {
                    this.mostrarError(response.message || "Error al cargar datos");
                }
            },
            error: (xhr, status, error) => {
                this.mostrarError("Error de conexión: " + error);
                $('#loadingTable').hide();
            }
        });
    }

    actualizarTabla(response) {
        const tbody = $('#tblDatosAntro tbody');
        tbody.empty();

        if (!this.datosPaciente || this.datosPaciente.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="bi bi-exclamation-triangle"></i> No hay datos antropométricos registrados
                    </td>
                </tr>
            `);
            return;
        }

        this.datosPaciente.forEach((registro, index) => {
            // Calcular percentiles para esta edad
            const percentilesTalla = this.obtenerPercentiles('talla', registro.edad_meses);
            const zScore = percentilesTalla ? 
                this.calcularZScore(registro.talla, percentilesTalla) : null;
            const percentilTalla = percentilesTalla ? 
                this.determinarPercentil(registro.talla, percentilesTalla) : 'N/A';

            const row = `
                <tr>
                    <td>${registro.fecha}</td>
                    <td class="text-center">${registro.edad_meses.toFixed(1)}</td>
                    <td class="text-center">${registro.talla ? registro.talla.toFixed(1) : '-'}</td>
                    <td class="text-center">${registro.peso ? registro.peso.toFixed(2) : '-'}</td>
                    <td class="text-center">${registro.circ_cefalica ? registro.circ_cefalica.toFixed(1) : '-'}</td>
                    <td class="text-center">
                        <span class="badge ${this.getPercentilBadgeClass(percentilTalla)}">
                            ${percentilTalla}
                        </span>
                    </td>
                    <td class="text-center">
                        ${zScore ? zScore.toFixed(2) : '-'}
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Actualizar información del paciente
        this.actualizarInfoPaciente(response.paciente);
    }

    actualizarInfoPaciente(paciente) {
        const infoHtml = `
            <div class="alert alert-light">
                <h6><i class="bi bi-person-circle"></i> Información del Paciente</h6>
                <p class="mb-1"><strong>Nombre:</strong> ${paciente.nombre}</p>
                <p class="mb-1"><strong>Sexo:</strong> ${paciente.sexo === 'F' ? 'Femenino' : 'Masculino'}</p>
                <p class="mb-1"><strong>Fecha Nacimiento:</strong> ${paciente.fecha_nacimiento}</p>
                <p class="mb-0"><strong>Registros:</strong> ${this.datosPaciente.length}</p>
            </div>
        `;
        
        // Actualizar en cada card
        $('#infoTalla').html(infoHtml);
        $('#infoPeso').html(infoHtml);
        $('#infoCirc').html(infoHtml);
    }

    obtenerPercentiles(tipo, edadMeses) {
        // En una implementación real, esto llamaría a tu API PHP
        // Por ahora simulamos con datos estáticos
        return this.simularPercentilesOMS(tipo, this.sexoPaciente, edadMeses);
    }

simularPercentilesOMS(tipo, sexo, edadMeses) {
    // Tablas OMS reales (datos de ejemplo - deberías usar tablas oficiales)
    const tablasOMS = {
        // TALLA (cm) - Niñas 0-24 meses
        'talla_F': [
            [0, 45.6, 47.2, 49.1, 51.0, 52.5],
            [3, 55.8, 57.6, 59.8, 61.9, 63.5],
            [6, 61.5, 63.5, 65.7, 68.0, 69.6],
            [9, 65.6, 67.6, 70.1, 72.6, 74.3],
            [12, 69.2, 71.3, 74.0, 76.7, 78.6],
            [18, 75.5, 77.9, 81.0, 84.1, 86.2],
            [24, 81.0, 83.8, 87.4, 90.9, 93.4]
        ],
        // TALLA (cm) - Niños 0-24 meses
        'talla_M': [
            [0, 46.3, 48.0, 49.9, 51.8, 53.4],
            [3, 57.6, 59.5, 61.8, 64.0, 65.7],
            [6, 63.6, 65.7, 68.4, 71.0, 72.9],
            [9, 67.7, 70.0, 73.0, 76.0, 78.2],
            [12, 71.3, 73.7, 77.0, 80.4, 82.8],
            [18, 77.4, 80.2, 84.1, 88.1, 91.0],
            [24, 83.0, 86.0, 90.4, 94.9, 98.3]
        ],
        // PESO (kg) - Niñas 0-24 meses
        'peso_F': [
            [0, 2.4, 2.8, 3.2, 3.7, 4.2],
            [3, 4.6, 5.2, 5.8, 6.6, 7.3],
            [6, 5.8, 6.5, 7.3, 8.2, 9.1],
            [9, 6.6, 7.3, 8.2, 9.2, 10.2],
            [12, 7.1, 7.9, 8.9, 10.0, 11.2],
            [18, 8.1, 9.1, 10.2, 11.6, 13.0],
            [24, 9.0, 10.1, 11.5, 13.0, 14.7]
        ],
        // PESO (kg) - Niños 0-24 meses
        'peso_M': [
            [0, 2.5, 2.9, 3.3, 3.9, 4.4],
            [3, 5.1, 5.7, 6.4, 7.2, 8.0],
            [6, 6.4, 7.1, 7.9, 8.8, 9.8],
            [9, 7.1, 7.9, 8.9, 9.9, 11.0],
            [12, 7.7, 8.6, 9.6, 10.8, 12.0],
            [18, 8.8, 9.8, 11.0, 12.3, 13.7],
            [24, 9.7, 10.8, 12.2, 13.7, 15.3]
        ],
        // CIRCUNFERENCIA CEFÁLICA (cm) - Niñas 0-24 meses
        'circ_cefalica_F': [
            [0, 31.7, 32.5, 33.3, 34.2, 34.9],
            [3, 37.7, 38.5, 39.3, 40.2, 40.9],
            [6, 40.3, 41.1, 41.9, 42.8, 43.6],
            [9, 41.8, 42.7, 43.5, 44.4, 45.2],
            [12, 42.8, 43.7, 44.5, 45.4, 46.2],
            [18, 44.1, 45.0, 45.9, 46.8, 47.6],
            [24, 45.0, 45.9, 46.8, 47.7, 48.5]
        ],
        // CIRCUNFERENCIA CEFÁLICA (cm) - Niños 0-24 meses
        'circ_cefalica_M': [
            [0, 32.1, 33.0, 33.9, 34.8, 35.5],
            [3, 38.3, 39.2, 40.1, 41.1, 41.8],
            [6, 40.7, 41.6, 42.5, 43.5, 44.2],
            [9, 42.1, 43.0, 44.0, 45.0, 45.7],
            [12, 43.0, 44.0, 45.0, 46.0, 46.8],
            [18, 44.2, 45.1, 46.1, 47.1, 47.9],
            [24, 45.0, 46.0, 47.0, 48.0, 48.8]
        ]
    };
    
    const clave = `${tipo}_${sexo}`;
    const tabla = tablasOMS[clave];
    
    if (!tabla) {
        console.warn(`No hay tabla para: ${clave}`);
        return {
            P3: 0, P15: 0, P50: 0, P85: 0, P97: 0
        };
    }
    
    // Encontrar los puntos más cercanos para interpolar
    let prev = null;
    let next = null;
    
    for (let punto of tabla) {
        if (punto[0] <= edadMeses) {
            prev = punto;
        } else {
            next = punto;
            break;
        }
    }
    
    // Si no hay siguiente, usar el último
    if (!next) next = prev;
    
    // Si no hay datos, retornar valores por defecto
    if (!prev) {
        return {
            P3: 0, P15: 0, P50: 0, P85: 0, P97: 0
        };
    }
    
    // Interpolar linealmente
    if (prev[0] === next[0] || !next) {
        return {
            P3: prev[1],
            P15: prev[2],
            P50: prev[3],
            P85: prev[4],
            P97: prev[5]
        };
    }
    
    const t = (edadMeses - prev[0]) / (next[0] - prev[0]);
    
    return {
        P3: prev[1] + (next[1] - prev[1]) * t,
        P15: prev[2] + (next[2] - prev[2]) * t,
        P50: prev[3] + (next[3] - prev[3]) * t,
        P85: prev[4] + (next[4] - prev[4]) * t,
        P97: prev[5] + (next[5] - prev[5]) * t
    };
}

    calcularZScore(valor, percentiles) {
        if (!valor || !percentiles) return null;
        
        const p50 = percentiles.P50;
        const sd = (percentiles.P85 - p50) / 1.036; // Aproximación SD
        
        if (sd > 0) {
            return (valor - p50) / sd;
        }
        return null;
    }

    determinarPercentil(valor, percentiles) {
        if (!valor || !percentiles) return 'N/A';
        
        if (valor <= percentiles.P3) return '< P3';
        if (valor <= percentiles.P15) return 'P3-P15';
        if (valor <= percentiles.P50) return 'P15-P50';
        if (valor <= percentiles.P85) return 'P50-P85';
        if (valor <= percentiles.P97) return 'P85-P97';
        return '> P97';
    }

    getPercentilBadgeClass(percentil) {
        switch(percentil) {
            case '< P3':
            case '> P97':
                return 'bg-danger';
            case 'P3-P15':
            case 'P85-P97':
                return 'bg-warning';
            case 'P15-P50':
            case 'P50-P85':
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }

    generarGraficas() {
        if (!this.datosPaciente || this.datosPaciente.length === 0) {
            this.mostrarMensajeSinDatos();
            return;
        }

        // Destruir gráficas existentes
        if (this.chartTalla) this.chartTalla.destroy();
        if (this.chartPeso) this.chartPeso.destroy();
        if (this.chartCirc) this.chartCirc.destroy();

        // Preparar datos
        const datosGrafica = this.prepararDatosGrafica();

        // Crear gráficas
        this.chartTalla = this.crearGraficaTalla(datosGrafica);
        this.chartPeso = this.crearGraficaPeso(datosGrafica);
        this.chartCirc = this.crearGraficaCirc(datosGrafica);
    }

    prepararDatosGrafica() {
        const datos = {
            edades: [],
            tallas: [],
            pesos: [],
            circs: [],
            fechas: []
        };

        // Filtrar y ordenar datos
        const datosFiltrados = this.datosPaciente
            .filter(r => r.edad_meses <= 60) // Solo hasta 5 años
            .sort((a, b) => a.edad_meses - b.edad_meses);

        datosFiltrados.forEach(registro => {
            datos.edades.push(registro.edad_meses);
            datos.tallas.push(registro.talla);
            datos.pesos.push(registro.peso);
            datos.circs.push(registro.circ_cefalica);
            datos.fechas.push(registro.fecha);
        });

        return datos;
    }

    crearGraficaTalla(datos) {
        const ctx = document.getElementById('chartTallaOMS').getContext('2d');
        
        // Generar curvas de percentiles
        const percentilesData = this.generarCurvasPercentiles('talla', datos.edades);
        
        return new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [
                    // Percentil P3
                    {
                        label: 'P3',
                        data: percentilesData.P3,
                        borderColor: 'rgba(220, 53, 69, 0.5)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Percentil P15
                    {
                        label: 'P15',
                        data: percentilesData.P15,
                        borderColor: 'rgba(255, 193, 7, 0.5)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Percentil P50
                    {
                        label: 'P50',
                        data: percentilesData.P50,
                        borderColor: 'rgba(40, 167, 69, 0.7)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Percentil P85
                    {
                        label: 'P85',
                        data: percentilesData.P85,
                        borderColor: 'rgba(255, 193, 7, 0.5)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Percentil P97
                    {
                        label: 'P97',
                        data: percentilesData.P97,
                        borderColor: 'rgba(220, 53, 69, 0.5)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Datos del paciente
                    {
                        label: 'Paciente',
                        data: datos.edades.map((edad, i) => ({
                            x: edad,
                            y: datos.tallas[i]
                        })).filter(p => p.y !== null),
                        borderColor: 'rgba(13, 110, 253, 1)',
                        backgroundColor: 'rgba(13, 110, 253, 0.8)',
                        borderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        showLine: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
				responsive: true,
				maintainAspectRatio: false, 
				aspectRatio: 2, 
				animation: {
					duration: 0 
				},
                plugins: {
                    title: {
                        display: true,
                        text: 'Talla para la Edad - Percentiles OMS',
                        font: { size: 16 }
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.dataset.label || '';
                                if (label === 'Paciente') {
                                    const index = context.dataIndex;
                                    return `${label}: ${context.parsed.y.toFixed(1)} cm (${datos.fechas[index]})`;
                                }
                                return `${label}: ${context.parsed.y.toFixed(1)} cm`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Edad (meses)'
                        },
                        min: 0,
                        max: Math.max(...datos.edades) + 2
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Talla (cm)'
                        },
                        beginAtZero: false
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
crearGraficaPeso(datos) {
    const ctx = document.getElementById('chartPesoOMS').getContext('2d');
    
    // Generar curvas de percentiles
    const percentilesData = this.generarCurvasPercentiles('peso', datos.edades);
    
    return new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [
                // Percentil P3
                {
                    label: 'P3',
                    data: percentilesData.P3,
                    borderColor: 'rgba(220, 53, 69, 0.5)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Percentil P15
                {
                    label: 'P15',
                    data: percentilesData.P15,
                    borderColor: 'rgba(255, 193, 7, 0.5)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Percentil P50
                {
                    label: 'P50',
                    data: percentilesData.P50,
                    borderColor: 'rgba(40, 167, 69, 0.7)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Percentil P85
                {
                    label: 'P85',
                    data: percentilesData.P85,
                    borderColor: 'rgba(255, 193, 7, 0.5)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Percentil P97
                {
                    label: 'P97',
                    data: percentilesData.P97,
                    borderColor: 'rgba(220, 53, 69, 0.5)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Datos del paciente
                {
                    label: 'Paciente',
                    data: datos.edades.map((edad, i) => ({
                        x: edad,
                        y: datos.pesos[i]
                    })).filter(p => p.y !== null),
                    borderColor: 'rgba(253, 126, 20, 1)', // Color naranja para peso
                    backgroundColor: 'rgba(253, 126, 20, 0.8)',
                    borderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    showLine: true,
                    tension: 0.3
                }
            ]
        },
        options: {
			responsive: true,
			maintainAspectRatio: false, 
			aspectRatio: 2, 
			animation: {
				duration: 0 
			},
            plugins: {
                title: {
                    display: true,
                    text: 'Peso para la Edad - Percentiles OMS',
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const label = context.dataset.label || '';
                            if (label === 'Paciente') {
                                const index = context.dataIndex;
                                return `${label}: ${context.parsed.y.toFixed(2)} kg (${datos.fechas[index]})`;
                            }
                            return `${label}: ${context.parsed.y.toFixed(2)} kg`;
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Edad (meses)'
                    },
                    min: 0,
                    max: Math.max(...datos.edades) + 2
                },
                y: {
                    title: {
                        display: true,
                        text: 'Peso (kg)'
                    },
                    beginAtZero: false
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

crearGraficaCirc(datos) {
    const ctx = document.getElementById('chartCircOMS').getContext('2d');
    
    // Generar curvas de percentiles
    const percentilesData = this.generarCurvasPercentiles('circ_cefalica', datos.edades);
    
    return new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [
                // Percentil P3
                {
                    label: 'P3',
                    data: percentilesData.P3,
                    borderColor: 'rgba(220, 53, 69, 0.5)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Percentil P15
                {
                    label: 'P15',
                    data: percentilesData.P15,
                    borderColor: 'rgba(255, 193, 7, 0.5)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Percentil P50
                {
                    label: 'P50',
                    data: percentilesData.P50,
                    borderColor: 'rgba(40, 167, 69, 0.7)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Percentil P85
                {
                    label: 'P85',
                    data: percentilesData.P85,
                    borderColor: 'rgba(255, 193, 7, 0.5)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Percentil P97
                {
                    label: 'P97',
                    data: percentilesData.P97,
                    borderColor: 'rgba(220, 53, 69, 0.5)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                },
                // Datos del paciente
                {
                    label: 'Paciente',
                    data: datos.edades.map((edad, i) => ({
                        x: edad,
                        y: datos.circs[i]
                    })).filter(p => p.y !== null),
                    borderColor: 'rgba(32, 201, 151, 1)', // Color verde agua para circunferencia
                    backgroundColor: 'rgba(32, 201, 151, 0.8)',
                    borderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    showLine: true,
                    tension: 0.3
                }
            ]
        },
        options: {
			responsive: true,
			maintainAspectRatio: false, 
			aspectRatio: 2, 
			animation: {
				duration: 0 
			},
			
            plugins: {
                title: {
                    display: true,
                    text: 'Circunferencia Cefálica para la Edad - Percentiles OMS',
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const label = context.dataset.label || '';
                            if (label === 'Paciente') {
                                const index = context.dataIndex;
                                return `${label}: ${context.parsed.y.toFixed(1)} cm (${datos.fechas[index]})`;
                            }
                            return `${label}: ${context.parsed.y.toFixed(1)} cm`;
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Edad (meses)'
                    },
                    min: 0,
                    max: Math.max(...datos.edades) + 2
                },
                y: {
                    title: {
                        display: true,
                        text: 'Circunferencia Cefálica (cm)'
                    },
                    beginAtZero: false
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

    generarCurvasPercentiles(tipo, edades) {
        const result = {
            P3: [],
            P15: [],
            P50: [],
            P85: [],
            P97: []
        };

        // Generar puntos para cada percentil en cada edad
        edades.forEach(edad => {
            const percentiles = this.obtenerPercentiles(tipo, edad);
            
            if (percentiles) {
                result.P3.push({ x: edad, y: percentiles.P3 });
                result.P15.push({ x: edad, y: percentiles.P15 });
                result.P50.push({ x: edad, y: percentiles.P50 });
                result.P85.push({ x: edad, y: percentiles.P85 });
                result.P97.push({ x: edad, y: percentiles.P97 });
            }
        });

        return result;
    }

    mostrarError(mensaje) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.card-body').prepend(errorHtml);
    }

    mostrarMensajeSinDatos() {
        const mensaje = `
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> No hay suficientes datos antropométricos para generar gráficas.
                <br><small>Se requieren al menos 2 registros con datos válidos.</small>
            </div>
        `;
        
        $('#chartTallaOMS').closest('.card-body').html(mensaje);
        $('#chartPesoOMS').closest('.card-body').html(mensaje);
        $('#chartCircOMS').closest('.card-body').html(mensaje);
    }
}


//fin scripts curvas

</script>


</body>
</html>
