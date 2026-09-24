<?php
// dashboard.php
require_once '../../assets/dbc.php'; // ajustar ruta si hace falta

// utilidad para obtener año-mes por defecto (YYYY-MM)
$default_ym = date('Y-m');
?>
<!doctype html>
<html lang="es">
<head>
  <style>
    .card-count { font-size: 2.25rem; font-weight:700; }
    .card-sub { font-size: .95rem; color: rgba(0,0,0,.6); }
    .card-header-alt-0 { background:#0d6efd; color:#fff; } /* azul */
    .card-header-alt-1 { background:#198754; color:#fff; } /* verde */
    .card-header-alt-2 { background:#fd7e14; color:#fff; } /* naranja */
    .card-header-alt-3 { background:#6f42c1; color:#fff; } /* lila */
    .small-muted { font-size:.9rem; color:#666; }
    .weekday-badge { font-size:.95rem; padding: .5rem 0.7rem; display:block; }
	.badge-dia {
		padding: 0.15rem 0.25rem !important; /* reduce ancho */
		font-size: 0.75rem;
	}
  </style>
</head>
<body>
<div class="container-fluid py-4">

  <div class="d-flex align-items-center mb-3">
    <h3 class="me-3"><i class="bi bi-calendar-check"></i> Estado del mes</h3>
    <div class="ms-auto d-flex gap-2 align-items-center">
      <label class="small-muted mb-0 me-2">Mes:</label>
      <input type="month" id="filterMes" class="form-control form-control-sm" value="<?php echo htmlspecialchars($default_ym); ?>">
      <button id="btnActualizar" class="btn btn-primary btn-sm ms-2"><i class="bi bi-arrow-clockwise"></i> Actualizar</button>
    </div>
  </div>

  <div id="cardsContainer" class="row g-3">
    <!-- cards se inyectan aquí -->
  </div>

  <hr class="my-4">

  <h5 class="mb-3">Consultas atendidas por día de la semana (mes seleccionado)</h5>
  <div id="weekContainer" class="row g-2">
    <!-- badges por día -->
  </div>

  <div class="mt-4 small-muted">
    <i class="bi bi-info-circle"></i> Los conteos corresponden al mes seleccionado. 
  </div>
</div>

<script>
(function(){
  const colors = ['card-header-alt-0','card-header-alt-1','card-header-alt-2','card-header-alt-3'];

  function fetchData(ym) {
    return $.ajax({
      url: FORM_URL+'rep/dashboard_stats.php',
      method: 'POST',
      dataType: 'json',
      data: { ym: ym }
    });
  }

  function renderDoctors(doctors) {
    const container = $('#cardsContainer');
    container.empty();
    if (!Array.isArray(doctors)) return;

    doctors.forEach((d, i) => {
      const hdrClass = colors[i % colors.length];
      const col = $(`
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-header ${hdrClass}">
              <div class="d-flex align-items-center">
                <div class="me-2"><i class="bi bi-person-badge"></i></div>
                <div>
                  <div class="fw-bold">${escapeHtml(d.medico)}</div>
                  <div class="small">${escapeHtml(d.especialidad || '')}</div>
                </div>
                <div class="ms-auto text-end">
                  <div class="card-sub">Citas programadas</div>
                  <div class="card-count text-white">${numberFormat(d.citas_programadas)}</div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="row gy-2">
                <div class="col-6">
                  <div class="small-muted">Confirmadas</div>
                  <div class="h4 mb-0">${numberFormat(d.citas_confirmadas)}</div>
                </div>
                <div class="col-6">
                  <div class="small-muted">Consultas atendidas</div>
                  <div class="h4 mb-0">${numberFormat(d.consultas_atendidas)}</div>
                </div>
              </div>
            </div>
            <div class="card-footer small text-muted">
              Mes: <strong>${escapeHtml(d.mes)}</strong>
            </div>
          </div>
        </div>
      `);
      container.append(col);
    });
  }

  function renderWeek(weekCounts) {
    const cont = $('#weekContainer');
    cont.empty();

    // Orden de días: Lunes..Domingo (usar array fija para consistencia)
    const days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const mapNames = {
      Monday: 'Lunes', Tuesday: 'Martes', Wednesday: 'Miércoles',
      Thursday: 'Jueves', Friday: 'Viernes', Saturday: 'Sábado', Sunday: 'Domingo'
    };

    days.forEach(day => {
      const val = parseInt(weekCounts[day] || 0);
      const badge = $(`
        <div class="col-12 col-md-2 col-lg-2">
          <div class="border rounded p-2 text-center">
            <span class="badge-dia fw-semibold">${mapNames[day]}</span>
            <div class="display-6 fw-bold">${numberFormat(val)}</div>
          </div>
        </div>
      `);
      cont.append(badge);
    });
  }

  function numberFormat(v){ return (typeof v === 'number' ? v : parseInt(v||0)) ; }
  function escapeHtml(s){ return String(s||'').replace(/[&<>"'\/]/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;'}[c]; }); }

  function loadAndRender() {
    const ym = $('#filterMes').val();
    fetchData(ym).done(function(resp){
      if (!resp || resp.error) {
        alert('Error cargando datos');
        return;
      }
      renderDoctors(resp.doctors || []);
      renderWeek(resp.week_counts || {});
    }).fail(function(){
      alert('Error en la petición al servidor');
    });
  }

  $('#btnActualizar').on('click', loadAndRender);
  // carga inicial
  $(function(){ loadAndRender(); });
})();
</script>
</body>
</html>
