<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body class="p-4">

  <div class="container">
    <h4 class="mb-4">Gestión de Catálogos</h4>

    <div class="mb-3">
      <label for="tablaseleccionada" class="form-label">Selecciona un catálogo:</label>
      <select class="form-select" id="tablaseleccionada" name="tablaseleccionada">
        <option value="cat_categoria_servicio">Categorías de análisis</option>
        <option value="cat_categoria_variable">Categorías de parámetros</option>
        <option value="cat_unidad_medida_variable">Unidades de medida</option>
     </select>
    </div>

    <div class="mb-3">
      <button class="btn btn-success" id="btnNuevo">Nuevo registro</button>
    </div>

    <table id="tablaCatalogo" class="table table-bordered table-striped" style="width:100%">
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
  <div class="modal fade" id="modalRegistro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="formRegistro">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Registro</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="id" name="id">
            <div class="mb-3">
              <label for="nombre" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="nombre" name="nombre" required>
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

  <script>
    let tabla, tablaSeleccionada = 'cat_categoria_servicio';

    function cargarTabla() {
      if (tabla) tabla.destroy();

      tabla = $('#tablaCatalogo').DataTable({
        ajax: {
          url: FORM_URL + 'cat/catalogo_listar.php',
          type: 'POST',
          data: { tabla: tablaSeleccionada }
        },
        columns: [
          { data: 'id' },
          { data: 'nombre' },
          {
            data: null,
            render: function (data) {
              return `
                <button class="btn btn-sm btn-warning editar" data-id="${data.id}" data-nombre="${data.nombre}" data-toggle='tooltip' title='Editar'><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger eliminar" data-id="${data.id}" data-toggle='tooltip' title='Eliminar'><i class="bi bi-trash"></i></button>
              `;
            }
          }
        ],
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

    $(document).ready(function () {
      cargarTabla();

      $('#tablaSelect').change(function () {
        tablaSeleccionada = $(this).val();
        cargarTabla();
      });

      $('#btnNuevo').click(function () {
        $('#id').val('');
        $('#nombre').val('');
        new bootstrap.Modal('#modalRegistro').show();
      });

      $('#tablaCatalogo tbody').on('click', '.editar', function () {
        $('#id').val($(this).data('id'));
        $('#nombre').val($(this).data('nombre'));
        new bootstrap.Modal('#modalRegistro').show();
      });

      $('#tablaCatalogo tbody').on('click', '.eliminar', function () {
        if (confirm('¿Desea eliminar este registro?')) {
          $.post(FORM_URL + 'cat/catalogo_eliminar.php', { tabla: tablaSeleccionada, id: $(this).data('id') }, function () {
            tabla.ajax.reload();
          });
        }
      });

      $('#formRegistro').submit(function (e) {
        e.preventDefault();
 console.log(tablaSeleccionada);
		const datos = $(this).serialize() + '&tabla=' + tablaSeleccionada;
        $.post(FORM_URL + 'cat/catalogo_guardar.php', datos, function () {
          bootstrap.Modal.getInstance(document.getElementById('modalRegistro')).hide();
          tabla.ajax.reload();
        });
      });
    });
  </script>
</body>
</html>
