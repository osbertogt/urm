<?php
if (!isset($_SESSION)) { session_start(); }
include_once '../../assets/dbc.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body class="bg-light">

<div class="container mt-4">
  <h4 class="mb-3"><i class="bi bi-people"></i> Costo de servicios</h4>

  <!-- 🔹 Filtros -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label for="filtroTipo" class="form-label">Tipo de servicio</label>
      <select id="filtroTipo" class="form-select">
        <option value="">Todos</option>
        <?php
        $sql = "SELECT DISTINCT tiposervicio FROM vw_servicios_paciente ORDER BY tiposervicio";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()):
          echo "<option value='" . htmlspecialchars($row['tiposervicio']) . "'>" . htmlspecialchars($row['tiposervicio']) . "</option>";
        endwhile;
        ?>
      </select>
    </div>
    <div class="col-md-3">
      <label for="fechaInicio" class="form-label">Desde</label>
      <input type="date" id="fechaInicio" class="form-control">
    </div>
    <div class="col-md-3">
      <label for="fechaFin" class="form-label">Hasta</label>
      <input type="date" id="fechaFin" class="form-control">
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button id="btnFiltrar" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
    </div>
  </div>

  <!-- 🔹 Tabla -->
  <div class="card shadow-sm">
    <div class="card-body">
      <table id="tablaServicios" class="table table-striped table-bordered w-100">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Tipo Servicio</th>
            <th>Número Ticket</th>
            <th>Paciente</th>
            <th>DPI</th>
            <th>Servicio</th>
            <th class="text-end">Costo (Q)</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th colspan="6" class="text-end">Total:</th>
            <th id="totalCosto" class="text-end"></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
  const tabla = $('#tablaServicios').DataTable({
    ajax: {
      url: FORM_URL+'rep/rp_costo_servicios_actions.php',
      type: 'POST',
      data: function (d) {
        d.action = 'listar';
        d.tiposervicio = $('#filtroTipo').val();
        d.fechaInicio = $('#fechaInicio').val();
        d.fechaFin = $('#fechaFin').val();
      },
      dataSrc: ''
    },
    columns: [
      { data: 'fecha' },
      { data: 'tiposervicio' },
      { data: 'numero_ticket' },
      { data: 'paciente' },
      { data: 'dpi' },
      { data: 'servicio' },
      {
        data: 'costo',
        className: 'text-end',
        render: function (data) {
          const num = parseFloat(data) || 0;
          return num.toLocaleString('es-GT', { minimumFractionDigits: 2 });
        }
      }
    ],
    order: [[0, 'desc'], [1, 'asc']],
    pageLength: 50,
    responsive: true,
    footerCallback: function (row, data) {
      let total = 0;
      data.forEach(r => total += parseFloat(r.costo) || 0);
      $('#totalCosto').html(total.toLocaleString('es-GT', { minimumFractionDigits: 2 }));
    }
  });

  // Filtrar resultados
  $('#btnFiltrar').on('click', function () {
    tabla.ajax.reload();
  });
});
</script>

</body>
</html>
