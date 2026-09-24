<?php
session_start();
require_once '../../../assets/dbc.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>

<div class="container my-4">
  <h2>Usuarios registrados</h2>
  <button id="btnNuevo" class="btn btn-success mb-3"><i class="bi bi-plus-lg"></i> Nuevo Usuario</button>

  <table id="tblUsuarios" class="table table-striped table-bordered" style="width:100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre Usuario</th>
        <th>Nombres y Apellidos</th>
        <th>Email</th>
        <th>Estado</th>
        <th>Rol</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal Agregar/Editar Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formUsuario" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalUsuarioLabel">Nuevo Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="id_usuario" name="id_usuario" value=""/>
          <div class="mb-3">
            <label for="nombre_usuario" class="form-label">Nombre de Usuario *</label>
            <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control" required maxlength="50" />
          </div>
          <div class="mb-3">
            <label for="nombres" class="form-label">Nombres *</label>
            <input type="text" id="nombres" name="nombres" class="form-control" required maxlength="100" />
          </div>
          <div class="mb-3">
            <label for="apellidos" class="form-label">Apellidos *</label>
            <input type="text" id="apellidos" name="apellidos" class="form-control" required maxlength="100" />
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" class="form-control" required maxlength="100" />
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña <small class="text-muted">(Sólo para nuevo usuario o cambiar)</small></label>
            <input type="password" id="password" name="password" class="form-control" minlength="6" maxlength="100" />
          </div>
          <div class="mb-3">
            <label for="id_rol" class="form-label">Rol *</label>
            <select id="id_rol" name="id_rol" class="form-select" required>
              <option value="">-- Seleccionar Rol --</option>
              <?php
              $res = $conn->query("SELECT id, nombre FROM cat_rol ORDER BY nombre");
              while ($rol = $res->fetch_assoc()) {
                  echo "<option value=\"{$rol['id']}\">".htmlspecialchars($rol['nombre'])."</option>";
              }
              ?>
            </select>
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
            <label class="form-check-label" for="activo">Activo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
let tblUsuarios;

$(document).ready(function() {
  // Inicializar DataTable
  tblUsuarios = $('#tblUsuarios').DataTable({
    ajax: {
      url: FORM_URL+'cat/seg/listar_usuarios.php',
      dataSrc: 'data'
    },
    columns: [
      { data: 'id' },
      { data: 'nombre_usuario' },
      { data: null, render: d => `${d.nombres} ${d.apellidos}` },
      { data: 'email' },
      { data: 'activo', render: d => d == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' },
      { data: 'rol' },
      { data: null,
        render: function(data) {
          return `
            <button class="btn btn-warning btn-sm btnEditar" data-id="${data.id}" title="Editar"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-primary btn-sm btnInactivar" data-id="${data.id}" title="Cambiar estado"><i class="bi bi-toggles2"></i></button>
          `;
        }
      }
    ]
  });

  // Abrir modal para nuevo usuario
  $('#btnNuevo').on('click', () => {
    $('#modalUsuarioLabel').text('Nuevo Usuario');
    $('#formUsuario')[0].reset();
    $('#id_usuario').val('');
    $('#password').prop('required', true);
    $('#activo').prop('checked', true);
    $('#modalUsuario').modal('show');
  });

  // Editar usuario
  $('#tblUsuarios').on('click', '.btnEditar', function() {
    const id = $(this).data('id');
    $.ajax({
      url: FORM_URL+'cat/seg/obtener_usuario.php',
      method: 'GET',
      data: { id },
      dataType: 'json',
      success: function(res) {
        if(res.success){
          $('#modalUsuarioLabel').text('Editar Usuario');
          $('#id_usuario').val(res.data.id);
          $('#nombre_usuario').val(res.data.nombre_usuario);
          $('#nombres').val(res.data.nombres);
          $('#apellidos').val(res.data.apellidos);
          $('#email').val(res.data.email);
          $('#id_rol').val(res.data.id_rol);
          $('#activo').prop('checked', res.data.activo == 1);
          $('#password').val('').prop('required', false);
          $('#modalUsuario').modal('show');
        } else {
          alert('Error: ' + res.message);
        }
      },
      error: () => alert('Error cargando datos del usuario')
    });
  });

  // Guardar usuario (crear o actualizar)
  $('#formUsuario').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
      url: FORM_URL+'cat/seg/guardar_usuario.php',
      method: 'POST',
      data: formData,
      dataType: 'json',
      success: function(res) {
        if(res.success){
          $('#modalUsuario').modal('hide');
          tblUsuarios.ajax.reload(null, false);
          //alert('Usuario guardado correctamente.');
        } else {
          alert('Error: ' + res.message);
        }
      },
      error: () => alert('Error al guardar usuario')
    });
  });

  // Inactivar usuario
  $('#tblUsuarios').on('click', '.btnInactivar', function() {
    if(!confirm('¿Confirmas que quieres cambiar el estado de este usuario?')) return;
    const id = $(this).data('id');

    $.ajax({
      url: FORM_URL+'cat/seg/inactivar_usuario.php',
      method: 'POST',
      data: { id },
      dataType: 'json',
      success: function(res) {
        if(res.success){
          tblUsuarios.ajax.reload(null, false);
          //alert('Usuario inactivado correctamente.');
        } else {
          alert('Error: ' + res.message);
        }
      },
      error: () => alert('Error al inactivar usuario')
    });
  });

});
</script>

</body>
</html>
