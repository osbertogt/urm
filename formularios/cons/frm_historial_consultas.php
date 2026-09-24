<?php
// consultas.php
require_once '../../assets/dbc.php'; // ajusta la ruta si es necesario
?>
<!doctype html>
<html lang="es">
<head>
  <style>
    .card-header { background:#2c5aa0; color:#fff; }
    .pdf-btn { min-width:110px; }
  </style>
</head>
<body>
<div class="container mt-4">
  <div class="d-flex align-items-center mb-3">
    <h4 class="me-auto"><i class="bi bi-journal-medical"></i> Historial de consultas </h4>
  </div>

  <table id="tblConsultas" class="table table-striped table-bordered display nowrap" style="width:100%">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Número</th>
        <th>Especialidad</th>
        <th>Motivo admisión</th>
        <th>Paciente</th>
        <th>Médico</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- scripts -->

<script>
$(document).ready(function(){
  const tbl = $('#tblConsultas').DataTable({
    ajax: {
      url: FORM_URL+'cons/listar_consultas_historial.php',
      type: 'POST',
      dataSrc: ''
    },
    columns: [
      { data: 'fecha' },
      { data: 'numero_ticket' },
      { data: 'especialidad' },
      { data: 'motivo' },
      { data: 'cliente' },
      { data: 'medico' },
      {
        data: null,
        orderable: false,
        className: 'text-center',
        render: function (d) {
          // botón que abre el pdf en nueva pestaña
          return `<a class="btn btn-sm btn-outline-primary pdf-btn" target="_blank" 
                    href="formularios/cons/generar_pdf_consulta.php?id_consulta=${encodeURIComponent(d.id)}" title="Generar PDF">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                  </a>`;
        }
      }
    ],
    responsive: true,
    order: [[0, 'desc']]
  });
});
</script>
</body>
</html>
