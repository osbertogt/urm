<?php
session_start();
require_once 'assets/dbc.php';

// Evitar acceso directo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validar campos
$usuario = trim($_POST['usuario'] ?? '');
$clave   = $_POST['clave'] ?? '';

if ($usuario === '' || $clave === '') {
    header('Location: index.php?error=1');
    exit;
}

// Buscar usuario activo
$sql = "SELECT id, nombre_usuario, nombres, apellidos, hash, id_rol
        FROM cat_usuario
        WHERE nombre_usuario = ?
          AND activo = 1
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php?error=1');
    exit;
}

$user = $result->fetch_assoc();

// Verificar contraseña
if (!password_verify($clave, $user['hash'])) {
    header('Location: index.php?error=1');
    exit;
}

// LOGIN OK → crear sesión
$_SESSION['usuario_id']     = $user['id'];
$_SESSION['usuario']        = $user['nombre_usuario'];
$_SESSION['nombre_completo']= $user['nombres'].' '.$user['apellidos'];
$_SESSION['id_rol']         = $user['id_rol'];
$_SESSION['cliente_activo'] = 0;

// Variable local para compatibilidad con tu código
$usuario = $_SESSION['usuario'];
if (empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<style>
  .modal-header {
    padding: 1rem;
    background-color: #416B95;
    color: white;
  }

  .modal-body {
    background-color: #E0FFFF;
    padding: 1rem; /* igual que el header */
  }

  .modal-content {
    border-radius: 12px;
    overflow: hidden;
  }
  .select2-container .select2-selection--single {
    height: 38px !important;
  }
  table.dataTable thead th {
	background-color: #416B95 !important;
	color: white !important;
  }
</style>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DataPlus - Healink</title>
	<?php 
	require_once 'assets/meta.php';
	require_once 'assets/references.php'; 
	require_once 'assets/dbc.php';
	$query_org = "SELECT nombre,nit,id_tipo_servicio_hosp FROM cat_organizacion LIMIT 1";
	$org = mysqli_fetch_assoc(mysqli_query($conn, $query_org));
	$_SESSION['tipo_h']=!empty($org['id_tipo_servicio_hosp'])   ? $org['id_tipo_servicio_hosp']   : '0';;

	?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
			<div class="col-md-2 p-2 min-vh-100" style="background-color: #416B95">
				<img src="assets/img/user_logo.png" class="rounded mx-auto d-block img-thumbnail mb-3" alt="Healink-DataPlus">
				<div id="menu-contenido">
					<?php include 'formularios/cat/seg/menu.php'; ?>
				</div>
				<br>
				<h5 class="d-block w-100">
				  <span class="badge rounded-pill bg-light text-dark d-block w-100 text-center text-wrap">
					<?php echo 'Usuario conectado: '.$_SESSION['usuario']; ?>
				  </span>
				</h5>

				  <button class="btn btn-danger d-block w-100 text-center" onclick="cerrarSesion()">
					<i class="bi bi-door-closed"></i> Cerrar sesión
				  </button>
			</div>
            <div class="col-md-10 p-3 d-flex" id="contenido" style="background-color: #FFFAFA">
 					<?php
						switch ($usuario) {
							case 'recepcion':
								//include 'formularios/cli/clientes_catalogo.php';
								break;
							case 'laboratorio':
								//include 'menu_lab.php';
								break;
							case 'farmacia':
								//include 'menu_farmacia.php';
								break;
						}
						?>
          </div>
        </div>
    </div>

    <script>
		$(document).ready(function () {
		  const params = new URLSearchParams(window.location.search);
		  const view = params.get("view");

		  if (view) {
			$("#contenido").load(view, function () {
			  if (view.includes("calendario.php")) {
				inicializarCalendario();
			  }
			});
		  }

		  $(".menu-link").click(function (e) {
			e.preventDefault();
			var pagina = $(this).attr("href");
			$("#contenido").load(pagina, function () {
			  if (pagina.includes("calendario.php")) {
				inicializarCalendario();
			  }
			});
		  });
		});
    </script>
	

	<script>
		function cargarModulo(moduloRuta, menuArchivo) {
		  // Mostrar spinner global y limpiar contenido
		  $('#spinner-carga').removeClass('d-none');
		  $('#contenido').html('');
		  $('#menu-contenido').html('');

		  // Simular demora para mostrar spinner (opcional)
		  setTimeout(function () {
			// Cargar módulo
			$('#contenido').load('formularios/' + moduloRuta + '.php', function () {
			  $('#spinner-carga').addClass('d-none');
			});

			// Cargar menú
			$('#menu-contenido').load(menuArchivo + '.php');
		  }, 300); // Ajusta si deseas un retardo visual
		}
	</script>
	<script>
		function inicializarCalendario() {
		  const calendarEl = document.getElementById('calendario');
		  if (!calendarEl) return;

		  const calendar = new FullCalendar.Calendar(calendarEl, {
			themeSystem: 'bootstrap5',
			initialView: 'dayGridMonth',
			locale: 'es',
			events: FORM_URL + 'cit/citas_calendario.php'
		  });
		  calendar.render();
		}
	</script>
	<script>
		function cerrarSesion() {
		  if (confirm('¿Deseas cerrar sesión?')) {
			window.location.href = 'logout.php';
		  }
		}
	</script>
</body>
</html>
