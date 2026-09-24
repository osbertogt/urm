<?php
session_start();
require_once '../../assets/dbc.php';
// Verificar si hay un cliente activo en sesión
if (!isset($_SESSION['cliente_activo']) || empty($_SESSION['cliente_activo'])) {
    die("No hay cliente activo seleccionado.");
}
$id_cliente = $_SESSION['cliente_activo'];
$sql = "
SELECT trim(a.nombre_1) nombre, b.nombre sexo, trim(a.direccion_calle_avenida) domicilio, edad_anios edad
FROM tbl_persona a
JOIN cat_sexo b ON b.id=a.id_sexo
WHERE a.id = $id_cliente
";
$res = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($res)) {
    $nombre_cliente = $row['nombre'];
    $sexo_cliente = $row['sexo'];
    $edad_cliente = $row['edad'];
    $domicilio_cliente = $row['domicilio'];
}
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
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-azul">
                        <h5 class="card-title mb-0"><i class="bi bi-heart-pulse"></i> Consultas registradas</h5>
                    </div>
                    <div class="card-body">
                        <button id="btnNuevaAtencion" class="btn btn-primary mb-3">
                            <i class="bi bi-plus-circle"></i> Nueva consulta
                        </button>
                        
                        <table id="tblAtenciones" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Atención</th>
                                    <th>Nombre del Paciente</th>
                                    <th>Diagnostico</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva/Editar Atención -->
    <div class="modal fade" id="modalAtencion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header card-header-azul">
                    <h5 class="modal-title">Registrar nueva consulta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAtencion" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_atencion_consulta" id="id_atencion_consulta">
                        <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
  								<div class="card mb-2">
								  <div class="card-header" style="background-color: #CCCCFF; color: black;"><i class="bi bi-person"></i> Datos generales</div>
								  <div class="card-body">
									<div class="row g-2">
									  <div class="col-md-4">
										<label for="nombre_cliente" class="form-label">Paciente</label>
										<input type="text" name="nombre_cliente" id="nombre_cliente" class="form-control" value="<?php echo $nombre_cliente; ?>" disabled>
									  </div>
									  <div class="col-md-2">
										<label for="sexo_cliente" class="form-label">Sexo</label>
										<input type="text" name="sexo_cliente" id="sexo_cliente" class="form-control" value="<?php echo $sexo_cliente; ?>" disabled>
									  </div>
									  <div class="col-md-1">
										<label for="edad_cliente" class="form-label">Edad</label>
										<input type="text" name="edad_cliente" id="edad_cliente" class="form-control" value="<?php echo $edad_cliente; ?>" disabled>
									  </div>
									  <div class="col-md-3">
										<label for="domicilio_cliente" class="form-label">Domicilio</label>
										<input type="text" name="domicilio_cliente" id="domicilio_cliente" class="form-control" value="<?php echo $domicilio_cliente; ?>" disabled>
									  </div>
									  <div class="col-md-2">
										<label for="fecha" class="form-label">Fecha</label>
										<input type="date" name="fecha" id="fecha" class="form-control" value="<?=date('Y-m-d')?>" required>
									  </div>
									</div>
								  </div>
								</div>
                      
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="ficha-tab" data-bs-toggle="tab" data-bs-target="#ficha" type="button" role="tab">Ficha</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="laboratorios-tab" data-bs-toggle="tab" data-bs-target="#analisis" type="button" role="tab">Laboratorios</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="medicacion-tab" data-bs-toggle="tab" data-bs-target="#medicacion" type="button" role="tab">Medicación</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="signos-tab" data-bs-toggle="tab" data-bs-target="#signos" type="button" role="tab">Signos Vitales</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content p-3" id="myTabContent">
                            <!-- TAB 1: FICHA -->
                            <!-- Datos generales -->
                            <div class="tab-pane fade show active" id="ficha" role="tabpanel">
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
                                        <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Principales síntomas y signos</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="sintomas" rows="3" placeholder="Describa los síntomas presentados"></textarea>
                                    </div>
                                </div>
                                
                                <!-- Anamnesis Remota -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-naranja">
                                        <h6 class="mb-0"><i class="bi bi-clipboard-check"></i> Antecedentes</h6>
                                    </div>
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
                                
                                <!-- Diagnóstico -->
                                <div class="card mb-3">
                                    <div class="card-header" style="background-color: #6eaff1; color: white;">
                                        <h6 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Diagnóstico</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="diagnostico" rows="3" placeholder="Diagnóstico médico"></textarea>
                                    </div>
                                </div>
                                
                                <!-- Indicaciones Médicas -->
                                <div class="card mb-3">
                                    <div class="card-header" style="background-color: #5F9EA0; color: white;">
                                        <h6 class="mb-0"><i class="bi bi-prescription2"></i> Indicaciones médicas</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="indicaciones_medicas" rows="3" placeholder="Indicaciones y recomendaciones médicas"></textarea>
                                    </div>
                                </div>
                                <!-- Analisis indicados -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-verde">
                                        <h6 class="mb-0"><i class="bi bi-prescription2"></i> Laboratorios</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" id="laboratorios" name="laboratorios" rows="3" placeholder="Análisis de laboratorio indicados"></textarea>
                                    </div>
                                </div>
                                <!-- Medicamentos administrados -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-lila">
                                        <h6 class="mb-0"><i class="bi bi-prescription2"></i> Medicamentos administrados</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" id="medicamentos_administrados" name="medicamentos_administrados" rows="3" placeholder="Medicamentos administrados"></textarea>
                                    </div>
                                </div>
                                <!-- Receta de medicamentos -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-azul">
                                        <h6 class="mb-0"><i class="bi bi-prescription2"></i> Receta de medicamentos</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" id="receta" name="receta" rows="3" placeholder="Receta de medicamentos administrados"></textarea>
                                    </div>
                                </div>
                            </div>
							 <!-- TAB 2: LABORATORIOS -->
							<div class="tab-pane fade" id="analisis" role="tabpanel">
								<div class="d-flex justify-content-between align-items-center mb-3">
									<h5>Historial de Laboratorios</h5>
									<button type="button" class="btn btn-primary btn-sm" id="btnAgregarLaboratorio">
										<i class="bi bi-plus-circle"></i> Agregar Laboratorio
									</button>
								</div>
								
								<table id="tblLaboratorios" class="table table-striped table-bordered" style="width:100%">
									<thead>
										<tr>
											<th>ID</th>
											<th>Fecha Laboratorio</th>
											<th>Servicio</th>
											<th>Resultado</th>
											<th>Fecha Atención</th>
											<th>Acciones</th>
										</tr>
									</thead>
									<tbody>
										<!-- Los datos se cargarán via AJAX -->
									</tbody>
								</table>
								
								<!-- Modal para Laboratorio (mantener igual) -->
								<!-- Modal para Laboratorio -->
								<div class="modal fade" id="modalLaboratorio" tabindex="-1" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header card-header-verde">
												<h5 class="modal-title" id="modalLaboratorioLabel">Agregar Resultado de Laboratorio</h5>
												<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<form id="formLaboratorio" enctype="multipart/form-data">
												<div class="modal-body">
													<input type="hidden" name="id_laboratorio" id="id_laboratorio">
													<input type="hidden" name="id_atencion" id="id_atencion_lab">
													
													<div class="mb-3">
														<label for="fecha_laboratorio" class="form-label">Fecha</label>
														<input type="datetime-local" class="form-control" id="fecha_laboratorio" name="fecha" required>
													</div>
													
													<div class="mb-3">
														<label for="id_servicio" class="form-label">Servicio</label>
														<select class="form-select" id="id_servicio" name="id_servicio" required>
															<option value="">Seleccionar servicio...</option>
															<!-- Las opciones se cargarán via AJAX -->
														</select>
													</div>
													
													<div class="mb-3">
														<label for="resultado" class="form-label">Resultado</label>
														<textarea class="form-control" id="resultado" name="resultado" rows="3" placeholder="Ingrese los resultados"></textarea>
													</div>
													
													<div class="mb-3">
														<label for="informe_pdf" class="form-label">Informe PDF</label>
														<input type="file" class="form-control" id="informe_pdf" name="informe_pdf" accept=".pdf">
														<small class="form-text text-muted">Formatos aceptados: PDF (Máx. 5MB)</small>
													</div>
													
													<div id="informe_actual" class="mb-3" style="display: none;">
														<label class="form-label">Informe actual</label>
														<div class="alert alert-info">
															<i class="bi bi-file-earmark-pdf"></i> 
															<span id="nombre_informe"></span>
															<button type="button" class="btn btn-sm btn-outline-danger ms-2" id="btnEliminarInforme">
																<i class="bi bi-trash"></i> Eliminar
															</button>
														</div>
													</div>
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
													<button type="submit" class="btn btn-primary">Guardar</button>
												</div>
											</form>
										</div>
									</div>
								</div>								

							</div>								
                            
                            <!-- TAB 3: MEDICACIÓN -->
                            <div class="tab-pane fade" id="medicacion" role="tabpanel">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Módulo de medicación - Próximamente
                                </div>
                            </div>
                            
                            <!-- TAB 4: SIGNOS VITALES -->
                            <div class="tab-pane fade" id="signos" role="tabpanel">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Módulo de signos vitales - Próximamente
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Atención</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#tblAtenciones').DataTable({
            ajax: {
                url: FORM_URL+'cons/obtener_atenciones.php',
                data: {id_cliente: <?php echo $id_cliente; ?>},
                dataSrc: ''
            },
            columns: [
                { data: 'id' },
                { 
                    data: 'fecha_atencion',
                    render: function(data) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                { data: 'nombre_paciente' },
                { data: 'diagnostico' },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <button class="btn btn-warning btn-sm btn-editar" data-id="${data.id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-eliminar" data-id="${data.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        // Abrir modal para nueva atención
        $('#btnNuevaAtencion').click(function() {
            $('#formAtencion')[0].reset();
            $('#id_atencion_consulta').val('');
            $('#modalAtencion').modal('show');
        });

        // Enviar formulario de atención
		$('#formAtencion').submit(function(e) {
			e.preventDefault();
			$.ajax({
				url: FORM_URL+'cons/guardar_atencion.php',
				type: 'POST',
				data: $(this).serialize(),
				dataType: 'json', // 👈 muy importante
				success: function(result) {
					console.log("Respuesta del servidor:", result);

					if (result.success) {
						$('#modalAtencion').modal('hide');
						table.ajax.reload();
						//alert('Atención guardada correctamente');
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

        // Manejar checks de anamnesis (solo uno seleccionado)
        $('input[type="checkbox"][name$="_si"], input[type="checkbox"][name$="_no"]').change(function() {
            const name = $(this).attr('name');
            const prefix = name.substring(0, name.lastIndexOf('_'));
            const isSi = name.endsWith('_si');
            
            if ($(this).is(':checked')) {
                // Desmarcar el opuesto
                $(`input[name="${prefix}_${isSi ? 'no' : 'si'}"]`).prop('checked', false);
            }
        });
		
		
    // Inicializar DataTable de laboratorios con historial completo
    function inicializarTablaLaboratorios() {
        if ($.fn.DataTable.isDataTable('#tblLaboratorios')) {
            $('#tblLaboratorios').DataTable().destroy();
        }
        
        $('#tblLaboratorios').DataTable({
            ajax: {
                url: FORM_URL+'cons/obtener_laboratorios.php',
                data: {id_cliente: <?php echo $id_cliente; ?>},
                dataSrc: ''
            },
            columns: [
                { data: 'id' },
                { 
                    data: 'fecha',
                    render: function(data) {
                        return data ? new Date(data).toLocaleString() : 'N/A';
                    }
                },
                { data: 'servicio' },
                { 
                    data: 'resultado',
                    render: function(data) {
                        return data || 'N/A';
                    }
                },
                { 
                    data: 'fecha_atencion',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString() : 'N/A';
                    },
                    title: 'Fecha Atención'
                },
                {
                    data: null,
                    render: function(data) {
                        let botones = `
                            <button class="btn btn-warning btn-sm btn-editar-lab" data-id="${data.id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-eliminar-lab" data-id="${data.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                        
                        if (data.informe) {
                            botones += `
                                <button class="btn btn-info btn-sm btn-ver-informe" data-id="${data.id}" data-archivo="${data.informe}">
                                    <i class="bi bi-eye"></i>
                                </button>
                            `;
                        }
                        
                        return botones;
                    }
                }
            ],
            order: [[1, 'desc']],
            language: {
                "emptyTable": "No hay laboratorios registrados para este paciente",
                "zeroRecords": "No se encontraron resultados"
            }
        });
    }

    // Cargar servicios para el select
    function cargarServicios() {
        $.getJSON(FORM_URL+'cons/obtener_servicios.php', function(data) {
            let html = '<option value="">Seleccionar servicio...</option>';
            data.forEach(servicio => {
                html += `<option value="${servicio.id}">${servicio.nombre}</option>`;
            });
            $('#id_servicio').html(html);
        });
    }

    // Event listeners para laboratorios
    $('#btnAgregarLaboratorio').click(function() {
        $('#formLaboratorio')[0].reset();
        $('#id_laboratorio').val('');
        $('#id_atencion_lab').val($('#id_atencion_consulta').val());
        $('#informe_actual').hide();
        cargarServicios();
        $('#modalLaboratorio').modal('show');
    });

    $(document).on('click', '.btn-editar-lab', function() {
        const id = $(this).data('id');
        $.getJSON(FORM_URL+'cons/obtener_laboratorio.php', {id}, function(data) {
            $('#id_laboratorio').val(data.id);
            $('#id_atencion_lab').val(data.id_atencion);
            $('#fecha_laboratorio').val(data.fecha);
            $('#id_servicio').val(data.id_servicio);
            $('#resultado').val(data.resultado);
            
            if (data.informe) {
                $('#informe_actual').show();
                $('#nombre_informe').text(data.informe);
            } else {
                $('#informe_actual').hide();
            }
            
            cargarServicios();
            $('#modalLaboratorio').modal('show');
        });
    });

    $(document).on('click', '.btn-eliminar-lab', function() {
        if (confirm('¿Eliminar este resultado de laboratorio?')) {
            const id = $(this).data('id');
            $.post(FORM_URL+'cons/eliminar_laboratorio.php', {id}, function() {
                $('#tblLaboratorios').DataTable().ajax.reload();
            });
        }
    });

    $(document).on('click', '.btn-ver-informe', function() {
        const archivo = $(this).data('archivo');
        window.open('informes/' + archivo, '_blank');
    });

    $('#formLaboratorio').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: FORM_URL+'cons/guardar_laboratorio.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        $('#modalLaboratorio').modal('hide');
                        $('#tblLaboratorios').DataTable().ajax.reload();
                        alert('Laboratorio guardado correctamente');
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (e) {
                    alert('Error al procesar la respuesta del servidor');
                }
            }
        });
    });

    $('#btnEliminarInforme').click(function() {
        if (confirm('¿Eliminar el informe actual?')) {
            const id = $('#id_laboratorio').val();
            $.post(FORM_URL+'cons/eliminar_informe.php', {id}, function() {
                $('#informe_actual').hide();
                alert('Informe eliminado correctamente');
            });
        }
    });

    // Inicializar tabla de laboratorios cuando se abre el modal
    $('#modalAtencion').on('shown.bs.modal', function() {
        inicializarTablaLaboratorios();
    });

    // Inicializar tabla de laboratorios cuando se hace clic en el tab
    $('#laboratorios-tab').click(function() {
        inicializarTablaLaboratorios();
    });

    // Cargar servicios cuando se abre el modal de laboratorio
    $('#modalLaboratorio').on('shown.bs.modal', function() {
        cargarServicios();
    });
});
</script>
</body>
</html>