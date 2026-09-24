<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>

<div class="container mt-4">
  <h5>Servicios registrados</h5>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAnalisis">
    <i class="bi bi-plus-circle"></i> Nuevo servicio
  </button>
  <br>
  <table id="tablaAnalisis" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Tipo de servicio</th>
        <th>Costo</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalAnalisis" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAnalisis">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAnalisisLabel">Nuevo servicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="id_analisis" name="id_analisis">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required>
          </div>
			<div class="mb-3" style="max-width: 200px;">
			  <label for="costo" class="form-label">Costo</label>
			  <input type="number" class="form-control" id="costo" name="costo" placeholder="0.00" step="0.01">
			</div>
          <div class="mb-3">
            <label for="id_tipo_atencion" class="form-label">Tipo de servicio</label>
            <select id="id_tipo_atencion" name="id_tipo_atencion" class="form-select" required></select>
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
$(document).ready(function() {
  // Inicializar DataTable
  let tablaAnalisis = $('#tablaAnalisis').DataTable({
    ajax: FORM_URL+'cat/listar_analisis.php',
    columns: [
      { data: 'id' },
      { data: 'nombre' },
      { data: 'tipo_atencion' },
      { data: 'costo' },
      {
        data: null,
        render: function(data) {
          return `
            <button class="btn btn-warning btn-sm btnEditar" data-id="${data.id}">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-danger btn-sm btnEliminar" data-id="${data.id}">
              <i class="bi bi-trash"></i>
            </button>
          `;
        },
        orderable: false
      }
    ]
  });

  // Cargar tipos de atención en el select
  $.getJSON(FORM_URL+'cat/obtener_tipos_atencion.php', function(data) {
    $('#id_tipo_atencion').html('<option value="">-- seleccionar --</option>');
    data.forEach(t => {
      $('#id_tipo_atencion').append(`<option value="${t.id}">${t.nombre}</option>`);
    });
  });

  // Guardar / Editar análisis
  $('#formAnalisis').submit(function(e) {
    e.preventDefault();
    $.post(FORM_URL+'cat/guardar_analisis.php', $(this).serialize(), function(resp) {
      if (resp.status === 'success') {
        tablaAnalisis.ajax.reload();
        $('#modalAnalisis').modal('hide');
      } else {
        alert(resp.message);
      }
    }, 'json');
  });

  // Editar
  $('#tablaAnalisis').on('click', '.btnEditar', function() {
    const id = $(this).data('id');
    $.getJSON(FORM_URL+'cat/obtener_analisis.php', { id }, function(data) {
      $('#id_analisis').val(data.id);
      $('#nombre').val(data.nombre);
      $('#costo').val(data.costo);
      $('#id_tipo_atencion').val(data.id_tipo_atencion);
      $('#modalAnalisisLabel').text('Editar servicio');
      $('#modalAnalisis').modal('show');
    });
  });

  // Eliminar
  $('#tablaAnalisis').on('click', '.btnEliminar', function() {
    if (!confirm('¿Seguro que desea eliminar este servicio?')) return;
    const id = $(this).data('id');
    $.post(FORM_URL+'cat/eliminar_analisis.php', { id }, function(resp) {
      if (resp.status === 'success') tablaAnalisis.ajax.reload();
      else alert(resp.message);
    }, 'json');
  });

  // Reset modal al cerrar
  $('#modalAnalisis').on('hidden.bs.modal', function() {
    $('#formAnalisis')[0].reset();
    $('#id_analisis').val('');
    $('#modalAnalisisLabel').text('Nuevo servicio');
  });
});
</script>
</body>
</html>
