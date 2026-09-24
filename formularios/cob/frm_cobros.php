<!DOCTYPE html>

<html lang="es">
<head>
</head>
<body>
<div class="container mt-4">
  <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalCuenta">
  <i class="bi bi-file-medical"></i>Nueva Cuenta</button>
  <h5>Cobros</h5>

<table id="tablaCuentas" class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Id</th>
      <th>Cliente</th>
      <th>Fecha</th>
      <th>Código</th>
      <th>Total</th>
      <th>Abono</th>
      <th>Saldo</th>
      <th>Acciones</th>
    </tr>
  </thead>
</table>

<!-- Modal Nueva Cuenta -->
<div class="modal fade" id="modalCuenta" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva Cuenta Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCuenta">
          <div class="row mb-3">
            <div class="col-md-3">
              <label>Cliente:</label>
              <select id="id_cliente" name="id_cliente" class="form-select"></select>
            </div>
            <div class="col-md-3">
              <label>Fecha:</label>
              <select id="selectFecha" name="fecha" class="form-select"></select>
            </div>
             <div class="col-md-3">
              <label>Forma de Pago</label>
              <select id="selectFormaPago1" name="selectFormaPago1" class="form-select"></select>
            </div>
           <div class="col-md-3">
              <label>Tipo de Precio:</label>
              <select id="selectTipoPrecio" name="id_tipo_precio" class="form-select"></select>
            </div>
          </div>

          <div id="detalleCuenta"></div>

          <div class="row mt-4">
            <div class="col-md-2">
              <label>% seguro:</label>
              <input type="number" name="porcentaje_seguro" id="porcentaje_seguro" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-2">
              <label>Nro Pagos:</label>
              <input type="number" name="numero_pagos" id="numero_pagos" class="form-control" min="1" value="1">
            </div>
            <div class="col-md-2">
              <label>Monto/Pago:</label>
              <input type="text" id="monto_pago" name="monto_pago" class="form-control" readonly>
            </div>
            <div class="col-md-2">
              <label>Abono:</label>
              <input type="number" name="abono" id="abono" name="abono" class="form-control" min="0" step="0.01">
            </div>
            <div class="col-md-2">
              <label>Saldo:</label>
              <input type="text" id="saldo" name="saldo" class="form-control" readonly>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="btnGuardarCuenta">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal de pagos -->
<div class="modal fade" id="modalPagos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-cash"></i> Pagos de la cuenta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="idCuentaPago">

        <h6>Pagos registrados</h6>
        <table id="tablaPagos" class="table table-striped table-bordered w-100">
          <thead class="table-light">
            <tr>
              <th>Fecha</th>
              <th>Total pagado</th>
              <th>Notas</th>
              <th>Acciones</th>
            </tr>
          </thead>
        </table>

        <hr>

        <h6>Registrar nuevo pago</h6>
        <form id="formNuevoPago">
          <div class="mb-3">
            <label class="form-label">Fecha y hora</label>
            <input type="text" class="form-control" id="fechaPago" readonly>
          </div>

          <table class="table table-sm table-bordered align-middle" id="tablaFormasPago">
            <thead class="table-light">
              <tr>
                <th>Forma de pago</th>
                <th>Monto</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><select class="form-select form-select-sm formaPago" name="id_forma_pago1"></select></td>
                <td><input type="number" class="form-control form-control-sm montoPago" name="monto_1" step="0.01" value="0.00"></td>
              </tr>
              <tr>
                <td><select class="form-select form-select-sm formaPago" name="id_forma_pago2"></select></td>
                <td><input type="number" class="form-control form-control-sm montoPago" name="monto_2" step="0.01" value="0.00"></td>
              </tr>
              <tr>
                <td><select class="form-select form-select-sm formaPago" name="id_forma_pago3"></select></td>
                <td><input type="number" class="form-control form-control-sm montoPago" name="monto_3" step="0.01" value="0.00"></td>
              </tr>
              <tr class="table-light">
                <td class="text-end fw-bold">Total</td>
                <td><input type="text" class="form-control form-control-sm" id="totalPago" readonly></td>
              </tr>
            </tbody>
          </table>

          <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea class="form-control" name="notas" rows="2"></textarea>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button id="btnGuardarPago" class="btn btn-success">
          <i class="bi bi-save"></i> Guardar Pago
        </button>
      </div>
    </div>
  </div>
</div>
</div>
<script>
$(document).ready(function() {
  const tabla = $('#tablaCuentas').DataTable({
    ajax: FORM_URL + 'cob/listar_cuentas.php',
	order: [[2, 'desc']],
    columns: [
      { data: 'id' },
      { data: 'cliente' },
      { data: 'fecha' },
      { data: 'codigo' },
      { data: 'total'},
      { data: 'abono'},
      { data: 'saldo'},
      {
        data: null,
        render: function(data, type, row) {
          return `
            <button class="btn btn-sm btn-danger eliminarCuenta" data-id="${row.id}" data-toggle="tooltip" title="Eliminar"><i class="bi bi-trash"></i></button>
			<button class="btn btn-sm btn-primary generaComp" data-id="${row.id}" data-toggle="tooltip" title="Comprobante"><i class="bi bi-card-list"></i></button>			
		    <button class="btn btn-sm btn-success btnPagos" data-id="${row.id}"><i class="bi bi-cash-stack"></i></button>
          `;
        }
      }
    ]
  });

  // Cargar select2 clientes
  $('#id_cliente').select2({
    ajax: {
      url: FORM_URL + 'cob/buscar_clientes.php',
      dataType: 'json',
      processResults: data => ({ results: data })
    },
	dropdownParent: $('#modalCuenta'),
	placeholder: 'Seleccione un cliente'
  });

  // Cargar tipo precio y forma de pago
  $.getJSON(FORM_URL + 'cob/listar_tipos_precio.php', data => {
    data.forEach(tp => $('#selectTipoPrecio').append(`<option value="${tp.id}">${tp.nombre}</option>`));
  });

  $.getJSON(FORM_URL + 'cob/listar_formas_pago.php', data => {
    data.forEach(fp => $('#selectFormaPago1').append(`<option value="${fp.id}">${fp.nombre}</option>`));
    data.forEach(fp => $('#selectFormaPago2').append(`<option value="${fp.id}">${fp.nombre}</option>`));
    data.forEach(fp => $('#selectFormaPago3').append(`<option value="${fp.id}">${fp.nombre}</option>`));
  });

  // Al seleccionar cliente, cargar fechas válidas
	$('#id_cliente').on('change', function () {
	  const id = $(this).val();
	  $.getJSON(FORM_URL + 'cob/buscar_fechas_cliente.php', { id_cliente: id }, res => {
		$('#selectFecha').html('<option value="">Seleccione</option>');
		res.data.forEach(f => {
		  $('#selectFecha').append(`<option value="${f.id}">${f.text}</option>`);
		});
	  });
	});

  // Mostrar detalle
$('#selectFecha, #selectTipoPrecio, #selectFormaPago1').on('change', function () {
  const cliente = $('#id_cliente').val();
  const fecha = $('#selectFecha').val();
  const tipo = $('#selectTipoPrecio').val();
  const formapago = $('#selectFormaPago1').val();

  if (cliente && fecha && tipo && formapago) {
    $.getJSON(FORM_URL + 'cob/detalle_cuenta.php', {
      id_cliente: cliente,
      fecha: fecha,
      id_tipo_precio: tipo,
	  id_forma_pago: formapago
    }, function (data) {
      let html = '';
      data.grupos.forEach(grupo => {
        html += `<h5>${grupo.tipo}</h5>`;
        html += `<table class="table table-bordered table-sm mb-3">
          <thead><tr>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Subtotal</th>
          </tr></thead><tbody>`;

        grupo.items.forEach(item => {
          html += `<tr>
            <td>${item.descripcion}</td>
            <td>${item.cantidad}</td>
            <td>Q ${item.precio}</td>
            <td>Q ${item.subtotal}</td>
          </tr>`;
        });

        html += `<tr class="table-light fw-bold">
          <td colspan="3" class="text-end">Subtotal ${grupo.tipo}:</td>
          <td>Q ${grupo.subtotal}</td>
        </tr></tbody></table>`;
      });

      html += `<div class="text-end fs-5 fw-bold">Total general: Q ${data.total_general}</div>`;
      $('#detalleCuenta')
        .html(html)
        .data('total_general', parseFloat(data.total_general.replace(/[^\d.]/g, '')) || 0);
		calcularTotales(parseFloat(data.total_general.replace(/[^\d.]/g, '')) || 0);
    });
  }
});

  // Calcular pagos y saldo
  $(document).on('input change', '#numero_pagos, #abono, #porcentaje_seguro', calcularTotales);

  function calcularTotales() {
    const subtotal = ($('#detalleCuenta').data('total_general')) || 0;
	const psegu = parseInt($('#porcentaje_seguro').val()) || 0;
	const tsegu = (subtotal * psegu)/100;
	const total = subtotal - tsegu;
    const pagos = parseInt($('#numero_pagos').val()) || 1;
    const abono = parseFloat($('#abono').val()) || 0;
    const monto = total / pagos;
    const saldo = total - abono;

    $('#monto_pago').val(monto.toFixed(2));
    $('#saldo').val(saldo.toFixed(2));
  }

  // Guardar cuenta
  $('#btnGuardarCuenta').click(function() {
    const formData = $('#formCuenta').serializeArray();
    formData.push({ name: 'total', value: $('#detalleCuenta').data('total_general') });
    formData.push({ name: 'monto_pago', value: $('#monto_pago').val() });
    formData.push({ name: 'saldo', value: $('#saldo').val() });
    formData.push({ name: 'codigo', value: generarCodigo() });
	console.log($('#formCuenta').serializeArray());
    $.post(FORM_URL + 'cob/guardar_cuenta.php', formData, function(resp) {
      tabla.ajax.reload();
      $('#modalCuenta').modal('hide');
    });
  });

  // Eliminar cuenta
  $('#tablaCuentas').on('click', '.eliminarCuenta', function () {
	const cuentaId = $(this).data('id');
	if (confirm('¿Estás seguro de eliminar esta cuenta?')) {
	  $.post(FORM_URL +'cob/eliminar_cuenta.php', { id: cuentaId }, function (res) {
		tabla.ajax.reload();
	  });
	}
  });

$('#tablaCuentas').on('click', '.generaComp', function () {
  const idAtencion = $(this).data('id');
  window.open(FORM_URL + `cob/informe_02.php?id_atencion=${idAtencion}`, '_blank');
});

  function generarCodigo() {
    const fecha = $('#selectFecha').val().replaceAll('-', '');
    const cliente = $('#id_cliente').val();
    return fecha + '-' + cliente;
  }

  // Inicializa fecha/hora local
  function actualizarFecha() {
    const now = new Date();
    const formatted = now.toLocaleString('sv-SE', { hour12: false }).replace(' ', 'T');
    $('#fechaPago').val(formatted.replace('T', ' '));
  }

  // Inicializa selects de forma de pago
  function cargarFormasPago() {
    $.ajax({
      url: FORM_URL+'cob/pagos_formas.php',
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        $('.formaPago').each(function() {
          const select = $(this);
          select.empty().append('<option value="">Seleccione</option>');
          data.forEach(f => select.append(`<option value="${f.id}">${f.nombre}</option>`));
        });
      }
    });
  }

  // Inicializa datatable de pagos
  let tablaPagos;
  function cargarPagos(idCuenta) {
    if ($.fn.DataTable.isDataTable('#tablaPagos')) {
      tablaPagos.ajax.url(FORM_URL+'cob/pagos_listar.php?id=' + idCuenta).load();
      return;
    }
    tablaPagos = $('#tablaPagos').DataTable({
      ajax: {
        url: FORM_URL+'cob/pagos_listar.php',
        type: 'GET',
        data: function(d) { d.id = $('#idCuentaPago').val(); },
        dataSrc: ''
      },
      columns: [
        { data: 'fecha' },
        { data: 'total', className: 'text-end' },
        { data: 'notas' },
        {
          data: null,
          orderable: false,
          render: function(row) {
            return `
              <button class="btn btn-sm btn-warning btnEditarPago" data-id="${row.id}">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-danger btnEliminarPago" data-id="${row.id}">
                <i class="bi bi-trash"></i>
              </button>`;
          }
        }
      ],
      responsive: true
    });
  }

  // Calcular total dinámicamente
  $(document).on('input', '.montoPago', function() {
    let total = 0;
    $('.montoPago').each(function() {
      total += parseFloat($(this).val()) || 0;
    });
    $('#totalPago').val(total.toFixed(2));
  });

  // Abrir modal desde tabla principal
  $(document).on('click', '.btnPagos', function() {
    const id = $(this).data('id');
    $('#idCuentaPago').val(id);
    actualizarFecha();
    cargarFormasPago();
    cargarPagos(id);
    $('#modalPagos').modal('show');
  });

  // Guardar nuevo pago
  $('#btnGuardarPago').click(function() {
    const formData = $('#formNuevoPago').serializeArray();
    formData.push({ name: 'id_cuenta', value: $('#idCuentaPago').val() });
    formData.push({ name: 'fecha', value: $('#fechaPago').val() });
    formData.push({ name: 'total', value: $('#totalPago').val() });

    $.ajax({
      url: FORM_URL+'cob/pagos_guardar.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(r) {
        if (r.success) {
          tablaPagos.ajax.reload();
			const modal = bootstrap.Modal.getInstance(document.getElementById('modalPagos'));
			modal.hide();
			$('#formNuevoPago')[0].reset();
          actualizarFecha();
          $('#totalPago').val('0.00');
        } else {
          alert('Error: ' + r.message);
        }
      },
      error: function() { alert('Error al guardar pago'); }
    });
  });

// Eliminar pago
$(document).on('click', '.btnEliminarPago', function() {
  const id = $(this).data('id');
  if (!confirm('¿Seguro que deseas eliminar este pago?')) return;

  $.ajax({
    url: FORM_URL+'cob/pagos_eliminar.php',
    type: 'POST',
    data: { id: id },
    dataType: 'json',
    success: function(r) {
      if (r.success) {
        tablaPagos.ajax.reload(null, false);
      } else {
        alert('Error al eliminar: ' + r.message);
      }
    },
    error: function() {
      alert('Error de conexión al eliminar pago.');
    }
  });
});


// Editar pago
$(document).on('click', '.btnEditarPago', function() {
  const id = $(this).data('id');
  $.ajax({
    url: FORM_URL+'cob/pagos_get.php',
    type: 'GET',
    data: { id: id },
    dataType: 'json',
    success: function(r) {
      if (r.success) {
        $('#idPagoEditar').val(r.data.id);
        $('#fechaEditar').val(r.data.fecha);
        $('#totalEditar').val(r.data.total);
        $('#notasEditar').val(r.data.notas);
        $('#modalEditarPago').modal('show');
      } else {
        alert('No se pudo obtener el pago.');
      }
    }
  });
});

$('#btnActualizarPago').click(function() {
  const data = $('#formEditarPago').serialize();
  $.ajax({
    url: FORM_URL+'cob/pagos_actualizar.php',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(r) {
      if (r.success) {
        $('#modalEditarPago').modal('hide');
        tablaPagos.ajax.reload(null, false);
      } else {
        alert('Error al actualizar: ' + r.message);
      }
    },
    error: function() {
      alert('Error de conexión al actualizar pago.');
    }
  });
});

$('#modalPagos').on('hidden.bs.modal', function () {
    // Recargar el DataTable de cuentas
    tabla.ajax.reload(null, false); // false = mantener la página actual
});

$('#modalPagos').on('hidden.bs.modal', function () {
    $('#modalPagos')[0].reset();
    actualizarFecha();
    $('#totalPago').val('0.00');
});

}); //fin document.ready
</script>


</body>
</html>
