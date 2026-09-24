<?php
require_once '../../assets/dbc.php';


$formas_pago = [];
$res = $conn->query("SELECT id, nombre FROM cat_formapago ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
  $formas_pago[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body class="p-4 bg-light">

<div class="container">
  <h4 class="mb-4"><i class="bi bi-wallet2 me-2"></i>Resumen de cobros</h4>

  <form id="filtroForm" class="row g-3 mb-3">
    <div class="col-md-3">
      <label class="form-label">Desde</label>
      <input type="date" name="desde" id="desde" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Hasta</label>
      <input type="date" name="hasta" id="hasta" class="form-control" required>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-search"></i> Filtrar
      </button>
    </div>
  </form>

  <div class="table-responsive">
    <table id="tblCuentas" class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>Fecha</th>
          <th>Código</th>
          <th>Cliente</th>
          <?php foreach ($formas_pago as $fp): ?>
            <th><?= htmlspecialchars($fp['nombre']) ?></th>
          <?php endforeach; ?>
          <th>Total</th>
        </tr>
      </thead>
      <tfoot class="table-secondary fw-bold">
        <tr>
          <th colspan="3" class="text-end">Totales:</th>
          <?php foreach ($formas_pago as $fp): ?>
            <th id="tot_fp_<?= $fp['id'] ?>" class="text-end"></th>
          <?php endforeach; ?>
          <th id="tot_global" class="text-end"></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<script>
$(document).ready(function() {
  const ajaxUrl = FORM_URL+'cob/cuentas_clientes_ajax.php';
  const formasPago = <?= json_encode($formas_pago) ?>;

  // Definir columnas dinámicamente
  let columns = [
    { data: 'fecha' },
    { data: 'codigo' },
    { data: 'cliente' }
  ];

  formasPago.forEach(fp => {
    columns.push({
      data: fp.nombre,
      className: 'text-end'
    });
  });

  columns.push({
    data: 'total',
    className: 'text-end fw-bold'
  });

  let tabla = $('#tblCuentas').DataTable({
    processing: false,
    serverSide: false,
    paging: true,
    searching: false,
    ajax: {
      url: ajaxUrl,
      type: 'GET',
      data: function(d) {
        d.desde = $('#desde').val();
        d.hasta = $('#hasta').val();
      },
      dataSrc: function(json) {
        // Actualizar totales globales dinámicamente
        formasPago.forEach(fp => {
          $('#tot_fp_' + fp.id).text(json.totales['fp_' + fp.id].toFixed(2));
        });
        $('#tot_global').text(json.totales.global.toFixed(2));
        return json.data;
      },
      error: function() {
        alert('Error cargando datos');
      }
    },
    columns: columns,
    order: [[0, 'desc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    },
	  dom: 'Bfrtip',
  buttons: [
    {
      extend: 'excelHtml5',
      footer: true, 
      text: '<i class="bi bi-file-earmark-excel"></i> Excel',
      titleAttr: 'Exportar a Excel',
      customize: function (xlsx) {
        
      }
    },
    {
      extend: 'pdfHtml5',
      footer: true, 
      text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
      titleAttr: 'Exportar a PDF',
      orientation: 'landscape',
      pageSize: 'A4',
      customize: function (doc) {
        
        doc.styles.tableFooter = {
          bold: true,
          fontSize: 11,
          color: 'black'
        };
      }
    },
    {
      extend: 'print',
      footer: true, 
      text: '<i class="bi bi-printer"></i> Imprimir',
      titleAttr: 'Imprimir'
    }
  ],
  footerCallback: function (row, data, start, end, display) {
    const api = this.api();

    // función para limpiar y convertir a número
    const num = val => typeof val === 'string'
      ? parseFloat(val.replace(/[^0-9.-]+/g, '')) || 0
      : typeof val === 'number' ? val : 0;

    // ejemplo: sumar columna 4 (efectivo) y 5 (cheque) y 6 (total)
    const totalEfectivo = api.column(4).data().reduce((a, b) => a + num(b), 0);
    const totalCheque = api.column(5).data().reduce((a, b) => a + num(b), 0);
    const totalGeneral = api.column(6).data().reduce((a, b) => a + num(b), 0);

    // mostrar totales en el footer
    $(api.column(4).footer()).html(totalEfectivo.toFixed(2));
    $(api.column(5).footer()).html(totalCheque.toFixed(2));
    $(api.column(6).footer()).html(totalGeneral.toFixed(2));
  }
  });

  $('#filtroForm').on('submit', function(e){
    e.preventDefault();
    tabla.ajax.reload();
  });
});
</script>
</body>
</html>
