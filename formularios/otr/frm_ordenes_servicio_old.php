<?php
if(!isset($_SESSION)) 
{ 
    session_start(); 
} 
// Verificar si hay un cliente activo en sesión
if (!isset($_SESSION['cliente_activo']) || empty($_SESSION['cliente_activo'])) {
    die("No hay paciente activo seleccionado.");
}
$id_cliente=$_SESSION['cliente_activo'];
require_once '../../assets/dbc.php';
$sql = "SELECT CONCAT_WS(' ',
            TRIM(nombre_1),
            TRIM(nombre_2),
            TRIM(nombre_3),
            TRIM(apellido_1),
            TRIM(apellido_2),
            IF(TRIM(apellido_casada) IS NULL OR TRIM(apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(apellido_casada)))
        ) AS paciente
        FROM tbl_persona
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$stmt->bind_result($paciente);
$stmt->fetch();
$stmt->close();


?>
<!DOCTYPE html>
<html lang="es">


<head>
</head>
<body>
  <div class="container mt-4">
    <button class="btn btn-primary mb-3" id="btnNuevaAtencion">
      <i class="bi bi-plus-circle"></i> Nueva orden de servicio
    </button>
    <h5>Ordenes de servicio registradas</h5>

    <table id="tablaAtenciones" class="table table-sm table-bordered table-striped" style="width:100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>Número</th>
          <th>Fecha</th>
          <th>Entrega Resultado</th>
          <th>Paciente</th>
          <th>Medico</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <!-- Modal de Atención -->
  <div class="modal fade" id="modalAtencion" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="formAtencion" method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="tituloModal">Nueva orden de servicio - <?php echo $paciente;?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id_atencion" id="id_atencion">
			<input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo $id_cliente;?>">
			<div class="row">
 				<div class="form-group col-md-3">
				  <label for="inputNumero">Número</label>
				  <input type="text" class="form-control" id="inputNumero" name="inputNumero" >
				</div>
				<div class="form-group col-md-3">
				  <label for="inputFecha">Fecha</label>
				  <input type="date" class="form-control" id="inputFecha" name="inputFecha" >
				</div>
			   <div class="form-group col-md-3">
				  <label>Médico</label>
				  <select name="id_medico" id="id_medico" class="form-select" required>
				   <?php
						require_once '../../assets/dbc.php';
						$sql = "SELECT c.id, 
						CONCAT_WS(' ',
							TRIM(c.nombre_1),
							TRIM(c.nombre_2),
							TRIM(c.nombre_3),
							TRIM(c.apellido_1),
							TRIM(c.apellido_2),
							IF(TRIM(c.apellido_casada) IS NULL OR TRIM(c.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(c.apellido_casada)))
						) AS medico
						FROM tbl_persona c
						WHERE c.id_tipopersona=3";
						$result = mysqli_query($conn, $sql);
						$options = "<option value=''>Seleccione</option>";
						while ($row = mysqli_fetch_assoc($result)) {
							$options .= "<option value='" . $row['id'] . "'>" . $row['medico'] . "</option>";
						}
						echo $options;
					?>
				   </select>
				</div>
				<div class="form-group col-md-3">
				  <label>Fecha entrega resultado</label>
				  <input type="date" name="fecha_entrega_resultado" id="fecha_entrega_resultado" class="form-control" required>
				</div>
				<div class="form-group col-md-1">
				  <input class="form-check-input" type="hidden" value="" id="inputStat" name="inputStat">
				</div>
			</div>
            <div class="row g-2 align-items-end">
				<div class="form-group col-md-8">
				  <label>Observaciones</label>
				  <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
				</div>
			</div>
            <hr>
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label>Tipo de servicio</label>
                <select id="tipoServicio" class="form-select"></select>
              </div>
              <div class="col-md-4">
                <label>Servicio</label>
                <select id="selectServicio" class="form-select"></select>
              </div>
              <div class="col-md-4 text-end">
                <button type="button" class="btn btn-secondary" id="btnAgregarServicio">
                  <i class="bi bi-plus-circle"></i> Agregar servicio
                </button>
              </div>
            </div>
            <div class="table-responsive mt-3">
              <table class="table table-bordered" id="tablaServicios">
                <thead>
                  <tr>
                    <th>Servicio</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
            <div class="row g-2 align-items-end">
				<div class="form-group col-md-3">
				  <input type="hidden" class="form-control" id="totalorden" name="totalorden"  >
				</div>
				<div class="form-group col-md-3">
				  <input type="hidden" class="form-control" id="abono" name="abono" >
				</div>
				<div class="form-group col-md-3">
				  <input type="hidden" class="form-control" id="saldo" name="saldo" readonly>
				</div>
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
      const tabla = $('#tablaAtenciones').DataTable({
        ajax: FORM_URL +'otr/listar_atenciones.php',
 		order: [[5, 'desc']],
	    columnDefs: [
		{ targets: '_all', width: 'auto' }
 	    ],
        columns: [
          { data: 'id' },
          { data: 'numero_ticket' },
		   {
			  data: 'fecha',
			  render: function (data) {
				const [y, m, d] = data.split('-');
				return `${d}-${m}-${y}`;
			  }
			},
			{
			  data: 'fecha_entrega_resultado',
			  render: function (data) {
				if (!data || data === '0000-00-00') return '';
				const [y, m, d] = data.split('-');
				return `${d}-${m}-${y}`;
			  }
			},
          { data: 'paciente' },
          { data: 'medico' },
          {
            data: null,
            render: function (data) {
              return `
                <button class="btn btn-sm btn-warning btnEditar" data-id="${data.id}" data-toggle='tooltip' title='Editar'><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger btnEliminar" data-id="${data.id}" data-toggle='tooltip' title='Eliminar'><i class="bi bi-trash"></i></button>
              `;
            },
            orderable: false
          }
        ]
      });

      $('#btnNuevaAtencion').click(function () {
        $('#formAtencion')[0].reset();
        $('#id_atencion').val('');
        $('#tablaServicios tbody').empty();
		const hoy = new Date().toISOString().split('T')[0];
		$('#inputFecha').val(hoy);
        $('#modalAtencion').modal('show');
      });

      $('#tipoServicio').select2({
        ajax: {
          url: FORM_URL + 'otr/buscar_tiposervicios.php',
          dataType: 'json',
          processResults: function (data) {
            return {
              results: data.map(s => ({ id: s.id, text: s.nombre, precio: s.precio }))
            };
          }
        },
        dropdownParent: $('#modalAtencion'),
        placeholder: 'Seleccione un tipo de servicio'
      });

	$('#tipoServicio').on('change', function () {
	  $('#selectServicio').val(null).trigger('change');
	});

	$('#selectServicio').select2({
	  ajax: {
		url: FORM_URL + 'otr/buscar_servicios.php',
		dataType: 'json',
		data: function (params) {
		  return {
			term: params.term || '',
			id_tipo_servicio: $('#tipoServicio').val() // <-- enviar tipo seleccionado
		  };
		},
		processResults: function (data) {
		  return {
			results: data.map(s => ({
			  id: s.id,
			  text: s.nombre,
			  precio: s.precio
			}))
		  };
		}
	  },
	  dropdownParent: $('#modalAtencion'),
	  placeholder: 'Seleccione un servicio'
	});

 function formatearNumero(num) {
    return num.toLocaleString('en-US', { minimumFractionDigits: 2 });
}

function obtenerValorNumerico(id) {
    return parseFloat($(id).val().replace(/,/g, '')) || 0;
}

// --- Evento al agregar servicio ---
$('#btnAgregarServicio').click(function () {
    const data = $('#selectServicio').select2('data')[0];
    if (!data) return;

    const precio = parseFloat(data.precio) || 0;

    // Agregar fila
    const row = `
        <tr>
            <td>${data.text}<input type="hidden" name="servicios[]" value="${data.id}"></td>
            <td><button type="button" class="btn btn-danger btn-sm btnQuitarServicio">
                <i class="bi bi-x-circle"></i></button>
            </td>
        </tr>`;
    $('#tablaServicios tbody').append(row);

    // Actualizar total
    let totalActual = obtenerValorNumerico('#totalorden');
    totalActual += precio;
    $('#totalorden').val(formatearNumero(totalActual));

    // Recalcular saldo
    recalcularSaldo();

    // Limpiar select
    $('#selectServicio').val(null).trigger('change');
});

// --- Evento al quitar servicio ---
$('#tablaServicios').on('click', '.btnQuitarServicio', function () {
    const fila = $(this).closest('tr');
    const precioStr = fila.find('td:eq(1)').text().replace(/,/g, '');
    const precio = parseFloat(precioStr) || 0;

    // Restar del total
    let totalActual = obtenerValorNumerico('#totalorden');
    totalActual -= precio;
    $('#totalorden').val(formatearNumero(totalActual));

    // Recalcular saldo
    recalcularSaldo();

    // Eliminar fila
    fila.remove();
});

// --- Evento al escribir en abono ---
$('#abono').on('input', function () {
    // Formatear mientras escribe
    //let valor = $(this).val().replace(/,/g, '');
    //if (!isNaN(valor) && valor !== '') {
    //    $(this).val(formatearNumero(parseFloat(valor)));
    //}
    recalcularSaldo();
});

// --- Función para recalcular saldo ---
function recalcularSaldo() {
    let total = obtenerValorNumerico('#totalorden');
    let abono = obtenerValorNumerico('#abono');
    let saldo = total - abono;
    if (saldo < 0) saldo = 0; // evitar negativos
    $('#saldo').val(formatearNumero(saldo));
}

// funcion guardar
 $('#formAtencion').submit(function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  $.ajax({
	url: FORM_URL + 'otr/guardar_atencion.php',
	method: 'POST',
	data: formData,
	processData: false,
	contentType: false,
	success: function (resp) {
	  $('#modalAtencion').modal('hide');
	  tabla.ajax.reload();
	}
  });
});

$('#tablaAtenciones').on('click', '.btnEditar', function () {
  const id = $(this).data('id');
  $.getJSON( FORM_URL +`otr/obtener_atencion.php?id=${id}`, function (data) {
  $('#id_atencion').val(data.id);
  $('#inputNumero').val(data.numero_ticket);
  $('#inputStat').prop('checked', data.stat == "1");
  $('#id_medico').val(data.id_medico);
  $('#fecha_entrega_resultado').val(data.fecha_entrega_resultado);
  $('#inputFecha').val(data.fecha);
  $('#totalorden').val(data.total);
  $('#abono').val(data.abono);
  $('#saldo').val(data.saldo);
  $('#tablaServicios tbody').empty();
  data.servicios.forEach(s => {
	$('#tablaServicios tbody').append(`
	  <tr>
		<td>${s.nombre}<input type="hidden" name="servicios[]" value="${s.id}"></td>
		<td>${s.precio ? parseFloat(s.precio).toLocaleString('en-US', { minimumFractionDigits: 2 }) : ''}</td>
		<td><button type="button" class="btn btn-danger btn-sm btnQuitarServicio"><i class="bi bi-x-circle"></i></button></td>
	  </tr>`);
  });
  $('#tituloModal').text('Editar orden');
  $('#modalAtencion').modal('show');
});
});
});

function generarContra(idAtencion) {
  // Lógica para redirigir o generar PDF
  window.open(FORM_URL + `otr/res/informe_02.php?id_atencion=${idAtencion}`, '_blank');
}

$('#tablaAtenciones').on('click', '.btnBarcode', function () {
  const idOrden = $(this).data('id');
  window.open(FORM_URL+'otr/barcode_imprimir.php?id_orden=' + idOrden, '_blank');
});

 </script>
</body>
</html>
