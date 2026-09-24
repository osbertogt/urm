<?php
// archivo: precios_servicio.php
require_once '../../assets/dbc.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
<div class="container mt-4">
  <h4>Gestión de Precios</h4>
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPrecio">
  <i class="bi bi-plus-circle"></i> Agregar Precio</button>

  <table id="tablaPrecios" class="table table-bordered table-striped w-100">
    <thead>
      <tr>
        <th>Tipo</th>
        <th>Nombre</th>
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
          <h5 class="modal-title">Nuevo Precio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
		  <input type="hidden" id="id_precio" name="id">

          <div class="mb-3">
            <label>Tipo de Servicio</label>
            <select class="form-select" id="tipo_servicio" name="tipo_servicio" required>
              <option value="">Seleccione</option>
              <option value="analisis">Análisis</option>
              <option value="servicios">Servicios</option>
 		    </select>
          </div>

          <div class="mb-3">
            <label>Análisis</label>
            <select class="form-select" id="elemento" name="elemento" required></select>
          </div>

          <div class="mb-3">
            <label>Tipo de Precio</label>
            <select class="form-select" id="id_tipo_precio" name="id_tipo_precio" required></select>
          </div>

          <div class="mb-3">
            <label>Precio</label>
            <input type="number" step="0.01" min="0" class="form-control" name="precio" id="precio" required>
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
    ajax: FORM_URL + 'cat/listar_precios.php?accion=listar',
    columns: [
      { data: 'tipo' },
      { data: 'nombre' },
      { data: 'tipo_precio' },
      { 
		data: 'precio',
		render: function (data) {
			if (data === null) return '';
			return parseFloat(data).toLocaleString('en-US', { minimumFractionDigits: 2 });
		}
      },
      { data: 'fecha_ultimo_precio' },
      {
        data: null,
        render: function (data) {
          return `<button class="btn btn-sm btn-warning btnEditar" data-id="${data.id}">Editar</button>
                  <button class="btn btn-sm btn-danger btnEliminar" data-id="${data.id}">Eliminar</button>`;
        },
        orderable: false
      }
    ]
  });

  $('#tipo_servicio').change(function () {
    const tipo = $(this).val();
    if (tipo) {
	$.getJSON(FORM_URL + 'cat/listar_elementos.php', { accion: 'elementos', tipo: tipo }, function (data) {
	  const $elemento = $('#elemento');
	  $elemento.empty().append('<option value="">Seleccione</option>');
	  data.forEach(item => {
		$elemento.append(`<option value="${item.id}">${item.nombre}</option>`);
	  });
		$elemento.select2({
		theme: 'bootstrap5',
		width: '100%',
		dropdownParent: $('#modalPrecio')
		});
	});
    }
  });

  $.getJSON(FORM_URL + 'cat/listar_tipo_precio.php', { accion: 'tipos' }, function (data) {
    const $select = $('#id_tipo_precio');
    $select.empty().append('<option value="">Seleccione</option>');
    data.forEach(function (item) {
      $select.append(`<option value="${item.id}">${item.nombre}</option>`);
    });
  });

  $('#formPrecio').submit(function (e) {
    e.preventDefault();
    $.post(FORM_URL + 'cat/guardar_precio.php', $(this).serialize(), function (resp) {
      if (resp === 'ok') {
        $('#modalPrecio').modal('hide');
        tabla.ajax.reload();
      } else {
        alert(resp);
      }
    });
  });

  $('#tablaPrecios').on('click', '.btnEliminar', function () {
    if (!confirm('¿Desea eliminar este precio?')) return;
    const id = $(this).data('id');
    $.post(FORM_URL + 'cat/eliminar_precio.php', { id }, function () {
      tabla.ajax.reload();
    });
  });
  
	$('#tablaPrecios').on('click', '.btnEditar', function () {
	  const id = $(this).data('id');

	  $.getJSON(FORM_URL + 'cat/obtener_precio.php', { id }, function (data) {
		// Rellenar campos
		$('#id_precio').val(data.id); // input hidden
		$('#tipo_servicio').val(data.tipo).trigger('change');

		// Esperar a que se carguen las opciones del select2 antes de asignar el valor
		setTimeout(() => {
		  $('#elemento').val(data.id_elemento).trigger('change');
		}, 300);

		$('#id_tipo_precio').val(data.id_tipo_precio).trigger('change');
		$('#precio').val(data.precio);

		// Mostrar el modal
		$('#modalPrecio').modal('show');
	  });
	});
  
  
  
});
</script>
</body>
</html>
