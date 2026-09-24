<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>DataPlus Guatemala</title>

  <!-- Bootstrap y estilos -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- jQuery debe ir antes de cualquier plugin que lo use -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Select2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="p-4">
<div class="container mt-4">
  <div class="row mb-3 align-items-end">
    <div class="col-md-6">
      <label class="form-label">Paquete</label>
      <div class="input-group">
        <select id="paquete" class="form-select"></select>
        <button class="btn btn-outline-primary" id="btnNuevoPaquete">+</button>
        <button class="btn btn-outline-warning" id="btnEditarPaquete">✏️</button>
        <button class="btn btn-outline-danger" id="btnEliminarPaquete">🗑️</button>
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label">Servicios</label>
      <select id="servicios" class="form-select" multiple></select>
      <button id="asociarServicios" class="btn btn-success mt-2">Asociar servicios</button>
    </div>
  </div>

  <h5>Servicios del paquete</h5>
  <table id="tablaServicios" class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Servicio</th>
        <th>Acción</th>
      </tr>
    </thead>
  </table>
</div>

<!-- Modal para agregar/editar paquete -->
<div class="modal fade" id="modalPaquete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formPaquete">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="tituloModalPaquete">Paquete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_paquete" id="id_paquete">
          <label>Nombre del paquete</label>
          <input type="text" name="nombre" id="nombre_paquete" class="form-control" required>
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
$(document).ready(function () {
  // Inicializa Select2
  $('#servicios').select2({
    placeholder: 'Seleccione análisis',
    width: '100%',
    ajax: {
      url: 'formularios/cat/servicios_listar.php',
      dataType: 'json',
      processResults: function (data) {
        return {
          results: data.map(s => ({ id: s.id, text: s.nombre }))
        };
      }
    }
  });

  // DataTable
  const tabla = $('#tablaServicios').DataTable({
    ajax: {
      url: 'formularios/cat/paquete_servicios_listar.php',
      data: function (d) {
        d.id_paquete = $('#paquete').val();
      },
      dataSrc: 'data'
    },
    columns: [
      { data: 'id' },
      { data: 'servicio' },
      {
        data: null,
        render: data => `<button class="btn btn-sm btn-danger eliminar" data-id="${data.id}">Eliminar</button>`
      }
    ]
  });

  $('#paquete').on('change', function () {
    tabla.ajax.reload();
  });

  $('#asociarServicios').on('click', function () {
    const id_paquete = $('#paquete').val();
    const servicios = $('#servicios').val();
    if (!id_paquete || servicios.length === 0) {
      alert('Seleccione un paquete y al menos un análisis.');
      return;
    }

    $.post('formularios/cat/paquete_servicios_guardar.php', {
      id_paquete,
      servicios
    }, function () {
      tabla.ajax.reload();
      $('#servicios').val(null).trigger('change');
    });
  });

  $('#tablaServicios').on('click', '.eliminar', function () {
    const id = $(this).data('id');
    if (confirm('¿Eliminar este análisis del paquete?')) {
      $.post('formularios/cat/paquete_servicios_eliminar.php', { id }, () => tabla.ajax.reload());
    }
  });

  function cargarPaquetesSeleccionados(idSeleccionado = null) {
    $.getJSON('formularios/cat/paquetes_listar.php', function (data) {
      const select = $('#paquete').empty().append('<option value="">Seleccione un paquete</option>');
      data.forEach(p => {
        const selected = (idSeleccionado && idSeleccionado == p.id) ? 'selected' : '';
        select.append(`<option value="${p.id}" ${selected}>${p.nombre}</option>`);
      });
      if (idSeleccionado) tabla.ajax.reload();
    });
  }

  $('#btnNuevoPaquete').on('click', () => {
    $('#formPaquete')[0].reset();
    $('#id_paquete').val('');
    $('#tituloModalPaquete').text('Nuevo Paquete');
    $('#modalPaquete').modal('show');
  });

  $('#btnEditarPaquete').on('click', () => {
    const id = $('#paquete').val();
    if (!id) return alert('Seleccione un paquete.');
    $.getJSON('formularios/cat/paquete_get.php', { id }, function (data) {
      $('#id_paquete').val(data.id);
      $('#nombre_paquete').val(data.nombre);
      $('#tituloModalPaquete').text('Editar Paquete');
      $('#modalPaquete').modal('show');
    });
  });

  $('#btnEliminarPaquete').on('click', () => {
    const id = $('#paquete').val();
    if (!id) return alert('Seleccione un paquete.');
    if (confirm('¿Eliminar paquete y todos sus análisis?')) {
      $.post('formularios/cat/paquete_eliminar.php', { id }, function () {
        cargarPaquetesSeleccionados();
        tabla.clear().draw();
      });
    }
  });

  $('#formPaquete').on('submit', function (e) {
    e.preventDefault();
    $.post('formularios/cat/paquete_guardar.php', $(this).serialize(), function (nuevoId) {
      $('#modalPaquete').modal('hide');
      cargarPaquetesSeleccionados(nuevoId);
    });
  });

  // Carga inicial
  cargarPaquetesSeleccionados();
});
</script>

</body>
</html>
