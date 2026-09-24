<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Catálogos CRUD</title>
	<!-- CSS primero -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

	<!-- JS en orden correcto -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

	<!-- DataTables JS -->
	<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

</head>
<body class="p-4">

<div class="container mt-4">
  <div class="row mb-3">
    <div class="col-md-4">
      <label for="departamento" class="form-label">Departamento</label>
      <select id="departamento" class="form-select">
        <option value="">Seleccione</option>
        <?php
          include '../../dbc.php';
          $sql = "SELECT id, nombre FROM cat_departamento ORDER BY nombre";
          $res = mysqli_query($conn, $sql);
          while ($row = mysqli_fetch_assoc($res)) {
            echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
          }
        ?>
      </select>
    </div>
    <div class="col-md-8 text-end">
	  <button class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#modalMunicipio" id="btnNuevoMunicipio">+ Nuevo Municipio</button>

    </div>
  </div>

  <table id="tablaMunicipios" class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Municipio</th>
        <th>Departamento</th>
        <th>Acciones</th>
      </tr>
    </thead>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalMunicipio" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formMunicipio">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Municipio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
          <div class="mb-3">
            <label>Nombre del Municipio</label>
            <input type="text" class="form-control" name="nombre" id="nombre" required>
          </div>
          <div class="mb-3">
            <label>Departamento</label>
            <select class="form-select" name="id_departamento" id="id_departamento" required>
              <?php
                $sql = "SELECT id, nombre FROM cat_departamento ORDER BY nombre";
                $res = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($res)) {
                  echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                }
              ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" type="submit">Guardar</button>
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>


<script>
	let tabla;

	$(document).ready(function () {
		
	// Reiniciar formulario al mostrar modal
	$('#modalMunicipio').on('show.bs.modal', function () {
	  $('#formMunicipio')[0].reset();
	  $('#id').val('');
	});

    tabla = $('#tablaMunicipios').DataTable({
    ajax: {
      url: 'formularios/cat/municipios_listar.php',
      dataSrc: 'data'
    },
    columns: [
      { data: 'id' },
      { data: 'nombre' },
      { data: 'departamento' },
      {
        data: null,
        render: function (data, type, row) {
          return `
            <button class="btn btn-sm btn-primary editar" data-id="${row.id}">Editar</button>
            <button class="btn btn-sm btn-danger eliminar" data-id="${row.id}">Eliminar</button>
          `;
        }
      }
    ]
  });

  // Guardar
	$('#formMunicipio').on('submit', function (e) {
	  e.preventDefault();
	  $.post('formularios/cat/municipios_guardar.php', $(this).serialize(), function (respuesta) {
		let res = JSON.parse(respuesta);
		if (res.status === 'ok') {
		  $('#modalMunicipio').modal('hide');
		  tabla.ajax.reload(null, false); // false = no reinicia el paginado

		  $('#formMunicipio')[0].reset();
		} else {
		  alert('Error al guardar: ' + res.mensaje);
		}
	  });
	});


  // Cargar datos para editar
  $('#tablaMunicipios').on('click', '.editar', function () {
    let id = $(this).data('id');
    $.getJSON('formularios/cat/municipios_get.php', { id }, function (data) {
      $('#id').val(data.id);
      $('#nombre').val(data.nombre);
      $('#id_departamento').val(data.id_departamento);
      $('#modalMunicipio').modal('show');
    });
  });

  // Eliminar
  $('#tablaMunicipios').on('click', '.eliminar', function () {
    let id = $(this).data('id');
    if (confirm('¿Eliminar municipio?')) {
      $.post('formularios/cat/municipios_eliminar.php', { id }, function () {
        tabla.ajax.reload();
      });
    }
  });
});
</script>

</body>
</html>
