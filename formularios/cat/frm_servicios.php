<?php
// index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
<div class="container mt-4">
    <button id="btnNuevo" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalAnalisis">
       <i class="bi bi-plus-circle"></i> Nuevo Análisis
    </button>
    <h5 class="mb-4">Análisis registrados</h5>

    <table id="tablaServicios" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Análisis</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalAnalisis" tabindex="-1">
    <div class="modal-dialog">
        <form id="formServicio">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Análisis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del análisis</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="id_tipo_analisis" class="form-label">Categoría</label>
                        <select id="id_tipo_analisis" name="id_tipo_analisis" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php
                            require_once '../../assets/dbc.php';
                            $sql = "SELECT id, nombre FROM cat_tipo_analisis ORDER BY nombre";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['nombre']) . "</option>";
                            }
                            ?>
                        </select>
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
<!-- Modal Variables -->
<div class="modal fade" id="modalVariables" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Parámetros del análisis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Tabla de variables -->
                <table id="tablaVariables" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Categoria</th>
                            <th>Nombre</th>
                            <th>U.Medida</th>
                            <th>Referencia</th>
                            <th>Máximo</th>
                            <th>Mínimo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <hr>

                <!-- Formulario agregar variable -->
                 <!-- Formulario agregar / editar variable -->
                <form id="formVariable">
                    <input type="hidden" id="id_variable" name="id">
                    <input type="hidden" id="id_servicio_var" name="id_servicio">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Categoría</label>
                            <select id="id_cat_categoria_variable" name="id_cat_categoria_variable" class="form-select" required></select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Unidad de medida</label>
                            <select id="id_unidad_medida_variable" name="id_unidad_medida_variable" class="form-select" required></select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Nombre variable</label>
                            <input type="text" id="nombre_variable" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Referencia</label>
                            <input type="text" id="referencia" name="referencia" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Valor máximo</label>
                            <input type="number" step="0.01" id="valor_max" name="valor_normal_maximo" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Valor mínimo</label>
                            <input type="number" step="0.01" id="valor_min" name="valor_normal_minimo" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Guardar variable</button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
//let tabla;
//let idServicioSeleccionado = 0;

$(document).ready(function () {
 
        // Si no está inicializado, lo creamos
        tabla = $('#tablaServicios').DataTable({
            ajax: FORM_URL + 'cat/obtener_servicios.php',
            columns: [
                { data: 'id' },
                { data: 'nombre_servicio' },
                { data: 'nombre_tipo' },
                {
                    data: null,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-warning me-1" 
                                onclick="editarServicio(${data.id}, '${data.nombre_servicio}', ${data.id_tipo_analisis})" 
                                title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger me-1" 
                                onclick="eliminarServicio(${data.id})" 
                                title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-info" 
                                onclick="gestionarVariables(${data.id}, ${data.id_tipo_analisis})" 
                                title="Parámetros">
                                <i class="bi bi-list-check"></i>
                            </button>
                        `;
                    }
                }
            ]
        });
    
	
    // Nuevo
    $('#btnNuevo').on('click', function () {
        $('#formServicio')[0].reset();
        $('#id').val('');
        $('#modalAnalisis').modal('show');
    });

    // Guardar
    $('#formServicio').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: FORM_URL + 'cat/guardar_servicio.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function (resp) {
                let res = JSON.parse(resp);
                if (res.ok) {
                    $('#modalAnalisis').modal('hide');
                    tabla.ajax.reload();
                } else {
                    alert(res.msg);
                }
            }
        });
    });

});

let tablaVariables;
//let idServicioSeleccionado = 0;
//let idTipoAnalisisSeleccionado = 0;

function gestionarVariables(id_servicio, id_tipo_analisis) {
    idServicioSeleccionado = id_servicio;
    idTipoAnalisisSeleccionado = id_tipo_analisis;
    $('#id_servicio_var').val(id_servicio);

    // Cargar selects dinámicos
    $.get(FORM_URL+'cat/obtener_categorias.php', { id_tipo_analisis }, function (resp) {
        let data = JSON.parse(resp);
        let select = $('#id_cat_categoria_variable');
        select.empty().append('<option value="">Seleccione...</option>');
        data.forEach(c => select.append(`<option value="${c.id}">${c.nombre}</option>`));
    });

    $.get(FORM_URL+'cat/obtener_unidades.php', function (resp) {
        let data = JSON.parse(resp);
        let select = $('#id_unidad_medida_variable');
        select.empty().append('<option value="">Seleccione...</option>');
        data.forEach(u => select.append(`<option value="${u.id}">${u.nombre}</option>`));
    });

    // Inicializar o recargar DataTable
    if (!tablaVariables) {
        tablaVariables = $('#tablaVariables').DataTable({
            ajax: {
                url: FORM_URL + 'cat/obtener_variables.php',
                data: function (d) {
                    d.id_servicio = idServicioSeleccionado;
                },
                dataSrc: ''
            },
            columns: [
                { data: 'id' },
                { data: 'categoria' },
                { data: 'nombre' },
                { data: 'unidad' },
                { data: 'referencia' },
                { data: 'valor_normal_maximo' },
                { data: 'valor_normal_minimo' },
                {
                    data: null,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-warning btn-editar-variable"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger btn-eliminar-variable"><i class="bi bi-trash"></i></button>
                        `;
                    }
                }
            ]
        });
    } else {
        tablaVariables.ajax.reload();
    }

    $('#modalVariables').modal('show');
}

// Guardar variable
$('#formVariable').on('submit', function (e) {
    e.preventDefault();
    $.post(FORM_URL + 'cat/guardar_variable.php', $(this).serialize(), function (resp) {
        let res = JSON.parse(resp);
        if (res.ok) {
            tablaVariables.ajax.reload();
            $('#formVariable')[0].reset();
            $('#id_variable').val('');
        } else {
            alert(res.msg);
        }
    });
});

// Editar variable
$('#tablaVariables').on('click', '.btn-editar-variable', function () {
    let data = tablaVariables.row($(this).parents('tr')).data();

    $('#id_variable').val(data.id);
    $('#id_cat_categoria_variable').val(data.id_cat_categoria_variable);
    $('#id_unidad_medida_variable').val(data.id_unidad_medida_variable);
    $('#nombre_variable').val(data.nombre);
    $('#referencia').val(data.referencia);
    $('#valor_max').val(data.valor_normal_maximo);
    $('#valor_min').val(data.valor_normal_minimo);
});

// Eliminar variable
$('#tablaVariables').on('click', '.btn-eliminar-variable', function () {
    let data = tablaVariables.row($(this).parents('tr')).data();
    if (confirm('¿Eliminar variable?')) {
        $.post(FORM_URL + 'cat/eliminar_variable.php', { id: data.id }, function (resp) {
            let res = JSON.parse(resp);
            if (res.ok) {
                tablaVariables.ajax.reload();
            } else {
                alert(res.msg);
            }
        });
    }
});


</script>
</body>
</html>
