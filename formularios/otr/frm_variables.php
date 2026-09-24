<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
  <div class="container mt-4">
    <h4>Variables Registradas</h4>
    <button class="btn btn-primary mb-3" id="btnNuevaVariable">
      <i class="bi bi-plus-circle"></i> Nueva Variable
    </button>

    <table id="tablaVariables" class="table table-bordered table-striped" style="width:100%">
      <thead>
        <tr>
          <th>Tipo de Análisis</th>
          <th>Categoría</th>
          <th>Variable</th>
          <th>Unidad</th>
          <th>Valor Mínimo</th>
          <th>Valor Máximo</th>
          <th>Referencia</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="modalVariable" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formVariable">
          <div class="modal-header">
            <h5 class="modal-title">Nueva Variable</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id_variable" id="id_variable">
            <div class="mb-2">
              <label>Tipo de Análisis</label>
              <select name="id_tipo_analisis" id="id_tipo_analisis" class="form-select" required></select>
            </div>
            <div class="mb-2">
              <label>Categoría</label>
              <select name="id_categoria_variable" id="id_categoria_variable" class="form-select" required></select>
            </div>
            <div class="mb-2">
              <label>Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-2">
              <label>Unidad de Medida</label>
              <select name="id_unidad_medida" id="id_unidad_medida" class="form-select" required></select>
            </div>
            <div class="mb-2">
              <label>Valor Mínimo</label>
              <input type="text" name="valor_minimo" class="form-control">
            </div>
            <div class="mb-2">
              <label>Valor Máximo</label>
              <input type="text" name="valor_maximo" class="form-control">
            </div>
            <div class="mb-2">
              <label>Referencia</label>
              <textarea name="referencia" class="form-control"></textarea>
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
      const tabla = $('#tablaVariables').DataTable({
        ajax: FORM_URL +'lab/listar_variables.php',
        columns: [
          { data: 'tipo_analisis' },
          { data: 'categoria' },
          { data: 'nombre' },
          { data: 'unidad' },
          { data: 'valor_minimo' },
          { data: 'valor_maximo' },
          { data: 'referencia' },
          {
            data: null,
            render: function (data) {
              return `
                <button class="btn btn-sm btn-warning btnEditar" data-id="${data.id}"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger btnEliminar" data-id="${data.id}"><i class="bi bi-trash"></i></button>
              `;
            },
            orderable: false
          }
        ],
		language: {
		  url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
		},
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

      function cargarSelect(id, url) {
        $.getJSON(url, function (data) {
          const $select = $(id);
          $select.empty();
          data.forEach(opt => {
            $select.append(`<option value="${opt.id}">${opt.nombre}</option>`);
          });
        });
      }

      $('#btnNuevaVariable').click(function () {
        $('#formVariable')[0].reset();
        $('#id_variable').val('');
        cargarSelect('#id_tipo_analisis', FORM_URL +'lab/listar_tipo_analisis.php');
        cargarSelect('#id_categoria_variable', FORM_URL +'lab/listar_categoria_variable.php');
        cargarSelect('#id_unidad_medida', FORM_URL +'lab/listar_unidades.php');
        $('#modalVariable').modal('show');
      });

      $('#tablaVariables').on('click', '.btnEditar', function () {
        const id = $(this).data('id');
        $.getJSON(FORM_URL +`lab/obtener_variable.php?id=${id}`, function (data) {
          $('#id_variable').val(data.id);
          $('#id_tipo_analisis').val(data.id_tipo_analisis);
          $('#id_categoria_variable').val(data.id_cat_categoria_variable);
          $('#id_unidad_medida').val(data.id_unidad_medida_variable);
          $('[name="nombre"]').val(data.nombre);
          $('[name="valor_minimo"]').val(data.valor_normal_minimo);
          $('[name="valor_maximo"]').val(data.valor_normal_maximo);
          $('[name="referencia"]').val(data.referencia);
          $('#modalVariable').modal('show');
        });
      });

      $('#tablaVariables').on('click', '.btnEliminar', function () {
        const id = $(this).data('id');
        if (confirm('¿Está seguro de eliminar esta variable?')) {
          $.post(FORM_URL +'lab/eliminar_variable.php', { id }, function () {
            tabla.ajax.reload();
          });
        }
      });

      $('#formVariable').submit(function (e) {
        e.preventDefault();
        $.post(FORM_URL +'lab/guardar_variable.php', $(this).serialize(), function () {
          $('#modalVariable').modal('hide');
          tabla.ajax.reload();
        });
      });
    });
  </script>
</body>
</html>
