<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>DataPlus - Healink</title>
  <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <style>
    body, html {
      height: 100%;
      margin: 0;
    }

    .bg-image {
      background-image: url('assets/img/fondo1.png'); 
      background-size: cover;
      background-position: center;
      filter: blur(0px);
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      z-index: -1;
      transition: filter 0.3s ease;
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background-color: rgba(0, 0, 0, 0.3); /* Oscurece fondo para mejor contraste */
      z-index: -1;
    }

    .login-card {
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 1rem;
      box-shadow: 0 0 20px rgba(0,0,0,0.3);
      overflow: hidden;
    }

    .login-logo {
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
    }

    .login-logo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      padding: 1rem;
    }

    @media (max-width: 768px) {
      .bg-image {
        filter: blur(4px); /* Desenfoque en móviles */
      }
      .login-logo {
        height: 200px;
      }
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center">

  <!-- Fondo y overlay -->
  <div class="bg-image"></div>
  <div class="overlay"></div>

  <!-- Contenedor del login -->
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        <div class="row login-card d-flex align-items-center justify-content-center">
          <!-- Formulario -->
          <div class="col-md-6 p-4">
 			<h4 class="d-flex align-items-center">
			  <i class="bi bi-r-circle me-2"></i>Healink-DataPlus
			</h4>
			<?php
			$error = $_GET['error'] ?? '';

			if ($error === '1') {
				echo '
				<div class="alert alert-danger d-flex align-items-center" role="alert">
					<i class="bi bi-exclamation-triangle-fill me-2"></i>
					Usuario o contraseña incorrectos
				</div>';
			}
			?>

			<h3 class="mb-4">Iniciar Sesión</h3>
			
            <form action="start.php" method="POST">
              <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="clave" class="form-label">Contraseña</label>
                <input type="password" name="clave" id="clave" class="form-control" required>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="recordar" id="recordar">
                <label class="form-check-label" for="recordar">Recordar usuario</label>
              </div>
              <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
          </div>
          <!-- Logo -->
          <div class="col-md-6 login-logo">
            <img src="assets/img/user_logo.png" alt="Healink-DataPlus"  >
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
