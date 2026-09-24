<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>

<!-- Modal -->
<div class="modal fade " id="modalProductos" tabindex="-1" aria-labelledby="tituloModal"  aria-modal="true" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formAsociacion">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="tituloModal">Productos/insumos por análisis</h5>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="servicio" class="form-label">Análisis</label>
            <select id="servicio" name="id_servicio" class="form-select" required></select>
          </div>

          <div class="mb-3">
            <label for="producto" class="form-label">Producto(s)</label>
            <select id="producto" class="form-select"></select>
          </div>

          <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad para cada producto</label>
            <input type="number" id="cantidad" class="form-control" step="0.01" min="0.01">
          </div>

          <button type="button" id="agregarProducto" class="btn btn-success mb-3">Agregar a la lista</button>

          <table id="tablaResumen" class="table table-bordered table-sm">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar todo</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
	$(document).ready(function () {
	  const productosSeleccionados = [];

		$('#servicio').select2({
		  placeholder: 'Seleccione servicio',
		  width: '100%',
		  dropdownParent: $('#modalProductos'), // ← Importante
		  ajax: {
			url: FORM_URL + 'cat/servicios_plistar.php',
			dataType: 'json',
			processResults: function (data) {
			  return {
				results: data.map(item => ({ id: item.id, text: item.text }))
			  };
			}
		  }
		});

		function cargarProductosAsociados(id_servicio) {
		  $.getJSON(FORM_URL + 'cat/servicio_productos_listar.php', { id_servicio }, function (data) {
			productosSeleccionados.length = 0;
			$('#tablaResumen tbody').empty();

			data.forEach(p => {
			  productosSeleccionados.push({ id: p.id, nombre: p.nombre, cantidad: p.cantidad });
			  $('#tablaResumen tbody').append(`
				<tr data-id="${p.id}">
				  <td>${p.nombre}</td>
				  <td>${p.cantidad}</td>
				  <td><button type="button" class="btn btn-sm btn-danger eliminar">🗑️</button></td>
				</tr>
			  `);
			});
		  });
		}

		$('#servicio').on('change', function () {
		  const id_servicio = $(this).val();
		  cargarProductosAsociados(id_servicio);
		});

		$('#producto').select2({
		  placeholder: 'Seleccione productos',
		  width: '100%',
		  multiple: false,
		  dropdownParent: $('#modalProductos'), // ← Importante
		  ajax: {
			url: FORM_URL + 'cat/productos_listar.php',
			dataType: 'json',
			processResults: function (data) {
			  return {
				results: data.map(item => ({ id: item.id, text: item.text }))
			  };
			}
		  }
		});

		$('#agregarProducto').on('click', function () {
		  const id = $('#producto').val();
		  const text = $('#producto').select2('data')[0]?.text || '';
		  const cantidad = parseFloat($('#cantidad').val());

		  if (!id || isNaN(cantidad) || cantidad <= 0) {
			alert('Seleccione un producto y una cantidad válida.');
			return;
		  }

		  const existente = productosSeleccionados.find(p => p.id == id);
		  if (!existente) {
			productosSeleccionados.push({ id, nombre: text, cantidad });
			$('#tablaResumen tbody').append(`
			  <tr data-id="${id}">
				<td>${text}</td>
				<td>${cantidad}</td>
				<td><button type="button" class="btn btn-sm btn-danger eliminar">🗑️</button></td>
			  </tr>
			`);
		  }

		  $('#producto').val(null).trigger('change');
		  $('#cantidad').val('');
		});

		  // Eliminar fila
		  $('#tablaResumen').on('click', '.eliminar', function () {
			const fila = $(this).closest('tr');
			const id = fila.data('id');
			fila.remove();
			const index = productosSeleccionados.findIndex(p => p.id == id);
			if (index !== -1) productosSeleccionados.splice(index, 1);
		  });

		  // Guardar todo
		  $('#formAsociacion').on('submit', function (e) {
			e.preventDefault();
			const id_servicio = $('#servicio').val();
			if (!id_servicio || productosSeleccionados.length === 0) {
			  alert('Seleccione un servicio y al menos un producto.');
			  return;
			}

			$.post(FORM_URL + 'cat/servicio_producto_guardar.php', {
			  id_servicio,
			  productos: JSON.stringify(productosSeleccionados)
			}, function (respuesta) {
			  alert('¡Guardado correctamente!');
			  location.reload();
			}, 'json');

		  });

		  // Abrir modal automáticamente
		  const modal = new bootstrap.Modal(document.getElementById('modalProductos'));
		  modal.show();
	});
</script>

</body>
</html>
