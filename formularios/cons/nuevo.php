$(document).ready(function() {
    // Inicializar DataTable principal
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
            dataType: 'json',
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
    
    // ========== LABORATORIOS ==========
    
    // Inicializar DataTable de laboratorios con historial completo
    function inicializarTablaLaboratorios() {
        if ($.fn.DataTable.isDataTable('#tblLaboratorios')) {
            $('#tblLaboratorios').DataTable().destroy();
        }
        
        $('#tblLaboratorios').DataTable({
            ajax: {
                url: FORM_URL+'cons/obtener_laboratorios.php',
                data: {id_cliente: <?php echo $id_cliente; ?>}, // Cambiado a id_cliente
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
            order: [[1, 'desc']], // Ordenar por fecha descendente
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
