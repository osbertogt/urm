<?php
// archivo: horarios_medicos.php
session_start();
require '../../assets/dbc.php';
?>
<!DOCTYPE html>
<html lang="es">
<style>
  .modal-header,
  .modal-body {
	background-color: #E0FFFF;
  }

  .modal-header {
	padding: 1rem;
	background-color: #97BBFE;
	color: white;
  }

  .modal-body {
	padding: 1rem; /* igual que el header */
  }

  .modal-content {
	border-radius: 12px;
	overflow: hidden;
  }
</style>

<head>
</head>
<body>
<div class="container mt-4">
  <h4>Horarios de atención (por dia)</h4>
  <button class="btn btn-primary mb-3" id="btnNuevo">Agregar Horario</button>
  <table id="tablaHorarios" class="table table-bordered">
    <thead>
      <tr>
        <th>Médico</th>
        <th>Especialidad</th>
        <th>Día</th>
        <th>Horario</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalHorario" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formHorario">
        <div class="modal-header">
          <h5 class="modal-title">Agregar/Editar Horario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
          <div class="mb-3">
            <label>Médico</label>
            <select name="id_medico" id="id_medico" class="form-select" required>
              <?php
              $sql = "SELECT p.id, CONCAT_WS(' ', p.nombre_1, p.nombre_2, p.apellido_1, p.apellido_2) AS nombre
                      FROM tbl_persona p
                      WHERE p.id_tipopersona=3 and p.id_especialidad IS NOT NULL";
              $result = $conn->query($sql);
              while ($row = $result->fetch_assoc()) {
                  echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
              }
              ?>
            </select>
          </div>
			<div class="mb-3">
			  <label for="dia_semana">Día de la semana</label>
			  <select name="dia_semana" id="dia_semana" class="form-select" required>
				<option value="1">Lunes</option>
				<option value="2">Martes</option>
				<option value="3">Miércoles</option>
				<option value="4">Jueves</option>
				<option value="5">Viernes</option>
				<option value="6">Sábado</option>
				<option value="0">Domingo</option>
			  </select>
			</div>
          <div class="mb-3">
            <label>Hora inicio</label>
            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
            <label>Hora final</label>
            <input type="time" name="hora_final" id="hora_final" class="form-control" required>
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
	const tabla = $('#tablaHorarios').DataTable({
	  ajax: FORM_URL + 'cit/listar_horarios.php',
	  columns: [
		{ data: 'medico' },
		{ data: 'especialidad' },
		{
		  data: 'dia_semana',
		  render: function (data) {
			const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
			return dias[data] ?? 'Desconocido';
		  }
		},
		{ data: 'hora' },
		{
		  data: null,
		  render: function (data) {
			return `
			  <button class="btn btn-warning btn-sm btnEditar" data-id="${data.id}">Editar</button>
			  <button class="btn btn-danger btn-sm btnEliminar" data-id="${data.id}">Eliminar</button>
			`;
		  }
		}
	  ]
	});

  $('#btnNuevo').click(function () {
    $('#formHorario')[0].reset();
    $('#id').val('');
    $('#modalHorario').modal('show');
  });

  $('#tablaHorarios').on('click', '.btnEditar', function () {
    const id = $(this).data('id');
    $.getJSON(FORM_URL +'cit/obtener_horario.php', { id }, function (data) {
      $('#id').val(data.id);
      $('#id_medico').val(data.id_medico);
      $('#dia_semana').val(data.dia_semana);
      $('#hora').val(data.hora);
      $('#modalHorario').modal('show');
    });
  });

  $('#tablaHorarios').on('click', '.btnEliminar', function () {
    const id = $(this).data('id');
    if (confirm('Desea eliminar este horario?')) {
      $.post(FORM_URL +'cit/eliminar_horario.php', { id }, function () {
        tabla.ajax.reload();
      });
    }
  });

  $('#formHorario').submit(function (e) {
    e.preventDefault();
    $.post(FORM_URL +'cit/guardar_horario.php', $(this).serialize(), function () {
      $('#modalHorario').modal('hide');
      tabla.ajax.reload();
    });
  });
});
</script>
</body>
</html>
