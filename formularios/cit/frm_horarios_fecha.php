<?php
// medico_horarios.php
require_once '../../assets/dbc.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body class="bg-light">

<div class="container py-4">
  <h4 class="mb-4"><i class="bi bi-calendar-week"></i> Horarios de atención (por fecha)</h4>

  <!-- Tabla -->
  <table id="tblHorarios" class="table table-striped table-hover table-bordered align-middle w-100">
    <thead class="table-primary">
      <tr>
        <th>ID</th>
        <th>Médico</th>
        <th>Fecha inicio</th>
        <th>Fecha final</th>
        <th>Disponibilidad</th>
        <th>Acciones</th>
      </tr>
    </thead>
  </table>

  <hr class="my-4">

  <!-- Formulario -->
  <h5><i class="bi bi-pencil-square"></i> Registrar / Editar horario</h5>
  <form id="formHorario" class="row g-3 mt-1">
    <input type="hidden" id="id" name="id">

    <div class="col-md-4">
      <label for="id_medico" class="form-label">Médico</label>
      <select id="id_medico" name="id_medico" class="form-select" required>
        <option value="">Seleccione...</option>
        <?php
        $res = mysqli_query($conn, "SELECT id, CONCAT_WS(' ', nombre_1, nombre_2, apellido_1, apellido_2) AS nombre FROM tbl_persona WHERE id_tipopersona=3 ORDER BY nombre_1");
        while ($row = mysqli_fetch_assoc($res)) {
          echo '<option value="'.htmlspecialchars($row['id']).'">'.htmlspecialchars($row['nombre']).'</option>';
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="fecha_inicio" class="form-label">Fecha inicio</label>
      <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required>
    </div>

    <div class="col-md-3">
      <label for="fecha_final" class="form-label">Fecha final</label>
      <input type="date" id="fecha_final" name="fecha_final" class="form-control" required>
    </div>

    <div class="col-md-2">
      <label for="habilitado" class="form-label">Disponible</label>
      <select id="habilitado" name="habilitado" class="form-select" required>
        <option value="1">Sí</option>
        <option value="0">No</option>
      </select>
    </div>

    <div class="col-12 mt-2">
      <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar</button>
      <button type="reset" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</button>
    </div>
  </form>
</div>

<script>
$(document).ready(function() {
  const ajaxURL = FORM_URL+'cit/ajax_medico_horarios.php';

  // Inicializar DataTable
  const tabla = $('#tblHorarios').DataTable({
    ajax: {
      url: ajaxURL,
      type: 'POST',
      data: { action: 'listar' },
      dataSrc: ''
    },
    columns: [
      { data: 'id' },
      { data: 'medico' },
      { data: 'fecha_inicio' },
      { data: 'fecha_final' },
      {
        data: 'habilitado',
        render: function(data) {
          return data == 1
            ? '<span class="text-success fw-bold">Disponible</span>'
            : '<span class="text-danger fw-bold">No disponible</span>';
        }
      },
      {
        data: null,
        render: function(row) {
          return `
            <button class="btn btn-sm btn-primary btnEditar" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-danger btnEliminar" data-id="${row.id}"><i class="bi bi-trash"></i></button>
          `;
        }
      }
    ],
    createdRow: function(row, data) {
      if (data.habilitado == 0) {
        $(row).find('td').css('color', 'red');
      }
    },
    responsive: true,
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
    }
  });

  // Enviar formulario (guardar o editar)
  $('#formHorario').on('submit', function(e) {
    e.preventDefault();
    const fd = $(this).serialize() + '&action=guardar';
    $.post(ajaxURL, fd, function(resp) {
      if (resp.success) {
        tabla.ajax.reload();
        $('#formHorario')[0].reset();
        $('#id').val('');
      } else {
        alert('Error: ' + resp.message);
      }
    }, 'json').fail(() => alert('Error de conexión.'));
  });

  // Editar
  $('#tblHorarios').on('click', '.btnEditar', function() {
    const id = $(this).data('id');
    $.post(ajaxURL, { action: 'obtener', id }, function(resp) {
      if (resp.success) {
        const d = resp.data;
        $('#id').val(d.id);
        $('#id_medico').val(d.id_medico);
        $('#fecha_inicio').val(d.fecha_inicio);
        $('#fecha_final').val(d.fecha_final);
        $('#habilitado').val(d.habilitado);
      } else alert('No se encontró el registro.');
    }, 'json');
  });

  // Eliminar
  $('#tblHorarios').on('click', '.btnEliminar', function() {
    if (!confirm('¿Eliminar este registro?')) return;
    const id = $(this).data('id');
    $.post(ajaxURL, { action: 'eliminar', id }, function(resp) {
      if (resp.success) tabla.ajax.reload();
      else alert('Error: ' + resp.message);
    }, 'json');
  });
});
</script>
</body>
</html>
