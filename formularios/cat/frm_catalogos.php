<?php
// catalogos.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body class="p-4">

<div class="container">
  <h2 class="mb-4">Gestión de Catálogos</h2>

  <div class="row mb-3">
    <div class="col-md-4">
      <label for="tablaSelect" class="form-label">Seleccionar Catálogo:</label>
      <select id="tablaSelect" class="form-select">
        <option value="">-- Seleccione --</option>
		<option value="cat_tipo_atencion">Tipos de servicio</option>
        <option value="cat_tipo_precio">Tipos de precio</option>
        <option value="cat_especialidad">Especialidades médicas</option>
        <option value="cat_vacuna">Vacunas</option>
        <option value="cat_dosis">Dosis vacunas</option>
      </select>
    </div>
    <div class="col-md-8 d-flex align-items-end">
      <button id="btnNuevo" class="btn btn-primary ms-3" disabled>Nuevo Registro</button>
    </div>
  </div>

  <table id="tablaDatos" class="table table-bordered table-striped w-100">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Acciones</th>
      </tr>
    </thead>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalRegistro" tabindex="-1">
  <div class="modal-dialog">
    <form id="formRegistro" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="id">
        <input type="hidden" name="tabla" id="tabla">

        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre</label>
          <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
	if (typeof tabla === 'undefined') {
		var tabla;
	}

$('#tablaSelect').on('change', function() {
  const tablaSeleccionada = $(this).val();
  $('#tabla').val(tablaSeleccionada);
  $('#btnNuevo').prop('disabled', tablaSeleccionada === '');

  if (tablaSeleccionada) {
 	if ($.fn.DataTable.isDataTable('#tablaDatos')) {
		$('#tablaDatos').DataTable().destroy();
	}
	$('#tablaDatos').empty().append(`
	<thead>
	  <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
	</thead>
	`);
	
    tabla = $('#tablaDatos').DataTable({
      ajax: {
        url: FORM_URL + 'cat/obtener_catalogo.php',
        type: 'POST',
        data: { tabla: tablaSeleccionada }
      },
      columns: [
        { data: 'id' },
        { data: 'nombre' },
        {
          data: null,
          render: function(data) {
            return `
              <button class="btn btn-warning btn-sm btnEditar" data-id="${data.id}" data-nombre="${data.nombre}">Editar</button>
              <button class="btn btn-danger btn-sm btnEliminar" data-id="${data.id}">Eliminar</button>
            `;
          }
        }
      ],
      language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
		dom: 'Bfrtip',
		
		buttons: [
        {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel-fill"></i>',
            titleAttr: 'Exportar a Excel',
            className: 'btn btn-success btn-sm'
        },
        {
            extend: 'pdfHtml5',
            text: '<i class="bi bi-file-earmark-pdf-fill"></i>',
            titleAttr: 'Exportar a PDF',
            className: 'btn btn-danger btn-sm'
        },
        {
            extend: 'print',
            text: '<i class="bi bi-printer-fill"></i>',
            titleAttr: 'Imprimir',
            className: 'btn btn-secondary btn-sm'
        }
		]
	  
    });
  }
});

$('#btnNuevo').on('click', function() {
  $('#id').val('');
  $('#nombre').val('');
  $('#modalRegistro').modal('show');
});

$('#tablaDatos').on('click', '.btnEditar', function() {
  $('#id').val($(this).data('id'));
  $('#nombre').val($(this).data('nombre'));
  $('#modalRegistro').modal('show');
});

$('#tablaDatos').on('click', '.btnEliminar', function() {
  if (confirm('¿Seguro que deseas eliminar este registro?')) {
    $.post(FORM_URL + 'cat/eliminar_catalogo.php', { tabla: $('#tabla').val(), id: $(this).data('id') }, function(resp) {
      tabla.ajax.reload();
    });
  }
});

$('#formRegistro').on('submit', function(e) {
  e.preventDefault();
  $.post(FORM_URL + 'cat/guardar_catalogo.php', $(this).serialize(), function(resp) {
    $('#modalRegistro').modal('hide');
    tabla.ajax.reload();
  });
});
</script>
</body>
</html>
