<!DOCTYPE html>
<html lang="es">
<head>
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
      <label class="form-label">Análisis</label>
      <select id="servicios" class="form-select" multiple></select>
      <button id="asociarServicios" class="btn btn-success mt-2">Asociar análisis</button>
    </div>
  </div>

  <h5>Análisis del paquete</h5>
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
		  <div class="mb-3">
		    <label for="precio" class="form-label">Precio del Paquete</label>
		    <input type="number" class="form-control" name="precio" id="precio" step="0.01" min="0" required>
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

$(document).ready(function () {

  let modalPaquete = new bootstrap.Modal(document.getElementById('modalPaquete'));
  $('#servicios').select2({
    placeholder: 'Seleccione análisis',
    width: '100%',
    ajax: {
      url: FORM_URL + 'cat/servicios_listar.php',
      dataType: 'json',
      processResults: function (data) {
        return {
          results: data.map(s => ({ id: s.id, text: s.nombre }))
        };
      }
    }
  });

  tabla = $('#tablaServicios').DataTable({
    ajax: {
      url: FORM_URL + 'cat/paquete_servicios_listar.php',
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

    $.post(FORM_URL + 'cat/paquete_servicios_guardar.php', {
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
      $.post(FORM_URL + 'cat/paquete_servicios_eliminar.php', { id }, () => tabla.ajax.reload());
    }
  });

	function cargarPaquetesSeleccionados(idSeleccionado = null) {
	  $.getJSON(FORM_URL + 'cat/paquetes_listar.php', function (data) {
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
	  $('#precio').val('');  
	  $('#tituloModalPaquete').text('Nuevo Paquete');
	  modalPaquete.show();
	});

	$('#btnEditarPaquete').on('click', () => {
	  const id = $('#paquete').val();
	  if (!id) return alert('Seleccione un paquete.');
	  $.getJSON(FORM_URL + 'cat/paquete_get.php', { id }, function (data) {
		$('#id_paquete').val(data.id);
		$('#nombre_paquete').val(data.nombre);
		$('#precio').val(data.precio);
		$('#tituloModalPaquete').text('Editar Paquete');
		modalPaquete.show();
	  });
	});

	$('#btnEliminarPaquete').on('click', () => {
	  const id = $('#paquete').val();
	  if (!id) return alert('Seleccione un paquete.');
	  if (confirm('¿Eliminar paquete y todos sus análisis?')) {
		$.post(FORM_URL + 'cat/paquete_eliminar.php', { id }, function () {
		  cargarPaquetesSeleccionados();
		  tabla.clear().draw();
		});
	  }
	});

	$('#formPaquete').on('submit', function (e) {
	  e.preventDefault();
	  $.post(FORM_URL + 'cat/paquete_guardar.php', $(this).serialize(), function (nuevoId) {
		$('#modalPaquete').modal('hide');
		cargarPaquetesSeleccionados(nuevoId);
	  });
	});
	cargarPaquetesSeleccionados();
	});
</script>

</body>
</html>
