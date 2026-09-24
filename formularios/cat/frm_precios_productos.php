<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
<div class="container py-4">
  <h4>Precios de Productos</h4>
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPrecio">
  <i class="bi bi-plus-circle"></i> Agregar Precio</button>

  <table id="tablaPrecios" class="table table-bordered table-striped w-100">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Tipo de Precio</th>
        <th>Precio</th>
        <th>Fecha</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalPrecio" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formPrecio">
        <div class="modal-header">
          <h5 class="modal-title">Registrar / Editar Precio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="id" name="id">
          <div class="mb-3">
            <label>Producto</label>
            <select id="id_producto" name="id_producto" class="form-select" required></select>
          </div>
          <div class="mb-3">
            <label>Tipo de Precio</label>
            <select id="id_tipo_precio" name="id_tipo_precio" class="form-select" required></select>
          </div>
          <div class="mb-3">
            <label>Precio</label>
            <input type="number" name="precio" id="precio" class="form-control" step="0.01" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
  const tabla = $('#tablaPrecios').DataTable({
    ajax: FORM_URL + 'cat/listar_precios_prod.php?accion=listar',
    columns: [
      { data: 'producto' },
      { data: 'tipo_precio' },
      { data: 'precio' },
      { data: 'fecha_ultimo_precio' },
      {
        data: null,
        render: function (data) {
          return `
            <button class="btn btn-warning btn-sm btnEditar" data-id="${data.id}">Editar</button>
            <button class="btn btn-danger btn-sm btnEliminar" data-id="${data.id}">Eliminar</button>
          `;
        },
        orderable: false
      }
    ]
  });

  function cargarSelects() {
    $.getJSON(FORM_URL + 'cat/listar_productos_prod.php?accion=productos', function (data) {
      const $select = $('#id_producto');
      $select.empty().append('<option value="">Seleccione</option>');
      data.forEach(el => {
        $select.append(`<option value="${el.id}">${el.nombre}</option>`);
      });
      $select.select2({ dropdownParent: $('#modalPrecio') });
    });

    $.getJSON(FORM_URL+'cat/listar_tipo_precio_prod.php?accion=tipos', function (data) {
      const $tipo = $('#id_tipo_precio');
      $tipo.empty().append('<option value="">Seleccione</option>');
      data.forEach(el => {
        $tipo.append(`<option value="${el.id}">${el.nombre}</option>`);
      });
    });
  }

  $('#modalPrecio').on('show.bs.modal', function () {
    cargarSelects();
    $('#formPrecio')[0].reset();
    $('#id').val('');
  });

  $('#formPrecio').submit(function (e) {
    e.preventDefault();
    $.post(FORM_URL+'cat/guardar_precio_prod.php', $(this).serialize(), function (resp) {
      $('#modalPrecio').modal('hide');
      tabla.ajax.reload();
    });
  });

  $('#tablaPrecios').on('click', '.btnEditar', function () {
	  const id = $(this).data('id');
	  $.getJSON(FORM_URL + 'cat/obtener_precio_prod.php', { id }, function (data) {
		$('#id').val(data.id);
		$('#id_producto').val(data.id_producto).trigger('change');
		$('#id_tipo_precio').val(data.id_tipo_precio);
		$('#precio').val(data.precio);
		$('#modalPrecio').modal('show');
	  });
  });

  $('#tablaPrecios').on('click', '.btnEliminar', function () {
    const id = $(this).data('id');
    if (confirm('¿Desea eliminar este precio?')) {
      $.post(FORM_URL+'cat/eliminar_precio_prod.php', { id }, function () {
        tabla.ajax.reload();
      });
    }
  });
});
</script>
</body>
</html>
