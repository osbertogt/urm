<?php
if(!isset($_SESSION)) {session_start();}
 
// Verificar si hay un cliente activo en sesión
if (!isset($_SESSION['cliente_activo']) || empty($_SESSION['cliente_activo'])) {
    //die("No hay paciente activo seleccionado.");
}
$id_cliente=$_SESSION['cliente_activo'];
$paciente_id = $_SESSION['cliente_activo'];

require_once '../../../assets/dbc.php';

$sql = "SELECT id, nombre_1, nombre_2, nombre_3, apellido_1, apellido_2, id_sexo, edad_anios, edad_meses, fecha_nacimiento
        FROM tbl_persona WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $paciente_id);
mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);
$pac = mysqli_fetch_assoc($res) ?: [];

function full_name($p){
    $parts = array_filter([$p['nombre_1'] ?? '', $p['nombre_2'] ?? '', $p['apellido_1'] ?? '', $p['apellido_2'] ?? '']);
    return trim(join(' ', $parts));
}

$paciente_nombre = htmlspecialchars(full_name($pac));

$paciente_sexo = '';

if (!empty($pac['id_sexo'])) {
    $r = mysqli_query($conn, "SELECT nombre FROM cat_sexo WHERE id = ".(int)$pac['id_sexo']);
    $s = mysqli_fetch_assoc($r); $paciente_sexo = $s['nombre'] ?? '';
}
$paciente_fnac = $pac['fecha_nacimiento'] ?? '';
$paciente_edad_anios = $pac['edad_anios'] ?? '';
$paciente_edad_meses = $pac['edad_meses'] ?? '';
$paciente_edad = $paciente_edad_anios." años, ".$paciente_edad_meses." meses.";
?>

<!doctype html>
<html lang="es">
<head>
  <style>
    .modal-dialog-scrollable .modal-body { max-height: calc(100vh - 180px); overflow-y:auto; }
    .card-header-azul { background:#2c5aa0; color:#fff; }
    .card-header-verde { background:#28a745; color:#fff; }
    .card-header-lila { background:#9b59b6; color:#fff; }
    .card-header-naranja { background:#fd7e14; color:#fff; }
    .card-header-rojo { background:#dc3545; color:#fff; }
  </style>
</head>
<body>
  <div class="container py-4">
  <div class="row">
    <div class="col-md-8">
      <h3 class="mb-0">Consultas pediatría</h3>
    </div>
	<?php if (isset($_SESSION['cliente_activo']) and !empty($_SESSION['cliente_activo'])) { ?>
		<div class="col-md-4 text-end">
		  <button id="btnNuevo" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAtencion"><i class="bi bi-plus-lg"></i> Nueva consulta</button>
		</div>
	<?php } ?>
  </div>

  <div class="row">
    <div class="col-12">
      <table id="tblAtenciones" class="table table-striped table-bordered w-100">
        <thead>
          <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Número Ticket</th>
            <th>Paciente</th>
            <th>Sexo</th>
            <th>Motivo Admisión</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- Modal: Atencion -->
	<!-- Modal Nueva Atención -->
	<div class="modal fade" id="modalAtencion" tabindex="-1" aria-labelledby="modalAtencionLabel" aria-hidden="true">
	  <div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content">
		  
		  <!-- Header -->
		  <div class="modal-header bg-primary text-white">
			<h5 class="modal-title" id="modalAtencionLabel">
			  <i class="bi bi-person-plus"></i> Nueva consulta pediátrica
			</h5>
			<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
		  </div>

		  <!-- Body -->
		  <div class="modal-body">
			<form id="formAtencion">
			  <!-- Datos generales del paciente -->
			  <div class="card mb-3">
				<div class="card-header bg-secondary text-white">
				  <i class="bi bi-person-badge"></i> Datos del paciente
				</div>
				<div class="card-body row g-3">
				  <div class="col-md-6">
					<label>Nombre completo</label>
					<input type="text" class="form-control" id="paciente_nombre" value="<?php echo $paciente_nombre ?>" disabled>
				  </div>
				  <div class="col-md-2">
					<label>Sexo</label>
					<input type="text" class="form-control" id="paciente_sexo" value="<?php echo $paciente_sexo ?>" disabled>
				  </div>
				  <div class="col-md-2">
					<label>Fecha nacimiento</label>
					<input type="date" class="form-control" id="paciente_fnac" value="<?php echo $paciente_fnac ?>" disabled>
				  </div>
				  <div class="col-md-2">
					<label>Edad</label>
					<input type="text" class="form-control" id="paciente_edad" value="<?php echo $paciente_edad ?>"  disabled>
				  </div>
				</div>
			  </div>

			  <!-- Tabs -->
			  <ul class="nav nav-tabs" id="tabsAtencion" role="tablist">
				<li class="nav-item">
				  <button class="nav-link active" id="tabFicha-tab" data-bs-toggle="tab" data-bs-target="#tabFicha" type="button" role="tab">
					<i class="bi bi-journal-text"></i> Ficha
				  </button>
				</li>
				<li class="nav-item">
				  <button class="nav-link" id="tabVacunas-tab" data-bs-toggle="tab" data-bs-target="#tabVacunas" type="button" role="tab">
					<i class="bi bi-syringe"></i> Vacunas
				  </button>
				</li>
				<li class="nav-item">
				  <button class="nav-link" id="tabCurvas-tab" data-bs-toggle="tab" data-bs-target="#tabCurvas" type="button" role="tab">
					<i class="bi bi-graph-up"></i> Curvas de crecimiento
				  </button>
				</li>
			  </ul>

			  <div class="tab-content mt-3">

				<!-- TAB FICHA -->
				<div class="tab-pane fade show active" id="tabFicha" role="tabpanel">

				  <!-- Tipo de admisión y fecha -->
				  <div class="row g-3 mb-3">
					<div class="col-md-6">
					  <label>Tipo de consulta</label>
					  <select class="form-select" name="id_motivo_admision" id="id_motivo_admision"></select>
					</div>
					<div class="col-md-6">
					  <label>Fecha de atención</label>
					  <input type="date" class="form-control" name="fecha_atencion" id="fecha_atencion" value="<?=date('Y-m-d')?>">
					</div>
				  </div>

				  <!-- Datos primera consulta -->
				  <div class="card mb-3">
					<div class="card-header bg-info text-white">
					  <i class="bi bi-baby"></i> Datos de primera consulta
					</div>
					<div class="card-body row g-3">
					  <div class="col-md-6">
						<label>Datos del parto</label>
						<textarea class="form-control" name="datos_parto" rows="2"></textarea>
					  </div>
					  <div class="col-md-6">
						<label>Datos del recién nacido</label>
						<textarea class="form-control" name="datos_recien_nacido" rows="2"></textarea>
					  </div>
					  <div class="col-md-6">
						<label>Alimentación 1er año</label>
						<textarea class="form-control" name="alimentacion_1er_anio" rows="2"></textarea>
					  </div>
					  <div class="col-md-6">
						<label>Desarrollo psicomotor</label>
						<textarea class="form-control" name="desarrollo_psicomotor" rows="2"></textarea>
					  </div>
					</div>
				  </div>

				  <!-- Antecedentes -->
				  <div class="card mb-3">
					<div class="card-header bg-warning text-dark">
					  <i class="bi bi-clipboard-data"></i> Antecedentes
					</div>
					<div class="card-body">
					  <div id="antecedentesContainer" class="table-responsive">
						<!-- Aquí se cargan dinámicamente con PHP/Ajax -->
					  </div>
					</div>
				  </div>

				  <!-- Cards de atención -->
				  <div id="cardsAtencion">
					
					<!-- Motivo de consulta -->
					<div class="card mb-3">
					  <div class="card-header bg-primary text-white">
						<i class="bi bi-chat-dots"></i> Motivo de consulta
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="motivo_consulta" rows="3"></textarea>
					  </div>
					</div>

					<!-- Historia de la enfermedad actual -->
					<div class="card mb-3">
					  <div class="card-header bg-success text-white">
						<i class="bi bi-journal-medical"></i> Historia de la enfermedad actual
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="historia_enfermedad_actual" rows="3"></textarea>
					  </div>
					</div>

					<!-- Examen físico -->
					<div class="card mb-3">
					  <div class="card-header bg-info text-white">
						<i class="bi bi-person-lines-fill"></i> Examen físico
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="examen_fisico" rows="3"></textarea>
					  </div>
					</div>

					<!-- Signos vitales -->
					<div class="card mb-3">
					  <div class="card-header bg-warning text-dark">
						<i class="bi bi-activity"></i> Signos vitales
					  </div>
					  <div class="card-body row g-3">
						<div class="col-md-3">
						  <label>Temperatura (°C)</label>
						  <input type="number" step="0.1" class="form-control" name="sv_temperatura">
						</div>
						<div class="col-md-3">
						  <label>Frec. Cardiaca</label>
						  <input type="number" class="form-control" name="sv_frecuencia_cardiaca">
						</div>
						<div class="col-md-3">
						  <label>Frec. Respiratoria</label>
						  <input type="number" class="form-control" name="sv_frecuencia_respiratoria">
						</div>
						<div class="col-md-3">
						  <label>Presión arterial</label>
						  <input type="text" class="form-control" name="sv_presion_arterial">
						</div>
					  </div>
					</div>

					<!-- Antropometría -->
					<div class="card mb-3">
					  <div class="card-header bg-secondary text-white">
						<i class="bi bi-rulers"></i> Antropometría
					  </div>
					  <div class="card-body row g-3">
						<div class="col-md-4">
						  <label>Talla (cm)</label>
						  <input type="number" step="0.1" class="form-control" name="talla">
						</div>
						<div class="col-md-4">
						  <label>Peso (kg)</label>
						  <input type="number" step="0.1" class="form-control" name="peso">
						</div>
						<div class="col-md-4">
						  <label>Circ. Cefálica (cm)</label>
						  <input type="number" step="0.1" class="form-control" name="circ_cefalica">
						</div>
					  </div>
					</div>

					<!-- Diagnóstico -->
					<div class="card mb-3">
					  <div class="card-header bg-danger text-white">
						<i class="bi bi-file-medical"></i> Diagnóstico
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="diagnostico" rows="3"></textarea>
					  </div>
					</div>

					<!-- Tratamiento -->
					<div class="card mb-3">
					  <div class="card-header bg-dark text-white">
						<i class="bi bi-capsule"></i> Tratamiento
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="tratamiento" rows="3"></textarea>
					  </div>
					</div>

					<!-- Medicamentos administrados -->
					<div class="card mb-3">
					  <div class="card-header bg-light text-dark">
						<i class="bi bi-bandaid"></i> Medicamentos administrados
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="medicamentos_administrados" rows="3"></textarea>
					  </div>
					</div>

					<!-- Receta -->
					<div class="card mb-3">
					  <div class="card-header bg-primary text-white">
						<i class="bi bi-clipboard-check"></i> Receta
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="receta" rows="3"></textarea>
					  </div>
					</div>

					<!-- Laboratorio -->
					<div class="card mb-3">
					  <div class="card-header bg-success text-white">
						<i class="bi bi-flask"></i> Laboratorio
					  </div>
					  <div class="card-body">
						<textarea class="form-control" name="laboratorio" rows="3"></textarea>
					  </div>
					</div>

				  </div>
				</div>

				<!-- TAB VACUNAS -->
				<div class="tab-pane fade" id="tabVacunas" role="tabpanel">
				  <div class="card mb-3">
					<div class="card-header bg-info text-white">
					  <i class="bi bi-syringe"></i> Historial de Vacunas del paciente
					</div>
					<div class="card-body">
					  
					  <!-- DataTable -->
					  <table id="tblVacunas" class="table table-striped table-bordered w-100 mb-3">
						<thead>
						  <tr>
							<th>ID</th>
							<th>Fecha</th>
							<th>Vacuna</th>
							<th>Dosis</th>
							<th>Observaciones</th>
							<th>Acciones</th>
						  </tr>
						</thead>
					  </table>

					  <!-- Formulario para agregar/editar -->
					  <div class="card mt-3">
						<div class="card-header bg-success text-white">
						  <i class="bi bi-plus-circle"></i> Agregar / Editar Vacuna
						</div>
						<div class="card-body">
						  <div id="formVacuna">
							<input type="hidden" id="vac_id" name="id" value="">
							<div class="row g-2 align-items-end">
							  <div class="col-md-3">
								<label class="form-label">Fecha</label>
								<input type="date" id="vac_fecha" name="fecha" class="form-control" readonly>
							  </div>
								<script>
								document.getElementById('vac_fecha').valueAsDate = new Date();
								</script>
							  <div class="col-md-4">
								<label class="form-label">Vacuna</label>
								<select id="vac_id_vacuna" name="id_vacuna" class="form-select" ></select>
							  </div>
							  <div class="col-md-3">
								<label class="form-label">Dosis</label>
								<select id="vac_id_dosis" name="id_dosis" class="form-select" ></select>
							  </div>
							  <div class="col-md-2 text-end">
								<button type="button" class="btn btn-success" id="btnGuardarVacuna">
								  <i class="bi bi-save"></i> Guardar
								</button>
							  </div>
							</div>

							<div class="row mt-3">
							  <div class="col-12">
								<label class="form-label">Observaciones</label>
								<textarea id="vac_observaciones" name="observaciones" class="form-control" rows="2"></textarea>
							  </div>
							</div>
						  </div>
						</div>
					  </div>

					</div>
				  </div>
				</div>

				<!-- TAB CURVAS -->
				
				<div class="tab-pane fade" id="tabCurvas" role="tabpanel">
				  <div class="row">
					<div class="col-12 mb-3">
					  <div class="card">
						<div class="card-header bg-secondary text-white"><i class="bi bi-list-ul"></i> Registros antropométricos</div>
						<div class="card-body">
						  <div class="table-responsive">
							<table id="tblAnthro" class="table table-sm table-striped">
							  <thead>
								<tr><th>#Atención</th><th>Fecha</th><th>Edad (meses)</th><th>Talla (cm)</th><th>Peso (kg)</th><th>Circ. cefálica (cm)</th></tr>
							  </thead>
							  <tbody></tbody>
							</table>
						  </div>
						</div>
					  </div>
					</div>

					<!-- Gráficas -->
					<div class="col-md-4">
					  <div class="card">
						<div class="card-header bg-primary text-white"><i class="bi bi-arrows-expand"></i> Talla para la edad</div>
						<div class="card-body">
						  <canvas id="chartTalla" height="200"></canvas>
						</div>
					  </div>
					</div>

					<div class="col-md-4">
					  <div class="card">
						<div class="card-header bg-success text-white"><i class="bi bi-bounding-box-circles"></i> Peso para la edad</div>
						<div class="card-body">
						  <canvas id="chartPeso" height="200"></canvas>
						</div>
					  </div>
					</div>

					<div class="col-md-4">
					  <div class="card">
						<div class="card-header bg-warning text-dark"><i class="bi bi-record-circle"></i> Circunferencia cefálica para la edad</div>
						<div class="card-body">
						  <canvas id="chartCirc" height="200"></canvas>
						</div>
					  </div>
					</div>

					<div class="col-12 mt-3 text-end">
					  <button id="btnGenerarPdfCurvas" class="btn btn-outline-primary">
						<i class="bi bi-file-earmark-pdf"></i> Generar PDF con datos y curvas
					  </button>
					</div>
				  </div>
				</div>

				<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
				<script>
				$(function(){
				  const ajax = FORM_URL+'cons/pdt/curvas_ajax.php';
				  let anthroData = [];
				  let sexoPaciente = 1; // 1 femenino, 2 masculino (se sobrescribe)
				  let fechaNacAprox = null;

				  function loadAnthro(){
					$.getJSON(ajax, { action: 'get_anthro' }, function(res){
					  if(!res || !res.success) { console.error(res); return; }
					  anthroData = res.data || [];
					  sexoPaciente = res.sexo || 1;
					  fechaNacAprox = res.fecha_nacimiento_aprox || null;
					  renderTable();
					  renderCharts();
					}).fail(function(){ alert('Error cargando datos antropométricos'); });
				  }

				  function renderTable(){
					const tbody = $('#tblAnthro tbody').empty();
					if(!Array.isArray(anthroData) || anthroData.length === 0){
					  tbody.append('<tr><td colspan="6" class="text-center">No hay registros</td></tr>');
					  return;
					}
					anthroData.forEach(r=>{
					  tbody.append(`<tr>
						<td>${r.id_atencion}</td>
						<td>${r.fecha}</td>
						<td>${r.age_months}</td>
						<td>${r.talla ?? ''}</td>
						<td>${r.peso ?? ''}</td>
						<td>${r.circ_cefalica ?? ''}</td>
					  </tr>`);
					});
				  }

				  // ---------- Percentiles de ejemplo (placeholder) ----------
				  // NOTA: Estas series SON ILUSTRATIVAS. Para uso clínico reemplaza por tablas LMS de la OMS.
				  // Generaremos percentiles simples para edades 0..60 meses.
				  function generateExamplePercentiles(maxMonths = 60, tipo = 'talla', sexo = 1){
					// ejemplo muy aproximado: valores crecientes linealmente (NO clínico)
					const months = Array.from({length: maxMonths+1}, (_,i)=>i);
					const p3 = months.map(m => baseline(tipo, sexo, m, -2));  // ~-2Z
					const p15 = months.map(m => baseline(tipo, sexo, m, -1));
					const p50 = months.map(m => baseline(tipo, sexo, m, 0));
					const p85 = months.map(m => baseline(tipo, sexo, m, 1));
					const p97 = months.map(m => baseline(tipo, sexo, m, 2));
					return { months, p3, p15, p50, p85, p97 };
				  }

				  // función baseline que devuelve una "curva" base según tipo y z
				  function baseline(tipo, sexo, m, z){
					// valores base ilustrativos (no clínicos):
					if(tipo === 'talla'){
					  // talla en cm: bebé nace ~46-50 y aumenta 0.5-1 cm/mes (simplificado)
					  const base0 = (sexo==2 ? 49 : 48); // varía por sexo
					  return +(base0 + 0.5 * m + z*1.5).toFixed(2);
					}
					if(tipo === 'peso'){
					  // peso en kg: nace ~3.2, incrementa variable
					  const base0 = (sexo==2 ? 3.3 : 3.2);
					  return +(base0 + 0.2 * m * 0.5 + z*0.5).toFixed(2); // simplificado
					}
					if(tipo === 'circ'){
					  // circunferencia en cm
					  const base0 = (sexo==2 ? 35.5 : 35.0);
					  return +(base0 + 0.1 * m + z*0.8).toFixed(2);
					}
					return 0;
				  }

				  // create charts
				  let chartTalla, chartPeso, chartCirc;
				  function renderCharts(){
					// prepare series of patient points
					const monthsPts = anthroData.map(r => r.age_months).filter(m => m !== null);
					const labels = anthroData.map(r => r.age_months ?? r.fecha);

					// use example percentiles range up to max recorded months or 60
					const maxMonth = Math.max( ...anthroData.map(r=>r.age_months || 0), 24 );
					const perc = generateExamplePercentiles(Math.max(60, maxMonth+6), 'talla', sexoPaciente);

					// datasets for talla
					const patientTallaX = anthroData.map(r=>r.age_months);
					const patientTallaY = anthroData.map(r=>r.talla);
					createOrUpdateLine('chartTalla', chartTalla, {
					  title: 'Talla (cm) para la edad',
					  xlabel: 'Edad (meses)',
					  perc: perc,
					  patientX: patientTallaX,
					  patientY: patientTallaY,
					  tipo:'talla'
					}, function(c){ chartTalla = c; });

					// peso
					const percPeso = generateExamplePercentiles(Math.max(60, maxMonth+6),'peso', sexoPaciente);
					const patientPesoX = anthroData.map(r=>r.age_months);
					const patientPesoY = anthroData.map(r=>r.peso);
					createOrUpdateLine('chartPeso', chartPeso, {
					  title: 'Peso (kg) para la edad',
					  xlabel: 'Edad (meses)',
					  perc: percPeso,
					  patientX: patientPesoX,
					  patientY: patientPesoY,
					  tipo:'peso'
					}, function(c){ chartPeso = c; });

					// circ
					const percCirc = generateExamplePercentiles(Math.max(60, maxMonth+6),'circ', sexoPaciente);
					const patientCircX = anthroData.map(r=>r.age_months);
					const patientCircY = anthroData.map(r=>r.circ_cefalica);
					createOrUpdateLine('chartCirc', chartCirc, {
					  title: 'Circ. cefálica (cm) para la edad',
					  xlabel: 'Edad (meses)',
					  perc: percCirc,
					  patientX: patientCircX,
					  patientY: patientCircY,
					  tipo:'circ'
					}, function(c){ chartCirc = c; });
				  }

				  function createOrUpdateLine(canvasId, existingChart, opts, cb){
					const ctx = document.getElementById(canvasId).getContext('2d');
					const months = opts.perc.months;
					const datasets = [
					  { label: 'P3', data: opts.perc.p3, borderColor: 'rgba(200,200,200,0.6)', fill: false, tension:0.2, pointRadius:0 },
					  { label: 'P15', data: opts.perc.p15, borderColor: 'rgba(170,170,170,0.6)', fill: false, tension:0.2, pointRadius:0 },
					  { label: 'P50', data: opts.perc.p50, borderColor: 'rgba(120,120,255,0.9)', fill: false, tension:0.2, pointRadius:0 },
					  { label: 'P85', data: opts.perc.p85, borderColor: 'rgba(170,170,170,0.6)', fill: false, tension:0.2, pointRadius:0 },
					  { label: 'P97', data: opts.perc.p97, borderColor: 'rgba(200,200,200,0.6)', fill: false, tension:0.2, pointRadius:0 },
					];

					// patient data as scatter on top
					const patientPoints = [];
					for(let i=0;i<opts.patientX.length;i++){
					  const x = opts.patientX[i];
					  const y = opts.patientY[i];
					  if(x === null || y === null || isNaN(x) || isNaN(y)) continue;
					  patientPoints.push({ x: x, y: y });
					}

					// if chart exists, update data
					if (existingChart) {
					  existingChart.data.labels = months;
					  existingChart.data.datasets = datasets.concat([{
						label: 'Paciente',
						data: patientPoints,
						borderColor: 'rgba(0,120,0,0.9)',
						backgroundColor: 'rgba(0,120,0,0.6)',
						showLine: false,
						pointRadius: 4
					  }]);
					  existingChart.update();
					  if (cb) cb(existingChart);
					  return;
					}

					// create new chart
					const cfg = {
					  type: 'scatter',
					  data: {
						labels: months,
						datasets: datasets.concat([{
						  label: 'Paciente',
						  data: patientPoints,
						  borderColor: 'rgba(0,120,0,0.9)',
						  backgroundColor: 'rgba(0,120,0,0.6)',
						  showLine: false,
						  pointRadius: 4
						}])
					  },
					  options: {
						plugins: {
						  legend: { display: false }
						},
						scales: {
						  x: {
							type: 'linear',
							title: { display: true, text: 'Edad (meses)' },
							min: 0,
							max: Math.max(...months)
						  },
						  y: {
							title: { display: true, text: opts.title }
						  }
						},
						elements: { line: { tension: 0.2 } }
					  }
					};
					const chart = new Chart(ctx, cfg);
					if (cb) cb(chart);
				  }

				  // generar PDF: enviamos las 3 imágenes (canvas) y la tabla (json)
				  $('#btnGenerarPdfCurvas').click(function(){
					const canvas1 = document.getElementById('chartTalla');
					const canvas2 = document.getElementById('chartPeso');
					const canvas3 = document.getElementById('chartCirc');
					const img1 = canvas1.toDataURL('image/png');
					const img2 = canvas2.toDataURL('image/png');
					const img3 = canvas3.toDataURL('image/png');
					// tabla como HTML o JSON
					const tablaHtml = $('#tblAnthro')[0].outerHTML;

					// enviar al servidor para generar PDF
					$.post('generar_curvas_pdf.php', {
					  img1: img1,
					  img2: img2,
					  img3: img3,
					  tabla: tablaHtml
					}, function(resp){
					  if (resp && resp.url) {
						window.open(resp.url, '_blank');
					  } else {
						alert('Error generando PDF');
					  }
					}, 'json').fail(function(){ alert('Error al generar PDF'); });
				  });

				  // inicial
				  loadAnthro();

				  // recargar al mostrar tab
				  $('#tabCurvas-tab').on('shown.bs.tab', function(){ loadAnthro(); });

				});
				</script>

			  </div>
			</form>
		  </div>
		  <!-- Footer -->
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
			  <i class="bi bi-x-circle"></i> Cancelar
			</button>
			<button type="submit" form="formAtencion" class="btn btn-success">
			  <i class="bi bi-save"></i> Guardar
			</button>
		  </div>

		</div>
	  </div>
	</div>

<script> // scripts atenciones
$(function(){
  const ajaxUrl = FORM_URL+'cons/pdt/atenciones_ajax.php';

  // DataTable: listar atenciones
  const table = $('#tblAtenciones').DataTable({
    ajax: { url: ajaxUrl, type: 'POST', data: { action: 'list' }, dataSrc: '' },
    columns: [
      { data: 'id' },
      { data: 'fecha' },
      { data: 'numero_ticket' },
      { data: 'paciente' },
      { data: 'sexo' },
      { data: 'motivo' },
      { data: null, orderable:false, render: d => `
          <button class="btn btn-sm btn-warning btn-edit" data-id="${d.id}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-danger btn-delete" data-id="${d.id}"><i class="bi bi-trash"></i></button>
          <button class="btn btn-sm btn-success btn-receta" data-id="${d.id}"><i class="bi bi-card-text"></i></button>
        ` }
    ],
    order: [[1,'desc']]
  });

  // cargar motivos de admisión y cat_antecedentes al abrir modal
  function cargarSelectMotivos(){
    $.getJSON(ajaxUrl, { action: 'get_motivos' }, function(data){
      let html = '<option value="">Seleccionar...</option>';
      data.forEach(m => html += `<option value="${m.id}">${m.nombre}</option>`);
      $('#id_motivo_admision').html(html);
    });
  }

  function cargarAntecedentes(){
    $('#antecedentesContainer').html('<div class="text-muted">Cargando...</div>');
    $.getJSON(ajaxUrl, { action: 'get_antecedentes' }, function(data){
      if(!Array.isArray(data) || data.length===0){
        $('#antecedentesContainer').html('<div class="text-muted">No hay antecedentes definidos.</div>');
        return;
      }
      let html = '';
      data.forEach(a=>{
        html += `<div class="row mb-2 align-items-center">
                   <div class="col-md-5"><strong>${a.nombre}</strong></div>
                   <div class="col-md-1"><input type="checkbox" class="form-check-input" name="ant_${a.id}_si" value="1"></div>
                   <div class="col-md-1"><input type="checkbox" class="form-check-input" name="ant_${a.id}_no" value="1"></div>
                   <div class="col-md-5"><input type="text" class="form-control" name="ant_${a.id}_obs" placeholder="Observaciones"></div>
                 </div>`;
      });
      $('#antecedentesContainer').html(html);
    });
  }

  // abrir modal nuevo
  $('#btnNuevo').click(function(){
    $('#formAtencion')[0].reset();
    $('#form_action').val('add');
    $('#id_atencion').val('');
    $('#numero_ticket').val('');
    cargarSelectMotivos();
    cargarAntecedentes();

    // comprobar si existe primera consulta para este paciente (servidor envía la data)
    $.getJSON(ajaxUrl, { action:'get_first_consulta', id_paciente: <?= $paciente_id ?> }, function(r){
      if(r && r.exists){
        // poblar y deshabilitar los campos del card primera consulta
        $('#datos_parto').val(r.datos_parto).prop('disabled', true);
        $('#datos_recien_nacido').val(r.datos_recien_nacido).prop('disabled', true);
        $('#alimentacion_1er_anio').val(r.alimentacion_1er_anio).prop('disabled', true);
        $('#desarrollo_psicomotor').val(r.desarrollo_psicomotor).prop('disabled', true);
      } else {
        // ninguno: habilitar campos para llenado
        $('#datos_parto,#datos_recien_nacido,#alimentacion_1er_anio,#desarrollo_psicomotor').prop('disabled', false).val('');
      }
      $('#modalAtencion').modal('show');
    });
  });

  // Editar: cargar datos de atencion y consulta
  $('#tblAtenciones').on('click', '.btn-edit', function(){
    const id = $(this).data('id');
    $.post(ajaxUrl, { action:'get', id }, function(resp){
      if(!resp || !resp.success){ alert('No se encontró registro'); return; }
      const d = resp.data;
      $('#form_action').val('edit');
      $('#id_atencion').val(d.id);
      $('#fecha').val(d.fecha);
      $('#numero_ticket').val(d.numero_ticket);
      $('#id_motivo_admision').val(d.id_motivo_admision);
      // llenar primeros campos y demas campos de consulta
      $('[name="datos_parto"]').val(d.datos_parto).prop('disabled', !!d.first_locked);
      $('[name="datos_recien_nacido"]').val(d.datos_recien_nacido).prop('disabled', !!d.first_locked);
      $('[name="alimentacion_1er_anio"]').val(d.alimentacion_1er_anio).prop('disabled', !!d.first_locked);
      $('[name="desarrollo_psicomotor"]').val(d.desarrollo_psicomotor).prop('disabled', !!d.first_locked);

      // antecedentes: primero limpiar, luego poblar
      cargarAntecedentes();
      setTimeout(function(){
        if(Array.isArray(d.antecedentes)){
          d.antecedentes.forEach(a=>{
            $(`[name="ant_${a.id_antecedente}_si"]`).prop('checked', a.valor==1);
            $(`[name="ant_${a.id_antecedente}_no"]`).prop('checked', a.valor==0);
            $(`[name="ant_${a.id_antecedente}_obs"]`).val(a.observaciones || '');
          });
        }
      }, 300);

      // datos de atencion
      $('[name="motivo_consulta"]').val(d.motivo_consulta);
      $('[name="historia_enfermedad_actual"]').val(d.historia_enfermedad_actual);
      $('[name="examen_fisico"]').val(d.examen_fisico);
      $('[name="sv_temperatura"]').val(d.sv_temperatura);
      $('[name="sv_frecuencia_cardiaca"]').val(d.sv_frecuencia_cardiaca);
      $('[name="sv_frecuencia_respiratoria"]').val(d.sv_frecuencia_respiratoria);
      $('[name="sv_presion_arterial"]').val(d.sv_presion_arterial);
      $('[name="sv_ausc_pulmonar"]').val(d.sv_ausc_pulmonar);
      $('[name="talla"]').val(d.talla);
      $('[name="peso"]').val(d.peso);
      $('[name="circ_cefalica"]').val(d.circ_cefalica);
      $('[name="diagnostico"]').val(d.diagnostico);
      $('[name="tratamiento"]').val(d.tratamiento);
      $('[name="medicamentos_administrados"]').val(d.medicamentos_administrados);
      $('[name="receta"]').val(d.receta);
      $('[name="laboratorio"]').val(d.laboratorio);

      $('#modalAtencion').modal('show');

    }, 'json').fail(function(){ alert('Error de servidor'); });
  });

  // Eliminar
  $('#tblAtenciones').on('click', '.btn-delete', function(){
    if(!confirm('Eliminar esta atención?')) return;
    const id = $(this).data('id');
    $.post(ajaxUrl, { action:'delete', id }, function(r){
      if(r && r.success) table.ajax.reload();
      else alert('Error al eliminar');
    }, 'json');
  });

  // Guardar (add | edit)
$('#formAtencion').submit(function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('id_paciente', <?= $paciente_id ?>);
    formData.append('action', 'add');
    
    $.post({
        url: ajaxUrl,
        data: formData,
        processData: false,
        contentType: false,
        success: function(resp){
            if(resp && resp.success){
                $('#modalAtencion').modal('hide');
                table.ajax.reload();
            } else {
                alert('Error: ' + (resp && resp.message ? resp.message : 'Error desconocido'));
            }
        },
        dataType: 'json'
    }).fail(function(){ alert('Error en servidor'); });
});
  // generar lista inicial
  cargarSelectMotivos();
  cargarAntecedentes();
});
</script>


<script>  // scripts vacunas
$(function(){
  const ajaxVac = FORM_URL+'cons/pdt/vacunas_ajax.php';
  // poner fecha hoy en formato yyyy-mm-dd y deshabilitado
  function setFechaHoy() {
    const hoy = new Date();
    const y = hoy.getFullYear();
    const m = String(hoy.getMonth()+1).padStart(2,'0');
    const d = String(hoy.getDate()).padStart(2,'0');
    $('#vac_fecha').val(`${y}-${m}-${d}`);
  }
  setFechaHoy();

  // inicializar selects (vacunas y dosis)
  function cargarSelects() {
    // vacunas
    $.getJSON(ajaxVac, { action: 'list_catalog_vacunas' }, function(data){
      let html = '<option value="">Seleccionar vacuna...</option>';
      data.forEach(v => html += `<option value="${v.id}">${escapeHtml(v.nombre)}</option>`);
      $('#vac_id_vacuna').html(html);
    });
    // dosis
    $.getJSON(ajaxVac, { action: 'list_catalog_dosis' }, function(data){
      let html = '<option value="">Seleccionar dosis...</option>';
      data.forEach(d => html += `<option value="${d.id}">${escapeHtml(d.nombre)}</option>`);
      $('#vac_id_dosis').html(html);
    });
  }
  function escapeHtml(s){ return String(s||'').replace(/[&<>"'\/]/g,function(c){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;'})[c];}); }

  // DataTable
  let tblVacunas = $('#tblVacunas').DataTable({
    ajax: {
      url: ajaxVac,
      type: 'POST',
      data: { action: 'list_vacunas' },
      dataSrc: ''
    },
    columns: [
      { data: 'id' },
      { data: 'fecha' },
      { data: 'vacuna' },
      { data: 'dosis' },
      { data: 'observaciones' },
      { data: null, orderable:false, render: function(d){
          return `
            <button class="btn btn-sm btn-warning btn-edit-vac" data-id="${d.id}"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-danger btn-del-vac" data-id="${d.id}"><i class="bi bi-trash"></i></button>
            <button class="btn btn-sm btn-success btn-carne-vac" data-id="${d.id}"><i class="bi bi-card-heading"></i></button>
          `;
        }
      }
    ],
    order: [[1,'desc']],
    language: { emptyTable: "No hay vacunas registradas" }
  });

  // cargar selects al abrir tab (si tu modal ya lo inicializa, también puedes invocar en show)
  $('#tabVacunas-tab').on('shown.bs.tab', function(){
    cargarSelects();
    tblVacunas.ajax.reload();
  });

	// submit add/edit
	$('#btnGuardarVacuna').on('click', function(e) {
		e.preventDefault();

		if (!$('#vac_id_vacuna').val()) { alert('Seleccione la vacuna'); return; }
		if (!$('#vac_id_dosis').val()) { alert('Seleccione la dosis'); return; }

		const formData = new FormData($('this')[0]);
		
		// Agregar campos adicionales
		formData.append('action', $('#vac_id').val() ? 'edit_vacuna' : 'add_vacuna');
		formData.append('id_paciente', <?= $paciente_id ?>); 
		formData.append('vac_id_vacuna', $('#vac_id_vacuna').val());
		formData.append('vac_id_dosis', $('#vac_id_dosis').val());
		formData.append('vac_observaciones', $('#vac_observaciones').val());
		formData.append('fecha_registro', new Date().toISOString());

		$.post({
			url: ajaxVac,
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function(resp) {
				if (resp?.success) {
					tblVacunas.ajax.reload();
					//$('#formVacuna')[0].reset();
					$('#vac_id').val('');
					setTimeout(setFechaHoy, 50);
					cargarSelects();
				} else {
					alert('Error: ' + (resp?.message || 'No se pudo guardar'));
				}
			}
		}).fail(function(){ 
			alert('Error de conexión'); 
		});
	});

  // editar: cargar datos al formulario
  $('#tblVacunas').on('click', '.btn-edit-vac', function(){
    const id = $(this).data('id');
    $.getJSON(ajaxVac, { action: 'get_vacuna', id }, function(r){
      if (!r || !r.success) { alert('Registro no encontrado'); return; }
      const d = r.data;
      $('#vac_id').val(d.id);
      $('#vac_fecha').val(d.fecha);
      $('#vac_id_vacuna').val(d.id_vacuna);
      $('#vac_id_dosis').val(d.id_dosis);
      $('#vac_observaciones').val(d.observaciones);
      // switch to Vacunas tab if needed
      const tabEl = new bootstrap.Tab($('#tabVacunas-tab'));
      tabEl.show();
    });
  });

  // eliminar
  $('#tblVacunas').on('click', '.btn-del-vac', function(){
    if (!confirm('Eliminar registro de vacuna?')) return;
    const id = $(this).data('id');
    $.post(ajaxVac, { action: 'delete_vacuna', id }, function(r){
      if (r && r.success) tblVacunas.ajax.reload();
      else alert('Error al eliminar');
    }, 'json').fail(function(){ alert('Error de conexión'); });
  });

  // generar carnet (stub): abre nueva ventana que podrías implementar para generar PDF del carnet
  $('#tblVacunas').on('click', '.btn-carne-vac', function(){
    const id = $(this).data('id');
    // implementación simple: abrir endpoint que genere carnet. Aquí dejo como ejemplo:
    window.open('vacunas_carnet.php?id=' + encodeURIComponent(id), '_blank');
  });

  // inicializar al cargar modal (si el modal ya está en DOM)
  setFechaHoy();
  cargarSelects();

}); // fin ready
</script>

</div>
</body>
</html>
