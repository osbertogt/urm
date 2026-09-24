<?php
require_once '../../assets/dbc.php';
$id_consulta = isset($_POST['id']) ? intval($_POST['id']) : 0;
$fecha = $id_medico = $id_especialidad = $id_cliente = '';

// Consulta para obtener los datos de la orden
if ($id_consulta > 0) {
$sql = "
    SELECT 
        c.id_cliente,
        c.id_medico,
        c.id_motivo_admision,
        c.fecha,
        CONCAT_WS(' ',
            TRIM(pac.nombre_1),
            TRIM(pac.nombre_2),
            TRIM(pac.nombre_3),
            TRIM(pac.apellido_1),
            TRIM(pac.apellido_2),
            IF(TRIM(pac.apellido_casada) IS NULL OR TRIM(pac.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(pac.apellido_casada)))
        ) AS nombre_paciente,
        CONCAT_WS(' ',
            TRIM(med.nombre_1),
            TRIM(med.nombre_2),
            TRIM(med.nombre_3),
            TRIM(med.apellido_1),
            TRIM(med.apellido_2),
            IF(TRIM(med.apellido_casada) IS NULL OR TRIM(med.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(med.apellido_casada)))
        ) AS nombre_medico
    FROM tbl_atencion c
    LEFT JOIN tbl_persona pac ON pac.id = c.id_cliente
    LEFT JOIN tbl_persona med ON med.id = c.id_medico
    WHERE c.id = $id_consulta
";
$res = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($res)) {
    $id_cliente = $row['id_cliente'];
    $id_medico = $row['id_medico'];
    $id_especialidad = $row['id_motivo_admision'];
    $fecha = $row['fecha'];
    $nombre_paciente = $row['nombre_paciente'];
    $nombre_medico = $row['nombre_medico'];
}

}
?>

<!DOCTYPE html>
<html lang="es">
<body>
	<!-- Modal para agregar atencion -->
	<div class="modal fade" id="modalSignosV" tabindex="-1" aria-labelledby="modalSignosVLabel" aria-hidden="true" data-bs-backdrop="false">
	  <div class="modal-dialog">
		<form id="formSignosV">
			<input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo $id_cliente; ?>">
			<input type="hidden" id="id_consulta" name="id_consulta" value="<?php echo $id_consulta; ?>">
			<div class="modal-content p-1" style="border: 2px solid blue;">
				<div class="modal-header p-0.5" style="background-color: #97BBFE; color: white;">
				  <h5 class="modal-title" id="modalSignosVLabel">Toma de signos vitales</h5>
				  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body"  style="background-color: #F0F8FF;">
					<div class="col mb-2">
						<div class="row-md-4">
						  <label class="form-label">Paciente</label>
						  <input type="text" class="form-control" value="<?php echo htmlspecialchars($nombre_paciente); ?>" disabled>
						</div>
						<div class="row-md-4">
						  <label class="form-label">Fecha ingreso</label>
						  <input type="date" class="form-control" id="fconsulta" name="fconsulta" disabled
							   value="<?php echo $fecha ? date('Y-m-d', strtotime($fecha)) : ''; ?>">
						</div>
						<div class="row-md-4">
						  <label class="form-label">Médico</label>
						  <input type="text" class="form-control" value="<?php echo htmlspecialchars($nombre_medico); ?>" disabled>
						</div>
					</div>
					<div class="row">
					  <div class="col-md">
						<div class="card mb-sm-3 shadow-sm">
						  <div class="card-header text-center"  style="background-color: #CCCCFF; color: black;">
							Signos vitales
						  </div>
						  <div class="card-body">

							<div class="row mb-2 align-items-center">
							  <div class="mb-3">
								<label for="fecha_hora" class="form-label">Fecha  y hora</label>
									<input type="datetime-local" class="form-control" id="fecha_hora" name="fecha_hora" required>
							  </div>
							</div>

							<div class="row mb-2 align-items-center">
							  <label for="presion" class="col-5 col-form-label">Presión arterial (mmHg)</label>
							  <div class="col-7">
								<input type="text" class="form-control" id="presion" name="presion">
							  </div>
							</div>

							<div class="row mb-2 align-items-center">
							  <label for="pulso" class="col-5 col-form-label">Pulso (latidos/min)</label>
							  <div class="col-7">
								<input type="text" class="form-control" id="pulso" name="pulso">
							  </div>
							</div>

							<div class="row mb-2 align-items-center">
							  <label for="respiracion" class="col-5 col-form-label">Respiración (resp/min)</label>
							  <div class="col-7">
								<input type="text" class="form-control" id="respiracion" name="respiracion">
							  </div>
							</div>

							<div class="row mb-2 align-items-center">
							  <label for="temperatura" class="col-5 col-form-label">Temperatura (°C)</label>
							  <div class="col-7">
								<input type="text" class="form-control" id="temperatura" name="temperatura">
							  </div>
							</div>
						  </div>
						</div>
					  </div>
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
		// funcion guardar toma de signos vitales
		$('#formSignosV').on('submit', function(e) {
			e.preventDefault();
			const formData = new FormData(this);
			$.ajax({
				url: FORM_URL + 'cons/guardar_signos.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				success: function(response) {
					console.log("Respuesta:", response);
					if(response.status === 'ok') {
						$('#modalSignosV').modal('hide');
						$('#formSignosV')[0].reset(); 
						tabla.ajax.reload();
					} else {
						alert(response.message);
					}
				},
				error: function(xhr) {
					alert("Error en la solicitud: " + xhr.responseText);
				}
			});
			$('#modalClienteLabel').text('Nueva toma de signos');
		});
	</script>

	<script>
	  document.addEventListener('DOMContentLoaded', () => {
		const now = new Date();
		const offset = now.getTimezoneOffset();
		const localDate = new Date(now.getTime() - offset * 60 * 1000);
		const formatted = localDate.toISOString().slice(0, 16);
		document.getElementById('fecha_hora').value = formatted;
	  });
	</script>


</body>
</html>
