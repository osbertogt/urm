<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
<div class="container mt-4">
    <h4>Gestión de Roles</h4>
    <button id="btnAddRol" class="btn btn-primary mb-3">
        <i class="bi bi-plus-lg"></i> Nuevo Rol
    </button>

    <table id="tblRoles" class="table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre del Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal para Agregar/Editar Rol -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <form id="formRol" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="rol_id">
                <div class="mb-3">
                    <label for="rol_nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="rol_nombre" required>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Ver/Editar Permisos -->
<div class="modal fade" id="modalPermisos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPermisosLabel">Permisos del Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Selects para agregar permisos -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <select id="selModulo" class="form-select">
                            <option value="">-- Módulo --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="selOpcion" class="form-select">
                            <option value="">-- Opción --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="selPermiso" class="form-select">
                            <option value="">-- Permiso --</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button id="btnAddPermiso" class="btn btn-success w-100">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Tabla permisos -->
                <table class="table table-bordered" id="tblPermisos">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th>Opción</th>
                            <th>Permiso</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>
</div>
<script>
let tblRoles;
let rolSeleccionado = null;

$(document).ready(function () {
    // Cargar tabla de roles

// Variable global para la tabla
let tblRoles = null;

function inicializarOActualizarTablaRoles() {
  if (!$.fn.DataTable.isDataTable('#tblRoles')) {
    tblRoles = $('#tblRoles').DataTable({
      ajax: FORM_URL + 'cat/seg/listar_roles.php',
      columns: [
        { data: 'id' },
        { data: 'nombre' },
        {
          data: null,
          render: function (data) {
            return `
              <button class="btn btn-warning btn-sm btnEdit" data-id="${data.id}" data-nombre="${data.nombre}" title="Editar">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-danger btn-sm btnDel" data-id="${data.id}" title="Eliminar">
                <i class="bi bi-trash"></i>
              </button>
              <button class="btn btn-info btn-sm btnPermisos" data-id="${data.id}" data-nombre="${data.nombre}" title="Ver Permisos">
                <i class="bi bi-key"></i>
              </button>
            `;
          }
        }
      ]
    });
  } else {
    tblRoles.ajax.reload(null, false); // false para no resetear la paginación
  }
}

  inicializarOActualizarTablaRoles();


    // Abrir modal para nuevo rol
    $('#btnAddRol').click(function () {
        $('#rol_id').val('');
        $('#rol_nombre').val('');
        $('#modalRol').modal('show');
    });

    // Guardar rol
    $('#formRol').submit(function (e) {
        e.preventDefault();
        $.post(FORM_URL+'cat/seg/guardar_rol.php', $(this).serialize(), function () {
            $('#modalRol').modal('hide');
            tblRoles.ajax.reload();
        });
    });

    // Editar rol
    $('#tblRoles').on('click', '.btnEdit', function () {
        const id = $(this).data('id');
        $.getJSON(FORM_URL+'cat/seg/get_rol.php', { id }, function (data) {
            $('#rol_id').val(data.id);
            $('#rol_nombre').val(data.nombre);
            $('#modalRol').modal('show');
        });
    });

    // Eliminar rol
    $('#tblRoles').on('click', '.btnDel', function () {
        if (confirm('¿Eliminar este rol?')) {
            const id = $(this).data('id');
            $.post(FORM_URL+'cat/seg/eliminar_rol.php', { id }, function () {
                tblRoles.ajax.reload();
            });
        }
    });

    // Ver permisos
    $('#tblRoles').on('click', '.btnPermisos', function () {
        rolSeleccionado = $(this).data('id');
		rolNombre = $(this).data('nombre');
        cargarSelects();
        cargarPermisos();
        $('#modalPermisos').modal('show');
		$('#modalPermisosLabel').text(`Permisos del rol: ${rolNombre}`);
    });

    // Cargar opciones cuando cambia módulo
    $('#selModulo').change(function () {
        const idModulo = $(this).val();
        $.getJSON(FORM_URL+'cat/seg/get_opciones.php', { id_modulo: idModulo }, function (data) {
            let html = '<option value="">-- Opción --</option>';
            data.forEach(o => html += `<option value="${o.id}">${o.nombre}</option>`);
            $('#selOpcion').html(html);
        });
    });

    // Agregar permiso
    $('#btnAddPermiso').click(function (e) {
        e.preventDefault();
        const datos = {
            id_rol: rolSeleccionado,
            id_modulo: $('#selModulo').val(),
            id_opcion: $('#selOpcion').val(),
            id_permiso: $('#selPermiso').val()
        };
        $.post(FORM_URL+'cat/seg/agregar_permiso.php', datos, function () {
            cargarPermisos();
        });
    });

    // Eliminar permiso
    $('#tblPermisos').on('click', '.btnDelPermiso', function () {
        if (confirm('¿Eliminar este permiso?')) {
            const id = $(this).data('id');
            $.post(FORM_URL+'cat/seg/eliminar_permiso.php', { id }, function () {
                cargarPermisos();
            });
        }
    });
});

function cargarSelects() {
    $.getJSON(FORM_URL+'cat/seg/get_modulos.php', function (data) {
        let html = '<option value="">-- Módulo --</option>';
        data.forEach(m => html += `<option value="${m.id}">${m.nombre}</option>`);
        $('#selModulo').html(html);
    });
    $.getJSON(FORM_URL+'cat/seg/get_permisos.php', function (data) {
        let html = '<option value="">-- Permiso --</option>';
        data.forEach(p => html += `<option value="${p.id}">${p.nombre}</option>`);
        $('#selPermiso').html(html);
    });
}

function cargarPermisos() {
    $.getJSON(FORM_URL+'cat/seg/listar_permisos.php', { id_rol: rolSeleccionado }, function (data) {
        let html = '';
        data.forEach(p => {
            html += `
                <tr>
                    <td>${p.modulo}</td>
                    <td>${p.opcion}</td>
                    <td>${p.permiso}</td>
                    <td>
                        <button class="btn btn-danger btn-sm btnDelPermiso" data-id="${p.id_permiso_rol}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        $('#tblPermisos tbody').html(html);
    });
}
</script>
</body>
</html>