<?php
$modo = 'embed';
  if(!isset($_SESSION)) { 
    session_start(); 
  } 
  require_once '../../assets/dbc.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
	<div class="container mt-4">
		<button class="btn btn-primary mb-3" id="btnNuevoPaciente">
        	<i class="bi bi-plus-circle"></i> Nuevo paciente
      	</button>
		<!--
		<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalCliente" id="btn_nuevo"><i class="bi bi-person-plus"></i> Nuevo paciente</button>
		-->
		<h5>Pacientes registrados</h5>

	  	<table id="tablaClientes" class="table table-sm table-striped table-bordered" style="width:100%">
			<thead>
		  		<tr>
					<th>ID</th>
					<th>Nombre</th>
					<th>Sexo</th>
					<th>Direccion</th>
					<th>Telefono</th>
					<th>DPI</th>
					<th>Acciones</th>
		  		</tr>
			</thead>
			<tbody></tbody>
	  	</table>
	</div>

<!-- Modal para cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1"  aria-labelledby="modalClienteLabel" aria-hidden="true" data-bs-backdrop="false">
  	<div class="modal-dialog modal-lg">
		<div class="modal-content">
    		<form id="formCliente" method="POST">
  				<div class="modal-header">
					<h5 class="modal-title" id="tituloModal"></h5>	
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body" >
					<input type="hidden" id="id_cliente" name="id_cliente">	
					<div class="row">
						<div class="form-group col-md-3">
            				<label for="nombre" class="form-label">1er. Nombre</label>
			            	<input type="text" class="form-control" id="nombre" name="nombre" required>
          				</div>
          				<div class="col-md-3">
            				<label for="nombre2" class="form-label">2do. Nombre</label>
            				<input type="text" class="form-control" id="nombre2" name="nombre2" >
          				</div>
          				<div class="col-md-3">
            				<label for="apellido1" class="form-label">1er. Apellido</label>
            				<input type="text" class="form-control" id="apellido1" name="apellido1" required>
          				</div>
          				<div class="col-md-3">
            				<label for="apellido2" class="form-label">2do. Apellido</label>
            				<input type="text" class="form-control" id="apellido2" name="apellido2" >
          				</div>
          				<div class="col-md-3">
            				<label for="apellido3" class="form-label">Apellido de Casada</label>
            				<input type="text" class="form-control" id="apellido3" name="apellido3" >
          				</div>
          				<div class="col-md-2">
           					<label for="sexo" class="form-label">Sexo</label>
            				<select class="form-select" id="sexo" name="sexo" required>
			   				<?php
								include_once '../../assets/dbc.php';
								$sql = "SELECT id, nombre FROM cat_sexo ORDER BY nombre";
								$result = mysqli_query($conn, $sql);
								$options = "<option value=''>Seleccione</option>";
								while ($row = mysqli_fetch_assoc($result)) {
									$options .= "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
								}
								echo $options;
							?>
            				</select>
          				</div>
          				<div class="col-md-3">
            				<label for="fnacimiento" class="form-label">Fecha nacimiento</label>
            				<input type="date" class="form-control" id="fnacimiento" name="fnacimiento" >
          				</div>
          				<div class="col-md-2">
            				<label for="edad_anios" class="form-label">Edad (años)</label>
            				<input type="number" class="form-control" id="edad_anios" name="edad_anios" >
          				</div>
          				<div class="col-md-2">
            				<label for="edad_meses" class="form-label">(meses)</label>
            				<input type="number" class="form-control" id="edad_meses" name="edad_meses" >
          				</div>
          				<div class="col-md-4">
            				<label for="nrespon" class="form-label">Padre, madre o responsable 1</label>
            				<input type="text" class="form-control" id="nrespon" name="nrespon" >
          				</div>
          				<div class="col-md-4">
            				<label for="nrespon2" class="form-label">Padre, madre o responsable 2</label>
            				<input type="text" class="form-control" id="nrespon2" name="nrespon2" >
          				</div>
          				<div class="col-md-3">
            				<label for="telefono" class="form-label">Telefono</label>
            				<input type="text" class="form-control" id="telefono" name="telefono" >
          				</div>
          				<div class="col-md-4">
            				<label for="direccion" class="form-label">Dirección</label>
            				<input type="text" class="form-control" id="direccion" name="direccion" >
          				</div>
          				<div class="col-md-4">
            				<label for="aldea" class="form-label">Aldea</label>
            				<input type="text" class="form-control" id="aldea" name="aldea" >
          				</div>
          				<div class="col-md-4">
            				<label for="departamento" class="form-label">Departamento</label>
            				<select class="form-select" id="departamento" name="departamento" >
			   				<?php
								include_once '../../assets/dbc.php';
								$sql = "SELECT id, nombre FROM cat_departamento ORDER BY nombre";
								$result = mysqli_query($conn, $sql);
								$options = "<option value=''>Seleccione</option>";
								while ($row = mysqli_fetch_assoc($result)) {
									$options .= "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
								}
								echo $options;
							?>
            				</select>
          				</div>
          				<div class="col-md-4">
            				<label for="municipio" class="form-label">Municipio</label>
            				<select class="form-select" id="municipio" name="municipio" >
              					<option value="">Seleccione</option>
            				</select>
          				</div>
          				<div class="col-md-4">
            				<label for="email" class="form-label">Correo electrónico</label>
            				<input type="text" class="form-control" id="email" name="email" >
          				</div>
          				<div class="col-md-4">
            				<label for="dpi" class="form-label">DPI</label>
            				<input type="text" class="form-control" id="dpi" name="dpi" >
          				</div>
          				<div class="col-md-4">
            				<label for="nit" class="form-label">NIT</label>
            				<input type="text" class="form-control" id="nit" name="nit" >
          				</div>
          				<div class="col-md-8">
            				<label for="id_motivo_consulta" class="form-label">Motivo de consulta</label>
            				<select class="form-select" id="id_motivo_consulta" name="id_motivo_consulta" >
			   				<?php
								include_once '../../assets/dbc.php';
								$sql = "SELECT id, nombre FROM cat_motivo_consulta ORDER BY nombre";
								$result = mysqli_query($conn, $sql);
								$options = "<option value=''>Seleccione</option>";
								while ($row = mysqli_fetch_assoc($result)) {
									$options .= "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
								}
								echo $options;
							?>
            				</select>
          				</div>
          				<div class="col-md-4">
            				<label for="id_etnia" class="form-label">Etnia</label>
            				<select class="form-select" id="id_etnia" name="id_etnia" >
			   				<?php
								include_once '../../assets/dbc.php';
								$sql = "SELECT id, nombre FROM cat_etnia ORDER BY nombre";
								$result = mysqli_query($conn, $sql);
								$options = "<option value=''>Seleccione</option>";
								while ($row = mysqli_fetch_assoc($result)) {
									$options .= "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
								}
								echo $options;
							?>
            				</select>
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
</div>

<script>
	$(document).ready(function () {
	  const tabla = $('#tablaClientes').DataTable({
		ajax: FORM_URL + 'cli/listar_clientes.php',
		responsive: true,
		dom: 'Bfrtip',
		order: [[0, 'desc']],
	    columnDefs: [
			{ targets: '_all', width: 'auto' }
 	    ],
        columns: [
          { data: 'id' },
		  { data: 'cliente'},
		  { data: 'sexo'},
		  { data: 'direccion'},
		  { data: 'telefono'},
		  { data: 'dpi_cui'},
		  {
		  data: null,
		  render: function (data) {
            return `
        		<button class="btn btn-sm btn-warning editar" data-id="${data.id}" data-toggle='tooltip' data-placement='left' title='Editar' ><i class='bi bi-pencil-square'></i></button>
         		<button class="btn btn-sm btn-danger eliminar" data-id="${data.id}" data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='bi bi-trash'></i></button>
         		<button class="btn btn-sm btn-success seleccionar" data-id="${data.id}" data-toggle='tooltip' data-placement='left' title='Seleccionar'><i class='bi bi-check2-circle'></i></button>
              `;
		  }, 	
          orderable: false
		}
		]
	  });

	  // Nuevo paciente
	  $('#btnNuevoPaciente').click(function () {
      	$('#id_cliente').val('');
	  	$('#formCliente')[0].reset();
      	$('#tituloModal').text('Nuevo Paciente');
      	$('#modalCliente').modal('show');
	  });
		
	  // Seleccionar cliente activo
	  $('#tablaClientes').on('click', '.seleccionar', function () {
		const clienteId = $(this).data('id');
		if (!clienteId) {
			alert('ID de cliente no válido');
			return;
		}
		$.post(FORM_URL +'cli/seleccionar_cliente.php', { id: clienteId }, function (res) {
			actualizarClienteActivo(clienteId);
		}, 'json');
  	  });

	  // Eliminar cliente
	  $('#tablaClientes').on('click', '.eliminar', function () {
		const clienteId = $(this).data('id');
		var ca_text = document.getElementById('Clienteactivo').textContent;
		var dom_ftx = ca_text.indexOf('( ') + 1;
		var dom_ltx = ca_text.indexOf(' )');
		var cli_inx = '';

		if (dom_ftx > 0) {
			cli_inx = ca_text.substring(dom_ftx, dom_ltx);
		}

		if (clienteId == cli_inx) {
			alert("El paciente a eliminar es el activo, por favor deseleccionelo y vuelva a intentarlo");
		}
		else {
			if (confirm('¿Seguro que desea eliminar el paciente?')) {
		  		$.post(FORM_URL +'cli/eliminar_cliente.php', { id: clienteId }, function (res) {
					tabla.ajax.reload();
		  		});
			}
		}
	  });
      
	  // funcion guardar cliente
	  $('#formCliente').on('submit', function(e) {
		e.preventDefault();
		$.ajax({
			url: FORM_URL +'cli/guardar_cliente.php', 
			method: 'POST',
			data: $(this).serialize(),
			dataType: 'json',
			success: function(respuesta) {
				if (respuesta.status === 'ok') {
					$('#modalClienteLabel').text('Nuevo paciente');
					$('#modalCliente').modal('hide');
					$('#formCliente')[0].reset();
					tabla.ajax.reload();
					if(respuesta.cliente_id) {
						$.post(FORM_URL +'cli/seleccionar_cliente.php', 
							   { id: respuesta.cliente_id }, 
							   function (res) {
								actualizarClienteActivo(respuesta.cliente_id);
						}, 'json');
					}	
				} 
				else 
				{
					alert('Error! ' + (respuesta.message || 'No se pudo guardar.'));
				}
			},      
			error: function() {
				alert('Error de red al guardar el cliente');
			}
		});
	  });

	  // Seleccionar cliente activo
	  $('#tablaClientes').on('click', '.seleccionar', function () {
	    const clienteId = $(this).data('id');
		if (!clienteId) {
			alert('ID de cliente no válido');
			return;
		}

		$.post(FORM_URL +'cli/seleccionar_cliente.php', { id: clienteId }, function (res) {
			actualizarClienteActivo(clienteId);
		}, 'json');
  	  });

	  // Editar cliente 
	  $('#tablaClientes').on('click', '.editar', function () {
		const id = $(this).data('id');

		$.get(FORM_URL +'cli/get_cliente.php', { id: id }, function (data) {
			const c = JSON.parse(data);

			$('#modalClienteLabel').text('Editar paciente');
			$('#formCliente')[0].reset();
			$('#id_cliente').val(c.id); // ID oculto
			$('#nombre').val(c.nombre_1);
			$('#nombre2').val(c.nombre_2);
			$('#nrespon').val(c.nombre_3);
			$('#nrespon2').val(c.nombre_4);
			$('#apellido1').val(c.apellido_1);
			$('#apellido2').val(c.apellido_2);
			$('#apellido3').val(c.apellido_casada);
			$('#sexo').val(c.id_sexo);
			$('#fnacimiento').val(c.fecha_nacimiento);
			$('#telefono').val(c.telefono);
			$('#direccion').val(c.direccion_calle_avenida);
			$('#email').val(c.email);
			$('#dpi').val(c.dpi_cui);
			$('#nit').val(c.nit);
			$('#aldea').val(c.aldea);
			$('#edad_anios').val(c.edad_anios);
			$('#edad_meses').val(c.edad_meses);
			$('#id_etnia').val(c.id_etnia);
			$('#id_motivo_consulta').val(c.id_motivo_consulta);

			// Cargar ubicación en cascada
			$.get(FORM_URL +'cli/get_departamentos.php', function (data) {
			  $('#departamento').html(data);
			  $('#departamento').val(c.id_departamento);

			  $.get(FORM_URL +'cli/get_ubicacion.php', { departamento_id: c.id_departamento }, function (data) {
				$('#municipio').html(data);
				$('#municipio').val(c.id_municipio);

				$.get(FORM_URL +'cli/get_ubicacion.php', { municipio_id: c.id_municipio }, function (data) {
				  $('#aldea').html(data);
				  $('#aldea').val(c.aldea);
				});
			  });
			});

			$('#tituloModal').text('Editar paciente');
			$('#modalCliente').modal('show');
		});
  	  });
		
	  $('#dpi').on('input', function() {
	  // Solo números y máximo 12 dígitos
		let valor = $(this).val().replace(/\D/g, '').substring(0, 13);
		$(this).val(valor);
	  });

	});
</script>

<script>
	// Cargar municipios al seleccionar un departamento
	$('#departamento').change(function () {
	  var deptoId = $(this).val();
	  $.get(FORM_URL +'cli/get_ubicacion.php', { departamento_id: deptoId }, function(data) {
		$('#municipio').html(data);
		$('#aldea').html('<option value="">Seleccione</option>'); // limpiar aldeas
	  });
	});

	// Cargar aldeas al seleccionar un municipio
	$('#municipio').change(function () {
	  var muniId = $(this).val();
	  $.get(FORM_URL +'cli/get_ubicacion.php', { municipio_id: muniId }, function(data) {
		$('#aldea').html(data);
	  });
	});
</script>

<!--
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const btnNuevo = document.getElementById('btn_nuevo');
    const modalLabel = document.getElementById('modalClienteLabel');

    btnNuevo.addEventListener('click', function () {
      modalLabel.textContent = 'Nuevo paciente';
      document.getElementById('id_cliente').value = '';
      const modal = new bootstrap.Modal(document.getElementById('modalCliente'));
      modal.show();
    });
  });
</script>
-->

<script>
	document.getElementById("fnacimiento").addEventListener("change", function() {
  	const fnac = new Date(this.value);
  	if (isNaN(fnac)) return;
  	const hoy = new Date();
  	let anios = hoy.getFullYear() - fnac.getFullYear();
  	let meses = hoy.getMonth() - fnac.getMonth();
  	let dias = hoy.getDate() - fnac.getDate();
  	if (dias < 0) {
    	meses--;
  	}
  	if (meses < 0) {
    	anios--;
    	meses += 12;
  	}
  	document.getElementById("edad_anios").value = anios >= 0 ? anios : 0;
  	document.getElementById("edad_meses").value = meses >= 0 ? meses : 0;
	});
</script>

<?php
if ($modo === 'normal') {
?>

	</body>
	</html>
<?php
}
?>